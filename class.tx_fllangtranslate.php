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

class tx_fllangtranslate {

	/**
	 * Change the row
	 */
	static function changeRow($table, &$row) {

		if (defined('PATH_t3lib')) {
			require_once(PATH_t3lib . 'class.t3lib_page.php');
		}

		if (isset($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'])) {
			$disabled = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'];
			unset($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled']);
		}

		$languages = self::getBEUserLanguages();
		$temp_sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$temp_sys_page->init(TRUE);
		foreach ($languages as $lang) {

			$row_temp = $temp_sys_page->getRecordOverlay($table, $row, $lang, '');

			if (sizeof($row_temp) > 5) {
				$row = $row_temp;
				$row[$GLOBALS['TCA'][$table]['ctrl']['languageField']] = 0;
				$row[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']] = 0;
				break;
			} # if
		} # foreach

		if (isset($disabled)) {
			$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] = $disabled;
		}
	}

	/**
	 * Get the BE user languages
	 */
	static function getBEUserLanguages() {
		return t3lib_div::trimExplode(',', $GLOBALS['BE_USER']->user['tx_fllangtranslate_langorder'], TRUE);
	}

}

?>