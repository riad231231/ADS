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

class ZenUtilityURI
{
	public static function isExternalPath($path = '')
	{
		if (!empty($path))
		{
			return substr($path, 0, 4) === 'http';
		}

		return false;
	}
}
