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

class ZenAddonScrollbottom extends ZenAddonBase
{
	public static $scrollIncompatible = array('ie6', 'iphone', 'ipod', 'ipad', 'blackberry', 'palmos', 'android');

	public static function getScript()
	{
		return '
			jQuery(document).ready(function(){
				jQuery("a.scroll").click(function() {
					if (location.pathname.replace(/^\//, "") == this.pathname.replace(/^\//, "")
						&& location.hostname == this.hostname) {
			var jQuerytarget = jQuery(this.hash);
			jQuerytarget = jQuerytarget.length && jQuerytarget || jQuery("[name=\" + this.hash.slice(1) +\"]");
			if (jQuerytarget.length) {
				var targetOffset = jQuerytarget.offset().top;
				jQuery("html, body").animate({scrollTop: targetOffset}, 1000);

				return false;
			}
		}
		});
		jQuery(window).scroll(function () {
			if (jQuery(this).scrollTop() != 0) {
				jQuery(".scroll").fadeOut();
			} else {
				jQuery(".scroll").fadeIn();
			}
		});
		});
		';
	}
}
