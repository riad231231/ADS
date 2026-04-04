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

jimport('joomla.environment.browser');

class ZenUtilityBrowser
{
	/**
	 * The framework instance
	 *
	 * @var    ZenUtilityBrowser
	 *
	 * @since  1.0.0
	 */
	protected static $instance;

	public $userAgent = '';

	public $isIE = false;

	public $isIE6 = false;

	private $version = '';

	public function __construct()
	{
		$this->userAgent = $this->getBrowser();
		$this->isIE = substr($this->userAgent, 0, 4) === 'msie';
		$this->isIE6 = $this->isBrowser('ie6');
	}

	/**
	 * Returns a refernce to the global ZenUtilityBrowser object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $zgf = ZenUtilityBrowser::getInstance();
	 *
	 * @return  ZenUtilityBrowser
	 *
	 * @since   1.0.0
	 */
	public static function getInstance()
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/*
	 * Method to return browser type
	 *
	 * @access public
	 * @param none
	 * @return string
	 */
	public function getBrowser()
	{
		if (!empty($this->userAgent))
		{
			return $this->userAgent;
		}

		$agentString = JBrowser::getInstance()->getAgentString();

		if (stripos($agentString, 'firefox') !== false) :
			$agent = 'firefox';
		elseif (stripos($agentString, 'chrome') !== false) :
			$agent = 'chrome';
		elseif (stripos($agentString, 'msie 9') !== false) :
			$agent = 'ie9';
		elseif (stripos($agentString, 'msie 8') !== false) :
			$agent = 'ie8';
		elseif (stripos($agentString, 'msie 7') !== false) :
			$agent = 'ie7';
		elseif (stripos($agentString, 'msie 6') !== false) :
			$agent = 'ie6';
		elseif (stripos($agentString, 'iphone') !== false || stripos($agentString, 'ipod') !== false) :
			$agent = 'iphone';
		elseif (stripos($agentString, 'ipad') !== false) :
			$agent = 'ipad';
		elseif (stripos($agentString, 'blackberry') !== false) :
			$agent = 'blackberry';
		elseif (stripos($agentString, 'palmos') !== false) :
			$agent = 'palm';
		elseif (stripos($agentString, 'android') !== false) :
			$agent = 'android';
		elseif (stripos($agentString, 'safari') !== false) :
			$agent = 'safari';
		elseif (stripos($agentString, 'opera') !== false) :
			$agent = 'opera';
		else :
			$agent = $agentString;
		endif;

		return $agent;
	}

	/*
	 * Method to detect a certain browser type
	 *
	 * @access public
	 * @param string $shortName
	 * @return string
	 */
	public function isBrowser($shortName)
	{
		return $this->getBrowser() === $shortName;
	}

	public function getVersion()
	{
		if ($this->version === '')
		{
			$this->version = JBrowser::getInstance()->getVersion();
		}

		return $this->version;
	}
}
