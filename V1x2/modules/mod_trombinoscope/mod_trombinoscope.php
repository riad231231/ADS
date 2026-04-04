<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once (dirname(__FILE__).'/helper.php');
jimport('syw.fonts');

// tests if the module is part of the component package or is just a standalone
$standalone = false;
jimport('joomla.filesystem.folder');
$folder = JPATH_ROOT.'/components/com_trombinoscopeextended/views/trombinoscope';
if (!JFolder::exists($folder)) {
	$standalone = true;
}

$list = modTrombinoscopeHelper::getContacts($params, $module);

if (empty($list)) {
	return;
}

$class_suffix = $module->id;

$urlPath = JURI::base().'modules/mod_trombinoscope/';
$doc = JFactory::getDocument();
$app = JFactory::getApplication();

$user = JFactory::getUser();
$groups	= $user->getAuthorisedViewLevels();

$globalparams = new JRegistry();

// Get the global parameters
$globalcontactparams = JComponentHelper::getParams('com_contact');

$globalparams->set("linka_name", $globalcontactparams->get('linka_name'));
$globalparams->set("linkb_name", $globalcontactparams->get('linkb_name'));
$globalparams->set("linkc_name", $globalcontactparams->get('linkc_name'));
$globalparams->set("linkd_name", $globalcontactparams->get('linkd_name'));
$globalparams->set("linke_name", $globalcontactparams->get('linke_name'));

$globalparams->set("default_image", $globalcontactparams->get('image'));

$show_errors = $params->get('show_errors', 0);

$photo_align = $params->get('pic_a');

$default_picture = $params->get('d_pic', '');
$keep_space = $params->get('k_s', true);
$contact_link = $params->get('l_to_c', 'none');
$link_access = $params->get('link_access', '1');
$generic_link = $params->get('generic_l', '');

$popup_x = $params->get('popup_x', '600');
$popup_y = $params->get('popup_y', '500');

$link_label = $params->get('l_lbl', '');
$link_to_edit = $params->get('l_to_edit', false);
$edit_link_label = $params->get('editl_lbl', '');

$format_style = $params->get('name_fmt', 'none');
$uppercase = $params->get('name_upper', 0);
$force_one_line = $params->get('force_one_line', 1);
$pre_name = $params->get('s_name_lbl', 0);
$name_label = $params->get('name_lbl', '');

$show_allfields_label = $params->get('s_f_lbl', 0);

$label_width = $params->get('lbl_w', '0');
$label_separator = $params->get('lbl_separator', '');

$show_field1_label = $params->get('s_f1_lbl', 0);
$show_field2_label = $params->get('s_f2_lbl', 0);
$show_field3_label = $params->get('s_f3_lbl', 0);
$show_field4_label = $params->get('s_f4_lbl', 0);
$show_field5_label = $params->get('s_f5_lbl', 0);
$show_field6_label = $params->get('s_f6_lbl', 0);
$show_field7_label = $params->get('s_f7_lbl', 0);

$theme = $params->get('theme', 'original');

$font_size_reference = $params->get('font_s', '14');
$iconfont_size = $params->get('ifont_s', '1');
$iconfont_color = str_replace('#', '', trim($params->get('ifont_c', '#000000')));

$bg_image = $params->get('bgimage', '');
$bg_color1 = str_replace('#', '', trim($params->get('bgcolor1', '')));
$bg_color2 = str_replace('#', '', trim($params->get('bgcolor2', '')));
$font_color = str_replace('#', '', trim($params->get('fontcolor', '')));

$style_social_icons = $params->get('social_styling', false);

$card_width = $params->get('card_w', 100);
$card_width_unit = $params->get('card_w_u', '%');
$card_height = $params->get('card_h', '');
$picture_width = $params->get('pic_w', 100);
$picture_height = $params->get('pic_h', 120);
$border_width = $params->get('border_w', 0);

$picture_width = $picture_width - $border_width * 2;
$picture_height = $picture_height - $border_width * 2;

