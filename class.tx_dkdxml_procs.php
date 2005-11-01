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
 * container class for pre- and post_processing functions used in 'dkd_xmlimport' extension.
 *
 * @author	Thorsten Kahler <thorsten.kahler@dkd.de>
 */
class tx_dkdxml_procs {

	function process_setTopProduct($config, &$importer) {
		
		$tstamps = array();
		$week = 0;
		$now = getdate();
//		$day = $now['mday'] - $now['wday'] + 1;
		$day = $now['mday'];
		
			// uids of products in pid/table
		$where_clause = ( ( $config['scope'] == 'pid' ) && ( $config['pid'] ) ) ? 'pid='.$config['pid'] : '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, seitenr', $config['table'],$where_clause);
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
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery($config['table'], $where, $fields_values);
			$query = implode(' ', t3lib_div::trimExplode("\n", $query) );
			mysql_query($query, $GLOBALS['TYPO3_DB']->link) OR
				$importer->log('error', 'Query failed: '.$query);
			$importer->log('sql', $query);
			$week++;
		}
		$importer->log('ok', sprintf('Setting a timestamp was successful for %s randomly ordered records from table %s.', $week, $config['table'] ) );
		return;
	}

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dkd_xmlimport/class.tx_dkdxml_procs.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/dkd_xmlimport/class.tx_dkdxml_procs.php"]);
}