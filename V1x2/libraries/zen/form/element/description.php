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

/**
 * Renders a description element with a message
 *
 * @package		Zen Library
 * @subpackage	Zen Library
 * @since		1.0.0
 */

class ZenFormElementDescription extends JElement
{
	/**
	* Element name
	*
	* @access	public
	* @var		string
	*/
	public $_name = 'description';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 * @since	1.0.0
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		return '<div class="alert">' . JText::_($value) . '</div>';
	}
}
