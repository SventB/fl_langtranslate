<?php
/**
 * @todo       General file information
 *
 * @category   Extension
 * @package    ...
 * @author     Tim Lochmüller <tim@fruit-lab.de>
 */


namespace FRUIT\FlLangtranslate\Hooks;

use TYPO3\CMS\Backend\Tree\Pagetree\CollectionProcessorInterface;
use TYPO3\CMS\Core\Error\DebugExceptionHandler;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * @todo       General class information
 *
 * @package    ...
 * @subpackage ...
 * @author     Tim Lochmüller <tim@fruit-lab.de>
 */
class PageTree implements CollectionProcessorInterface {

	/**
	 * Post process the subelement collection of a specific node
	 *
	 * @param \TYPO3\CMS\Backend\Tree\Pagetree\PagetreeNode           $node
	 * @param integer                                                 $mountPoint
	 * @param integer                                                 $level
	 * @param \TYPO3\CMS\Backend\Tree\Pagetree\PagetreeNodeCollection $nodeCollection
	 *
	 * @return void
	 */
	public function postProcessGetNodes($node, $mountPoint, $level, $nodeCollection) {
		if (!sizeof($nodeCollection)) {
			return;
		}
		foreach ($nodeCollection as $i => $node) {
			/** @var $node \TYPO3\CMS\Backend\Tree\Pagetree\PagetreeNode */

			if ($node->getType() === 'pages') {
				#$node->setText('<span class="t3-icon t3-icon-flags t3-icon-flags-gb t3-icon-gb" title="en" style="padding:0px;">&nbsp;</span>' . $node->getText());
				#$node->setLabelIsEditable(FALSE);
				#DebuggerUtility::var_dump($node->getId());
				#$nodeCollection->offsetSet($i, $node);
			}

		}
	}

	/**
	 * Post process the collection of tree mounts
	 *
	 * @param string                                                  $searchFilter
	 * @param \TYPO3\CMS\Backend\Tree\Pagetree\PagetreeNodeCollection $nodeCollection
	 *
	 * @return void
	 */
	public function postProcessGetTreeMounts($searchFilter, $nodeCollection) {

	}

	/**
	 * Post process the subelement collection of a specific node-filter combination
	 *
	 * @param \TYPO3\CMS\Backend\Tree\Pagetree\PagetreeNode           $node
	 * @param string                                                  $searchFilter
	 * @param integer                                                 $mountPoint
	 * @param \TYPO3\CMS\Backend\Tree\Pagetree\PagetreeNodeCollection $nodeCollection
	 *
	 * @return void
	 */
	public function postProcessFilteredNodes($node, $searchFilter, $mountPoint, $nodeCollection) {

	}
}