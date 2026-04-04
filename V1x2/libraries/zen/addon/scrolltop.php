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

JLoader::import('zen.addon.base', ZEN_LIBRARY_PATH);

class ZenAddonScrolltop extends ZenAddonBase
{
	private static $incompatibleBrowsers = array('ie6', 'iphone', 'ipod', 'ipad', 'blackberry', 'palmos', 'android');

	public static function addStyle($style = 'dark')
	{
		if ($style === 'dark')
		{
			$background = '#121212';
			$color = '#fff';
		}
		elseif ($style === 'light')
		{
			$background = '#f7f7f7';
			$color = '#333';
		}

		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('#toTop {width:100px;z-index: 10;border: 1px solid #333; background:' . $background . '; text-align:center; padding:5px; position:fixed; bottom:0px; right:0px; cursor:pointer; display:none; color:' . $color . ';text-transform: lowercase; font-size: 0.7em;}');
	}

	public static function getScript($scrollTopText = '^')
	{
		return '
			jQuery(document).ready(function() {
				jQuery(function () {
					var scrollDiv = document.createElement("div");
					jQuery(scrollDiv).attr("id", "toTop").html("' . $scrollTopText . '").appendTo("body");
					jQuery(window).scroll(function () {
						if (jQuery(this).scrollTop() != 0) {
							jQuery("#toTop").fadeIn();
						} else {
							jQuery("#toTop").fadeOut();
						}
					});
					jQuery("#toTop").click(function () {
						jQuery("body,html").animate({
							scrollTop: 0
						},
						800);
					});
				});
			});
		';
	}
}
