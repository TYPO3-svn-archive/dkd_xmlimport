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
 * SAPI wrapper for automated XML import
 *
 * @author	Thorsten Kahler <thorsten.kahler@dkd.de>
 */


if (! ( $_GET['configFile'] && $_GET['varsFile'] && $_GET['function'] && $_GET['varsPart'] ) ) {
	die('Use parameters "configFile", "varsFile", "function" and "varsPart".');
} else {
	$configFile = urldecode( $_GET['configFile'] );
	$varsFile = urldecode( $_GET['varsFile'] );
	$varsPart = urldecode( $_GET['varsPart'] );
	$function = urldecode( $_GET['function'] );
	$dir = dirname( $_SERVER['SCRIPT_FILENAME'] );

		// vars are set, auto-import script can be included
	require($dir.'/auto-import.inc.php');
}


?>