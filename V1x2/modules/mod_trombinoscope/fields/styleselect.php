<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// no direct access
defined( '_JEXEC' ) or die;

jimport('joomla.form.formfield');

class JFormFieldStyleSelect extends JFormField
{
	protected $type = 'StyleSelect';

	protected function getInput() {
		
		jimport('joomla.filesystem.folder');		
		
		$lang = JFactory::getLanguage();
		
		$path = '/modules/mod_trombinoscope/themes';		
		
		$optionsArray = JFolder::folders(JPATH_SITE.$path);
		
		foreach($optionsArray as $option) {
			$upper_option = strtoupper($option);
			$lang->load('com_trombinoscopeextended_theme_'.$option);
			$translated_option = JText::_('MOD_TROMBINOSCOPE_THEME_'.$upper_option.'_LABEL');
				
			if (empty($translated_option) || substr_count($translated_option, 'TROMBINOSCOPE') > 0) {
				$translated_option = ucfirst($option);
			}
			
			$options[] = JHTML::_('select.option', $option, $translated_option, 'value', 'text', $disable=false );
		}
		
		$attributes = 'class="inputbox"';

		return JHTML::_('select.genericlist', $options, $this->name, $attributes, 'value', 'text', $this->value, $this->id);
	}
}
?>