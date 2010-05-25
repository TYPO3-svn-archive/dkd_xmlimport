<?php
/*
 *  CVS Versioning: $Id$
 */

/***************************************************************
*  Copyright notice
*
*  (c) 2004 Maryna Sigayeva (maryna.sigayeva@dkd.de)
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
 * BE class providing functionality for the 'dkd_xmlimport' aka 'dkd_vivesco_xmlimport' extension.
 *
 * @author	Thorsten Kahler <thorsten.kahler@dkd.de>
 */

require_once( t3lib_extMgm::extPath('dkd_xmlimport', 'class.tx_dkdxml_procs.php') );

class tx_dkdxml_importer {
	var $prefixId = 'tx_dkdxml_importer';				// do it like the FE

	var $config = array();				// the selected configuration array
	var $conf_selected = '';			// the configuration which was selected
	var $vars;				// configuration of the current functionality
	var $selections = array();
	var $extConf;				// Extension configuration

	var $listUploadDir = '';
	var $runTimeFactor = 0.8;			// used to control time-consuming parts
	var $backRef;			// back reference to importer object, used for pre/post processing

	var $log = array ( 'ok' => array(), 'error' => array());				// the default log arrays
	var $logFiles = array ( 'ok' => 'SDOUT', 'error' => 'STDERR');				// the default log arrays
	var $logHTML = '';				// string for HTML output of log data



	/*************************
	 *
	 * INITIALIZATION AND FINALIZATION
	 *
	 *************************/

	/**
	 * Initialization for Command Line Interface
	 *
	 * @param	string	$configFile	filename of configuration file
	 * @param	mixed	$vars	current function configuration, ~ piVars; can be array with vars or filename
	 * @param	string	$part	optional part of the vars-Array
	 * @return	void
	 */
	function init($configFile, $vars, $part='')	{

		$this->extConf = unserialize ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dkd_xmlimport']);

		if (is_string($vars) ) {
			$this->vars = $this->getVars($vars, $part);
		} else {
			$this->vars = $vars;
		}
/*
		if (! (is_array($vars) && count($vars) ) ) {
			$this->log( 'error', '"$this->vars" is not an array!');
			return 1;
		} else {
			$this->vars = $vars;
		}
*/


		$this->config = $this->getConfig($configFile, $this->vars['config']);

		if (! (is_array($this->config) && count($this->config) ) ) {
			$this->log( 'error', '"$this->config" is not an array!');
			return 2;
		}

		$this->backRef = $this;

		return 0;

	}


	/*
	 * Do final things
	 *
	 * @return void
	 */
	function finish() {

		$this->writeLog();

		return;

	}




	/**
	 * Reads the configuration from the file selected in extension configuration (ExtMgr)
	 *
	 * @param	string	$filename	filename points to the config file
	 * @param	string	$sel	sel is set, if one of the settings is already selected
	 * @return	array	selected settings from config file
	 */
	function getConfig($filename, $sel='') {

		$config = array();

		$filename = t3lib_div::getFileAbsFileName($filename);

		if ( is_file($filename) ) {
			include ($filename);
			if (is_array($conf) && count($conf)) {
				$this->selections = array_keys($conf);

				if ( $sel && array_key_exists($sel, $conf) ) {
					$this->conf_selected = $sel;
					$config  = $conf[$sel];
				}
			}
		} else {
			$this->log( 'error', 'Config File not Found!');
		}


		return $config;
	}




	/**
	 * Reads the "piVars" from a file
	 * This method is required for CLI.
	 * By passing an optional second parameter, you can select one field
	 * (sub-array) of the whole configuration array.
	 *
	 * @param	string	$filename	filename points to the "vars" file
	 * @param	string	$part	part of the array to be selected
	 * @return	array	set of piVars from the "vars" file
	 */
	function getVars($filename, $part='') {

			// the vars file contains data structured like TS, so we use the TS parser
		require_once(PATH_t3lib.'class.t3lib_tsparser.php');
		$parser = t3lib_div::makeInstance('t3lib_tsparser');

		$filename = ($filename{0} == '/') ? $filename : PATH_site.$filename;
		$vars = '';


		if ( is_file($filename) ) {

				// read and parse file
			$varString = t3lib_div::getUrl($filename);
			$parser->parse($varString);
			$vars = $parser->setup;

				// search for subparts
			if ($part != '') {
				$partsArray = t3lib_div::trimExplode( '.', $part );
				$p = array_shift($partsArray).'.';
				while ( array_key_exists ( $p, $vars ) ) {
					$vars = $vars[$p];
					$p = array_shift($partsArray).'.';
				}
			}

		} else {
			$this->log( 'error', 'Config File not Found!');
			return;
		}

		return $vars;
	}




