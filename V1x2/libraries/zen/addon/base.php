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

JLoader::import('zen.utility.browser', ZEN_LIBRARY_PATH);

class ZenAddonBase
{
	private static $incompatibleBrowsers = array();

	public static function browserIsCompatible()
	{
		$browser = ZenUtilityBrowser::getInstance();
		return !in_array($browser->userAgent, self::$incompatibleBrowsers);
	}

	public static function getScriptFile()
	{
		return false;
	}

	public static function getScript()
	{
		return false;
	}
}
