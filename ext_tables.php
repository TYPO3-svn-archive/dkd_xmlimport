<?php
/*
 *  CVS Versioning: $Id$
 */

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
		
	t3lib_extMgm::addModule('web','txdkdxmlimportM1','after:func',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}
?>