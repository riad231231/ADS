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

defined('ZEN_LIBRARY_MEDIA_URI') or define('ZEN_LIBRARY_MEDIA_URI', JURI::root(true) . '/media/zen/');
define('ZEN_LIBRARY_DEFAULT_JQUERY', '1.8.2');
define('ZEN_LIBRARY_NEXT_SCRIPT_TOKEN', '#$NEXT_SCRIPT$#');
define('ZEN_LIBRARY_JQUERY_REGEX', '([\/a-zA-Z0-9_:\.-]*)jquery([0-9\.-]|min|pack)*?.js');
define('ZEN_LIBRARY_DEFAULT_MOOTOOLS_PATH', '//ajax.googleapis.com/ajax/libs/mootools/1.4.5/mootools-yui-compressed.js');
define('ZEN_LIBRARY_DEFAULT_MOOTOOLS_MORE_PATH', '/media/system/js/mootools-more.js');
defined('ZEN_LIBRARY_PATH') or define('ZEN_LIBRARY_PATH', null);
define('ZEN_LIBRARY_CACHE_PATH', 'cache/zen_library/');

/**
 *
 */
class ZenScriptHandler
{
	private static $options;

	private static $jQueryFromCDN;

	private static $mootoolsFromCDN;

	private static $mootoolsMoreFromCDN;

	private static $loadScriptFromCDN;

	private static $jQueryPath;

	public static $buffer;

	public static function setJQueryLoadedFlag()
	{
		$app = JFactory::getApplication();

		if (version_compare(JVERSION, '3.0', '<'))
		{
			$app->set('jquery', true);
		}
		else
		{
			$app->jquery = true;
		}
	}

	public static function checkJQueryLoadedFlag()
	{
		$app = JFactory::getApplication();

		if (version_compare(JVERSION, '3.0', '<'))
		{
			return (bool) $app->get('jquery');
		}
		else
		{
			return isset($app->jquery) && (bool) $app->jquery === true;
		}
	}

	private static function getJQueryPath($version = 'latest', $source = 'google')
	{
		$path = '';

		// Check jQuery Version
		if ($version === 'latest')
		{
			if ($source === 'google')
			{
				$path = '//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js';
			}
			elseif ($source === 'jquery')
			{
				$path = '//code.jquery.com/jquery-latest.min.js';
			}
		}
		else
		{
			if ($source === 'local')
			{
				$path = ZEN_LIBRARY_MEDIA_URI . 'js/jquery/jquery-' . $version . '.min.js';
			}
			elseif ($source === 'google')
			{
				$path = '//ajax.googleapis.com/ajax/libs/jquery/' . $version . '/jquery.min.js';
			}
			elseif ($source === 'jquery')
			{
				$path = '//code.jquery.com/jquery-' . $version . '.min.js';
			}
		}

		// Tell other extensions jQuery has been loaded
		self::setJQueryLoadedFlag();

		return $path;
	}

	public static function loadLocalJQuery($version = '', $loadNoConflict = true)
	{
		if (empty($version))
		{
			$version = ZEN_LIBRARY_DEFAULT_JQUERY;
		}

		$doc = JFactory::getDocument();
		$doc->addScript(ZEN_LIBRARY_MEDIA_URI . 'js/jquery/jquery-' . $version . '.min.js');

		if ($loadNoConflict)
		{
			$doc->addScript(ZEN_LIBRARY_MEDIA_URI . 'js/jquery/jquery-noconflict.js');
		}

		self::setJQueryLoadedFlag();
	}

	private static function checkJQueryVersion($version, $source)
	{
		// There is no "latest" local version
		if (($version === 'latest' && $source === 'local') || empty($version))
		{
			$version = ZEN_LIBRARY_DEFAULT_JQUERY;
		}

		return $version;
	}

	private static function sanitizeCustomScripts($scripts)
	{
		return preg_replace('/\n\s\'"/m', '', trim($scripts));
	}

