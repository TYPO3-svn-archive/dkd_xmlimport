<?php
/*
 *  CVS Versioning: $Id$
 */

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/dkd_xmlimport/mod1/');
// define('TYPO3_MOD_PATH', 'ext/dkd_xmlimport/mod1/');
$BACK_PATH='../../../../typo3/';
// $BACK_PATH='../../../';
$MCONF['name']='web_txdkdxmlimportM1';

	
$MCONF['access']='user,group';
$MCONF['script']='index.php';

$MLANG['default']['tabs_images']['tab'] = $BACK_PATH. 'gfx/fileicons/xml.gif';
$MLANG['default']['ll_ref']='LLL:EXT:dkd_xmlimport/mod1/locallang_mod.php';
?>