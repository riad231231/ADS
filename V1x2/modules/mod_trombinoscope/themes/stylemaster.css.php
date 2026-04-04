<?php 
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

$security = 0;
if (isset($_GET["$security"])) {
	$security = $_GET['security'];
}

define('_JEXEC', $security);

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
header("Content-type: text/css; charset=UTF-8");
    
// Grab module id from the request
$suffix = $_GET['suffix']; 

$show_links = false;
if (isset($_GET['links'])) {
	$show_links = true;
}

$show_vcard = false;
if (isset($_GET['vcard'])) {
	$show_vcard = true;
}

$card_width = 100;
if (isset($_GET['card_w'])) {
	$card_width = (int)$_GET['card_w'];
}

$card_width_unit = '%';
if (isset($_GET['card_w_u'])) {
	$card_width_unit = $_GET['card_w_u'];
}

$card_height = 0;
if (isset($_GET['card_h'])) {
	$card_height = (int)$_GET['card_h'];
}

$border_width = 0;
if (isset($_GET['border_w'])) {
	$border_width = (int)$_GET['border_w'];
}

$picture_width = 0;
if (isset($_GET['pic_w'])) {
	$picture_width = (int)$_GET['pic_w'];
}

$picture_height = 0;
if (isset($_GET['pic_h'])) {
	$picture_height = (int)$_GET['pic_h'];
}

$show_picture = false;
if (isset($_GET['s_pic'])) {
	$show_picture = (bool)$_GET['s_pic'];
}

$show_text = true;

$force_one_line = true;
if (isset($_GET['force_n'])) {
	$force_one_line = (bool)$_GET['force_n'];
}

//$float = false;
//if (isset($_GET['float'])) {
	//$float = (bool)$_GET['float'];
//}

$overflow = false;
if (isset($_GET['flow'])) {
	$overflow = true;
}

$label_width = 0;
if (isset($_GET['l_w'])) {
	$label_width = (int)$_GET['l_w'];
}

$bgimage = '';
if (isset($_GET['bgimage'])) {
	$bgimage = $_GET['bgimage'];
}

$bgcolor1 = '';
if (isset($_GET['bgc1'])) {
	$bgcolor1 = '#'.$_GET['bgc1'];
}

$bgcolor2 = '';
if (isset($_GET['bgc2'])) {
	$bgcolor2 = '#'.$_GET['bgc2'];
}

$picture_shadow = false;
if (isset($_GET['pic_s'])) {
	$picture_shadow = true;
}

$pic_bgcolor = 'transparent';
if (isset($_GET['pic_bgc'])) {
	$pic_bgcolor = '#'.$_GET['pic_bgc'];
}

$fontcolor = '';
if (isset($_GET['fc'])) {
	$fontcolor = '#'.$_GET['fc'];
}

$font_size = '14';
if (isset($_GET['fs'])) {
	$font_size = $_GET['fs'];
}

$iconfont_size = '1';
if (isset($_GET['ifs'])) {
	$iconfont_size = $_GET['ifs'];
}

$iconfont_color = '#000000';
if (isset($_GET['ifc'])) {
	$iconfont_color = '#'.$_GET['ifc'];
}

$arrow_size = 1;
if (isset($_GET['as'])) {
	$arrow_size = $_GET['as'];
}

$arrow_offset = 0;
if (isset($_GET['ao'])) {
	$arrow_offset = $_GET['ao'];
}

$theme = $_GET['theme'];

// calculated variables

// calculate margins if card width is in %
$margin_in_perc = 0;
if ($card_width_unit == '%') {
	$cards_per_row = (int)(100 / $card_width);
	$left_for_margins = 100 - ($cards_per_row * $card_width);
	$margin_in_perc = $left_for_margins / ($cards_per_row * 2);
}

$links = array();

$links[] = 'style.css.php';
$links[] = $theme.'/style.css.php';
  
function compress($buffer) {
	/* remove comments */
	$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
	/* remove tabs, spaces, newlines, etc. */
	$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
	return $buffer;
}

ob_start("compress");
	
foreach ($links as $link) {
	include $link;
}

ob_end_flush();
?>