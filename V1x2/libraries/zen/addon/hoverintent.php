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

JLoader::import('zen.addon.base', ZEN_LIBRARY_PATH);

class ZenAddonHoverintent extends ZenAddonBase
{
	public static function getScriptFile()
	{
		return ZEN_LIBRARY_MEDIA_URI . 'js/addons/menus/jquery-hoverintent.min.js';
	}
}
