<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	System.Jblibrary
 * @author		Joomla Bamboo - design@joomlabamboo.com
 * @copyright 	Copyright (c) 2013 Joomla Bamboo. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @version		2.1.4
 */
/** Thanks to onejQuery for being the inspiration of our unique jQuery function **/
/** ensure this file is being included by a parent file */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

if (version_compare(JVERSION, '3.0'))
{
	jimport('joomla.filesystem.folder');
}

require_once JPATH_ROOT . '/media/plg_jblibrary/includes/defines.php';

JLoader::import('zen.utility.browser', ZEN_LIBRARY_PATH);
JLoader::import('zen.script.handler', ZEN_LIBRARY_PATH);
JLoader::import('zen.utility.uri', ZEN_LIBRARY_PATH);

JLoader::import('zen.utility.benchmark', ZEN_LIBRARY_PATH);

/**
 * JB Library System Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	System.Jblibrary
 * @since       1.0
 */
class plgSystemJblibrary extends JPlugin
{
	public function __construct(&$subject, $config)
	{
		// Stop plugin if app is administrator
		if (JFactory::getApplication()->isAdmin()) return;

		parent::__construct($subject, $config);

		$this->loadLanguage();
	}

	public function onBeforeRender()
	{
		$browser = ZenUtilityBrowser::getInstance();
		if ((bool) $this->params->get('scrollTop', 1) && !$browser->isIE6)
		{
			$scrollTopStyle = $this->params->get('scrollStyle', 'dark');

			JLoader::import('zen.addon.scrolltop', ZEN_LIBRARY_PATH);
			ZenAddonScrolltop::addStyle($scrollTopStyle);
		}
	}

	public function onAfterRender()
	{
		// ZenUtilityBenchmark::start('onAfterRender');
		$browser = ZenUtilityBrowser::getInstance();

		$options = new stdClass;
		$options->useScriptLoader         = (bool)$this->params->get('usescriptloader', 0);
		$options->moveScriptsToBottom     = (bool)$this->params->get('moveScriptsToBottom', 0);
		$options->jQueryLoad              = (bool)$this->params->get('loadJQuery', 1);
		$options->jQuerySource            = $this->params->get('source', 'google');
		$options->jQueryVersion           = $this->params->get('jQueryVersion');
		$options->jQueryNoConflict        = (bool)$this->params->get('noConflict', 1);
		$options->jQueryRemoveOther       = (bool)$this->params->get('jqunique', 1);
		$options->jQueryRegex             = $this->params->get('jqregex', ZEN_LIBRARY_JQUERY_REGEX);
		$options->mootoolsHandle          = (bool)$this->params->get('handleMootools', 0);
		$options->mootoolsStrip           = (bool)$this->params->get('stripMootools', 0);
		$options->mootoolsMoreStrip       = (bool)$this->params->get('stripMootoolsMore', 0);
		$options->mootoolsReplace         = (bool)$this->params->get('replaceMootools', 0);
		$options->mootoolsMoreReplace     = (bool)$this->params->get('replaceMootoolsMore', 0);
		$options->mootoolsPath            = $this->params->get('mootoolsPath', ZEN_LIBRARY_DEFAULT_MOOTOOLS_PATH);
		$options->mootoolsMorePath        = $this->params->get('mootoolsMorePath', ZEN_LIBRARY_DEFAULT_MOOTOOLS_MORE_PATH);
		$options->customScripts           = ZenScriptHandler::prepareCustomScripts($this->params->get('addCustomScripts', ''));
		$options->customScriptsStrip      = (bool)$this->params->get('stripCustom', 0);
		$options->customScriptsToStrip    = ZenScriptHandler::prepareCustomScriptsToStrip($this->params->get('customScripts', ''));

		// Lazy load Images
		if ($this->params->get('lazyload', 0))
		{
			JLoader::import('zen.addon.lazyload', ZEN_LIBRARY_PATH);
			$options->customScripts[] = ZenAddonLazyloadimages::getScriptFile();
			$options->scriptDeclarations[] = ZenAddonLazyloadimages::getScript($this->params->get('llSelector', 'img'));
		}

		// IE 6 Warning
		if ($this->params->get('ie6Warning', 1) && $browser->isIE6)
		{
			JLoader::import('zen.addon.ie6warning', ZEN_LIBRARY_PATH);
			$options->customScripts[] = ZenAddonIe6warning::getScriptFile();
		}

		// Scroll Top
		if ($this->params->get('scrollTop', 1))
		{
			JLoader::import('zen.addon.scrolltop', ZEN_LIBRARY_PATH);
			if (ZenAddonScrolltop::browserIsCompatible())
			{
				$text = $this->params->get('scrollText', '^ Back To Top');

				if ($this->params->get('scrollTextTranslate', 1))
				{
					$text = JText::_($text);
				}

				$options->scriptDeclarations[] = ZenAddonScrolltop::getScript($text);

				unset($text);
			}
		}

		$buffer = JResponse::getBody();
		$buffer = ZenScriptHandler::handleScripts($buffer, $options);
		JResponse::setBody($buffer);

		// ZenUtilityBenchmark::stop('onAfterRender');
		return true;
	}
}
?>
