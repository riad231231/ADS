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

class ZenFormFieldDescription extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	public $type = 'Description';

	/**
	 * Method to get the field input.
	 *
	 * @return  string   The field input.
	 * @since   1.0.0
	 */
	public function getInput()
	{
		return '<div class="alert">' . JText::_($value) . '</div>';
	}
}
