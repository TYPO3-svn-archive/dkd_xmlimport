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
 * CLI script for the 'dkd_xmlimport' extension.
 *
 * @author	Thorsten Kahler <thorsten.kahler@dkd.de>
 */


define("PATH_typo3", dirname(dirname(dirname(dirname($dir))))."/typo3/");
define("PATH_site", dirname(PATH_typo3)."/");
define("PATH_t3lib", PATH_typo3."t3lib/");
define("PATH_typo3conf", PATH_site."typo3conf/");	// Typo-configuraton path
define('TYPO3_MODE','BE');

if (substr($dir,strlen(PATH_site))!="typo3conf/ext/dkd_xmlimport/mod1")	{
	die("Wrong path... This '".substr($dir,strlen(PATH_site))."' should be the last part of '".$dir."'");
} 
	
require(PATH_t3lib."class.t3lib_div.php");
require(PATH_t3lib."class.t3lib_extmgm.php");

require(PATH_t3lib."config_default.php");		
if (!defined ("TYPO3_db")) 	die ("The configuration file was not included.");

require(PATH_t3lib.'class.t3lib_db.php');		// The database library
$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');

// Connect to the database
$result = $GLOBALS['TYPO3_DB']->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password); 
if (!$result)	{
	die("Couldn't connect to database at ".TYPO3_db_host);
}
$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);



// print_r( array( $_SERVER, PATH_typo3, PATH_site, PATH_t3lib, PATH_typo3conf, TYPO3_MODE, t3lib_extmgm::extPath('dkd_xmlimport') ) );

require_once( t3lib_extmgm::extPath('dkd_xmlimport', '/class.tx_dkdxml_importer.php') );

$SOBE = t3lib_div::makeInstance("tx_dkdxml_importer");

if ( $SOBE->init( $configFile, $varsFile, $varsPart ) == 0 ) {
		// if initialization succeeds, do the things

	switch( $function ) {
		case 'importXML' :
// t3lib_div::debug("function: importXML");
				$SOBE->config['pid'] = intval( $SOBE->vars['page_id'] );
				if( $SOBE->vars['import'] ) {
// t3lib_div::debug("IF: do import\n");
					
					if ( $SOBE->extConf['backup'] && ! $SOBE->backupRecords( $SOBE->config['table'], $SOBE->config['pid'] ) ) {
// t3lib_div::debug("IF: no backup possible");
						$SOBE->log('error', 'Could not backup records, so insert was skipped');
					} else {
// t3lib_div::debug("IF: did backup\n");
				
						$auth = ( $SOBE->config['http_user'] && $SOBE->config['http_password'] ) ? sprintf('%s:%s@', $SOBE->config['http_user'], $SOBE->config['http_password'] ) : '';
						$url = 'http://' . $auth . $SOBE->config['url'];
				
						if (! $assoc = $SOBE->fetchData($url) ) {
// t3lib_div::debug("IF: can't fetch data");
							$SOBE->log('error', 'The URL was not reachable: '.$url);
						} else {
// t3lib_div::debug("IF: data fetched from URL");
				
							$table = $SOBE->assocArray2Table($assoc);
							
							if (is_array($SOBE->config['preProcessing'])) {
								$SOBE->doProcessing( $SOBE->config['preProcessing']['function'], $SOBE->config['preProcessing']['params'] );
							}
				
							$log = $SOBE->insertRecords($table);
							if (is_array($SOBE->config['postProcessing'])) {
								$SOBE->doProcessing( $SOBE->config['postProcessing']['function'], $SOBE->config['postProcessing']['params'] );
							}
							
							if ( is_array($log) && ( $log['rows'] != $log['success'] + $log['failed'] ) )  {
// t3lib_div::debug("IF: something fishy...");
								$SOBE->log( 'error', sprintf( 'Something fishy happened: There were %d records to insert, but %d were successful and %d failed', $log['rows'], $log['success'], $log['failed'] ) );
								$SOBE->logFiles['error'] .= t3lib_div::uniqueList($SOBE->logFiles.',html');
							} elseif ( $log['failed'] ) {
// t3lib_div::debug("IF: no insert failed");
								$SOBE->log( 'error' , sprintf( 'An error occured during insertion of %d datasets: %d were successful but %d failed', $log['rows'], $log['success'], $log['failed'] ) );
								$SOBE->logFiles['error'] .= t3lib_div::uniqueList($SOBE->logFiles.',html');
							} else {
// t3lib_div::debug("IF: everything worked fine");
								$SOBE->log ( 'ok', sprintf( '%d rows were inserted successful', $log['rows'] ) );
							}
							$SOBE->logFiles['error'] = 'STDERR,' . $SOBE->extConf['log_file'];
							$SOBE->logFiles['ok'] = 'STDOUT,' . $SOBE->extConf['log_file'];
							if ( count($SOBE->log['error']) == 0 ) {
								$SOBE->log['sql'] = array();
								$SOBE->log ( 'sql', 'No errors during processing, so the SQL log has been dropped.');
							}
						}
					}
				}
			break;
		case 'importPics' :
t3lib_div::debug("function: importPics");
			break;
		default:
t3lib_div::debug("function: default");
			break;
	}

}
$SOBE->finish();



?>