$picture_bgcolor = str_replace('#', '', trim($params->get('pic_bgcolor', '')));
//$float = $params->get('float', true);
$picture_shadow = $params->get('pic_shadow', false);
$picture_hover_type = $params->get('pic_hover_type', 'none');

$show_picture = $params->get('s_pic', true);
$keep_picture_space = $params->get('k_pic_s', true);
$overflow = $params->get('overflow', false);

$show_vcard = $params->get('s_v', false);
$vcard_type = $params->get('vcard_type', 'p');

$show_featured = $params->get('s_f', false);

$crop_picture = $params->get('crop_pic', 0);
$clear_cache = $params->get('clear_cache', 1);
$tmp_path = str_replace(JPATH_ROOT.'/', '', $app->getCfg('tmp_path')); // to get just 'tmp' for instance
$filter = $params->get('filter', 'none');

$category_showing = $params->get('s_cat', 'sl');
$cat_view_id = $params->get('cat_views', '');
$show_category = false;
$link_to_category = false;
$link_to_view_category = false;
switch ($category_showing) {
	case 's' :
		$show_category = true;
		break;
	case 'sl' :
		$show_category = true;
		$link_to_category = true;
		break;
	case 'sv' :
		$show_category = true;
		$link_to_view_category = true;
		break;
	default :
		break;
}

$tags_showing = $params->get('s_tag', 'h');
$tags_view_id = $params->get('tag_views', '');
$show_tags = false;
$link_to_tags = false;
$link_to_view_tags = false;
switch ($tags_showing) {
	case 's' :
		$show_tags = true;
		break;
	case 'sl' :
		$show_tags = true;
		$link_to_tags = true;
		break;
	case 'sv' :
		$show_tags = true;
		$link_to_view_tags = true;
		break;
	default :
		break;
}

$header_showing = $params->get('s_h', 'h');
$header_html_tag = $params->get('h_tag', '4');
$header_view_id = $params->get('header_views', '');
$show_category_header = false;
$link_to_category_header = false;
$link_to_view_category_header = false;
switch ($header_showing) {
	case 'sc' :
		$show_category_header = true;
		break;
	case 'slc' :
		$show_category_header = true;
		$link_to_category_header = true;
		break;
	case 'svc' :
		$show_category_header = true;
		$link_to_view_category_header = true;
		break;
	default :
		break;
}

$cat_order = $params->get('c_order', '');

$field1 = $params->get('f1', 'none');
$field2 = $params->get('f2', 'none');
$field3 = $params->get('f3', 'none');
$field4 = $params->get('f4', 'none');
$field5 = $params->get('f5', 'none');
$field6 = $params->get('f6', 'none');
$field7 = $params->get('f7', 'none');

$linkfield1 = $params->get('lf1', 'none');
$linkfield2 = $params->get('lf2', 'none');
$linkfield3 = $params->get('lf3', 'none');
$linkfield4 = $params->get('lf4', 'none');
$linkfield5 = $params->get('lf5', 'none');

$show_links = true;
if ($linkfield1 == 'none' && $linkfield2 == 'none' && $linkfield3 == 'none' && $linkfield4 == 'none' && $linkfield5 == 'none') {
	$show_links = false;
}

$field1_label = $params->get('f1_lbl', '');
$field2_label = $params->get('f2_lbl', '');
$field3_label = $params->get('f3_lbl', '');
$field4_label = $params->get('f4_lbl', '');
$field5_label = $params->get('f5_lbl', '');
$field6_label = $params->get('f6_lbl', '');
$field7_label = $params->get('f7_lbl', '');

$field1_access = $params->get('f1_access', '1');
$field2_access = $params->get('f2_access', '1');
$field3_access = $params->get('f3_access', '1');
$field4_access = $params->get('f4_access', '1');
$field5_access = $params->get('f5_access', '1');
$field6_access = $params->get('f6_access', '1');
$field7_access = $params->get('f7_access', '1');

