<?php

########################################################################
# Extension Manager/Repository config file for ext "fl_langtranslate".
#
# Auto generated 02-10-2011 15:10
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Language Translation',
	'description' => 'Now it is possible for a editor to select the language from which the "Copy Item for tranlate" get the Content',
	'category' => 'be',
	'shy' => 0,
	'version' => '0.2.2',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'TYPO3_version' => '',
	'PHP_version' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'be_users',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Tim Lochmueller',
	'author_email' => 'webmaster@fruit-lab.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' =>
		array (
			'depends' =>
				array (
					'typo3' => '4.5.0-6.2.99',
				),
			'conflicts' =>
				array (
				),
			'suggests' =>
				array (
				),
		),
);

?>