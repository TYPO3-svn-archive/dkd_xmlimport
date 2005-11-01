#! /usr/bin/php -q
<?php
/*
 *  CVS Versioning: $Id$
 */

/***************************************************************
*  Copyright notice
*
*  (c) 2004 d.k.d Internet Service GmbH www.dkd.de
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


if (! ( $_SERVER['argc'] == 4 || $_SERVER['argc'] == 5 ) ) {
	die(
"
XML import script

" . basename($_SERVER['argv'][0]) . " <config> <vars> <function>[:<part>| <part>]

This script must be called with three or four arguments:
config file\t-\tthe basic configuration
vars file\t-\tconfiguration for the current function
function\t-\tthe function to execute [importXML|importPics]
part\t-\toptional part of the vars array (this can be appended to function with colon \":\" as separator)
"
	);
} else {
	$configFile = strval( $_SERVER['argv'][1] );
	$varsFile = strval( $_SERVER['argv'][2] );
	$function = strval( $_SERVER['argv'][3] );
	if ( isset( $_SERVER['argv'][4] ) ) {
		$varsPart = strval( $_SERVER['argv'][4] );
	} else {
		list ($function, $varsPart) = explode ( ':', $function );
	}
}
$dir = dirname($_SERVER['argv'][0]);

	// vars are set, auto-import script can be included
require($dir.'auto-import.inc.php');

?>