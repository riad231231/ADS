<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');

/**
 *
 * @author Olivier Buisard
 *
 * for Joomla 3+ ONLY
 *
 */
class JFormFieldSYWVerboseText extends JFormField
{
	protected $type = 'SYWVerboseText';

	protected $prefix;
	protected $postfix;
	protected $max;
	protected $min;
	protected $unit;
	protected $icon;
	protected $help;
	protected $maxLength;

	protected function getInput()
	{
		$html = '';

		$lang = JFactory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$hint = '';

		if ($this->element['useglobal'])
		{
			$component = JFactory::getApplication()->input->getCmd('option');

			// Get correct component for menu items
			if ($component == 'com_menus')
			{
				$link      = $this->form->getData()->get('link');
				$uri       = new JUri($link);
				$component = $uri->getVar('option', 'com_menus');
			}

			$params = JComponentHelper::getParams($component);
			$value  = $params->get($this->fieldname);

			// Try with global configuration
			if (is_null($value))
			{
				$value = JFactory::getConfig()->get($this->fieldname);
			}

			// Try with menu configuration
			if (is_null($value) && JFactory::getApplication()->input->getCmd('option') == 'com_menus')
			{
				$value = JComponentHelper::getParams('com_menus')->get($this->fieldname);
			}

			if (!is_null($value))
			{
				$hint = JText::sprintf('JGLOBAL_USE_GLOBAL_VALUE', (string) $value);
			}
		}

		if (empty($hint) && (isset($this->min) || isset($this->max)))
		{
			$min = isset($this->min) ? JText::sprintf('LIB_SYW_SYWVERBOSETEXT_MIN', $this->min) : '';
			$max = isset($this->max) ? JText::sprintf('LIB_SYW_SYWVERBOSETEXT_MAX', $this->max) : '';

			$hint = ($min && $max) ? $min.' - '.$max : '';

			if (empty($hint))
			{
				$hint = $min ? $min : '';
			}

			if (empty($hint))
			{
				$hint = $max ? $max : '';
			}
		}

		if (empty($hint) && $this->hint)
		{
			$hint = $this->translateHint ? JText::_($this->hint) : JText::sprintf('LIB_SYW_SYWVERBOSETEXT_HINT', $this->hint);
		}

		$hint = $hint ? ' placeholder="'.$hint.'"' : '';

		$size = $this->size ? ' size="' . $this->size . '"' : '';

		$style = $size ? ' style="width:auto"' : '';

		$class = $this->class ? ' class="' . $this->class . '"' : '';

		$overall_class = empty($this->icon) ? '' : 'input-prepend';
		$overall_class .= empty($this->unit) ? '' : ' input-append';
		$overall_class = trim($overall_class);
		$overall_class = empty($overall_class) ? '' : ' class="'.$overall_class.'"';

		$html .= '<div'.$overall_class.'>';

		if ($this->prefix || $this->icon) {
			$html .= '<div class="add-on">';

			if ($this->icon) {
				JHtml::_('stylesheet', 'syw/fonts-min.css', false, true);
				$html .= '<i class="'.$this->icon.'"></i>';

				if ($this->prefix) {
					$html .= '&nbsp;';
				}
			}

			if ($this->prefix) {
				$html .= '<span>'.$this->prefix.'</span>';
			}

			$html .= '</div>';
		}

		$html .= '<input type="text" name="'.$this->name.'" id="'.$this->id.'"'.' value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"'.$class.$style.$size.$this->maxLength.$hint.' />';

		if ($this->postfix) {
			$html .= '<div class="add-on">'.$this->postfix.'</div>';
		}

		if ($this->unit) {
			$html .= '<div class="add-on">'.$this->unit.'</div>';
		}

		$html .= '</div>';

		if ($this->help) {
			$html .= '<span class="help-block">'.JText::_($this->help).'</span>';
		}

		return $html;
	}

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return) {
			$this->prefix = isset($this->element['prefix']) ? (string)$this->element['prefix'] : '';
			$this->postfix = isset($this->element['postfix']) ? (string)$this->element['postfix'] : '';
			$this->max = isset($this->element['max']) ? (string)$this->element['max'] : null;
			$this->min = isset($this->element['min']) ? (string)$this->element['min'] : null;
			$this->unit = isset($this->element['unit']) ? (string)$this->element['unit'] : '';
			$this->help = isset($this->element['help']) ? (string)$this->element['help'] : '';
			$this->icon = isset($this->element['icon']) ? (string)$this->element['icon'] : '';
			$this->maxLength = isset($this->element['maxlength']) ? ' maxlength="' . ((string)$this->maxLength) . '"' : '';
		}

		return $return;
	}

}
?>