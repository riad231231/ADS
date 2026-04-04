<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	System.Jblibrary
 * @author		Joomla Bamboo - design@joomlabamboo.com
 * @copyright 	Copyright (c) 2013 Joomla Bamboo. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @version		2.1.4
 *
 * This file is inside media folder because for plugins, J1.5 and J2.5 use different folders.
 * TODO: After drop J1.5 support remove this file
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// J1.5 can't install libraries, so we added it to media folder
// J2.5+ will use native libraries folder
if (version_compare(JVERSION, '1.6', '<'))
{
	define('ZEN_LIBRARY_MEDIA_URI', JURI::root(true) . '/media/plg_jblibrary/zen/media/');
	define('ZEN_LIBRARY_PATH', JPATH_SITE . '/media/plg_jblibrary/zen/libraries');
}
else
{
	define('ZEN_LIBRARY_PATH', null);
}
