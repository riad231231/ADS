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

JLoader::import('zen.grid.template.manifest', ZEN_LIBRARY_PATH);

class ZenGridFramework
{
	/**
	 * The framework instance
	 *
	 * @var    ZenGridFramework
	 *
	 * @since  1.0.0
	 */
	protected static $instance;

	/**
	 * The global application object.
	 * We use this as a cache avoiding a lot of variables
	 * with the same reference for JApplication instances.
	 * So we should use: $framework->app->method();
	 * instead of call $app = JFactory::getApplication();
	 * $app->method(); everywhere.
	 *
	 * @var    JApplication
	 *
	 * @since  1.0.0
	 */
	public $app;

	/**
	 * The global document object
	 *
	 * @var    JDocument
	 *
	 * @since  1.0.0
	 */
	public $doc;

	protected $templateManifest;

	protected $frameworkManifest;

	protected $templateParams;

	protected static $isJ15 = false;

	public static $hasOldFramework = null;

	public static $hasJbLibrary = null;

	protected $plugin;

	public $template;

	public function __construct($plugin = null)
	{
		$this->app = JFactory::getApplication();
		$this->doc = JFactory::getDocument();
		$this->plugin = $plugin;
		self::$isJ15 = version_compare(JVERSION, '1.6', '<');

		if (is_object($this->plugin))
		{
			$this->plugin->loadLanguage();
		}

		$this->template = ZenGridTemplate::getInstance();
	}

