<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Tim Lochmueller (webmaster@fruit-lab.de)
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

class user_t3lib_tceforms_hook extends t3lib_TCEmain {

	public function getMainFields_preProcess($table, $row, t3lib_TCEforms &$obj) {
		if (!t3lib_extMgm::isLoaded('static_info_tables')) {
			return;
		}
		if (!sizeof($obj->getAdditionalPreviewLanguages())) {
			require_once(t3lib_extMgm::extPath('fl_langtranslate', 'class.tx_fllangtranslate.php'));
			foreach (tx_fllangtranslate::getBEUserLanguages() as $uid) {
				if ($sys_language_rec = t3lib_BEfunc::getRecord('sys_language', $uid)) {
					$obj->cachedAdditionalPreviewLanguages[$uid] = array('uid' => $uid);
					if ($sys_language_rec['static_lang_isocode']) {
						$staticLangRow = t3lib_BEfunc::getRecord('static_languages', $sys_language_rec['static_lang_isocode'], 'lg_iso_2');
						if ($staticLangRow['lg_iso_2']) {
							$obj->cachedAdditionalPreviewLanguages[$uid]['uid'] = $uid;
							$obj->cachedAdditionalPreviewLanguages[$uid]['ISOcode'] = $staticLangRow['lg_iso_2'];
						}
					}
				}
			}
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fl_langtranslate/4.5.x-4.x.x/class.user_t3lib_tceforms_hook.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fl_langtranslate/4.5.x-4.x.x/class.user_t3lib_tceforms_hook.php']);
}
?>