<?php
/**
 * @package     Zen Library
 * @subpackage  Zen Library
 * @author      Joomla Bamboo - design@joomlabamboo.com
 * @copyright   Copyright (c) 2013 Joomla Bamboo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @version     1.0.2
 */

// Check to ensure this file is within the rest of the framework
defined('_JEXEC') or die();

/**
 * Class to represent manifest info for Template
 *
 * @package  Zen Library
 * @since    1.0.0
 */
class ZenGridTemplateManifest extends JObject
{
	public $name;

	public $template;

	public $creationDate;

	public $author;

	public $copyright;

	public $authorEmail;

	public $authorUrl;

	public $version;

	public $description;

	public $positions;

	/**
	 * Class constructor
	 *
	 * @param   string  $xmlPath  XML content
	 */
	public function __construct($xmlPath = '')
	{
		parent::__construct();

		if (!empty($xmlPath))
		{
			$this->loadManifestFromXML($xmlPath);
		}
	}

	/**
	 * Load the manifest file
	 *
	 * @param   string  $xmlFile  XML File path
	 *
	 * @return   bool              True if successful, or False otherwise.
	 */
	private function loadManifestFromXML($xmlFile = '')
	{
		$xml = null;

		if (version_compare(JVERSION, '1.6', '<'))
		{
			$xml = simplexml_load_file($xmlFile);
		}
		else
		{
			$xml = JFactory::getXML($xmlFile);
		}

		if (!$xml)
		{
			$this->_errors[] = JText::sprintf('File not found: %s', $xmlFile);

			return false;
		}
		else
		{
			$this->name         = (string) $xml->name;
			$this->template     = (string) $xml->template;
			$this->creationDate = (string) $xml->creationDate;
			$this->author       = (string) $xml->author;
			$this->copyright    = (string) $xml->copyright;
			$this->authorEmail  = (string) $xml->authorEmail;
			$this->authorUrl    = (string) $xml->authorUrl;
			$this->version      = (string) $xml->version;
			$this->description  = (string) $xml->description;

			// It is not just an Array, for compatibility with current extensions version,
			// that expect $manifest->positions->position as the array.
			$this->positions = new stdClass;
			$this->positions->position = array();

			foreach ($xml->positions->position as $position)
			{
				$this->positions->position[] = (string) $position;
			}

			return true;
		}
	}
}