	private static function checkScriptsConfig()
	{
		self::$loadScriptFromCDN   = self::$options->jQuerySource !== 'local';
		self::$mootoolsFromCDN     = self::$options->mootoolsReplace && ZenUtilityURI::isExternalPath(self::$options->mootoolsPath);
		self::$mootoolsMoreFromCDN = self::$options->mootoolsMoreReplace && ZenUtilityURI::isExternalPath(self::$options->mootoolsMorePath);
		self::$loadScriptFromCDN   = self::$loadScriptFromCDN || self::$mootoolsFromCDN || self::$mootoolsFromCDN;

		// Backward param compatibility
		if (self::$options->jQueryVersion === 'none')
		{
			self::$options->jQueryLoad = false;
			self::$options->jQueryVersion = 'latest';
		}

		self::$options->jQueryVersion = self::checkJQueryVersion(self::$options->jQueryVersion, self::$options->jQuerySource);
		self::$jQueryPath = self::getJQueryPath(self::$options->jQueryVersion, self::$options->jQuerySource);

		// TODO: build a class for options with default values
		if (!isset(self::$options->combineScripts))
		{
			self::$options->combineScripts = false;
		}

		if (!isset(self::$options->moveScriptsToBottom))
		{
			self::$options->moveScriptsToBottom = false;
		}
	}

	public static function prepareCustomScripts($scripts = array())
	{
		if (is_string($scripts) && !empty($scripts))
		{
			// Sanitize value
			$scripts = self::sanitizeCustomScripts($scripts);
			$scripts = explode(',', $scripts);
		}

		return $scripts;
	}

	public static function prepareCustomScriptsToStrip($scripts = array())
	{
		if (is_string($scripts) && !empty($scripts))
		{
			$scripts = preg_split("/[\s,]+/", $scripts);
		}

		return $scripts;
	}

