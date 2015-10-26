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

class ux_t3lib_TCEmain extends t3lib_TCEmain {

	function localize($table, $uid, $language) {
		global $TCA;

		$newId = false;
		$uid = intval($uid);

		if ($TCA[$table] && $uid) {
			t3lib_div::loadTCA($table);

			if (($TCA[$table]['ctrl']['languageField'] && $TCA[$table]['ctrl']['transOrigPointerField'] && !$TCA[$table]['ctrl']['transOrigPointerTable']) || $table === 'pages') {
				if ($langRec = t3lib_BEfunc::getRecord('sys_language', intval($language), 'uid,title')) {
					if ($this->doesRecordExist($table, $uid, 'show')) {

						$row = t3lib_BEfunc::getRecordWSOL($table, $uid);    // Getting workspace overlay if possible - this will localize versions in workspace if any



						/* Hack start */
						require_once(t3lib_extMgm::extPath('fl_langtranslate', 'class.tx_fllangtranslate.php'));
						tx_fllangtranslate::changeRow($table, $row);
						/* Hack end */



						if (is_array($row)) {
							if ($row[$TCA[$table]['ctrl']['languageField']] <= 0 || $table === 'pages') {
								if ($row[$TCA[$table]['ctrl']['transOrigPointerField']] == 0 || $table === 'pages') {
									if ($table === 'pages') {
										$pass = $TCA[$table]['ctrl']['transForeignTable'] === 'pages_language_overlay' && !t3lib_BEfunc::getRecordsByField('pages_language_overlay', 'pid', $uid, ' AND ' . $TCA['pages_language_overlay']['ctrl']['languageField'] . '=' . intval($langRec['uid']));
										$Ttable = 'pages_language_overlay';
										t3lib_div::loadTCA($Ttable);
									} else {
										$pass = !t3lib_BEfunc::getRecordLocalization($table, $uid, $langRec['uid'], 'AND pid=' . intval($row['pid']));
										$Ttable = $table;
									}

									if ($pass) {

										// Initialize:
										$overrideValues = array();
										$excludeFields = array();

										// Set override values:
										$overrideValues[$TCA[$Ttable]['ctrl']['languageField']] = $langRec['uid'];
										$overrideValues[$TCA[$Ttable]['ctrl']['transOrigPointerField']] = $uid;

										// Set exclude Fields:
										foreach ($TCA[$Ttable]['columns'] as $fN => $fCfg) {
											if ($fCfg['l10n_mode'] == 'prefixLangTitle') {   // Check if we are just prefixing:
												if (($fCfg['config']['type'] == 'text' || $fCfg['config']['type'] == 'input') && strlen($row[$fN])) {
													list($tscPID) = t3lib_BEfunc::getTSCpid($table, $uid, '');
													$TSConfig = $this->getTCEMAIN_TSconfig($tscPID);

													if (isset($TSConfig['translateToMessage']) && strlen($TSConfig['translateToMessage'])) {
														$translateToMsg = @sprintf($TSConfig['translateToMessage'], $langRec['title']);
													}
													if (!strlen($translateToMsg)) {
														$translateToMsg = 'Translate to ' . $langRec['title'] . ':';
													}

													$overrideValues[$fN] = '[' . $translateToMsg . '] ' . $row[$fN];
												}
											} elseif (t3lib_div::inList('exclude,noCopy,mergeIfNotBlank', $fCfg['l10n_mode']) && $fN != $TCA[$Ttable]['ctrl']['languageField'] && $fN != $TCA[$Ttable]['ctrl']['transOrigPointerField']) {    // Otherwise, do not copy field (unless it is the language field or pointer to the original language)
												$excludeFields[] = $fN;
											}
											/* Hack start */ elseif (($fCfg['config']['type'] == 'text' || $fCfg['config']['type'] == 'input') && strlen($row[$fN])) {
												$overrideValues[$fN] = $row[$fN];
											}
											/* Hack end */
										}

										if ($Ttable === $table) {

											// Execute the copy:
											$newId = $this->copyRecord($table, $uid, -$uid, 1, $overrideValues, implode(',', $excludeFields), $language);
										} else {

											// Create new record:
											$copyTCE = t3lib_div::makeInstance('t3lib_TCEmain');
											/* @var $copyTCE t3lib_TCEmain  */
											$copyTCE->stripslashes_values = 0;
											$copyTCE->cachedTSconfig = $this->cachedTSconfig;   // Copy forth the cached TSconfig
											$copyTCE->dontProcessTransformations = 1;	// Transformations should NOT be carried out during copy

											$copyTCE->start(array($Ttable => array('NEW' => $overrideValues)), '', $this->BE_USER);
											$copyTCE->process_datamap();

											// Getting the new UID as if it had been copied:
											$theNewSQLID = $copyTCE->substNEWwithIDs['NEW'];
											if ($theNewSQLID) {
												// If is by design that $Ttable is used and not $table! See "l10nmgr" extension. Could be debated, but this is what I chose for this "pseudo case"
												$this->copyMappingArray[$Ttable][$uid] = $theNewSQLID;
												$newId = $theNewSQLID;
											}
										}
									} else
										$this->newlog('Localization failed; There already was a localization for this language of the record!', 1);
								} else
									$this->newlog('Localization failed; Source record contained a reference to an original default record (which is strange)!', 1);
							} else
								$this->newlog('Localization failed; Source record had another language than "Default" or "All" defined!', 1);
						} else
							$this->newlog('Attempt to localize record that did not exist!', 1);
					} else
						$this->newlog('Attempt to localize record without permission', 1);
				} else
					$this->newlog('Sys language UID "' . $language . '" not found valid!', 1);
			} else
				$this->newlog('Localization failed; "languageField" and "transOrigPointerField" must be defined for the table!', 1);
		}
		return $newId;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fl_langtranslate/4.3.x-4.4.x/class.ux_t3lib_tcemain.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fl_langtranslate/4.3.x-4.4.x/class.ux_t3lib_tcemain.php']);
}
?>