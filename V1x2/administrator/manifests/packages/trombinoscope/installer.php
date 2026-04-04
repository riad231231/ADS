<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Script file for the Trombinoscope Extended Free module package
 */
class pkg_trombinoscopeInstallerScript
{	
	static $minimum_needed_library_version = '1.2.0';
	static $download_link = 'http://www.simplifyyourweb.com/index.php/downloads/category/23-libraries';
	
	/**
	 * Called before an install/update/uninstall method
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent) 
	{ 
		echo '<br />';
	}
	
	/**
	 * Called after an install/update/uninstall method
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent) 
	{	
		if ($type != 'uninstall') {
			
			echo '<div style="padding: 0 0 20px 0; text-align: center">';
			echo '<img src="../modules/mod_trombinoscope/images/logo_free_module.png" />';
			echo '<br /><br />'.JText::_('PKG_TROMBINOSCOPE_VERSION');
			echo '<br /><br />Olivier Buisard @ <a href="http://www.simplifyyourweb.com" target="_blank">Simplify Your Web</a>';
			echo '</div>';
			
			// warn about release
			
			echo '<div class="alert alert-warning">';
			echo '    <span>'.JText::_('PKG_TROMBINOSCOPE_WARNING_RELEASENOTES').'</span>';
			echo '</div>';
			
			// move default silhouettes to /images
			
			$imagefiles = array();
			$imagefiles[] = 'no-image-available-100x120.jpg';
			$imagefiles[] = 'no-photo-86x110.jpg';
			$imagefiles[] = 'silhouette-100x120.jpg';
			$imagefiles[] = 'silhouette-transparent-100x120.png';
				
			foreach ($imagefiles as $imagefile) {
				$src = JPATH_ROOT.'/modules/mod_trombinoscope/images/'.$imagefile;
				$dest = JPATH_ROOT.'/images/'.$imagefile;
			
				if (!JFile::copy($src, $dest)) {
					echo '<div class="alert alert-warning">';
					echo '    <span>'.JText::sprintf('PKG_TROMBINOSCOPE_WARNING_COULDNOTCOPYFILE', $imagefile).'</span>';
					echo '</div>';
				}
			}
			
			// check if syw library is present
			
			if (!JFolder::exists(JPATH_ROOT.'/libraries/syw')) {
				echo '<div class="alert alert-warning">';
				echo '    <span>'.JText::_('PKG_TROMBINOSCOPE_MISSING_SYWLIBRARY').'</span><br />';
				echo '    <a href="'.self::$download_link.'" target="_blank">'.JText::_('PKG_TROMBINOSCOPE_DOWNLOAD_SYWLIBRARY').'</a>';
				echo '</div>';
			} else {
				jimport('syw.version');
				if (SYWVersion::isCompatible(self::$minimum_needed_library_version)) {
					echo '<div class="alert alert-success">';
					echo '    <span>'.JText::_('PKG_TROMBINOSCOPE_COMPATIBLE_SYWLIBRARY').'</span>';
					echo '</div>';
				} else {
					echo '<div class="alert alert-warning">';
					echo '    <span>'.JText::_('PKG_TROMBINOSCOPE_NONCOMPATIBLE_SYWLIBRARY').'</span><br />';
					echo '    <span>'.JText::_('PKG_TROMBINOSCOPE_UPDATE_SYWLIBRARY').JText::_('PKG_TROMBINOSCOPE_OR').'</span>';
					echo '    <a href="'.self::$download_link.'" target="_blank">'.strtolower(JText::_('PKG_TROMBINOSCOPE_DOWNLOAD_SYWLIBRARY')).'</a>';
					echo '</div>';
				}
			}
		}
		
		return true;
	}	
	
	/**
	 * Called on installation
	 *
	 * @return  boolean  True on success
	 */
	public function install($parent) { }
	
	/**
	 * Called on update
	 *
	 * @return  boolean  True on success
	 */
	public function update($parent) { }
	
	/**
	 * Called on uninstallation
	 */
	public function uninstall($parent) { }
}
?>