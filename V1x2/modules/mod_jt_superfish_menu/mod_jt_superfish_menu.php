<?php
/*
# ------------------------------------------------------------------------
# Templates for Joomla 2.5 / Joomla 3.x
# ------------------------------------------------------------------------
# Copyright (C) 2011-2013 Jtemplate.ru. All Rights Reserved.
# @license - PHP files are GNU/GPL V2.
# Author: Makeev Vladimir
# Websites:  http://www.jtemplate.ru/en 
# ---------  http://code.google.com/p/jtemplate/   
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die;

require_once (JPATH_SITE.'/modules/mod_menu/helper.php'); 

$list		= modMenuHelper::getList($params);
$active		= modMenuHelper::getActive($params);
$active_id 	= $active->id;
$path		= $active->tree;
$showAll	= $params->get('showAllChildren');
$class_sfx	= htmlspecialchars($params->get('class_sfx'));

$jt_style_menu	= $params->get('stylemenu');
$jt_menu		= $params->get('jtmenu');
$animation		= $params->get('animation');
$delay			= $params->get('delay');
$speed			= $params->get('speed');
$autoarrows		= $params->get('autoarrows');

if(count($list)) {
	require JModuleHelper::getLayoutPath('mod_jt_superfish_menu', $params->get('layout', 'default'));
	echo JText::_(MOD_JT);
}
