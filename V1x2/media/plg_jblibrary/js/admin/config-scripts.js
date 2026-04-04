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

	var isJoomla30 = $('#jform_params_loadJQuery').parent()[0].tagName.toLowerCase() === 'div';

	// Convert Joomla 3.0 admin fields
	var convertFieldsToJoomla30 = function(elements)
	{
		var element = '';

		elements = elements.split(',');
		for (var i = 0; i < elements.length; i++)
		{
			element = $(elements[i]);
			element = element.parent();
			elements[i] = element;
		}

		return elements;
	}

	var toggleFields = function(elements, show)
	{
		if (isJoomla30)
		{
			elements = convertFieldsToJoomla30(elements);
			$.each(elements, function(i, element) {
				if (show)
				{
					element.parent().show();
				}
				else
				{
					element.parent().hide();
				}
			});
		}
		else
		{
			if (show)
			{
				$(elements).parent().show();
			}
			else
			{
				$(elements).parent().hide();
			}
		}
	}

	// Use script loader
	var useScriptLoaderField = $('#jform_params_usescriptloader');
	var moveScriptsToBottomField = $('#jform_params_moveScriptsToBottom');
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

	var updateScriptsToBottomFields = function()
	{
		defaultMoveScriptsToBottomField = moveScriptsToBottomField.val();
	}

	if (isJoomla30)
	{
		useScriptLoaderField.chosen().change(updateScriptsHandlerFields);
		moveScriptsToBottomField.chosen().change(updateScriptsToBottomFields);
	}
	else
	{
		useScriptLoaderField.change(updateScriptsHandlerFields);
		moveScriptsToBottomField.change(updateScriptsToBottomFields);
	}
	updateScriptsHandlerFields();

	// Load jQuery?
	var updatejQueryFields = function()
	{
		var loadJQuery = $('#jform_params_loadJQuery').val() == 1;
		var elements   = '#jform_params_jQueryVersion, #jform_params_source, #jform_params_noConflict';

		toggleFields(elements, loadJQuery);
	}
	$('#jform_params_loadJQuery').change(updatejQueryFields);
	updatejQueryFields();

	// Remove other jQuery?
	var updateOtherJQueryFields = function()
	{
		var removeJQuery = $('#jform_params_jqunique').val() == 1;
		var elements     = '#jform_params_jqregex';

		toggleFields(elements, removeJQuery);
	}
	$('#jform_params_jqunique').change(updateOtherJQueryFields);
	updateOtherJQueryFields();

	// jQuery Versions
	var textLatest = '';
	var updatejQueryVersions = function() {
		var jQuerySource = $('#jform_params_source').val();

		if (jQuerySource !== 'local') {
			var option = $('#jform_params_jQueryVersion option[value="latest"]');

			if (option.length == 0) {
				option = $('<option value="latest"></option>');
				option.html(textLatest);
				$('#jform_params_jQueryVersion').append(option);
			}
		}
		else {
			var option = $('#jform_params_jQueryVersion option[value="latest"]');
			if (textLatest === '')
			{
				textLatest = option.html(); // Backup translated text
			}
			option.remove();
		}
	}
	$('#jform_params_source').change(updatejQueryVersions);
	updatejQueryVersions();

	// Handle Mootools?
	var updateMootoolsFields = function()
	{
		var handleMootools      = $('#jform_params_handleMootools').val() == 1;
		var stripMootools       = $('#jform_params_stripMootools').val() == 1;
		var stripMootoolsMore   = $('#jform_params_stripMootoolsMore').val() == 1;
		var replaceMootools     = $('#jform_params_replaceMootools').val() == 1;
		var replaceMootoolsMore = $('#jform_params_replaceMootoolsMore').val() == 1;

		if (handleMootools)
		{
			toggleFields('#jform_params_stripMootools, #jform_params_stripMootoolsMore', true);

			if (stripMootools)
			{
				toggleFields('#jform_params_replaceMootools, #jform_params_mootoolsPath', false);
			}
			else
			{
				toggleFields('#jform_params_replaceMootools', true);
				toggleFields('#jform_params_mootoolsPath', replaceMootools);
			}

			if (stripMootoolsMore)
			{
				toggleFields('#jform_params_replaceMootoolsMore, #jform_params_mootoolsMorePath', false);
			}
			else
			{
				toggleFields('#jform_params_replaceMootoolsMore', true);
				toggleFields('#jform_params_mootoolsMorePath', replaceMootoolsMore);
			}
		}
		else
		{
			toggleFields('#jform_params_stripMootools'
				+ ', #jform_params_stripMootoolsMore'
				+ ', #jform_params_mootoolsPath'
				+ ', #jform_params_mootoolsMorePath'
				+ ', #jform_params_replaceMootools'
				+ ', #jform_params_replaceMootoolsMore'
			, false);
		}
	}
	$('#jform_params_handleMootools'
		+ ', #jform_params_stripMootools'
		+ ', #jform_params_stripMootoolsMore'
		+ ', #jform_params_replaceMootools'
		+ ', #jform_params_replaceMootoolsMore'
	).change(updateMootoolsFields);
	updateMootoolsFields();

	// Strip other scripts?
	var updateOtherScriptsFields = function() {
		var stripScripts = $('#jform_params_stripCustom').val() == 1;
		var elements     = '#jform_params_customScripts';

		toggleFields(elements, stripScripts);
	}
	$('#jform_params_stripCustom').change(updateOtherScriptsFields);
	updateOtherScriptsFields();

	// Scroll Top Button
	var updateScrollTopFields = function() {
		var scrollTop = $('#jform_params_scrollTop').val() == 1;
		var elements  = '#jform_params_scrollStyle, #jform_params_scrollTextTranslate, #jform_params_scrollText';

		toggleFields(elements, scrollTop);
	}
	$('#jform_params_scrollTop').change(updateScrollTopFields);
	updateScrollTopFields();

	// Enable Lazy Load?
	var updateLazyLoadFields = function() {
		var lazyLoad = $('#jform_params_lazyLoad').val() == 1;
		var elements = '#jform_params_llSelector';

		toggleFields(elements, lazyLoad);
	}
	$('#jform_params_lazyLoad').change(updateLazyLoadFields);
	updateLazyLoadFields();
});
