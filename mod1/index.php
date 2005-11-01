<?php
/*
 *  CVS Versioning: $Id$
 */

/***************************************************************
*  Copyright notice
*
*  (c) 2004 Thorsten Kahler (thorsten.kahler@dkd.de)
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
 * Module 'XML-Apotheken' for the 'dkd_xmlimport' extension.
 *
 * @author	Thorsten Kahler <thorsten.kahler@dkd.de>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

require_once ('class.tx_dkdxml_impexp.php');

// Make instance:
$SOBE = t3lib_div::makeInstance("tx_dkdxml_impexp");
$SOBE->init();

/*
// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);
*/
//print_r($SOBE);
$SOBE->main();
$SOBE->printContent();

?>