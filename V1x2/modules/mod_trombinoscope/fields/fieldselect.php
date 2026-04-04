<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// no direct access
defined( '_JEXEC' ) or die;

JFormHelper::loadFieldClass('list');

class JFormFieldFieldSelect extends JFormFieldList
{
	protected $type = 'FieldSelect';

	protected function getInput() {
		
		$options[] = JHTML::_('select.option', 'empty', JText::_('MOD_TROMBINOSCOPE_VALUE_EMPTY'), 'value', 'text', $disable=false);
				
		$options[] = JHTML::_('select.option', 'c_p', JText::_('MOD_TROMBINOSCOPE_VALUE_POSITION'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'tel', JText::_('MOD_TROMBINOSCOPE_VALUE_TELEPHONE'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'mob', JText::_('MOD_TROMBINOSCOPE_VALUE_MOBILE'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'fax', JText::_('MOD_TROMBINOSCOPE_VALUE_FAX'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'mail', JText::_('MOD_TROMBINOSCOPE_VALUE_EMAIL'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'web', JText::_('MOD_TROMBINOSCOPE_VALUE_WEBPAGE'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'add', JText::_('MOD_TROMBINOSCOPE_VALUE_ADDRESS'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'sub', JText::_('MOD_TROMBINOSCOPE_VALUE_SUBURB'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'st', JText::_('MOD_TROMBINOSCOPE_VALUE_STATE'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'p_c', JText::_('MOD_TROMBINOSCOPE_VALUE_POSTCODE'), 'value', 'text', $disable=false);
		//$options[] = JHTML::_('select.option', 'f_a', JText::_('MOD_TROMBINOSCOPE_VALUE_FORMATTEDADDRESS'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'f_f_a', JText::_('MOD_TROMBINOSCOPE_VALUE_FULLYFORMATTEDADDRESS'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'cou', JText::_('MOD_TROMBINOSCOPE_VALUE_COUNTRY'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'misc', JText::_('MOD_TROMBINOSCOPE_VALUE_MISC'), 'value', 'text', $disable=false);

		// if content plugin 'additional contact fields' is enabled, add the fields here
		if (JPluginHelper::isEnabled('content', 'additionalcontactfields')) {
		
			$plugin = JPluginHelper::getPlugin('content', 'additionalcontactfields');
			$params = json_decode($plugin->params);
		
			if (isset($params->gender) && $params->gender) {
				$options[] = JHTML::_('select.option', 'gen', JText::_('MOD_TROMBINOSCOPE_VALUE_GENDER'), 'value', 'text', $disable=false);
			}
		
			if (isset($params->birthdate) && $params->birthdate) {
				$options[] = JHTML::_('select.option', 'dob', JText::_('MOD_TROMBINOSCOPE_VALUE_BIRTHDATE'), 'value', 'text', $disable=false);
				$options[] = JHTML::_('select.option', 'age', JText::_('MOD_TROMBINOSCOPE_VALUE_AGE'), 'value', 'text', $disable=false);
			}
		
			if (isset($params->company) && $params->company) {
				$options[] = JHTML::_('select.option', 'com', JText::_('MOD_TROMBINOSCOPE_VALUE_COMPANY'), 'value', 'text', $disable=false);
			}
		
			if (isset($params->department) && $params->department) {
				$options[] = JHTML::_('select.option', 'dep', JText::_('MOD_TROMBINOSCOPE_VALUE_DEPARTMENT'), 'value', 'text', $disable=false);
			}
		
			if (isset($params->map) && $params->map) {
				$options[] = JHTML::_('select.option', 'map', JText::_('MOD_TROMBINOSCOPE_VALUE_MAP'), 'value', 'text', $disable=false);
			}
		
			if (isset($params->skype) && $params->skype) {
				$options[] = JHTML::_('select.option', 'skype', 'Skype', 'value', 'text', $disable=false);
			}
		}
		
		$options[] = JHTML::_('select.option', 'a', JText::_('MOD_TROMBINOSCOPE_VALUE_LINKA'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'a_sw', JText::_('MOD_TROMBINOSCOPE_VALUE_LINKA_SAMEWINDOW'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'b', JText::_('MOD_TROMBINOSCOPE_VALUE_LINKB'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'b_sw', JText::_('MOD_TROMBINOSCOPE_VALUE_LINKB_SAMEWINDOW'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'c', JText::_('MOD_TROMBINOSCOPE_VALUE_LINKC'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'c_sw', JText::_('MOD_TROMBINOSCOPE_VALUE_LINKC_SAMEWINDOW'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'd', JText::_('MOD_TROMBINOSCOPE_VALUE_LINKD'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'd_sw', JText::_('MOD_TROMBINOSCOPE_VALUE_LINKD_SAMEWINDOW'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'e', JText::_('MOD_TROMBINOSCOPE_VALUE_LINKE'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'e_sw', JText::_('MOD_TROMBINOSCOPE_VALUE_LINKE_SAMEWINDOW'), 'value', 'text', $disable=false);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		
		$attributes = 'class="inputbox"';

		return JHTML::_('select.genericlist', $options, $this->name, $attributes, 'value', 'text', $this->value, $this->id);
	}
}
?>