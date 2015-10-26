<?php
/**
 * DataHandler XClass
 *
 * @category   Extension
 * @package    ...
 * @author     Tim Lochmüller <tim@fruit-lab.de>
 */

namespace FRUIT\FlLangtranslate\Xclass;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DataHandler XClass
 *
 * @package    ...
 * @subpackage ...
 * @author     Tim Lochmüller <tim@fruit-lab.de>
 */
class DataHandler extends \TYPO3\CMS\Core\DataHandling\DataHandler {

	/**
	 * Localizes a record to another system language
	 * In reality it only works if transOrigPointerTable is not set. For "pages" the implementation is hardcoded
	 *
	 * @param string $table Table name
	 * @param integer $uid Record uid (to be localized)
	 * @param integer $language Language ID (from sys_language table)
	 * @return mixed The uid (integer) of the new translated record or FALSE (boolean) if something went wrong
	 * @todo Define visibility
	 */
	public function localize($table, $uid, $language) {
		$newId = FALSE;
		$uid = (int)$uid;
		if ($GLOBALS['TCA'][$table] && $uid && $this->isNestedElementCallRegistered($table, $uid, 'localize') === FALSE) {
			$this->registerNestedElementCall($table, $uid, 'localize');
			if ($GLOBALS['TCA'][$table]['ctrl']['languageField'] && $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'] && !$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerTable'] || $table === 'pages') {
				if ($langRec = BackendUtility::getRecord('sys_language', (int)$language, 'uid,title')) {
					if ($this->doesRecordExist($table, $uid, 'show')) {
						// Getting workspace overlay if possible - this will localize versions in workspace if any
						$row = BackendUtility::getRecordWSOL($table, $uid);


						/* Hack start */
						require_once(\t3lib_extMgm::extPath('fl_langtranslate', 'class.tx_fllangtranslate.php'));
						\tx_fllangtranslate::changeRow($table, $row);
						/* Hack end */

						if (is_array($row)) {
							if ($row[$GLOBALS['TCA'][$table]['ctrl']['languageField']] <= 0 || $table === 'pages') {
								if ($row[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']] == 0 || $table === 'pages') {
									if ($table === 'pages') {
										$pass = $GLOBALS['TCA'][$table]['ctrl']['transForeignTable'] === 'pages_language_overlay' && !BackendUtility::getRecordsByField('pages_language_overlay', 'pid', $uid, (' AND ' . $GLOBALS['TCA']['pages_language_overlay']['ctrl']['languageField'] . '=' . (int)$langRec['uid']));
										$Ttable = 'pages_language_overlay';
									} else {
										$pass = !BackendUtility::getRecordLocalization($table, $uid, $langRec['uid'], ('AND pid=' . (int)$row['pid']));
										$Ttable = $table;
									}
									if ($pass) {
										// Initialize:
										$overrideValues = array();
										$excludeFields = array();
										// Set override values:
										$overrideValues[$GLOBALS['TCA'][$Ttable]['ctrl']['languageField']] = $langRec['uid'];
										$overrideValues[$GLOBALS['TCA'][$Ttable]['ctrl']['transOrigPointerField']] = $uid;
										// Copy the type (if defined in both tables) from the original record so that translation has same type as original record
										if (isset($GLOBALS['TCA'][$table]['ctrl']['type']) && isset($GLOBALS['TCA'][$Ttable]['ctrl']['type'])) {
											$overrideValues[$GLOBALS['TCA'][$Ttable]['ctrl']['type']] = $row[$GLOBALS['TCA'][$table]['ctrl']['type']];
										}
										// Set exclude Fields:
										foreach ($GLOBALS['TCA'][$Ttable]['columns'] as $fN => $fCfg) {
											// Check if we are just prefixing:
											if ($fCfg['l10n_mode'] == 'prefixLangTitle') {
												if (($fCfg['config']['type'] == 'text' || $fCfg['config']['type'] == 'input') && strlen($row[$fN])) {
													list($tscPID) = BackendUtility::getTSCpid($table, $uid, '');
													$TSConfig = $this->getTCEMAIN_TSconfig($tscPID);
													if (isset($TSConfig['translateToMessage']) && strlen($TSConfig['translateToMessage'])) {
														$translateToMsg = @sprintf($TSConfig['translateToMessage'], $langRec['title']);
													}
													if (!strlen($translateToMsg)) {
														$translateToMsg = 'Translate to ' . $langRec['title'] . ':';
													}
													$overrideValues[$fN] = '[' . $translateToMsg . '] ' . $row[$fN];
												}
											} elseif (GeneralUtility::inList('exclude,noCopy,mergeIfNotBlank', $fCfg['l10n_mode']) && $fN != $GLOBALS['TCA'][$Ttable]['ctrl']['languageField'] && $fN != $GLOBALS['TCA'][$Ttable]['ctrl']['transOrigPointerField']) {
												// Otherwise, do not copy field (unless it is the language field or
												// pointer to the original language)
												$excludeFields[] = $fN;
											}
										}
										if ($Ttable === $table) {
											// Get the uid of record after which this localized record should be inserted
											$previousUid = $this->getPreviousLocalizedRecordUid($table, $uid, $row['pid'], $language);
											// Execute the copy:
											$newId = $this->copyRecord($table, $uid, -$previousUid, 1, $overrideValues, implode(',', $excludeFields), $language);
											$autoVersionNewId = $this->getAutoVersionId($table, $newId);
											if (is_null($autoVersionNewId) === FALSE) {
												$this->triggerRemapAction($table, $newId, array($this, 'placeholderShadowing'), array($table, $autoVersionNewId), TRUE);
											}
										} else {
											// Create new record:
											/** @var $copyTCE \TYPO3\CMS\Core\DataHandling\DataHandler */
											$copyTCE = $this->getLocalTCE();
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
									} else {
										$this->newlog('Localization failed; There already was a localization for this language of the record!', 1);
									}
								} else {
									$this->newlog('Localization failed; Source record contained a reference to an original default record (which is strange)!', 1);
								}
							} else {
								$this->newlog('Localization failed; Source record had another language than "Default" or "All" defined!', 1);
							}
						} else {
							$this->newlog('Attempt to localize record that did not exist!', 1);
						}
					} else {
						$this->newlog('Attempt to localize record without permission', 1);
					}
				} else {
					$this->newlog('Sys language UID "' . $language . '" not found valid!', 1);
				}
			} else {
				$this->newlog('Localization failed; "languageField" and "transOrigPointerField" must be defined for the table!', 1);
			}
		}
		return $newId;
	}
} 