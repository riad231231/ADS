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

JLoader::import('zen.form.field.description', ZEN_LIBRARY_PATH);

class JFormFieldDescription extends ZenFormFieldDescription {}
