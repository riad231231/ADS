<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die;

$lang = JFactory::getLanguage();

$path = '/modules/mod_trombinoscope/themes';

$folders = JFolder::folders(JPATH_SITE.$path);

foreach($folders as $folder) { 
	$upper_folder = strtoupper($folder);
	
	$lang->load('com_trombinoscopeextended_theme_'.$folder); // com_ is not a mistake
	
	$translated_folder = JText::_('MOD_TROMBINOSCOPE_THEME_'.$upper_folder.'_LABEL');

	if (empty($translated_folder) || substr_count($translated_folder, 'TROMBINOSCOPE') > 0) {
		$translated_folder = ucfirst($folder);
	}
	
	$styles[$folder] = array($translated_folder);	
}
?>
	
<div style="max-height: 190px; overflow: auto; background-color: #F4F4F4">
	<?php foreach($styles as $style => $style_array): ?>
		<div style="display: inline-block; float: left; height: 148px; margin: 5px; padding: 15px; border: 1px solid #CCC; text-align: center; background-color: #FFF; position: relative">
			<img src="<?php echo JURI::root(true).$path ?>/<?php echo $style ?>/images/<?php echo $style ?>_card_landscape.png" style="margin-bottom: 10px; max-height: 110px; max-width: 180px" />
			<p style="position: absolute; left: 0; bottom: 0; width: 100%"><?php echo $style_array[0] ?></p>
		</div>
	<?php endforeach; ?>
</div>