	/**
	 * Stores data from DB in XML file
	 *
	 * @param	string		$table	which table to store
	 * @param	string		$pid	the page to fetch the data sets from
	 * @return	bool		true=storing succeeded; false=storing failed
	 */
	function backupRecords($table, $pid=0){

			// use TYPO3 core class
		require_once (PATH_t3lib."class.t3lib_xml.php");
		$exporterClass = t3lib_div::makeInstanceClassName('t3lib_xml');
		$XMLExporter = new $exporterClass($this->prefixId.'_backup');


			// set fields to export
		$fieldArray = $GLOBALS['TYPO3_DB']->admin_get_fields($table);
		$fields = implode( ',', array_keys($fieldArray) );


			// do the DB query
		$queryParts = array (
			'SELECT' => $fields,
			'FROM' => $table,
			'WHERE' => $pid ? sprintf( 'pid=%u', $pid ) : ''
		);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);

		if ( $GLOBALS['TYPO3_DB']->sql_error() ) {
			$this->log( 'error', 'Retrieval of current data failed while backing up.');
			return FALSE;
		}


			// generate XML from data sets
		$XMLExporter->setRecFields($table, $fields);
		$XMLExporter->renderHeader();
		$XMLExporter->renderRecords($table, $res);
		$XMLExporter->renderFooter();
		$xml = $XMLExporter->getResult();

		if (! strlen($xml) ) {
			$this->log( 'error', 'XML Parsing failed while backing up current data.');
			return FALSE;
		}


			// save XML to file

			// construct backup path
		$dir = $this->extConf['backup_path'];
		$dir .= substr( $dir, -1 ) == '/' ? $this->config['xml_dir'] : '/'.$this->config['xml_dir'];		// append subdir for specific table
			// convert DOS path and reduce double slashes if needed
		$dir = t3lib_div::fixWindowsFilePath( $dir );
		$dir = t3lib_div::isAbsPath($dir) ? $dir : PATH_site.$dir;

		if(!file_exists($dir)) {
			if (! t3lib_div::mkdir($dir) ) {
				$this->log( 'error', "Failed creating backup directory while backing up current data to directory $dir.");
				return FALSE;
			}
		}

		if(file_exists($dir)){
			$file = sprintf( '%s/%s_%s.xml', $dir, $this->config['table'], date('Y-m-d_H-i-s') );

			if ( ! $file_handle = fopen( $file, 'w' ) ) {
				$this->log( 'error', "Cannot open file $file.");
				return FALSE;
			}
			if ( ! fwrite( $file_handle, $xml ) ) {
				fclose($file_handle);
				$this->log( 'error', "Cannot write to file $file.");
				return FALSE;
			}
			fclose($file_handle);
			$this->log( 'ok', "Backup written to file $file.");
			return TRUE;

		} else {
			return FALSE;
		}