	/**
	 * Avoid issues with https and requests for http (insecure url)
	 * Remove the protocol (http, https)
	 * Before: http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js
	 * After:  //ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js
	 *
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	public static function getRelativeProtocolURL($url)
	{
		return preg_replace('#^(http|https):(//.*)#', '$2', $url);
	}

	public static function handleScripts($buffer, stdClass $options)
	{
		self::$buffer = $buffer;
		// unset($buffer);
		self::$options = $options;

		self::checkScriptsConfig();
		self::$options->customScripts = self::prepareCustomScripts(self::$options->customScripts);
		self::$options->customScriptsToStrip = self::preparecustomScriptsToStrip(self::$options->customScriptsToStrip);

		// Remove custom text from body (inline scripts, etc)
		if (isset(self::$options->customTextStrip) && !empty(self::$options->customTextStrip))
		{
			$strip = self::$options->customTextStrip;
			foreach ($strip as $text)
			{
				self::$buffer = preg_replace($text, '', self::$buffer);
			}
			unset($strip, $text);
		}

		// Find all external scripts in head and body
		$bufferScripts = array();
		$cleanScripts = array();
		preg_match_all('#<script[^>]*src[\s]*=[\"\']([^\"\']*)[\"\'][^>]*>[^<]*<\/script>#', self::$buffer, $bufferScripts);

		$scriptDeclarations = array();
		if (isset(self::$options->scriptDeclarations) && is_array(self::$options->scriptDeclarations))
		{
			$scriptDeclarations = $options->scriptDeclarations;
		}

		// Check if need to add custom scripts and inject it into the found body scripts
		if (!empty(self::$options->customScripts))
		{
			if (!is_array(self::$options->customScripts))
			{
				self::$options->customScripts = array(self::$options->customScripts);
			}

			$bufferScripts[1] = array_merge($bufferScripts[1], self::$options->customScripts);
		}

		if (!empty($bufferScripts))
		{
			// Remove explicit duplicated scripts
			$scriptPaths = array_unique($bufferScripts[1]);
			if (!empty($scriptPaths))
			{
				unset($bufferScripts[1]);

				// Temp vars to sort scripts later
				$pathMootools     = '';
				$pathMootoolsMore = '';
				$pathJoomlaCore   = '';
				$pathJoomlaCaption = '';
				$pathJoomlaModal = '';
				$pathJoomlaValidate = '';
				$pathJoomlaCalendarSetup = '';
				$pathJoomlaCalendar = '';
				$pathJoomlaCombobox = '';
				$pathJoomlaHighlighter = '';
				$pathJoomlaMooRainbow = '';
				$pathJoomlaMooTree = '';
				$pathJoomlaMultiselect = '';
				$pathJoomlaPasswordstrength = '';
				$pathJoomlaProgressbar = '';
				$pathJoomlaSwf = '';
				$pathJoomlaSwitcher = '';
				$pathJoomlaTabs = '';
				$pathJoomlaUploader = '';

				// Check if there is Mootools, Mootools More, Core and remove Custom Scripts
				$i = 0;
				foreach ($scriptPaths as $script)
				{
					if (substr_count($script, 'mootools-core'))
					{
						// Should handle Mootools?
						if (self::$options->mootoolsHandle)
						{
							if (!self::$options->mootoolsStrip)
							{
								$firstMootools = empty($pathMootools);
								if (self::$options->mootoolsReplace)
								{
									$script = self::$options->mootoolsPath;
								}

								// Avoid duplicated mootools-core
								if ($firstMootools)
								{
									$pathMootools = $script;
								}
							}
						}
						else
						{
							$pathMootools = $script;
						}

						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'mootools-more'))
					{
						if (self::$options->mootoolsHandle)
						{
							if (!self::$options->mootoolsMoreStrip)
							{
								$firstMootoolsMore = empty($pathMootoolsMore);
								if (self::$options->mootoolsMoreReplace)
								{
									$script = self::$options->mootoolsMorePath;
								}

								// Avoid duplicated mootools-more
								if ($firstMootoolsMore)
								{
									$pathMootoolsMore = $script;
								}
							}
						}
						else
						{
							$pathMootoolsMore = $script;
						}

						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/core.js'))
					{
						$pathJoomlaCore = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/caption.js'))
					{
						$pathJoomlaCaption = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/modal.js'))
					{
						$pathJoomlaModal = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/validate.js'))
					{
						$pathJoomlaValidate = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/calendar-setup.js'))
					{
						$pathJoomlaCalendarSetup = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/calendar.js'))
					{
						$pathJoomlaCalendar = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/combobox.js'))
					{
						$pathJoomlaCombobox = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/highlighter.js'))
					{
						$pathJoomlaHighlighter = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/mooRainbow.js'))
					{
						$pathJoomlaMooRainbow = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/mootree.js'))
					{
						$pathJoomlaMooTree = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/multiselect.js'))
					{
						$pathJoomlaMultiselect = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/passwordstrength.js'))
					{
						$pathJoomlaPasswordstrength = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/progressbar.js'))
					{
						$pathJoomlaProgressbar = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/swf.js'))
					{
						$pathJoomlaSwf = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/switcher.js'))
					{
						$pathJoomlaSwitcher = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/tabs.js'))
					{
						$pathJoomlaTabs = $script;
						unset($scriptPaths[$i]);
					}
					elseif (substr_count($script, 'media/system/js/uploader.js'))
					{
						$pathJoomlaUploader = $script;
						unset($scriptPaths[$i]);
					}
					elseif (self::$options->customScriptsStrip && !empty(self::$options->customScriptsToStrip))
					{
						// Remove specified scripts
						foreach(self::$options->customScriptsToStrip as $deadScript)
						{
							$scriptRegex = "([\/a-zA-Z0-9_:\.-]*)" . trim($deadScript);
							if (preg_match("~{$scriptRegex}~", $script))
							{
								unset($scriptPaths[$i]);
							}
						}
					}
					elseif (substr_count($script, 'jquery-noconflict') && self::$options->jQueryNoConflict)
					{
						// Remove duplicated noConflict script
						unset($scriptPaths[$i]);
					}
					elseif (self::$options->jQueryRemoveOther) // Remove other jQuery?
					{
						if (preg_match('~' . self::$options->jQueryRegex . '~', $script))
						{
							unset($scriptPaths[$i]);
						}
					}

					$i++;
				}

				/**
				 * Start script reorder:
				 *
				 * 1. Script Loader
				 * 2. Mootools
				 * 3. Mootools More
				 * 4. Joomla Core JS
				 * 5. Joomla Caption JS
				 * 6. Joomla Modal JS
				 * 7. Joomla Validate JS
				 * 8. Joomla Calendar Setup JS
				 * 9. Joomla Calendar JS
				 * 10. Joomla Highlighter JS
				 * 11. Joomla mooRainbow JS
				 * 12. Joomla mootree JS
				 * 13. Joomla multiselect JS
				 * 14. Joomla Passwordstrength JS
				 * 15. Joomla Progressbar JS
				 * 16. Joomla swf JS
				 * 17. Joomla Switcher JS
				 * 18. Joomla Tabs JS
				 * 19. Joomla Uploader JS
				 * 20. Joomla Combobox JS
				 * 21. jQuery
				 * 22. jQuery noConflict
				 * 23. Other JS scripts
				 * 24. User Script Files
				 */

