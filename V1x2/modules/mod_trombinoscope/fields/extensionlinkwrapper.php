<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('extensionlink');

class JFormFieldExtensionLinkWrapper extends JFormFieldExtensionLink 
{	
	public $type = 'ExtensionLinkWrapper';
	
	protected function isStandalone() {		
		$folder = JPATH_ROOT.'/components/com_trombinoscopeextended/views/trombinoscope'; // when adding themes, even if the component is not installed, it adds the folder
		if (!JFolder::exists($folder)) {
			return true;
		}
				
		return false;
	}
	
	public function getLabel() {		
		$condition = ($this->element['condition'] == "true") ? true : false;
		if ((self::isStandalone() && $condition) || (!self::isStandalone() && !$condition)) {
			return parent::getLabel();
		}
			
		return '';
	}
	
	public function getInput() {
		$condition = ($this->element['condition'] == "true") ? true : false;
		if ((self::isStandalone() && $condition) || (!self::isStandalone() && !$condition)) {
			return parent::getInput();
		}
		
		return '';
	}
	
}
?>