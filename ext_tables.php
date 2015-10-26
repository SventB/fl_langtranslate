<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$tempColumns = Array(
	'tx_fllangtranslate_langorder' => Array(
		'exclude' => 0,
		'label'   => 'LLL:EXT:fl_langtranslate/locallang_db.xml:be_users.tx_fllangtranslate_langorder',
		'config'  => Array(
			'type'                => 'select',
			'foreign_table'       => 'sys_language',
			'foreign_table_where' => 'ORDER BY title',
			'size'                => 5,
			'minitems'            => 0,
			'maxitems'            => 50,
		)
	),
);

$loadTca = FALSE;


if (!class_exists('getNumericTypo3Version') || t3lib_utility_VersionNumber::convertVersionNumberToInteger(t3lib_utility_VersionNumber::getNumericTypo3Version()) < 6002000) {
	$loadTca = TRUE;
}

if ($loadTca) {
	t3lib_div::loadTCA('be_users');
}

t3lib_extMgm::addTCAcolumns('be_users', $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes('be_users', 'tx_fllangtranslate_langorder;;;;1-1-1');
?>