				// 1. Script Loader
				self::$options->useScriptLoader = self::$options->useScriptLoader && !self::$options->combineScripts;
				if (self::$options->useScriptLoader)
				{
					$cleanScripts[] = ZEN_LIBRARY_MEDIA_URI . 'js/tools/scriptloader.min.js';
				}

				// 2. Mootools
				if (!empty($pathMootools))
				{
					$cleanScripts[] = $pathMootools;
				}

				// 3. Mootools More
				// Add Mootools more just if we have Mootools
				if (!empty($pathMootoolsMore))
				{
					$cleanScripts[] = $pathMootoolsMore;
				}

				// 4. Joomla Core JS
				if (!empty($pathJoomlaCore))
				{
					$cleanScripts[] = $pathJoomlaCore;
				}

				// 5. Joomla Caption JS
				if (!empty($pathJoomlaCaption))
				{
					$cleanScripts[] = $pathJoomlaCaption;
				}

				// 6. Joomla Modal JS
				if (!empty($pathJoomlaModal))
				{
					$cleanScripts[] = $pathJoomlaModal;
				}

				// 7. Joomla Validade JS
				if (!empty($pathJoomlaValidate))
				{
					$cleanScripts[] = $pathJoomlaValidate;
				}

				// 8. Joomla Calendar Setup JS
				if (!empty($pathJoomlaCalendarSetup))
				{
					$cleanScripts[] = $pathJoomlaCalendarSetup;
				}

				// 9. Joomla Calendar JS
				if (!empty($pathJoomlaCalendarSetup))
				{
					$cleanScripts[] = $pathJoomlaCalendar;
				}

				// 10. Joomla Combobox JS
				if (!empty($pathJoomlaCombobox))
				{
					$cleanScripts[] = $pathJoomlaCombobox;
				}

				// 11. Joomla Highlighter JS
				if (!empty($pathJoomlaHighlighter))
				{
					$cleanScripts[] = $pathJoomlaHighlighter;
				}

				// 12. Joomla MooRainbow JS
				if (!empty($pathJoomlaMooRainbow))
				{
					$cleanScripts[] = $pathJoomlaMooRainbow;
				}

				// 13. Joomla MooTree JS
				if (!empty($pathJoomlaTree))
				{
					$cleanScripts[] = $pathJoomlaTree;
				}

				// 14. Joomla Multiselect JS
				if (!empty($pathJoomlaMultiselect))
				{
					$cleanScripts[] = $pathJoomlaMultiselect;
				}

				// 15. Joomla Password Strength JS
				if (!empty($pathJoomlaPasswordstrength))
				{
					$cleanScripts[] = $pathJoomlaPasswordstrength;
				}

				// 16. Joomla Progressbar JS
				if (!empty($pathJoomlaProgressbar))
				{
					$cleanScripts[] = $pathJoomlaProgressbar;
				}

