/**
 * scriptloader.js
 *
 * Script loader, specially for CDN fallback behavior.
 * Adapted from Steve Souders code (thanks Steve) by
 * Anderson G. Martins from Joomla Bamboo.
 *
 * Added the option to set a fallback URL, which will be used
 * trying to load a local copy of the requested file.
 *
 * @package		Zen Library
 * @version		1.0.2
 * @author		Joomla Bamboo - design@joomlabamboo.com
 * @copyright 	Copyright (c) 2013 Joomla Bamboo. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * loadScript
 *
 * @author  Steve Sourders, Anderson G. Martins
 * @param   String    url          URL where we find the script to load it.
 * @param   String    fallbackURL  The local URL that will be called if remote URL fails
 * @param   Function  callback     The function that will be called after complete loaded.
 */
function loadScript(url, fallbackURL, callback)
{
	var script = document.createElement("script")
	script.type = "text/javascript";

	if (script.readyState) // IE
	{
		script.onreadystatechange = function()
		{
			if (script.readyState == "loaded" ||
				script.readyState == "complete")
			{
				script.onreadystatechange = null;

				if ((typeof callback) === 'function')
				{
					callback();
				}
			}
			else if (script.readyState == "error")
			{
				if ((typeof fallbackURL) === 'string' && fallbackURL !== '')
				{
					// Try to move forward with the fallback URL
					loadScript(fallbackURL, null, callback);
				}
			}
		};
	}
	else // Others
	{
		script.onload = function()
		{
			if ((typeof callback) === 'function')
			{
				callback();
			}
		};

		script.onerror = function()
		{
			if ((typeof fallbackURL) === 'string' && fallbackURL !== '')
			{
				// Try to move forward with the fallback URL
				loadScript(fallbackURL, null, callback);
			}
		}
	}

	script.src = url;
	document.getElementsByTagName("head")[0].appendChild(script);
}
