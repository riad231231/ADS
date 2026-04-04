<?php
/**
 * @package		Zen Library
 * @subpackage	Zen Library
 * @author		Joomla Bamboo - design@joomlabamboo.com
 * @copyright 	Copyright (c) 2013 Joomla Bamboo. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @version		1.0.2
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

JLoader::import('zen.script.handler', ZEN_LIBRARY_PATH);

/**
 * Add admin assets to Document
 *
 * @package		Zen Library
 * @subpackage	Zen Library
 * @since		1.0.0
 */

class ZenFormFieldAdminassets extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Adminassets';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 * @since	1.0.0
	 */
	public function getInput()
	{
		$doc = JFactory::getDocument();

		if (version_compare(JVERSION, '3.0', '<'))
		{
			ZenScriptHandler::loadLocalJQuery();

			$doc->addStyleSheet(ZEN_LIBRARY_MEDIA_URI . 'css/admin/default.css');
		}

		return '';
	}
}