				// 17. Joomla SWF JS
				if (!empty($pathJoomlaSwf))
				{
					$cleanScripts[] = $pathJoomlaSwf;
				}

				// 18. Joomla Switcher JS
				if (!empty($pathJoomlaSwitcher))
				{
					$cleanScripts[] = $pathJoomlaSwitcher;
				}

				// 19. Joomla Tabs JS
				if (!empty($pathJoomlaTabs))
				{
					$cleanScripts[] = $pathJoomlaTabs;
				}

				// 20. Joomla Uploader JS
				if (!empty($pathJoomlaUploader))
				{
					$cleanScripts[] = $pathJoomlaUploader;
				}

				// Should load jQuery?
				if (self::$options->jQueryLoad)
				{
					// 21. jQuery
					$cleanScripts[] = self::$jQueryPath;

					// 22. jQuery noConflict
					if (self::$options->jQueryNoConflict)
					{
						$cleanScripts[] = ZEN_LIBRARY_MEDIA_URI . 'js/jquery/jquery-noconflict.js';
					}
				}

				// 23. Other JS Scripts
				if (!empty($scriptPaths))
				{
					$cleanScripts = array_merge($cleanScripts, $scriptPaths);
					unset($scriptPaths);
				}

				// 24. User Script Files
				$userFiles = JFolder::files('media/zen/user', 'js', false, true);
				if ($userFiles)
				{
					if (!empty($userFiles))
					{
						foreach ($userFiles as $file)
						{
							$cleanScripts[] = (string) JURI::root(true) . '/' . $file;
						}
					}
					unset($userFiles, $file);
				}

				// Remove old external script declarations
				self::$buffer = str_replace($bufferScripts[0], '', self::$buffer);

				if (self::$options->useScriptLoader)
				{
					// Move all scripts (head, body) to the end of body
					$scriptSource = self::$buffer;
				}
				else
				{
					// Move just head scripts, after all external scripts
					preg_match('#<head[^>]*>([.\n\r\s<a-bA-Z0-9\D]*)</head>#', self::$buffer, $match);
					$scriptSource = $match[0];
				}

				// Get explicit script tags
				preg_match_all("/<script[^>]*>(.*)<\/script>/Uis", $scriptSource, $internalJS);
				if (!empty($internalJS[0]))
				{
					foreach ($internalJS[1] as $i => $js)
					{
						// Check if that script has a document.write, what could break the page if moved
						if (preg_match("/document\.write[\s]*\(/Uis", $js))
						{
							// Ignore scripts with document.write
							unset($internalJS[0][$i], $internalJS[1][$i]);
						}
					}

					// Remove old script  declaration
					self::$buffer = str_replace($internalJS[0], '', self::$buffer);

					$scriptDeclarations = array_merge($internalJS[1], $scriptDeclarations);
				}

				$tags = '';

