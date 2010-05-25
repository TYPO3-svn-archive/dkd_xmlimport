<?php
/*
 *  CVS Versioning: $Id$
 */

/***************************************************************
*  Copyright notice
*
*  (c) 2004-2005 Thorsten Kahler (thorsten.kahler@dkd.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * BE class providing functionality for the 'dkd_xmlimport' extension.
 *
 * @author	Thorsten Kahler <thorsten.kahler@dkd.de>
 */



require_once (PATH_t3lib.'class.t3lib_scbase.php');
require_once( t3lib_extMgm::extPath('dkd_xmlimport', 'class.tx_dkdxml_importer.php') );

$GLOBALS['LANG']->includeLLFile('EXT:dkd_xmlimport/mod1/locallang.php');


class tx_dkdxml_impexp extends t3lib_SCbase {

	/**
	 * the import "worker"
	 * @var	tx_dkdxml_importer
	 */ 
	protected $importer;

	var $prefixId = 'tx_dkdxml_impexp';				// do it like the FE
	var $pageinfo = '';
	var $extConf;				// Extension configuration
	var $vars;				// PI vars
	var $content = '';
	var $log = array ( 'ok' => array(), 'error' => array());				// the default log arrays
	var $logFiles = array ( 'ok' => 'html', 'error' => 'html');				// the default log arrays




	/**
	 * Initialization
	 *
	 * @return	integer	Error code 
	 */
	function init()	{
//		global $BE_USER,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		$this->MOD_MENU = $this->modMenu();

		parent::init();
		
		$vars = t3lib_div::_GP($this->prefixId);

		$this->extConf = unserialize ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dkd_xmlimport']);

		$this->importer = t3lib_div::makeInstance('tx_dkdxml_importer');
		$this->importer->init($this->extConf['config_file'], $vars);
 		$this->vars = $this->importer->vars;
 		$this->config = &$this->importer->config;
 		$this->conf_selected = $this->importer->conf_selected;
 		$this->selections = $this->importer->selections;
 		
 		$this->importer->log = &$this->log;
 		$this->importer->logFiles = &$this->logFiles;

			// set the permissions clause for import and export functions
		$permission_bitmask = ( 1 | 16 );		// permissions: "show" and "edit content" 
		$this->permsClause_ImpExp = $GLOBALS['BE_USER']->getPagePermsClause( $permission_bitmask );

		
		return 0;

	}
	

	
	/*
	 * Do final things
	 *
	 * @return void
	 */
	function finish() {
		
		$this->importer->finish();
		
		$this->content .= $this->importer->logHTML;

		return;
		
	}

	/**
	 * Generate settings for the modus menu (function selector)
	 *
	 * @return array
	 */
	 function modMenu() {
		return array (
			'function' => Array (
				'1' => $GLOBALS['LANG']->getLL('function1'),
				'2' => $GLOBALS['LANG']->getLL('function2'),
				'3' => $GLOBALS['LANG']->getLL('function3'),
				'4' => $GLOBALS['LANG']->getLL('function4'),
			)
		);
	}

	
	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main()	{
//		global $BE_USER,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

			// Access check!
			// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
			// additionally check whether BE user has sufficient rights for import/export
		$page = t3lib_BEfunc::getRecord( 'pages', $this->id, '*', ' AND '.$this->permsClause_ImpExp );
		$access = ( is_array( $this->pageinfo )  && is_array( $page ) );
		
		if (($this->id && $access) || ($GLOBALS['BE_USER']->user['admin'] && !$this->id))	{
		
				// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $GLOBALS['BACK_PATH'];
			$this->doc->docType = 'xhtml_trans';
			$this->doc->form='<form action="index.php" method="POST">';
		
				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';
		
			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);
		
			$this->content.=$this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content.=$this->doc->header($GLOBALS['LANG']->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);
			$this->content .= $this->hiddenField('id', $this->id);
		
		
			// Render content:
			$this->moduleContent();
		
		
			// ShortCut
			if ($GLOBALS['BE_USER']->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}
		
			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero
		
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $GLOBALS['BACK_PATH'];
		
			$this->content.=$this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content.=$this->doc->header($GLOBALS['LANG']->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			
				// short error message:
			$pageTitle = is_array($this->pageinfo) ? $this->pageinfo['title'] : '<em>unknown</em>';
			$this->content .= $this->doc->icons(2). ' ';
			$this->content .= sprintf( $GLOBALS['LANG']->getLL('message_access_denied'), $pageTitle, $this->id );

			$this->content.=$this->doc->spacer(10);
		}

	}





