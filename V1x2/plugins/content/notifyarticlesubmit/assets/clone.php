<?php
/**
 * NofifyArticleSubmit Clone
 *
 * @package		NofifyArticleSubmit
 * @author Gruz <arygroup@gmail.com>
 * @copyright	Copyleft - All rights reversed
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
$plg_type = 'content';
$plg_name = 'notifyarticlesubmit';

if(version_compare(JVERSION,'1.6.0','ge')) {
	$path = JPATH_PLUGINS.'/'.$plg_type.'/'.$plg_name.'/'.$plg_name.'.php';
}
else {
	$path = JPATH_PLUGINS.'/'.$plg_type.'/'.$plg_name.'.php';
}

if (class_exists('plgContentNotifyarticlesubmitCore') || file_exists($path)) {
	if (!class_exists('plgContentNotifyarticlesubmitCore') ) { require_once ($path); }
	class plgContentNotifyarticlesubmitClonePrototype extends plgContentNotifyarticlesubmitCore
	{
		 public function __construct(& $subject, $config) {
			parent::__construct($subject, $config);
		}

	}
}
