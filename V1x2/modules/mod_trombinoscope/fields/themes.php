<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

class JFormFieldThemes extends JFormField {
		
	public $type = 'Themes';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		
		$html = '';
		
		$type = strtolower($this->type);
		
 		ob_start();
 			require dirname(__FILE__).'/'.$type.'/tmpl/default.php';
			$html .= ob_get_contents();
 		ob_end_clean();
 		
 		return $html;
	}

}
?>