<?php
defined('TYPO3_MODE') or die('Access denied.');

if (defined('TYPO3_version') && t3lib_div::int_from_ver(TYPO3_version) > 6002000) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass']['fl_langtranslate'] = 'FRUIT\\FlLangtranslate\\Hooks\\TceForms';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/tree/pagetree/class.t3lib_tree_pagetree_dataprovider.php']['postProcessCollections']['fl_langtranslate'] = 'FRUIT\\FlLangtranslate\\Hooks\\PageTree';
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\DataHandling\\DataHandler'] = array(
		'className' => 'FRUIT\\FlLangtranslate\\Xclass\\DataHandler',
	);
} else {
	$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['t3lib/class.t3lib_tcemain.php'] = t3lib_extMgm::extPath('fl_langtranslate', '4.5.x-4.x.x/class.ux_t3lib_tcemain.php');
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass']['fl_langtranslate'] = t3lib_extMgm::extPath('fl_langtranslate', '4.5.x-4.x.x/class.user_t3lib_tceforms_hook.php:user_t3lib_tceforms_hook');
}