$linkfield1_access = $params->get('lf1_access', '1');
$linkfield2_access = $params->get('lf2_access', '1');
$linkfield3_access = $params->get('lf3_access', '1');
$linkfield4_access = $params->get('lf4_access', '1');
$linkfield5_access = $params->get('lf5_access', '1');

$letter_count = $params->get('l_c', '');
$strip_tags = $params->get('s_t', 1);
$keep_tags = $params->get('keep_tags');

$link_email = $params->get('link_e', 1);
$cloak_email = $params->get('cloak_e', 1);
$email_substitut = $params->get('e_substitut', '');
$webpage_substitut = $params->get('w_substitut', '');
$protocol = $params->get('protocol', true);

$address_format = $params->get('a_fmt', 'ssz');
$address_link_with_map = $params->get('a_link_map', false);
$auto_map_params = trim($params->get('auto_map_params', ''));
$birthdate_format = $params->get('dob_format', 'F d');

$trombparams = new JRegistry();
$trombparams->set('protocol', $protocol);
$trombparams->set('keep_space', $keep_space);
$trombparams->set('link_email', $link_email);
$trombparams->set('cloak_email', $cloak_email);
$trombparams->set('email_substitut', $email_substitut);
$trombparams->set('webpage_substitut', $webpage_substitut);
$trombparams->set('letter_count', $letter_count);
$trombparams->set('strip_tags', $strip_tags);
$trombparams->set('keep_tags', $keep_tags);
$trombparams->set('all_pre', $show_allfields_label);
$trombparams->set('label_separator', $label_separator);
$trombparams->set('birthdate_format', $birthdate_format);
$trombparams->set('link_address_with_map', $address_link_with_map);
$trombparams->set('auto_map_params', $auto_map_params);

// load icon font
SYWFonts::loadIconFont();

// carousel

$arrow_class = '';
$show_arrows = false;

$arrow_prev_left = false;
$arrow_next_right = false;
$arrow_prev_top = false;
$arrow_next_bottom = false;
$arrow_prevnext_bottom = false;