	/**
	 * Generates the module content
	 */
	function moduleContent()	{

		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content = $this->importXML();
				$this->content .= $this->doc->section($GLOBALS['LANG']->getLL('importXML_header'),$content,0,1);
			break;
			case 2:
				$content = $this->importPics();
				$this->content .= $this->doc->section($GLOBALS['LANG']->getLL('importPics_header'),$content,0,1);
			break;
			case 3:
				//$content = implode(', ', $this->listUploadDir());
				$this->listUploadDir = $this->importer->listUploadDir();
				$this->content .= $this->importer->deleteFileFromUploadDir();
				$this->content .= $this->listFilesFromUploadDir();
				if ( is_array ( t3lib_div::_GP ( 'VIEW' ) ) )$this->content .=$this->viewFile(2);
				if ( is_array ( t3lib_div::_GP ( 'RESTORE' ) ) )$this->content .=$this->restoreDBfromFile(2);
				//$content.='<div align=center><strong>Menu item #2...</strong></div>';
				//$this->content.=$this->doc->section('Message #2:',$content,0,1);
			break;
			case 4:
				$content='<div align="center"><strong>Menu item #3...</strong></div>';
				$this->content .= $this->doc->section('Message #3:',$content,0,1);
			break;
		}
	}
	
	

	/**
	 * Prints out the module HTML
	 */
	function printContent()	{
		
		$this->finish();

		$this->content .= $this->doc->endPage();
		echo $this->content;
	}





	function stringToOrdList ( $val ) {
		$length = strlen($val);
		$out = array ();

		for ($count=0; $count < $length; $count++) {
			$out[] = ord ( ( substr ( $val, $count, 1 ) ) );
		}

		$out = implode ( ',', $out );

		return $out;
	}

	function listFilesFromUploadDir() {

		if ( is_array ( $this->listUploadDir ) && count($this->listUploadDir) > 0 ) {
			foreach ( $this->listUploadDir AS $key => $val ) {
				switch ( ( string ) $this->MOD_SETTINGS['function'] ) {
					case 2:
						$content .= sprintf ( '
								<tr><td>%s</td>
								',
								$val
						);
						$jumpUrl =  htmlspecialchars ( sprintf ( 'index.php?id=%s&SET[function]=2&VIEW[%s]=1', t3lib_div::_GP( 'id' ), $key ) );
						$content .= sprintf ( '
							<td align="right"><img src="%sgfx/zoom.gif" alt="%s" border="0" style="cursor: hand" title="%s" onclick="jumpToUrl(\'%s\'); return false;" /></td>
							',
							$GLOBALS['BACK_PATH'],
							$GLOBALS['LANG']->getLL ( 'files_view_file' ),
							$GLOBALS['LANG']->getLL ( 'files_view_file' ),
							$jumpUrl
						);
						$jumpUrl =  htmlspecialchars ( sprintf ( 'index.php?id=%s&SET[function]=2&RESTORE[%s]=1', t3lib_div::_GP( 'id' ), $key ) );
						$content .= sprintf ( '
							<td align="right"><img src="%sgfx/upload.gif" alt="%s" border="0" style="cursor: hand" title="%s" onclick="if (confirm(String.fromCharCode(%s))) {jumpToUrl(\'%s\');} return false;" /></td>
							',
							$GLOBALS['BACK_PATH'],
							$GLOBALS['LANG']->getLL ( 'files_restore_file' ),
							$GLOBALS['LANG']->getLL ( 'files_restore_file' ),
							$this->stringToOrdList ( $GLOBALS['LANG']->getLL ( 'files_restore_warning' ) ),
							$jumpUrl
						);
						$jumpUrl =  htmlspecialchars ( sprintf ( 'index.php?id=%s&SET[function]=2&DEL[%s]=1', t3lib_div::_GP( 'id' ), $key ) );
						$content .= sprintf ( '
							<td align="right"><img src="%sgfx/garbage.gif" alt="%s" border="0" style="cursor: hand" title="%s" onclick="if (confirm(String.fromCharCode(%s))) {jumpToUrl(\'%s\');} return false;" /></td>
							',
							$GLOBALS['BACK_PATH'],
							$GLOBALS['LANG']->getLL ( 'files_delete_file' ),
							$GLOBALS['LANG']->getLL ( 'files_delete_file' ),
							$this->stringToOrdList ( $GLOBALS['LANG']->getLL ( 'files_delete_warning' ) ),
							$jumpUrl
						);

						$content .= '</tr>';
					break;

					case 1:
						$jumpUrl =  htmlspecialchars ( sprintf ( 'index.php?id=%s&SET[function]=1&RESTORE[%s]=1', t3lib_div::_GP( 'id' ), $key ) );
						$content .= sprintf ( '
							<tr><td>%s</td><td align="right"><img src="%sgfx/import.gif" alt="%s" border="0"  title="%s" onclick="jumpToUrl(\'%s\'); return false;" /></td></tr>
							',
							$val,
							$GLOBALS['BACK_PATH'],
							$GLOBALS['LANG']->getLL ( 'files_restore_file' ),
							$GLOBALS['LANG']->getLL ( 'files_restore_file' ),
							$jumpUrl
						);
					break;

					case 3:
						$jumpUrl =  htmlspecialchars ( sprintf ( 'index.php?id=%s&SET[function]=3&EDIT[%s]=1', t3lib_div::_GP( 'id' ), $key ) );
						$content .= sprintf ( '
							<tr><td>%s</td><td align="right"><img src="%sgfx/edit2.gif" alt="%s" border="0" title="%s" onclick="jumpToUrl(\'%s\'); return false;" /></td></tr>
							',
							$val,
							$GLOBALS['BACK_PATH'],
							$GLOBALS['LANG']->getLL ( 'files_edit_file' ),
							$GLOBALS['LANG']->getLL ( 'files_edit_file' ),
							$jumpUrl
						);
					break;
				}
			}
			$content = sprintf ( '
				<table id="dkdcsvimport-list">
					<tbody>
						%s
					</tbody>
				</table>
				',
				$content
			);
		}else{
			$content = sprintf ( '
				<div>
					%s
				</div>
				',
				$GLOBALS['LANG']->getLL ( 'message_no_files_in_folder' )
			);
		}
		return $this->doc->section ( $GLOBALS['LANG']->getLL ( 'message_list_files' ), $content, 0, 1);
	}



	function restoreDBfromFile ( $mode ) {

		if ( is_array ( t3lib_div::_GP ( 'RESTORE' ) ) && count ( t3lib_div::_GP ( 'RESTORE' ) ) == 1 ) {
			$files = $this->importer->listUploadDir();
			foreach(t3lib_div::_GP ( 'RESTORE' ) AS $key => $val){
				$file = $this->config['xml_dir'] . '/'.$files[$key];

				if(is_file($file)){
					$content .= "<strong>Restore file: $file</strong>";
					$assoc = $this->importer->fetchData($file);

					if($this->importer->checkStruktur($assoc)){
						$table = $this->importer->assocArray2Table($assoc);
						$this->importer->write2DB($table);
					}
					else $content = "Datenstruktur in XML-File entspricht der Struktur der Tabelle";


				}
			}

		}else{
			$content = sprintf ( '
				<div>
					%s
				</div>
				',
				$GLOBALS['LANG']->getLL ( 'message_no_file_selected' )
			);

			return $this->doc->section ( $GLOBALS['LANG']->getLL ( 'message' . $mode ), $content, 0, 1);
		}
	}

	function viewFile ( $mode ) {

		if ( is_array ( t3lib_div::_GP ( 'VIEW' ) ) ){
			$files = $this->importer->listUploadDir();
			foreach ( t3lib_div::_GP ( 'VIEW' ) AS $key => $val ) {
				$file = $this->config['xml_dir'] . '/'.$files[$key];

				if(is_file($file)){
					$content .= "<strong>View file: $file</strong>";		// .phpversion();
					$assoc = $this->importer->fetchData($file);

					$table = $this->importer->assocArray2Table($assoc);
					$content .= $this->showXMLTable($table);

				} else {
					$content .= "not a file";
				}
			}
		}
		return $this->doc->section ( $GLOBALS['LANG']->getLL ( 'message' . $mode ), $content, 0, 1);
	}


	function showXMLTable($table) {
		$out .= '<table>';
		$out .= '<tr>';
		foreach ($table[0] as $key => $value) {
			$out .= '<td>'.$key.'</td>';
		}
		$out .= '</tr>';
		$k=1;
		foreach ($table as $row) {
			$out .= '<tr>';
			if (is_array($row)) {
				foreach ($row as $col) {
					$out .= '<td '.(fmod($k,2)? 'bgcolor="#D9D5C9"': 'bgcolor="#EDE9DD"').'>'.$col.'</td>';
				}
			}
			$k++;
			$out .= '</tr>';
		}
		$out .= '</table>';
		return $out;
	}






	function importXML(){
		
		$select = chr(10).'<select name="'. $this->prefixId. '[config]">';
		foreach($this->selections as $sel){
			$found = ($this->conf_selected == $sel) ? ' selected="selected"' : '';
			$select .= sprintf('<option value="%s"%s>%s</option>', $sel, $found, $sel);
		}
		$select .= '</select>'.chr(10);
		$content .= sprintf('<h4>%s</h4><p>%s</p>', $GLOBALS['LANG']->getLL('importXML_conf_header'), $GLOBALS['LANG']->getLL('importXML_conf_hint'));
		$content .= '
			<p>
				<form action="index.php" method="POST">'.
					$select.
					$this->hiddenField('id', $this->id).
					'<input type="submit" value="AUSWÄHLEN">
				</form>
			</p>';


		if( $this->conf_selected ) {

			$this->config['pid'] = intval( $this->vars['page_id'] );
			$content.="<p>Aktuelle Einstellungen für den Import:</p>";
			foreach($this->config as $key => $value){
				$value = ( t3lib_div::inList( 'http_user,http_password', $key ) ) ? '******' : $value;
				$content.='<p>'.$key.' => '.$value.' </p>';
			}	
			$content .= sprintf (
				'<form action="index.php" method="POST">
					<p><br>Importieren in Seite:<br>
					<input type="text" maxlength="5" size="5" name="%s[page_id]" value="%s"></p>
					<br>
					<input type="submit" value="IMPORT">
				' ,
				$this->prefixId, $this->config['pid']);
			$content .= $this->hiddenField('id', $this->id);
			$content .= $this->hiddenField('import', '1', 1);
			$content .= $this->hiddenField('config', $this->conf_selected, 1);
			$content .= '</form>';

			if( $this->vars['import'] ) {
				
				if ( $this->extConf['backup'] && ! $this->importer->backupRecords( $this->config['table'], $this->config['pid'] ) ) {

					$this->importer->log('error', 'Could not backup records, so insert was skipped');

				} else {

					$proto = isset($this->config['http_protocol']) ? $this->config['http_protocol'] : 'http://';
					$auth = ( $this->config['http_user'] && $this->config['http_password'] ) ? sprintf('%s:%s@', $this->config['http_user'], $this->config['http_password'] ) : '';
					$url = $proto . $auth . $this->config['url'];

					if (! $assoc = $this->importer->fetchData($url) ) {
						$this->importer->log('error', 'The URL was not reachable: '.$url);
					} else {

						$table = $this->importer->assocArray2Table($assoc);
						
						if (is_array($this->config['preProcessing'])) {
							$this->importer->doProcessing( $this->config['preProcessing']['function'], $this->config['preProcessing']['params'] );
						}
	
						$log = $this->importer->insertRecords($table);
						if (is_array($this->config['postProcessing'])) {
							$this->importer->doProcessing( $this->config['postProcessing']['function'], $this->config['postProcessing']['params'] );
						}
						
						
						$this->logFiles['error'] = $this->extConf['log_file'] ? $this->extConf['log_file'] : 'html';
						
						if ( is_array($log) && ( $log['rows'] != $log['success'] + $log['failed'] ) )  {
							$msg = sprintf( 'Something fishy happened: There were %d records to insert, but %d were successful and %d failed', $log['rows'], $log['success'], $log['failed'] );
							$this->importer->log( 'error', $msg );
							$content .= sprintf( '<p class="bgColor">%s</p>', $msg );
						} elseif ( $log['failed'] ) {
							$msg = sprintf( 'An error occured during insertion of %d datasets: %d were successful but %d failed', $log['rows'], $log['success'], $log['failed'] );
							$this->importer->log( 'error' , $msg );
							$content .= sprintf( '<p class="bgColor">%s</p>', $msg );
						} else {
							$msg = sprintf( '%d rows were inserted successful', $log['rows'] );
							$this->importer->log( 'ok', $msg );
							$content .= sprintf( '<h3>%s</h3>', $msg );
							$content .= $this->backLink( $GLOBALS['LANG']->getLL('importXML_backLink', 'back'), '<h4>|</h4>', TRUE );
						}
//						$this->logFiles['ok'] = $this->extConf['log_file'] ? 'html,'.$this->extConf['log_file'] : 'html';
					}
				}
			}

		} 
			
		return $content;
	}
	





	function importPics(){
		
		$content = '';
		
		$select = "\n".'<select name="'. $this->prefixId. '[config]">';
		foreach($this->selections as $sel){
			$found = ($this->conf_selected == $sel) ? ' selected="selected"' : '';
			$select .= sprintf('<option value="%s"%s>%s</option>', $sel, $found, $sel);
		}
		$select .= '</select>'."\n";
		$content .= sprintf('<h4>%s</h4><p>%s</p>', $GLOBALS['LANG']->getLL('importPics_conf_header'), $GLOBALS['LANG']->getLL('importPics_conf_hint'));
		$content .= '<p>'.$select.'<input type="submit" value="'. $GLOBALS['LANG']->getLL('form_select') .'"></p>';


		if ($this->conf_selected) {

			$current = ($this->conf_selected == $this->vars['config_sel']);
	
			if ( ! ( is_array($this->config['pictures']) && count($this->config['pictures']) ) ) {
				$content .= '<p><br>' . $GLOBALS['LANG']->getLL('importPics_noPics') . '</p>';
			} else {

				$content .= sprintf( '<h3>%s:</h3>', $GLOBALS['LANG']->getLL('importPics_currentSettings') );
				$content .= sprintf( $GLOBALS['LANG']->getLL('importPics_url'), $this->config['pictures_url'] );
				
					// select fields
				$pics_selected = '';
				$pics_selected_flag = false;
				foreach($this->config['pictures'] as $field => $pics){
					$content .= sprintf('<p>DB-%s: %s<br>', $GLOBALS['LANG']->getLL('create_table_field'), $field );

						// select picture variant
					foreach($pics as $i =>$pic) {
						$checked = ($this->vars['pics'][$field][$i]) ? ' checked' : '';
						$label = sprintf($pic, $GLOBALS['LANG']->getLL('importPics_placeholder_fieldVal') );
						$content .= sprintf('<input type="checkbox" name="%s[pics][%s][%s]" value="1"%s> %s<br>', $this->prefixId, $field, $i, $checked, $label);
						if ($current) {
							if ( $this->vars['pics'][$field][$i] ) {
								$pics_selected .= $this->hiddenField( sprintf('pics][%s][%s', $field, $i), '1', 1);
								$pics_selected_flag = true;
							} else {
								$pics_selected .= $this->hiddenField( sprintf('pics][%s][%s', $field, $i), '0', 1);
							}
						}
					}
					
					$content .= '</p>';
					
				}

					// select page
				$pages = '';
				$content .= sprintf ('<h4>%s</h4>', $GLOBALS['LANG']->getLL('importPics_pids_head') );
				$content .= '<p><select name="'. $this->prefixId. '[pid]">';
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('distinct pid, pid label', $this->config['table'], '1');
				$row = array ('pid' => 'all', 'label' => $GLOBALS['LANG']->getLL('importPics_pids_all') );
				do {
					$sel = ( $current && ( $row['pid'] == $this->vars['pid'] ) ) ? ' selected' : '';
					$content .= sprintf( '<option%s>%s</option>', $sel, $row['pid'] );
					$pages .= ($sel==' selected') ? $this->hiddenField( 'pid', $row['pid'], 1) : '';
				 } while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) );
				 $content .= '</select></p>';

					// set text for submit button
				$confirm_button = $GLOBALS['LANG']->getLL('form_select');
				
				if ( $current && $pics_selected_flag && strlen($pages) ) {

						// switch to import mode
					$confirm_button = $GLOBALS['LANG']->getLL( $this->vars['import'] ? 'form_import' : 'form_confirm' );
					$content .= $this->hiddenField( 'import', '1', 1);

						// write hidden inputs to freeze selections
					$content .= $pics_selected;
					$content .= $pages;

						// set pointer for import step
					$this->vars['pointer'] = isset($this->vars['pointer']) ? $this->vars['pointer'] : 0;
					
						// set up log for file activities
					$this->log['file'] = array();
					$this->logFiles['file'] = $this->extConf['log_file'];

						// restrain runtime of costly code
					$time_start = $this->getmicrotime();
					$time_end = $time_start + intval( ini_get('max_execution_time') ) * $this->importer->runTimeFactor;

					$fields = array();				// which db fields should be read
					foreach( $this->vars['pics'] as $field => $variants ) {
							// is a variant selected?
						foreach ($variants as $index => $checked ) {
							if  ($checked) {
								$fields[$field][] = $this->config['pictures'][$field][$index];
								$variant_count++;
							}
						}
					}

					$select_fields = implode( ', ', array_keys($fields) );
					$from_table = $this->config['table'];
					$where_clause = ($this->vars['pid'] == 'all') ? '' : 'pid='.$this->vars['pid'];
					$where_clause .=  t3lib_BEfunc::deleteClause( $this->config['table'] );
					$orderBy = 'uid';
					$limit = sprintf('%s,%s', $this->vars['pointer'], 99999 );
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $from_table, $where_clause, '', $orderBy, $limit);
					
					$proto = isset($this->config['http_protocol']) ? $this->config['http_protocol'] : 'http://';
					$auth = ( $this->config['http_user'] && $this->config['http_password'] ) ? sprintf('%s:%s@', $this->config['http_user'], $this->config['http_password']) : '';
					$url .= $proto . $auth . $this->config['picture_url'];
					$url = ( substr($url, -1) == '/' ) ? $url : $url.'/';

						// alle nötigen Infos sind eingesmammelt
					if ($this->vars['import']) {

						$timeout = false;				// Flag: weitere Import-Schritte nötig?
						$pic_count = 0;				// Counter: wieviele Bilder wurden kopiert
						while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
							foreach ($row as $field => $pic) {
								if ($pic != '') {
									foreach ($fields[$field] as $template) {
										$file = $url . sprintf($template, $pic);
										$err = $this->importer->copy($file, $this->config['picture_dir']);
										if (! $err) {
											$pic_count++;
										} else {
											$this->logFile['error'] = 'html';
											switch ((string)$err) {
												case '3' :
													break;
												case '1' :
												case '2' :
												case '4' :
												default  :
													break 4;
											}
										}
									}
								}
							}
							$this->vars['pointer']++;
								// Laufzeit checken
							if ($this->getmicrotime() >= $time_end) {
								$timeout = true;
								break;
							}
						}
					}
					$content .= $this->hiddenField('pointer', $this->vars['pointer'], 1);
					$content .= $this->hiddenField('pic_count', $this->vars['pic_count'] + $pic_count, 1);
				}
				if ( (! $this->vars['pointer']) || $timeout ) {
						// processing active
					if (isset($timeout)) {
						$this->importer->log('file', sprintf('** imported %s pictures up to record %s', $this->vars['pic_count'], $this->vars['pointer']) );
						$reset_button = '';
					} else {
						$reset_button = sprintf( '<input type="reset" value="%s">', $GLOBALS['LANG']->getLL('form_reset') );
					}
					$msg = sprintf( $GLOBALS['LANG']->getLL('importPics_pic_count'), $pic_count);
					$this->importer->log('file', $msg);
					$content .= sprintf('<p>%s</p>', $msg);
					$content .= sprintf( '<br><p>%s %s</p>', $GLOBALS['LANG']->getLL('importPics_step'),  $this->vars['pointer'] );
					$content .= $this->hiddenField( 'config_sel', $this->conf_selected, 1);
					$content .= sprintf('<p><input type="submit" value="%s"> %s</p>', $confirm_button, $reset_button );
				} else {
						// processing finished
					$msg = sprintf( $GLOBALS['LANG']->getLL('importPics_completed_msg'), $this->vars['pointer'], $this->vars['pic_count'] + $pic_count );
					$this->importer->log('ok', $msg);
					$this->importer->log('file', $msg);
					$content .= '<h3>' . $msg . '</h3>';
					$content .= $this->backLink( $GLOBALS['LANG']->getLL('importPics_backLink', 'back'), '<h4>|</h4>', TRUE );
				}

			}

		} 
			
		return $content;
	}



	/**
	 * generate HTML tag <input type="hidden" ...>
	 *
	 * @param	string	$name	attribute "name"
	 * @param	string	$value	attribute "value"
	 * @param	string	$prefix	optional prefix, if value in an array should be set
	 * @return	string
	 *
	*/
	function hiddenField($name, $value, $prefix='') {
		$name = $prefix ? sprintf('%s[%s]', $this->prefixId, $name) : $name;
		$closingSlash = ' /';
		return sprintf('<input type="hidden" name="%s" value="%s"%s>', $name, $value, $closingSlash);
	}



	/**
	 *	generate link to "clean" function
	 *
	*/
	function backLink($linkText, $wrap, $focus=FALSE, $accessKey='c') {
			// display link to return to "clean" form
		$currentFunction = t3lib_div::GPvar('SET');
		$currentFunction = intval( $currentFunction['function'] );
		$wrap = t3lib_div::trimExplode( '|', $wrap);
		
		if ( $accessKey != '' ) {
			$accessKey = $accessKey{0};
			$accessKeyCode = sprintf( 'accesskey="%s"', $accessKey );

			if ( $focus) {
				$linkText = eregi_replace(
					sprintf ( '([^%1$s]*)%1$s(.*)', $accessKey ),
					sprintf ( '\1<em class="accesskey">%s</em>\2', $accessKey ),
					$linkText
				);
			}
		} else {
			$accessKeyCode = '';
		}

		$link = htmlspecialchars( 'index.php?SET[function]='. $currentFunction .'&id='. $this->id );
		$content = sprintf( '<a href="%s" %s target="_self">%s</a>', $link, $accessKeyCode, $linkText ) ;
		$content = $wrap[0] . $content . $wrap[1];

		return $content;
	}



	/**
	 * Unix timestamp with microseconds
	 *
	 * @return	float	seconds since 1970-1-1
	 */
	function getmicrotime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}



	function process_setTopProduct() {
		
		$tstamps = array();
		$week = 0;
		$now = getdate();
//		$day = $now['mday'] - $now['wday'] + 1;
		$day = $now['mday'];
		
			// uids of products in pid/table
		$where_clause = ( ( $this->config['scope'] == 'pid' ) && ( $this->config['pid'] ) ) ? 'pid='.$this->config['pid'] : '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, seitenr', $this->config['table'],$where_clause);
		$seed = array_shift( explode( ' ', microtime() ) );
		srand($seed);
		while ( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res) ) {
			$records[] = array_merge( $row, array( 'order' => rand() ) );

				// add one timestamp for every record
			$tstamps[] = mktime( 0, 0, 0, $now['mon'], ($week*7 + $day), $now['year'] );
			$week++;
		}
		
			// sort records by rand
		usort ( $records, create_function('$a,$b', 'return strcmp($a[\'order\'], $b[\'order\']);') );
		
		foreach ($records as $r) {
			$temp_records[$r['seitenr']][] = $r;
		}
		$records = array();
		$records = array_merge($temp_records[2], $temp_records[1]);
		
			// update records
		$week = 0;
		foreach ($records as $record) {
			$where = 'uid='.$record['uid'];
			$fields_values = array('tstamp' => $tstamps[$week]) ;
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery($this->config['table'], $where, $fields_values);
			$query = implode(' ', t3lib_div::trimExplode("\n", $query) );
			mysql_query($query, $GLOBALS['TYPO3_DB']->link) OR
				$this->importer->log('error', 'Query failed: '.$query);
			$this->importer->log('sql', $query);
			$week++;
		}
		$this->importer->log('ok', sprintf('Setting a timestamp was successful for %s randomly ordered records from table %s.', $week, $this->config['table'] ) );
		return;
	}
	
	
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dkd_xmlimport/mod1/class.tx_dkdxml_impexp.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dkd_xmlimport/mod1/class.tx_dkdxml_impexp.php"]);
}


?>