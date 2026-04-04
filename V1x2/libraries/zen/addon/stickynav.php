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

class ZenAddonStickynav extends ZenAddonBase
{
	public static function getScript($stickynavThreshold = 200)
	{
		return '
			jQuery(window).scroll(function(e){
				el = jQuery("#navwrap"); // element you want to scroll
				var navHeight = jQuery(el).height();

				jQueryscrolling = 0; // Position you want element to assume during scroll
				jQuerybounds = ' . (int) $stickynavThreshold . '; // boundary of when to change element to fixed and scroll

				if (jQuery(this).scrollTop() > jQuerybounds && el.css("position") != "fixed") {
					jQuery(el).css({"position": "fixed", "top": jQueryscrolling, "display": "none"}).addClass("sticky").fadeIn("slow");
					jQuery("body").addClass("sticky");

					jQuery("body").prepend("<div id=\"stickyreplace\"></div>");
					jQuery("#stickyreplace").height(navHeight);
				}
				if (jQuery(this).scrollTop() < jQuerybounds && el.css("position") != "absolute") {
					jQuery(el).css({"position": "relative", "top": "0px"}).removeClass("sticky");
					jQuery("body").removeClass("sticky");
					jQuery("#stickyreplace").remove();
				}
			});
		';
	}
}
