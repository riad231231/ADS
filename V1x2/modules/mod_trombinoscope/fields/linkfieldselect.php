<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// no direct access
defined( '_JEXEC' ) or die;

JFormHelper::loadFieldClass('list');

class JFormFieldLinkFieldSelect extends JFormFieldList
{
	protected $type = 'LinkFieldSelect';

	protected function getInput() {
		
		$options[] = JHTML::_('select.option', 'mail', JText::_('MOD_TROMBINOSCOPE_VALUE_EMAIL'), 'value', 'text', $disable=false);
		$options[] = JHTML::_('select.option', 'web', JText::_('MOD_TROMBINOSCOPE_VALUE_WEBPAGE'), 'value', 'text', $disable=false);
		
		// if content plugin 'additional contact fields' is enabled, add the fields here
		if (JPluginHelper::isEnabled('content', 'additionalcontactfields')) {
				
			$plugin = JPluginHelper::getPlugin('content', 'additionalcontactfields');
			$params = json_decode($plugin->params);
				
			if (isset($params->map) && $params->map) {
				$options[] = JHTML::_('select.option', 'map', JText::_('MOD_TROMBINOSCOPE_VALUE_MAP'), 'value', 'text', $disable=false);
			}
				
			if (isset($params->facebook) && $params->facebook) {
				$options[] = JHTML::_('select.option', 'facebook', 'Facebook', 'value', 'text', $disable=false);
			}
				
			if (isset($params->twitter) && $params->twitter) {
				$options[] = JHTML::_('select.option', 'twitter', 'Twitter', 'value', 'text', $disable=false);
			}
				
			if (isset($params->linkedin) && $params->linkedin) {
				$options[] = JHTML::_('select.option', 'linkedin', 'LinkedIn', 'value', 'text', $disable=false);
			}
				
			if (isset($params->googleplus) && $params->googleplus) {
				$options[] = JHTML::_('select.option', 'googleplus', 'Google+', 'value', 'text', $disable=false);
			}
				
			if (isset($params->youtube) && $params->youtube) {
				$options[] = JHTML::_('select.option', 'youtube', 'YouTube', 'value', 'text', $disable=false);
			}
				
			if (isset($params->instagram) && $params->instagram) {
				$options[] = JHTML::_('select.option', 'instagram', 'Instagram', 'value', 'text', $disable=false);
			}
				
			if (isset($params->pinterest) && $params->pinterest) {
				$options[] = JHTML::_('select.option', 'pinterest', 'Pinterest', 'value', 'text', $disable=false);
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