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

// render offline layout
echo $warp['template']->render('offline', array('title' => JText::_('TPL_WARP_OFFLINE_PAGE_TITLE'), 'error' => 'Offline', 'message' => JText::_('TPL_WARP_OFFLINE_PAGE_MESSAGE')));