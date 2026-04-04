<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JFormFieldThemeRadio extends JFormField
{
	public $type = 'ThemeRadio';

	protected function getInput()
	{
		// Initialize variables.
		$html = array();
			
		// Initialize some field attributes.
		$class     = !empty($this->class) ? ' class="radio ' . $this->class . '"' : ' class="radio"';
		$required  = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$disabled  = $this->disabled ? ' disabled' : '';
		$readonly  = $this->readonly;
	
		// Start the radio field output.
		$html[] = '<fieldset id="' . $this->id . '"' . $class . $required . $autofocus . $disabled . ' >';
	
		// Get the field options.
		$options = $this->getOptions();
	
		// Build the radio field output.
		foreach ($options as $i => $option)
		{
			// Initialize some option attributes.
			$checked = ((string) $option->value == (string) $this->value) ? ' checked="checked"' : '';
			$class = !empty($option->class) ? ' class="' . $option->class . '"' : '';
	
			$disabled = !empty($option->disable) || ($readonly && !$checked);
	
			$disabled = $disabled ? ' disabled' : '';
	
			// Initialize some JavaScript option attributes.
			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
			$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';
	
			$html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '" value="'
					. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $required . $onclick
					. $onchange . $disabled . ' />';
	
			$title =  JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname));
			$html[] = '<label title="'. $title .'" for="' . $this->id . $i . '"' . $class . '>'
					.'<img style="margin-top: 0; height: 32px" src="'.$option->image.'" alt="'. $title .'" title="'. $title .'" />'
					. '</label>';
	
			$required = '';
		}
	
		// End the radio field output.
		$html[] = '</fieldset>';
	
		return implode($html);
	}
	
	protected function getOptions()
	{
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
				
			$tmp = JHTML::_('select.option', $option, $translated_option, 'value', 'text', $disable = false);
						
			$tmp->image = JURI::root(true).$path.'/'.$option.'/images/'.$option.'_card_landscape.png';

			$options[] = $tmp;
		}

		return $options;
	}
}