$carousel_configuration = $params->get('carousel_config', 'none');
if ($carousel_configuration != 'none') {

	jimport('syw.libraries');

	JHtml::_('jquery.framework');

	SYWLibraries::loadCarousel();

	$horizontal = false;
	if ($carousel_configuration == 'h') {
		$horizontal = true;
	}

	$visible_items = trim($params->get('visible_items', ''));
	if (!$horizontal && empty($visible_items)) {
		$visible_items = 1;
	}

	$direction = 'left';
	if (!$horizontal) {
		$direction = 'up';
	}

	$move_at_once = $params->get('moveatonce', 'all');
	if ($move_at_once == 'all') {
		$move_at_once = $visible_items;
	} else {
		$move_at_once = 1;
	}

	$arrows = $params->get('arrows', 'none');

	switch ($arrows) {
		case 'around':
			$show_arrows = true;
			if ($horizontal) {
				$arrow_class = ' side_navigation';
				$arrow_prev_left = true;
				$arrow_next_right = true;
			} else {
				$arrow_class = ' above_navigation';
				$arrow_prev_top = true;
				$arrow_next_bottom = true;
			}
			break;
		case 'under':
			$arrow_class = ' under_navigation';
			$show_arrows = true;
			$arrow_prevnext_bottom = true;
			break;
		case 'title':
			$show_arrows = true;
			break;
	}

	$arrow_size = $params->get('arrowsize', 1);
	$arrow_offset = $params->get('arrowoffset', 0);

	$auto = $params->get('auto', 1);
	$speed = $params->get('speed', 1000);
	$interval = $params->get('interval', 3000);

	$request_path = 'jQuery(document).ready(function($) {';	
	
	$request_path .= '$(".te_'.$class_suffix.' .personlist").carouFredSel({';

	$request_path .= 'direction: "'.$direction.'",';
	if ($horizontal) {
		$request_path .= 'height: "auto",';
		$request_path .= 'width: "100%",';
	} else {
		$request_path .= 'height: "variable",';
		$request_path .= 'width: "100%",';
	}

	//$request_path .= 'padding: [0, 50],'; // does not work

	if ($show_arrows) {
		$request_path .= 'prev: "#prev_'.$class_suffix.'",';
		$request_path .= 'next: "#next_'.$class_suffix.'",';
	}

	$request_path .= 'items: {';
	if ($horizontal) {		
		$request_path .= 'width: "'.$card_width.'px",';
		$request_path .= 'height: "100%",';
		if (empty($visible_items)) {
			$request_path .= 'visible: "variable"';
		} else {
			$request_path .= 'visible: {';
			$request_path .= 'min: 1,';
			$request_path .= 'max: '.$visible_items;
			$request_path .= '}';
		}
	} else {
		$request_path .= 'width: "variable",';
		$request_path .= 'height: "variable",';
		$request_path .= 'visible: '.$visible_items.',';
		$request_path .= 'minimum: 1';
	}
	$request_path .= '},';

	$request_path .= 'scroll: {';
	if (!empty($move_at_once)) {
		$request_path .= 'items: '.$move_at_once.',';
	}
	$request_path .= 'fx: "scroll",';
	$request_path .= 'duration: '.$speed.',';
	$request_path .= 'pauseOnHover: true';
	$request_path .= '},';

	$request_path .= 'auto: {';
	if (!$auto) {
		$request_path .= 'play: false,';
	}
	$request_path .= 'timeoutDuration: '.$interval;
	$request_path .= '},';

	$request_path .= 'cookie: ".te_'.$class_suffix.'",';

	$request_path .= 'swipe: {';
	$request_path .= 'onTouch: true,';
	$request_path .= 'onMouse: true';
	$request_path .= '}';
		
	$request_path .= '}, {';

	$request_path .= 'wrapper: {';
	$request_path .= 'element: "div",';
	$request_path .= 'classname: "carousel_wrapper"';
	$request_path .= '}';

	$request_path .= '});';

	$request_path .= '});';

	$doc->addScriptDeclaration($request_path);
}

// styles

$request_path = $urlPath.'themes/stylemaster.css.php?security='.defined('_JEXEC').'&amp;suffix='.$class_suffix;
$request_path .= '&amp;card_w='.$card_width.'&amp;card_h='.$card_height;
$request_path .= '&amp;force_n='.$force_one_line;
if ($card_width_unit != '%') {
	$request_path .= '&amp;card_w_u='.$card_width_unit;
}
if ($show_links) {
	$request_path .= '&amp;links=1';
}
if ($show_vcard) {
	$request_path .= '&amp;vcard=1';
}
//$request_path .= '&amp;float='.$float;
if ($show_picture) {
	$request_path .= '&amp;s_pic=1&amp;pic_w='.$picture_width.'&amp;pic_h='.$picture_height.'&amp;border_w='.$border_width;
	if ($picture_shadow) {
		$request_path .= '&amp;pic_s=1';
	}
	if (!empty($picture_bgcolor) && $picture_bgcolor != 'transparent') {
		$request_path .= '&amp;pic_bgc='.$picture_bgcolor;
	}
	if ($overflow) {
		$request_path .= '&amp;flow=1';
	}
}
$request_path .= '&amp;fs='.$font_size_reference;
if (!empty($bg_image)) {
	$request_path .= '&amp;bgimage='.$bg_image;
}
if (!empty($bg_color1) && $bg_color1 != 'transparent') {
	$request_path .= '&amp;bgc1='.$bg_color1;
}
if (!empty($bg_color2) && $bg_color2 != 'transparent') {
	$request_path .= '&amp;bgc2='.$bg_color2;
}
if (!empty($font_color) && $font_color != 'transparent') {
	$request_path .= '&amp;fc='.$font_color;
}
if ($label_width > 0) {
	$request_path .= '&amp;l_w='.$label_width;
}
$request_path .= '&amp;ifs='.$iconfont_size.'&amp;ifc='.$iconfont_color;

