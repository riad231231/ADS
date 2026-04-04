<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('message');

class JFormFieldMessageWrapper extends JFormFieldMessage {
	
	public $type = 'MessageWrapper';
	
	protected function hide_it() 
	{		
		$folder = JPATH_ROOT.'/components/com_trombinoscopeextended/views/trombinoscope'; // when adding themes, even if the component is not installed, it adds the folder
		if (!JFolder::exists($folder)) {
			return false;
		}
				
		return true;
	}
	
	public function getLabel() 
	{			
		if (!self::hide_it()) {
			return parent::getLabel();
		}
			
		return '';
	}
	
	public function getInput() 
	{
		if (!self::hide_it()) {
			return parent::getInput();
		}
		
		return '';
	}
	
}
?>