	/**
	 * Returns a refernce to the global ZenGridFramework object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $zgf = ZenGridFramework::getInstance();
	 *
	 * @return  ZenGridFramework
	 *
	 * @since   1.0.0
	 */
	public static function getInstance($plugin = null)
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
			self::$instance = new self($plugin);
		}

		return self::$instance;
	}

	public static function hasOldFramework()
	{
		if (self::$hasOldFramework === null)
		{
			$path = JPATH_ROOT . '/templates/zengridframework/index.php';
			self::$hasOldFramework = (bool) file_exists($path);
		}

		return self::$hasOldFramework;
	}

	public static function hasJbLibrary()
	{
		if (self::$hasJbLibrary === null)
		{
			if (self::$isJ15)
			{
				$path = JPATH_ROOT . '/plugins/system/jblibrary.php';
			}
			else
			{
				$path = JPATH_ROOT . '/plugins/system/jblibrary/jblibrary.php';
			}

			self::$hasJbLibrary = (bool) file_exists($path);
		}

		return self::$hasJbLibrary;
	}

	/**
	 * Load language files for this plugin
	 *
	 * @param   string $extension Extension name
	 * @param   string $basePath  The base path
	 */
	public function loadLanguage($extension = 'plg_system_zengridframework', $basePath = JPATH_ADMINISTRATOR)
	{
		// Load the admin language file
		$this->plugin->loadLanguage($extension, $basePath);

		// Loads English language file as fallback (for undefined stuff in other language file)
		JFactory::getLanguage()->load($extension, $basePath, 'en-GB', true);
	}

	public function getFrameworkMediaPath()
	{
		return '/media/system/zengridframework';
	}

	public function templateIsOldFramework()
	{

	}

	public function templateIsCompatible()
	{
		$template = $this->template->name;

		if (!$template)
		{
			return false;
		}

		return file_exists(JPATH_SITE . '/templates/' . $template . '/includes/config.php');
	}

	/*
	 * Method to get the parent Menu-Item of the current page
	 *
	 * @static
	 * @access public
	 * @param int $level
	 * @return string
	 */
	public function getActiveParent($level = 0)
	{
		// Fetch the active menu-item
		$menu = JFactory::getApplication()->getMenu();
		$active = $menu->getActive();

		// Get the parent (at a certain level)
		$parent = $active->tree[$level];

		// Return the title of this Menu-Item
		return $menu->getItem($parent)->name;
	}

	/*
	 * Method to determine whether the current page is the Joomla! homepage
	 *
	 * @static
	 * @access public
	 * @param null
	 * @return bool
	 */
	public function isHome()
	{
		// Fetch the active menu-item
		$menu = JFactory::getApplication()->getMenu();
		$active = $menu->getActive();

		// Return whether this active menu-item is home or not
		if (isset($active))
		return (boolean)$active->home;
		else return;
	}

	/*
	 * Method to add a global title to every page title
	 *
	 * @static
	 * @access public
	 * @param string $global_title
	 * @return null
	 */
	public function addGlobalTitle($global_title = null)
	{
		// Get the current title
		$document = JFactory::getDocument();
		$title = $document->getTitle();

		// Add the global title to the current title
		$document->setTitle($title . '' . $global_title);
	}

	/**
	 * Count modules for an array of positions
	 *
	 * @param  array  $positions Array with the positions
	 * @return int               The sum of modules on that positions
	 */
	public function countModulesForPositions($positions = array())
	{
		$total = 0;

		foreach ($positions as $position)
		{
			$total += $this->countModules($position);
		}

		return $total;
	}

	/*
	* Method to determine whether a certain module is loaded or not
	*
	* @static
	* @access public
	* @param mixed $name Can be a module name, or an array of names
	* @return boolean
	*/
	public static function hasModule($name, $title = null)
	{
		if (is_array($name))
		{
			foreach ($name as $module)
			{
				if (self::hasModule($module))
				{
					return true;
				}
			}
		}
		else
		{
			$modules	= self::loadPublishedModules();
			$total		= count($modules);
			for ($i = 0; $i < $total; $i++)
			{
				// Match the name of the module
				if ($modules[$i]->name == $name)
				{
					// Match the title if we're looking for a specific instance of the module
					if (! $title || $modules[$i]->title == $title)
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	public function getFrameworkPath()
	{
		if (self::$isJ15)
		{
			return '/plugins/system/zengridframework';
		}
		else
		{
			return '/plugins/system/zengridframework/zengridframework';
		}
	}

	/**
	 * Returns the template manifest
	 *
	 * @param  boolean $force Force to reload the manifest
	 * @return [type]         The manifest XML
	 */
	public function getFrameworkManifest($force = false)
	{
		if (!isset($this->frameworkManifest) || $force)
		{
			if (self::$isJ15)
			{
				$path = '/plugins/system/zengridframework.xml';
			}
			else
			{
				$path = $this->getFrameworkPath() . '/../zengridframework.xml';
			}

			$this->frameworkManifest = simplexml_load_file(JPATH_ROOT . $path);
		}

		return $this->frameworkManifest;
	}

	/**
	 * Returns the template manifest
	 *
	 * @param  boolean $force Force to reload the manifest
	 * @return [type]         The manifest XML
	 */
	public function getTemplateManifest($force = false)
	{
		if (!isset($this->templateManifest) || $force)
		{
			$this->templateManifest = new ZenGridTemplateManifest(JPATH_ROOT . '/templates/' . $this->template->name . '/templateDetails.xml');
		}

		return $this->templateManifest;
	}

	/**
	 * Get the template style params
	 *
	 * @param  string $param The param name
	 * @return string        The param value
	 */
	public function getParam($param)
	{
		if (!isset($this->templateParams))
		{
			if (self::$isJ15)
			{
				$template = $this->template->name;
				$cont = null;
				$ini  = JPATH_THEMES . '/' . $template . '/params.ini';
				$xml  = JPATH_THEMES . '/' . $template . '/templateDetails.xml';

				jimport('joomla.filesystem.file');
				if (JFile::exists($ini))
				{
					$cont = JFile::read($ini);
				}
				else
				{
					$cont = null;
				}

				$this->templateParams = new JParameter($cont, $xml, $template);
			}
			else
			{
				if ($this->app->isAdmin())
				{
					jimport('joomla.environment.request');
					$id	= JRequest::getInt('id');

					// Load the site template params from the database
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->select('params');
					$query->from('#__template_styles');
					$query->where('id = ' . $id);
					$db->setQuery($query);
					$result = $db->loadObject();

					$this->templateParams = new JRegistry($result->params);
				}
				else
				{
					$template = $this->app->getTemplate(true);
					$this->templateParams = $template->params;
				}
			}
		}

		$value = $this->templateParams->get($param);
		if ($value)
		{
			return $value;
		}
		else
		{
			return false;
		}
	}

	/*
	 * Method to get the HTML of a splitmenu
	 *
	 * @static
	 * @access public
	 * @param string $menu
	 * @param int $startLeve
	 * @param int $endLevel
	 * @param bool $show_children
	 * @return string
	 */
	public function getSplitMenu($menu = 'mainmenu', $startLevel = 0, $endLevel = 1, $show_children = false)
	{
		// Import the module helper
		jimport('joomla.application.module.helper');

		// Get a new instance of the mod_mainmenu module
		$module = JModuleHelper::getModule('mod_mainmenu', 'mainmenu');
		if (!empty($module) && is_object($module))
		{

			// Construct the module parameters (as a string)
			$params = "menutype=".$menu."\n"
				. "showAllChildren=".$show_children."\n"
				. "startLevel=".$startLevel."\n"
				. "endLevel=".$endLevel;
			$module->params = $params;

			// Construct the module options
			$options = array('style' => 'raw');

			// Render this module
			$document = JFactory::getDocument();
			$renderer = $document->loadRenderer('module');
			$output = $renderer->render($module, $options);

			return $output;
		}

		return null;
	}

	/*
	 * Method to determine the number of modules published to a templates module position
	 * Replicated from core J helper
	 *
	 * @static
	 * @access public
	 * @param string $condition
	 * @return int
	 */
	public function countModules($condition)
	{
		$result = '';

		if (self::$isJ15)
		{
			$words = explode(' ', $condition);
			for ($i = 0; $i < count($words); $i+=2)
			{
				// odd parts (modules)
				$name		= strtolower($words[$i]);
				$words[$i]	= ((isset($this->_buffer['modules'][$name])) && ($this->_buffer['modules'][$name] === false)) ? 0 : count(self::getModules($name));
			}
		}
		else
		{
			$document = JFactory::getDocument();
			$operators = '(\+|\-|\*|\/| == |\!=|\<\>|\<|\>|\<=|\>=|and|or|xor)';
			$words = preg_split('# '.$operators.' #', $condition, null, PREG_SPLIT_DELIM_CAPTURE);
			for ($i = 0, $n = count($words); $i < $n; $i+=2)
			{
				// odd parts (modules)
				$name      = strtolower($words[$i]);
				$buffer    = $document->getBuffer();
				$words[$i] = ((isset($buffer['modules'][$name])) && ($buffer['modules'][$name] === false)) ? 0 : count(self::getModules($name));
			}
		}

		$str = 'return '.implode(' ', $words).';';
		return eval($str);
	}

	/*
	 * Method to determine the number of modules published to a templates module position
	 *
	 * @static
	 * @access public
	 * @param string $name
	 * @return Module Object array
	 */
	public function getModules($position)
	{
		if (self::$isJ15)
		{
			$position	= strtolower($position);
			$result		= array();

			$modules = self::loadPublishedModules();

			$total = count($modules);
			for ($i = 0; $i < $total; $i++) {
				if ($modules[$i]->position == $position) {
					$result[] =& $modules[$i];
				}
			}
			if (count($result) == 0) {
				if (JRequest::getBool('tp')) {
					$result[0] = JModuleHelper::getModule('mod_'.$position);
					$result[0]->title = $position;
					$result[0]->content = $position;
					$result[0]->position = $position;
				}
			}
		}
		else
		{
			$app		= JFactory::getApplication();
			$position	= strtolower($position);
			$result		= array();

			$modules = self::loadPublishedModules();

			$total = count($modules);
			for ($i = 0; $i < $total; $i++)
			{
				if ($modules[$i]->position == $position) {
					$result[] = &$modules[$i];
				}
			}

			if (count($result) == 0) {
				if (JRequest::getBool('tp') && JComponentHelper::getParams('com_templates')->get('template_positions_display')) {
					$result[0] = self::getModule('mod_'.$position);
					$result[0]->title = $position;
					$result[0]->content = $position;
					$result[0]->position = $position;
				}
			}
		}

		return $result;
	}

	/*
	 * Method to return a certain modules parameters
	 *
	 * @static
	 * @access public
	 * @param string $name
	 * @return array
	 */
	public function getModuleParams($name = '')
	{
		// Import the module helper
		jimport('joomla.application.module.helper');

		$module = JModuleHelper::getModule($name);
		if (is_object($module))
		{
			if (self::$isJ15)
			{
				$mod_params = new JParameter($module->params);
				return $mod_params;
			}
			else
			{
				return $module->params;
			}
		}

		return false;
	}

	/*
	 * Method to return a certain module parameter for every instance of a module
	 *
	 * @static
	 * @access public
	 * @param string $name
	 * @return array
	 */
	public function getModuleParamArray($name = '', $param = '')
	{
		$result		= array();
		$modArray 	= array();
		$modules	= self::loadPublishedModules();
		$total		= count($modules);
		for ($i = 0; $i < $total; $i++)
		{
			// Match the name of the module
			if ($modules[$i]->name == $name)
			{
				$modArray[] =& $modules[$i];
			}
		}

		// Import the module helper
		jimport('joomla.application.module.helper');
		foreach ($modArray as $module)
		{
			if (is_object($module))
			{
				if (self::$isJ15)
				{
					$mod_params = new JParameter($module->params);
				}
				else
				{
					$mod_params = $module->params;
				}

				$result[] = $mod_params->get($param);
			}
		}

		return $result;
	}

	/*
	 * Method to verify if a certain module has a certain parameter value
	 *
	 * @static
	 * @access public
	 * @param string $name, $param, $value
	 * @return boolean
	 */
	public function hasModuleParamValue($name = '', $param = '', $value = '')
	{
		return in_array($value, self::getModuleParamArray($name, $param));
	}

	/*
	 * Method to return a certain modules parameters for plugin events
	 *
	 * @static
	 * @access public
	 * @param string $name
	 * @return array
	 */
	public function getModuleParamsZGF($name = '')
	{
		// Import the module helper
		jimport('joomla.application.module.helper');

		$module = self::getModule($name);
		if (is_object($module))
		{
			if (self::$isJ15)
			{
				$mod_params = new JParameter($module->params);
				return $mod_params;
			}
			else
			{
				return $module->params;
			}
		}

		return false;
	}

	/*
	 * Method to return a module
	 *
	 * @static
	 * @access public
	 * @param string $name
	 * @return Module Object
	 */
	public function getModule($name, $title = null)
	{
		$result  = null;
		$modules = self::loadPublishedModules();
		$total   = count($modules);

		for ($i = 0; $i < $total; $i++)
		{
			// Match the name of the module
			if ($modules[$i]->name == $name || $modules[$i]->module == $name)
			{
				// Match the title if we're looking for a specific instance of the module
				if (!$title || $modules[$i]->title == $title)
				{
					// Found it
					$result = &$modules[$i];
					break;	// Found it
				}
			}
		}

		// If we didn't find it, and the name is mod_something, create a dummy object
		if (is_null($result) && substr($name, 0, 4) == 'mod_')
		{
			$result = new stdClass;
			$result->id        = 0;
			$result->title     = '';
			$result->module    = $name;
			$result->position  = '';
			$result->content   = '';
			$result->showtitle = 0;
			$result->control   = '';
			$result->params    = '';
			$result->user      = 0;
		}

		return $result;
	}

	/**
	 * Load published modules
	 *
	 * @access	private
	 * @return	array
	 */
	public static function loadPublishedModules()
	{
		static $modules;

		if (isset($modules))
		{
			return $modules;
		}

		if (self::$isJ15)
		{
			global $mainframe, $Itemid;

			$user	= JFactory::getUser();
			$db		= JFactory::getDBO();

			$aid	= $user->get('aid', 0);

			$modules	= array();

			$wheremenu = isset($Itemid) ? ' AND (mm.menuid = '. (int) $Itemid .' OR mm.menuid = 0)' : '';

			$query = 'SELECT id, title, module, position, content, showtitle, control, params'
				. ' FROM #__modules AS m'
				. ' LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id'
				. ' WHERE m.published = 1'
				. ' AND m.access <= '. (int)$aid
				. ' AND m.client_id = '. (int)$mainframe->getClientId()
				. $wheremenu
				. ' ORDER BY position, ordering';

			$db->setQuery($query);

			if (null === ($modules = $db->loadObjectList())) {
				JError::raiseWarning('SOME_ERROR_CODE', JText::_('Error Loading Modules') . $db->getErrorMsg());
				return false;
			}

			$total = count($modules);
			for ($i = 0; $i < $total; $i++)
			{
				//determine if this is a custom module
				$file					= $modules[$i]->module;
				$custom 				= substr($file, 0, 4) == 'mod_' ?  0 : 1;
				$modules[$i]->user  	= $custom;
				// CHECK: custom module name is given by the title field, otherwise it's just 'om' ??
				$modules[$i]->name		= $custom ? $modules[$i]->title : substr($file, 4);
				$modules[$i]->style		= null;
				$modules[$i]->position	= strtolower($modules[$i]->position);
			}
		}
		else
		{
			$Itemid     = JRequest::getInt('Itemid');
			$app		= JFactory::getApplication();
			$user		= JFactory::getUser();
			$groups		= implode(', ', $user->getAuthorisedViewLevels());
			$lang 		= JFactory::getLanguage()->getTag();
			$clientId 	= (int) $app->getClientId();

			$cache 		= JFactory::getCache ('com_modules', '');
			$cacheid 	= md5(serialize(array($Itemid, $groups, $clientId, $lang)));

			if (!($modules = $cache->get($cacheid))) {
				$db	= JFactory::getDbo();

				$query = $db->getQuery(true);
				$query->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params, mm.menuid');
				$query->from('#__modules AS m');
				$query->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = m.id');
				$query->where('m.published = 1');

				$query->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id');
				$query->where('e.enabled = 1');

				$date = JFactory::getDate();
				$now = $date->toMySQL();
				$nullDate = $db->getNullDate();
				$query->where('(m.publish_up = '.$db->Quote($nullDate).' OR m.publish_up <= '.$db->Quote($now).')');
				$query->where('(m.publish_down = '.$db->Quote($nullDate).' OR m.publish_down >= '.$db->Quote($now).')');

				$query->where('m.access IN ('.$groups.')');
				$query->where('m.client_id = '. $clientId);
				$query->where('(mm.menuid = '. (int) $Itemid .' OR mm.menuid <= 0)');

				// Filter by language
				if ($app->isSite() && $app->getLanguageFilter()) {
					$query->where('m.language IN (' . $db->Quote($lang) . ', ' . $db->Quote('*') . ')');
				}

				$query->order('m.position, m.ordering');

				// Set the query
				$db->setQuery($query);
				$modules = $db->loadObjectList();
				$modules	= array();

				if ($db->getErrorNum()){
					JError::raiseWarning(500, JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $db->getErrorMsg()));
					return $modules;
				}

				// Apply negative selections and eliminate duplicates
				$negId	= $Itemid ? -(int)$Itemid : false;
				$dupes	= array();
				for ($i = 0, $n = count($modules); $i < $n; $i++)
				{
					$module = &$modules[$i];

					// The module is excluded if there is an explicit prohibition or if
					// the Itemid is missing or zero and the module is in exclude mode.
					$negHit	= ($negId === (int) $module->menuid)
					|| (!$negId && (int)$module->menuid < 0);

					if (isset($dupes[$module->id])) {
						// If this item has been excluded, keep the duplicate flag set,
						// but remove any item from the cleaned array.
						if ($negHit) {
							unset($modules[$module->id]);
						}
						continue;
					}

					$dupes[$module->id] = true;

					// Only accept modules without explicit exclusions.
					if (!$negHit) {
						//determine if this is a custom module
						$file				= $module->module;
						$custom				= substr($file, 0, 4) == 'mod_' ?  0 : 1;
						$module->user		= $custom;
						// Custom module name is given by the title field, otherwise strip off "mod_"
						$module->name		= $custom ? $module->title : substr($file, 4);
						$module->style		= null;
						$module->position	= strtolower($module->position);
						$modules[$module->id]	= $module;
					}
				}

				unset($dupes);

				// Return to simple indexing that matches the query order.
				$modules = array_values($modules);

				$cache->store($modules, $cacheid);
			}
		}

		return $modules;
	}

	/**
	 * Check if should clear the cache, based on request params
	 *
	 * @return mixed
	 */
	public function checkClearCacheAction()
	{
		// Check for clear cache command
		$clearCache = JRequest::getVar('clearcache', 0, 'get');
		if ($clearCache === 'css' || $clearCache === 'js')
		{
			$hasCache = false;
			$cacheDir = JPATH_ROOT . '/cache/zengridframework/' . $clearCache . '/';

			foreach (glob($cacheDir . '*') as $file)
			{
				chmod($file, 0777);
				unlink($file);
				$hasCache = true;
			}

			if (file_exists($cacheDir))
			{
				chmod($cacheDir, 0777);
				rmdir($cacheDir);
			}

			// Check if the cache was completely cleaned
			$resp = new stdClass;
			if ($hasCache)
			{
				$resp->result = count(glob($cacheDir.'*')) > 0 ? 0 : 1;
			}
			else
			{
				$resp->result = -1;
			}

			JResponse::setBody(json_encode($resp));

			return true;
		}

		return;
	}

	public function disableIECompatMode()
	{
		if (!headers_sent())
		{
			header('X-UA-Compatible: IE=edge');
		}
	}
}
