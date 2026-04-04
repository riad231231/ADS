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

class ZenGridTemplate
{
	/**
	 * The framework instance
	 *
	 * @var    ZenGridTemplate
	 *
	 * @since  1.0.0
	 */
	protected static $instance;

	public $name;

	public function __construct()
	{
		$this->name = $this->getName();
	}

	/**
	 * Returns a refernce to the global ZenGridTemplate object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $zgf = ZenGridTemplate::getInstance();
	 *
	 * @return  ZenGridTemplate
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

	public function getScriptFile($absolutePath = false)
	{
		$base = $absolutePath ? JPATH_ROOT : JURI::base();
		return $base . '/templates/' . $this->name . '/js/template.js';
	}

	/**
	 * Get the current template name. If is Site Application, so use the loaded template.
	 * If is Administrator Application, so load the template name from database.
	 *
	 * @return string The template name
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$app = JFactory::getApplication();
			$name = '';

			if ($app->isAdmin())
			{
				jimport('joomla.environment.request');

				if (version_compare(JVERSION, '1.6', '<'))
				{
					$id	= JRequest::getVar('cid');
					$id = $id[0];

					if (JRequest::getCmd('option') === 'com_templates'
						&& JRequest::getCmd('task') === 'edit'
						&& !empty($id)
					)
					{
						$name = $id;
					}
				}
				else
				{
					$id	= JRequest::getInt('id');

					if (JRequest::getCmd('option') === 'com_templates'
						&& JRequest::getCmd('view') === 'style'
						&& JRequest::getCmd('layout') === 'edit'
						&& !empty($id)
					)
					{
						// Load the site template name from the database
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$query->select('template');
						$query->from('#__template_styles');
						$query->where('id = ' . $id);
						$db->setQuery($query);
						$result = $db->loadObject();

						$name = $result->template;
					}
				}
			}
			else
			{
				$name = $app->getTemplate();
			}

			$this->name = $name;
		}

		return $this->name;
	}
}