if ($carousel_configuration != 'none') {
	if ($arrow_size != 1) {
		$request_path .= '&amp;as='.$arrow_size;
	}
	if ($arrow_offset > 0) {
		$request_path .= '&amp;ao='.$arrow_offset;
	}
}

$request_path .= '&amp;theme='.$theme;

$user_styles = trim($params->get('style_overrides', ''));
if (!empty($user_styles)) {
	$user_styles = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $user_styles); // minify the CSS code
}

// caching of the stylesheet. If success, includes user style overrides

$clear_css_cache = $params->get('clear_css_cache', true);
$trouble_in_paradise = false;

if ($clear_css_cache) {
	
	$css_file = htmlspecialchars_decode($request_path); // replace &amp; with &
	$css_content = modTrombinoscopeHelper::getFileContent($css_file);
			
	if ($css_content != false) {
		$result = file_put_contents(JPATH_ROOT.'/modules/mod_trombinoscope/themes/stylemaster_'.$module->id.'.css', $css_content);
		if ($result === false) {
			$trouble_in_paradise = true;
		} else {
			if (!empty($user_styles)) {
				$result = file_put_contents(JPATH_ROOT.'/modules/mod_trombinoscope/themes/stylemaster_'.$module->id.'.css', $user_styles, FILE_APPEND);
				if ($result === false) {
					$trouble_in_paradise = true;
				}
			}
		}
	} else {
		$trouble_in_paradise = true;
	}

} else {
	if (!JFile::exists(JPATH_ROOT.'/modules/mod_trombinoscope/themes/stylemaster_'.$module->id.'.css')) {
		$trouble_in_paradise = true;
	}
}

if ($trouble_in_paradise) {
	$doc->addStyleSheet($request_path);
	if (!empty($user_styles)) {
		$doc->addStyleDeclaration($user_styles);
	}
} else {
	$doc->addStyleSheet(JURI::base(true).'/modules/mod_trombinoscope/themes/stylemaster_'.$module->id.'.css');
}

// adding responsiveness...

$min_card_width = $params->get('min_card_w', '');
$max_card_width = $params->get('max_card_w', '');
if ($card_width_unit == '%' && !empty($min_card_width)) { // and there is a min/max width

	JHtml::_('jquery.framework');

	$request_path = $urlPath.'themes/stylemaster.js.php?security='.defined('_JEXEC').'&amp;suffix='.$class_suffix;
	$request_path .= '&amp;card_w='.$card_width;
	$request_path .= '&amp;min_w='.$min_card_width;
	if (!empty($max_card_width)) {
		$request_path .= '&amp;max_w='.$max_card_width;
	}
	
	// caching the javascript

	$trouble_in_paradise = false;
	
	if ($clear_css_cache) {
	
		$js_file = htmlspecialchars_decode($request_path); // replace &amp; with &
		$js_content = modTrombinoscopeHelper::getFileContent($js_file);
	
		if ($js_content != false) {
			$result = file_put_contents(JPATH_ROOT.'/modules/mod_trombinoscope/themes/stylemaster_'.$module->id.'.js', $js_content);
			if ($result === false) {
				$trouble_in_paradise = true;
			}
		} else {
			$trouble_in_paradise = true;
		}
	
	} else {
		if (!JFile::exists(JPATH_ROOT.'/modules/mod_trombinoscope/themes/stylemaster_'.$module->id.'.js')) {
			$trouble_in_paradise = true;
		}
	}
	
	if ($trouble_in_paradise) {
		$doc->addScript($request_path);
	} else {
		$doc->addScript(JURI::base(true).'/modules/mod_trombinoscope/themes/stylemaster_'.$module->id.'.js');
	}
}

require(JModuleHelper::getLayoutPath('mod_trombinoscope', $params->get('layout', 'default')));
?>
