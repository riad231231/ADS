<?php
/**
* @package   Pixel Point Creative  | Adroit
* @author    Pixel Point Creative http://www.pixelpointcreative.com
* @copyright Copyright (C) Pixel Point Creative, LLC
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

require_once(dirname(__FILE__)."/warp/warp.php");

$warp = Warp::getInstance();

// add paths
$warp['path']->register(dirname(__FILE__).'/warp/systems/joomla/helpers','helpers');
$warp['path']->register(dirname(__FILE__).'/warp/systems/joomla/layouts','layouts');
$warp['path']->register(dirname(__FILE__).'/layouts','layouts');
$warp['path']->register(dirname(__FILE__).'/js', 'js');
$warp['path']->register(dirname(__FILE__).'/css', 'css');

// init system
$warp['system']->init();