				/**
				 * Process cleaned script list.
				 *
				 * If use CDN, every script will be added inside a callback.
				 * That callback is called when the loader succesfull load the script.
				 * If can not load it, so the fallback URL will be used.
				 * After load the fallback script, the callback is called.
				 *
				 * The ZEN_LIBRARY_NEXT_SCRIPT_TOKEN is used (replaced) to insert each script loader inside the prior callback param.
				 */
				$loadScriptStack = ZEN_LIBRARY_NEXT_SCRIPT_TOKEN;
				if (!empty($cleanScripts))
				{
					$tags = '';

					// Check if should move scripts to the end of body
					$scriptsDestination = '</head>';
					if (self::$options->useScriptLoader || self::$options->moveScriptsToBottom)
					{
						$scriptsDestination = '</body>';
					}

					// Combine and cache scripts
					if (self::$options->combineScripts)
					{
						$tags .= self::getCachedScriptTag($cleanScripts);
					}
					else
					{
						// Prepare fallback scripts if there is any script from a CDN
						$fallbackScripts = array();
						if (self::$options->useScriptLoader && self::$loadScriptFromCDN)
						{
							// Check if the script has a local copy inside fallback folder
							$fallbackScripts = JFolder::files('media/zen/js/fallback', 'js', false, true);
						}

						$firstScript = true;
						foreach ($cleanScripts as $scriptURL)
						{
							// Check if the JS needs to be loaded using Script Loader - CDN Fallback
							// First script is the loader, so ignore it
							if (self::$options->useScriptLoader && self::$loadScriptFromCDN && !$firstScript)
							{
								// Load using the script loader

								// If is Mootools from CDN, use a fallback script
								$fallbackURL = '';
								if (($scriptURL === self::$options->mootoolsPath) && self::$mootoolsFromCDN)
								{
									$fallbackURL = '/media/system/js/mootools-core.js';
								}
								// If is Mootools More from CDN, use a fallback script
								elseif (($scriptURL === self::$options->mootoolsMorePath) && self::$mootoolsMoreFromCDN)
								{
									$fallbackURL = '/media/system/js/mootools-more.js';
								}
								// If is jQuery from CDN, use a fallback script
								elseif (($scriptURL === self::$jQueryPath) && self::$loadScriptFromCDN)
								{
									$fallbackURL = self::getJQueryPath(ZEN_LIBRARY_DEFAULT_JQUERY, 'local');
								}
								else
								{
									// Get a fallback for any external script with a local copy
									if (!empty($fallbackScripts))
									{
										$i = 0;
										foreach ($fallbackScripts as $fallbackScript)
										{
											if (ZenUtilityURI::isExternalPath($scriptURL)
													&& basename($scriptURL) === basename($fallbackScript))
											{
												$fallbackURL = $fallbackScript;
												unset($fallbackScripts[$i]);
											}
											$i++;
										}
									}
								}

								$scriptURL = self::getRelativeProtocolURL($scriptURL);
								$tmpJS = "loadScript('{$scriptURL}','{$fallbackURL}',function(){" . ZEN_LIBRARY_NEXT_SCRIPT_TOKEN . "});";
								$loadScriptStack = str_replace(ZEN_LIBRARY_NEXT_SCRIPT_TOKEN, $tmpJS, $loadScriptStack);
							}
							else
							{
								// Load as normal script tag
								$scriptURL = self::getRelativeProtocolURL($scriptURL);
								$tags .= "<script src=\"{$scriptURL}\" type=\"text/javascript\"></script>\n";
								$firstScript = false;
							}
						}
					}

					// Inject scripts into head or body, or just the script loader chain
					if (!empty($tags))
					{
						self::$buffer = str_replace($scriptsDestination, $tags . $scriptsDestination, self::$buffer);
					}

					unset($tags);
				}

				// Add the explicit script declarations to the end of body or head
				if (!empty($scriptDeclarations))
				{
					$explicitScripts = '';
					foreach ($scriptDeclarations as $script)
					{
						// Add all explicit scripts together, to the same script tag
						if ($loadScriptStack !== ZEN_LIBRARY_NEXT_SCRIPT_TOKEN)
						{
							$explicitScripts .= "{$script}\n";
						}
						else
						{
							$explicitScripts .= "<script type=\"text/javascript\">{$script}</script>\n";
						}
					}

					// If is not using script loader, so add all explicit scripts to the end of body
					if (!($loadScriptStack !== ZEN_LIBRARY_NEXT_SCRIPT_TOKEN))
					{
						self::$buffer = str_replace($scriptsDestination, $explicitScripts . $scriptsDestination, self::$buffer);
						$explicitScripts = '';
					}
				}

