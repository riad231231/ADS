/**
 * @package		Joomla.Plugin
 * @subpackage	System.Jblibrary
 * @author		Joomla Bamboo - design@joomlabamboo.com
 * @copyright 	Copyright (c) 2013 Joomla Bamboo. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @version		2.1.4
 */

jQuery(document).ready(function()
{
	$ = jQuery;

	var toggleFields = function(elements, show)
	{
		if (show)
		{
			$(elements).parent().parent().show();
		}
		else
		{
			$(elements).parent().parent().hide();
		}
	}

	// Use script loader
	var useScriptLoaderField = $('#paramsusescriptloader');
	var moveScriptsToBottomField = $('#paramsmoveScriptsToBottom');
	var defaultMoveScriptsToBottomField = moveScriptsToBottomField.val();
	var updateScriptsHandlerFields = function()
	{
		if (useScriptLoaderField.val() == 1)
		{
			moveScriptsToBottomField.val(1);
			moveScriptsToBottomField.attr('disabled', 'disabled');
		}
		else
		{
			moveScriptsToBottomField.val(defaultMoveScriptsToBottomField);
			moveScriptsToBottomField.removeAttr('disabled');
		}
	}
	useScriptLoaderField.change(updateScriptsHandlerFields);
	moveScriptsToBottomField.change(function() {
		defaultMoveScriptsToBottomField = moveScriptsToBottomField.val();
	});
	updateScriptsHandlerFields();

	// Load jQuery?
	var updatejQueryFields = function()
	{
		var loadJQuery = $('#paramsloadJQuery').val() == 1;
		var elements   = '#paramsjQueryVersion, #paramssource, #paramsnoConflict';

		toggleFields(elements, loadJQuery);
	}
	$('#paramsloadJQuery').change(updatejQueryFields);
	updatejQueryFields();

	// Remove other jQuery?
	var updateOtherJQueryFields = function()
	{
		var removeJQuery = $('#paramsjqunique').val() == 1;
		var elements     = '#paramsjqregex';

		toggleFields(elements, removeJQuery);
	}
	$('#paramsjqunique').change(updateOtherJQueryFields);
	updateOtherJQueryFields();

	// jQuery Versions
	var textLatest = '';
	var updatejQueryVersions = function() {
		var jQuerySource = $('#paramssource').val();

		if (jQuerySource !== 'local') {
			var option = $('#paramsjQueryVersion option[value="latest"]');

			if (option.length == 0) {
				option = $('<option value="latest"></option>');
				option.html(textLatest);
				$('#paramsjQueryVersion').append(option);
			}
		}
		else {
			var option = $('#paramsjQueryVersion option[value="latest"]');
			if (textLatest === '')
			{
				textLatest = option.html(); // Backup translated text
			}
			option.remove();
		}
	}
	$('#paramssource').change(updatejQueryVersions);
	updatejQueryVersions();

	// Handle Mootools?
	var updateMootoolsFields = function()
	{
		var handleMootools      = $('#paramshandleMootools').val() == 1;
		var stripMootools       = $('#paramsstripMootools').val() == 1;
		var stripMootoolsMore   = $('#paramsstripMootoolsMore').val() == 1;
		var replaceMootools     = $('#paramsreplaceMootools').val() == 1;
		var replaceMootoolsMore = $('#paramsreplaceMootoolsMore').val() == 1;

		if (handleMootools)
		{
			toggleFields('#paramsstripMootools, #paramsstripMootoolsMore', true);

			if (stripMootools)
			{
				toggleFields('#paramsreplaceMootools, #aramsmootoolsPath', false);
			}
			else
			{
				toggleFields('#paramsreplaceMootools', true);
				toggleFields('#paramsmootoolsPath', replaceMootools);
			}

			if (stripMootoolsMore)
			{
				toggleFields('#paramsreplaceMootoolsMore, #paramsmootoolsMorePath', false);
			}
			else
			{
				toggleFields('#paramsreplaceMootoolsMore', true);
				toggleFields('#paramsmootoolsMorePath', replaceMootoolsMore);
			}
		}
		else
		{
			toggleFields('#paramsstripMootools'
				+ ', #paramsstripMootoolsMore'
				+ ', #paramsmootoolsPath'
				+ ', #paramsmootoolsMorePath'
				+ ', #paramsreplaceMootools'
				+ ', #paramsreplaceMootoolsMore'
			, false);
		}
	}
	$('#paramshandleMootools'
		+ ', #paramsstripMootools'
		+ ', #paramsstripMootoolsMore'
		+ ', #paramsreplaceMootools'
		+ ', #paramsreplaceMootoolsMore'
	).change(updateMootoolsFields);
	updateMootoolsFields();

	// Strip other scripts?
	var updateOtherScriptsFields = function() {
		var stripScripts = $('#paramsstripCustom').val() == 1;
		var elements     = '#paramscustomScripts';

		toggleFields(elements, stripScripts);
	}
	$('#paramsstripCustom').change(updateOtherScriptsFields);
	updateOtherScriptsFields();

	// Scroll Top Button
	var updateScrollTopFields = function() {
		var scrollTop = $('#paramsscrollTop').val() == 1;
		var elements  = '#paramsscrollStyle, #paramsscrollTextTranslate, #paramsscrollText';

		toggleFields(elements, scrollTop);
	}
	$('#paramsscrollTop').change(updateScrollTopFields);
	updateScrollTopFields();

	// Enable Lazy Load?
	var updateLazyLoadFields = function() {
		var lazyLoad = $('#paramslazyLoad').val() == 1;
		var elements = '#paramsllSelector';

		toggleFields(elements, lazyLoad);
	}
	$('#paramslazyLoad').change(updateLazyLoadFields);
	updateLazyLoadFields();
});
