<?php
/**
 * TCE Forms Hook
 *
 * @category   Extension
 * @package    ...
 * @author     Tim Lochmüller <tim@fruit-lab.de>
 */


namespace FRUIT\FlLangtranslate\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * @todo       General class information
 *
 * @package    ...
 * @subpackage ...
 * @author     Tim Lochmüller <tim@fruit-lab.de>
 */
class TceForms {

	/**
	 * @param                                    $table
	 * @param                                    $row
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $obj
	 */
	public function getMainFields_preProcess($table, $row, \TYPO3\CMS\Backend\Form\FormEngine &$obj) {
		if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
			return;
		}
		if (!sizeof($obj->getAdditionalPreviewLanguages())) {
			require_once(ExtensionManagementUtility::extPath('fl_langtranslate', 'class.tx_fllangtranslate.php'));
			foreach (\tx_fllangtranslate::getBEUserLanguages() as $uid) {
				if ($sys_language_rec = BackendUtility::getRecord('sys_language', $uid)) {
					$obj->cachedAdditionalPreviewLanguages[$uid] = array('uid' => $uid);
					if ($sys_language_rec['static_lang_isocode']) {
						$staticLangRow = BackendUtility::getRecord('static_languages', $sys_language_rec['static_lang_isocode'], 'lg_iso_2');
						if ($staticLangRow['lg_iso_2']) {
							$obj->cachedAdditionalPreviewLanguages[$uid]['uid'] = $uid;
							$obj->cachedAdditionalPreviewLanguages[$uid]['ISOcode'] = $staticLangRow['lg_iso_2'];
						}
					}
				}
			}
		}

		// trigger the register of the default language data
		if ($obj->additionalPreviewLanguageData === array()) {
			$obj->registerDefaultLanguageData($table, $row);
		}
	}
} 