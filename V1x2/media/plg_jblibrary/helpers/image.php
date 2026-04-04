<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	System.Jblibrary
 * @author		Joomla Bamboo - design@joomlabamboo.com
 * @copyright 	Copyright (c) 2013 Joomla Bamboo. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @version		2.1.4
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ROOT . '/media/plg_jblibrary/includes/defines.php';

// TODO: Update all JB extensions and remove this file
//Resize image options: exact, portrait, landscape, auto, crop, topleft, center
JLoader::import('zen.image.resizer', ZEN_LIBRARY_PATH);
class resizeImageHelper extends ZenImageResizer {}