		return FALSE;
	}




	/**
	 * insert records from php array into DB
	 *
	 * @param	array	$table	an array of records
	 * @return	array	array with numer of records / inserted records / failed records
	 */
	function insertRecords($table){

		$this->log['sql'] = array();
		$this->logFiles['sql'] = $this->extConf['log_file'];

			// generate list of fields
		$query = 'SHOW COLUMNS FROM '. $this->config['table'];
		$res = mysql_query($query);
		while ( $row = mysql_fetch_assoc($res)) {
			$fieldsInDB[] = $row['Field'];
		}

		$delete = sprintf('DELETE FROM %s WHERE pid=%s', $this->config['table'], $this->config['pid']);
		if ( (! isset( $this->config['clear_table'] ) ) || ( isset( $this->config['clear_table'] ) && $this->config['clear_table'] ) ) {
			if ( ! mysql_query($delete) ) {
				$this->log( 'error', sprintf('Fehler: %s erzeugt %s', $delete, mysql_error()) );
				return;
			} else {
				$this->log( 'ok', $delete );
			}
		} else {

			$counter = array('rows' => count($table), 'success' => 0, 'failed' => 0);
			foreach ($table as $row) {
				$fields = array();
				$row['pid'] = $this->config['pid'];
				$insert = 'INSERT INTO '.$this->config['table'].' SET ';
				foreach($fieldsInDB as $field){
						// check mapping
					if ( is_array( $this->config['mapping'][$field]) ) {
						$mapping = $this->config['mapping'][$field];
						$val = ( isset($mapping['xml_fieldname']) ) ? $row[$mapping['xml_fieldname']] : $row[$field];
						if ( $mapping['cast'] && t3lib_div::inList( 'boolean,bool,integer,int,float,double,string', $mapping['cast'] ) )
							settype( $val, $mapping['cast'] );
						if ($mapping['function']) {
							$params = $mapping['function'][1];
							if( in_array( 'THIS', $params) ) {
								foreach ($params as $k => $v) {
									if ($v == 'THIS') {
										$params[$k] = $val;
									}
								}
							}
							$params = ( is_array($params) && count($params) ) ? $params : NULL;
							$val = call_user_func ($mapping['function'][0], $params);
						}
						unset($mapping);

					} else {
						$val = $row[$field];
					}

					$fields[] = sprintf( '%s = \'%s\'', $field, $GLOBALS['TYPO3_DB']->quoteStr( $val, $this->config['table'] ) );
					unset ($val);
				}
				$insert .= implode( ', ', $fields);
				if ( mysql_query($insert) ) {
					$this->log( 'sql', $insert );
					$counter['success']++;
				} else {
					$this->log( 'sql', sprintf('Fehler:<br>%s erzeugt <em>%s</em>', $insert, mysql_error()) );
					$counter['failed']++;
				}
			}
		}

       	return $counter;
	}



	/**
	* from rothenberger
	*/
	function listUploadDir() {
		return t3lib_div::getFilesInDir ( $this->config['xml_dir'],'',0,'mtime' );
	}



	function deleteFileFromUploadDir () {

//debug(t3lib_div::_GP ( 'DEL' ));
		if ( is_array ( t3lib_div::_GP ( 'DEL' ) ) ) {
			$files = $this->listUploadDir();
//debug($files);
			foreach ( t3lib_div::_GP ( 'DEL' ) AS $key => $val ) {

//debug($this->config['xml_dir'] . $files[$key] );
				unlink ( $this->config['xml_dir'] . '/'.$files[$key] );
			}
			$this->listUploadDir = $this->listUploadDir();
		}
	}



	function checkStruktur($assoc){
		$out = array();
		foreach ($assoc['root'][0][0] as $col) {
			if($col['tag']!="R") $out[] = strtolower($col['tag']);
		}
		$keys = array();
		$query = "SHOW COLUMNS FROM ".$this->config['table'];
		$res = mysql_query($query) or die("Query failed : " . mysql_error());
		while($row = mysql_fetch_assoc($res)){
			$keys[] =$row['Field'];
		}
		if($keys==$out) return true;
		else return false;
	}



	function write2DB($table){
		$delete = "DELETE FROM ".$this->config['table']." WHERE 1";
		mysql_query($delete);
		foreach ($table as $row) {
			if (is_array($row)) {
				$query = "insert into ".$this->config['table'];
				$query .= " ( ".implode(", ", array_keys($row)).") values";
				$query .= " ( '".implode("', '", array_values($row))."')";
				mysql_query($query) or die("Query failed : " . mysql_error());
				//print $query."<br>";
			}
		}
	}



	function assocArray2Table($assoc){
		$table = array();
		foreach ($assoc['root'][0] as $row) {
			if (is_array($row)) {
				$record = array();
				foreach ($row as $col) {
					if(is_array($col)) {
//						$record[strtolower($col['tag'])]= $col['value'];
						$record[$col['tag']]= $col['value'];
					}
				}
				$table[]=$record;
			}
		}
		return $table;
	}


	function fetchData($url) {

		$vals = array();		// array generated by xml parser

		$data = t3lib_div::getURL($url);
		if( $data == '' ) {
			$this->log( 'error', 'Could not fetch data from URL '. $url );
			return false;
		}

		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, TRUE);
		xml_parse_into_struct( $parser, $data, $vals );

		$parserError = xml_get_error_code($parser);
		xml_parser_free($parser);

		if( ! $vals ) {
			$this->log( 'error', 'Error! XML-Parser returned: ' . xml_error_string( $parserError ) );
			return false;
		}

		$pointer = 0;	// start converting into assoc array at first item
		$assoc = $this->xml_parse_into_assoc($vals, $pointer);

		return $assoc;
	}




	/**
	 * Generates nested array from list of XML parser events
	 * Calls itself recursively
	 * 
	 * @param	array	$vals list of XML parser events
	 * @param	integer	$i points to currently interpreted events
	 * @see
	 */
	function xml_parse_into_assoc($vals, &$i) {
		$ret = array();
		while ($i++ < sizeof($vals)) {
			$tag = $vals[$i];
			if ($i==0) print_r($tag);
			switch($tag['type']) {
				case "cdata":
					$ret['value'] .= $tag['value'];
				break;
				case "complete":
					unset($tag['type']);
					unset($tag['level']);
					$tag['value'] = trim($tag['value']);
					if ($tag['value'] == "") unset($tag['value']);
					$attr = $tag['attributes'];
					unset($tag['attributes']);
					if ( is_array( $attr ) ) {
						$ret[] = array_merge($tag, $attr);
					}else{
						$ret[] = $tag;
					}
				break;
				case "open":
					unset($tag['type']);
					unset($tag['level']);
					$tag += $this->xml_parse_into_assoc($vals, $i);
					$tag['value'] = trim($tag['value']);
					if ($tag['value'] == "") unset($tag['value']);
					$attr = $tag['attributes'];
					if (!$attr) $attr = array();
					unset($tag['attributes']);
					if ( is_array( $attr ) ) {
						$ret[] = array_merge($tag, $attr);
					}else{
						$ret[] = $tag;
					}
				break;
				case "close":
					if ($i == sizeof($vals)-1) {
						return  array($tag['tag'] => $ret);
					} else {
						return $ret;
					}
				break;
			}
		}
	}





	/**
	 * fetch file from URL and save it to local directory
	 *
	 * @param	string	$from	the URL of the file
	 * @param	string	$to	the local directory
	 * @return	int	error-codes: 0=ok; 1=could not create dir; 2=could not write to dir; 3=could not read from URL; 4=could not write to file;
	 */
	function copy($from, $to) {
			// change path type from relative to absolute if necessary
		$dir = ($to{0} == '/') ? $to : PATH_site.$to;

			// check R/W rights
		if ( ! is_dir($dir) ) {
			if (! t3lib_div::mkdir($dir) ) {
				$this->log('error', sprintf('Could not create directory %s! Please check file permissions.', $to) );
				return 1;
			}
		}
		if ( ! is_writable($dir) ) {
			$this->log('error', sprintf('Directory %s was not writable! Please check file permissions.', $to) );
			return 2;
		}

			// fetch file
		if ( ! $content = @t3lib_div::getURL($from) ) {
			$this->log('error', sprintf('Could not read %s!', $from) );
			return 3;
		}

			// write file
		$filename = array_pop( t3lib_div::trimExplode( '/', $from) );
		$dir .= (substr($dir, -1) == '/') ? '' : '/';
		if (! t3lib_div::writeFile($dir.$filename, $content) ) {
			$this->log( 'error', sprintf('Could not write file "%s" to directory %s', $filename, $to) );
			return 4;
		}

			// write log
		$this->log('file', sprintf('File %s mirrored to dir %s', $from, $dir.$filename) );
		return 0;
	}



	/*
	 * Do the pre- and post-processing
	 *
	 * @param	mixed	$func	callback function
	 * @param	array	$params	optional parameters
	 * @return	mixed	return value of the function being called
	 */
	 function doProcessing($func='', $params='') {
		if ( is_callable($func) ) {
			if ( is_array($params) ) {
					// parse the params array for the special marker __this:
				$paramCount = count($params);
				$marker = '__this:';
				$markerLength = strlen($marker);
				for ($i=0; $i<$paramCount; $i++) {
					if ( substr( $params[$i], 0, $markerLength ) == $marker ) {
						$p = substr( $params[$i], $markerLength );
						if ( $p{0} == '&') {
							$p = substr( $p, 1);
							$params[$i] = $this->$p;
						} else {
							$params[$i] = $this->$p;
						}
					}
				}
				$ret = call_user_func_array( $func, $params);

			} else {
				$ret = call_user_func_array( $func );
			}
		}
		return $ret;
	}



	/*************************
	 *
	 * LOGGING FUNCTIONS
	 *
	 *************************/


	/*
	 * Write messages to log-array
	 *
	 * @param	string	$type	the part of the log-array (default: ok, error)
	 * @param	string	$message	the message to be logged
	 * @return	bool	success of logging
	 */

	function log($type, $message) {
		$typelist = implode(',', array_keys ($this->log));
		if (t3lib_div::inList ($typelist, $type) ) {
			if ( is_array( $message) && count($message) ) {
				$this->log[$type] = array_merge ($this->log[$type], $message);
			} else {
				$this->log[$type][] = $message;
			}
			return true;
		} else {
			return false;
		}
	}



	/*
	 * Write log-array to distinction
	 *
	 *
	 * @return	void
	 */
	function writeLog() {

			// write log messages to corresponding "file"
		foreach ($this->log as $log => $messages) {
			if ( is_array($messages) && count($messages) ) {
				$logfiles = t3lib_div::trimExplode( ',', $this->logFiles[$log] );
				foreach ($logfiles as $logfile) {

					switch ( strtolower($logfile ) )  {
						case 'null':
								// throw away the log
							break;
						case 'html':
								// write HTML
								$this->logHTML .= '<hr><h4>The "' . $log . '"-Log:</h4>';
								foreach ($messages as $m) {
									$this->logHTML .= sprintf( '<p style="padding-bottom: 0.3em;">%s</p>', $m);
								}
							break;
						case 'stdout':
						case 'stderr':
						default:
								// write unformatted
							$content = "\n\n\n *** The \"$log\"-Log: ***\n";
							$content .= strftime( '%Y-%m-%d %H:%M:%S', time() ) . "\n";

							$content .= implode("\n", $messages) . "\n";

							if ( t3lib_div::inList('stdout,stderr', strtolower($logfile ) ) ) {

									// write it to standard out / standard error; common for CLI usage
								if ( defined( strtoupper($logfile) ) ) {
									fwrite ( strtoupper($logfile), $content );
								} else {
									$handle = fopen( sprintf( 'php://%s', strtolower($logfile) ), 'a' );
									fwrite ( $handle, $content );
									fclose ($handle);
								}

							} else {

									// decide if file is local or global
								$logfile = t3lib_div::fixWindowsFilePath($logfile);
								$filename = t3lib_div::isAbsPath($logfile) ? $logfile : PATH_site.$logfile;

								if ( ! is_file($filename)) {
									if ( ! is_dir( t3lib_div::dirname($filename) ) ) {
										t3lib_div::mkdir( t3lib_div::dirname($filename) );
									}
									if ( ! touch($filename) ) {
										echo sprintf( '<h2>Error while writing log to file <em>%s</em>. Please check directory permissions!</h2>', $filename);
										break;
									}
								}
								if( is_writable($filename) ) {
									$handle = fopen ( $filename, 'a' );
									fwrite ( $handle, $content );
									fclose ($handle);
								} else {
									echo sprintf( '<h2>Error while writing log to file <em>%s</em>. Please check file permissions!</h2>', $filename);
								}
							}

							break;
					}
				}
			}
		}
		return;
	}

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dkd_xmlimport/class.tx_dkdxml_importer.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dkd_xmlimport/class.tx_dkdxml_importer.php"]);
}


?>