<?php

########################################################################
# Extension Manager/Repository config file for ext: "dkd_xmlimport"
# 
# Auto generated 01-11-2005 17:01
# 
# Manual updates:
# Only the data in the array - anything else is removed by next write
########################################################################

$EM_CONF[$_EXTKEY] = Array (
	'title' => 'd.k.d XML-Import',
	'description' => 'A BE module to import data in XML files into the DB. Provides a library class and and a complete BE module.',
	'category' => 'be',
	'author' => 'Thorsten Kahler',
	'author_email' => 'thorsten.kahler@dkd.de',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod1',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'd.k.d Internet Service GmbH',
	'private' => '',
	'download_password' => '',
	'version' => '0.5.0',	// Don't modify this! Managed automatically during upload to repository.
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.1.0-4.3.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:22:{s:28:"class.tx_dkdxml_importer.php";s:4:"aca8";s:25:"class.tx_dkdxml_procs.php";s:4:"f325";s:21:"ext_conf_template.txt";s:4:"2949";s:12:"ext_icon.gif";s:4:"76eb";s:14:"ext_tables.php";s:4:"ac91";s:22:"doc/class_diagrams.txt";s:4:"39b3";s:14:"doc/manual.sxw";s:4:"7eb1";s:19:"doc/wizard_form.dat";s:4:"85f2";s:20:"doc/wizard_form.html";s:4:"7c4d";s:24:"mod1/auto-import.inc.php";s:4:"1068";s:31:"mod1/class.tx_dkdxml_impexp.php";s:4:"fc31";s:14:"mod1/clear.gif";s:4:"cc11";s:17:"mod1/cli_conf.php";s:4:"a3c9";s:18:"mod1/cli_index.php";s:4:"5b05";s:17:"mod1/cli_init.php";s:4:"e462";s:13:"mod1/conf.php";s:4:"7935";s:14:"mod1/index.php";s:4:"ccac";s:18:"mod1/locallang.php";s:4:"4296";s:22:"mod1/locallang_mod.php";s:4:"d2d1";s:19:"mod1/moduleicon.gif";s:4:"8074";s:19:"mod1/sapi_index.php";s:4:"23a9";s:17:"mod1/vars-test.ts";s:4:"c60a";}',
);

?>