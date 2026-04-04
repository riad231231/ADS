<?php
/**
* @package   Pixel Point Creative  | Adroit
* @author    Pixel Point Creative http://www.pixelpointcreative.com
* @copyright Copyright (C) Pixel Point Creative, LLC
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// include config	
include_once(dirname(__FILE__).'/config.php');

// get warp
$warp = Warp::getInstance();

// load main template file, located in /layouts/template.php
echo $warp['template']->render('template');