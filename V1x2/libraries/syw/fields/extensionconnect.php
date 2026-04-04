<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldExtensionConnect extends JFormField
{
	public $type = 'ExtensionConnect';

	protected function getLabel()
	{
		$lang = JFactory::getLanguage();
		$lang->load('lib_syw.sys', JPATH_SITE);

		$html = '';

		//$html .= '<div style="clear: both;">'.JText::_('LIB_SYW_EXTENSIONCONNECT_CONNECT_LABEL').'</div>';

		return $html;
	}

	protected function getInput()
	{
		JHtml::_('stylesheet', 'syw/fonts-min.css', false, true);
		JHtml::_('bootstrap.tooltip');

		$html = '<div style="padding-top: 5px; overflow: inherit">';

		$html .= '<a class="label hasTooltip" style="background-color: #02b0e8; padding: 4px 8px; margin: 0 3px 3px 0;" title="@simplifyyourweb" href="https://twitter.com/simplifyyourweb" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewbox="0 0 512 512" aria-hidden="true"><path fill="currentColor" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg> X-Twitter</a>';
		$html .= '<a class="label hasTooltip" style="background-color: #43609c; padding: 4px 8px; margin: 0 3px 3px 0;" title="simplifyyourweb" href="https://www.facebook.com/simplifyyourweb" target="_blank"><i class="SYWicon-facebook" aria-hidden="true">&nbsp;</i>Facebook</a>';
		$html .= '<a class="label" style="background-color: #ff8f00; padding: 4px 8px; margin: 0 3px 3px 0;" href="https://simplifyyourweb.com/latest-news?format=feed&amp;type=rss" target="_blank"><i class="SYWicon-rss" aria-hidden="true">&nbsp;</i>News feed</a>';

		$html .= '</div>';

		return $html;
	}

}
?>