				// Inject the script loader stack as the last script to body
				if ($loadScriptStack !== ZEN_LIBRARY_NEXT_SCRIPT_TOKEN) // Empty?
				{
					$lastCallback = '';
					if (!empty($explicitScripts))
					{
						// All that explicit scripts to the last callback, so they will be called after all scripts are loaded
						$lastCallback = $explicitScripts;
					}

					// Fix last loader callback as empty param
					$loadScriptStack = str_replace(ZEN_LIBRARY_NEXT_SCRIPT_TOKEN, $lastCallback, $loadScriptStack);
					// Add container script tag
					$loadScriptStack = "<script type=\"text/javascript\">{$loadScriptStack}</script>";
					// Inject into body
					self::$buffer = str_replace($scriptsDestination, $loadScriptStack . $scriptsDestination, self::$buffer);
				}

				unset($scriptDeclarations, $script, $cleanScripts);
			}

			// Strip old bottomscripts tag from body
			self::$buffer = str_replace ("__BOTTOMSCRIPTS__", '', self::$buffer);
		}

		return self::$buffer;
	}

	public static function checkCacheJSPath()
	{
		$path = ZEN_LIBRARY_CACHE_PATH . 'js/';
		if (!JFolder::exists($path))
		{
			JFolder::create($path);
		}
	}

	public static function getCachedScriptTag($cleanScripts)
	{
		self::checkCacheJSPath();

		$fileListStr = implode("\n", $cleanScripts);
		$checkSum = md5($fileListStr);
		$generateNewCache = false;
		$jsBuffer = '';

		// Look for a cached version or create it
		$cacheFileName = 'zenlibrary_' . $checkSum . '.js.php';
		$cacheJSPath = ZEN_LIBRARY_CACHE_PATH . 'js/';
		$cacheFullPath = JPATH_SITE . '/' . $cacheJSPath . $cacheFileName;

		// TODO: Grab the cache time from parameters
		$cacheTime = '100000';

		// Set up the check to see if the file exists already or hasnt expired
		// The following was originally referenced from the Motif framework created by Cory Webb.
		if (JFile::exists($cacheFullPath))
		{
			// Check if the cache has expired
			$generateNewCache = (time() - filectime($cacheFullPath)) > $cacheTime;
		}
		else
		{
			$generateNewCache = true;
		}

		if ($generateNewCache)
		{
			// Create a cached and combined version for scripts
			$jsBuffer = '';

			// Merge files
			foreach ($cleanScripts as $scriptURL)
			{
				// Check if is a local or remote path
				if (!preg_match('#^(http:|https:)?//#', $scriptURL))
				{
					// Local file, add full path
					$scriptURL = JPATH_SITE . $scriptURL;

					$content = JFile::read($scriptURL);
				}
				else
				{
					// Remote files, fix the relative protocol to remote files
					if (substr($scriptURL, 0, 2) === '//')
					{
						$scriptURL = 'http:' . $scriptURL;
					}

					$content = file_get_contents($scriptURL);
				}

				if (empty($content))
				{
					throw new Exception("Zen Library: Could not get file content (empty content): " . $scriptURL, 1);
				}

				$jsBuffer .= "\n" . $content;
			}
			unset($content, $scriptURL);

			// Minify
			if (isset($options->minifyCombinedScripts) && (bool) $options->minifyCombinedScripts)
			{
				JLoader::import('zen.utility.jsmin', ZEN_LIBRARY_PATH);
				$jsBuffer = ZenUtilityJsmin::minify($jsBuffer);
			}

			// Add a expire header
			$expire = gmdate("D, d M Y H:i:s", time() + $cacheTime);
			$jsBuffer = '<?php
ob_start ("ob_gzhandler");
header("Content-type: application/x-javascript; charset: UTF-8");
header("Cache-Control: must-revalidate");
header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600). " GMT");
/* Compressed Files:
' . $fileListStr . '
*/
?>
' . $jsBuffer;

			if (!JFile::write($cacheFullPath, $jsBuffer))
			{
				throw new JException("Zen Library: Error saving cache for combined scripts", 2);
			}

			unset($jsBuffer);
		}

		// We have cache, so lets use it
		$scriptURL = self::getRelativeProtocolURL(JURI::base() . $cacheJSPath . $cacheFileName);

		return "<script src=\"{$scriptURL}\" type=\"text/javascript\"></script>\n";
	}
}
