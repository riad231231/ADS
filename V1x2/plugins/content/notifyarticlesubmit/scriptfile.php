<?php
/**
 * The installer script which installs languages and performs migrating
 *
 * @package		NotifyArticleSubmit
 * @subpackage	NotifyArticleSubmit.Script
 * @author Gruz <arygroup@gmail.com>
 * @copyright	Copyleft - All rights reversed
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Script file
 */

class plgContentNotifyarticlesubmitInstallerScript {
	function __construct() {
		}
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) {
		// $parent is the class calling this method
		//$parent->getParent()->setRedirectURL('index.php?option=com_helloworld');
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) {
		// $parent is the class calling this method
		//echo '<p>' . JText::_('COM_HELLOWORLD_UNINSTALL_TEXT') . '</p>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) {
		// $parent is the class calling this method
		//echo '<p>' . JText::_('COM_HELLOWORLD_UPDATE_TEXT') . '</p>';

		if(version_compare(JVERSION,'3.0','ge')) {
			return;
		}

		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select all records from the user profile table where key begins with "custom.".
		// Order it by the ordering field.
		$query->select(array('extension_id','element','params','name'));
		$query->from('#__extensions');
		$query->where('element LIKE \'notifyarticlesubmit%\'');
		$query->order('ordering ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		// Load the results as a list of stdClass objects.
		$results = $db->loadObjectList();

		$new_params = new JRegistry;
		$new_params->{'{notificationgroup'} = new stdClass;
		$notificationgroup_example = array(
		'20111101161148}',
		'20111101161220}',
		'20111101161236}',
		'20111101161730}',
		'20111101161745}',
		'20111101161842}',
		'20111101161855}',
		'20111228015026}',
		'attachdiffinfo',
		'attachpreviousversion',
		'ausers_additionalmailadresses',
		'ausers_allowusergroups',
		'ausers_allowusergroups12}',
		'ausers_allowusergroupsselection',
		'ausers_allowusers',
		'ausers_allowusers12}',
		'ausers_allowusersselection',
		'ausers_articlegroups',
		'ausers_articlegroups12}',
		'ausers_articlegroupsselection',
		'ausers_articles',
		'ausers_articles12}',
		'ausers_articlesselection',
		'ausers_authorincludefulltext',
		'ausers_authorincludeintrotext',
		'ausers_excludeusers',
		'ausers_includearticletitle',
		'ausers_includeauthorname',
		'ausers_includebackendeditlink',
		'ausers_includecategorytree',
		'ausers_includecreateddate',
		'ausers_includefrontendeditlink',
		'ausers_includefrontendviewlink',
		'ausers_includefulltext',
		'ausers_includeintrotext',
		'ausers_includemodifieddate',
		'ausers_includemodifiername',
		'ausers_modifierincludefulltext',
		'ausers_modifierincludeintrotext',
		'ausers_notifymodifier',
		'ausers_notifymodifier1}',
		'ausers_notifyon',
		'ausers_notifyon012}',
		'ausers_notifyon02_a1}',
		'ausers_notifyon02}',
		'ausers_notifyonaction',
		'ausers_notifyonlyifcanview',
		'ausers_notifyusergroups',
		'ausers_notifyusergroups12}',
		'ausers_notifyusergroupsselection',
		'ausers_notifyusers',
		'ausers_notifyusers12}',
		'ausers_notifyusersselection',
		'ausersnotifyonaction}',
		'author_foranyuserchanges',
		'author_notifyonaction',
		'author_notifyonaction123456}',
		'emailformat',
		'emailformat_html}',
		'emailformat_plaintext}',
		'general_spaser1',
		'includediffinfo_html',
		'includediffinfo_text',
		'message_settings}',
		'messagebodycustom',
		'messagebodysource',
		'messagebodysource_custom}',
		'messagebodysource_hardcoded}',
		'messagesubjectcustom',
		'{20111101161148',
		'{20111101161220',
		'{20111101161236',
		'{20111101161730',
		'{20111101161745',
		'{20111101161842',
		'{20111101161855',
		'{20111228015026',
		'{ausers_allowusergroups12',
		'{ausers_allowusers12',
		'{ausers_articlegroups12',
		'{ausers_articles12',
		'{ausers_notifymodifier1',
		'{ausers_notifyon012',
		'{ausers_notifyon02',
		'{ausers_notifyon02_a1',
		'{ausers_notifyusergroups12',
		'{ausers_notifyusers12',
		'{ausersnotifyonaction',
		'{author_notifyonaction123456',
		'{emailformat_html',
		'{emailformat_plaintext',
		'{message_settings',
		'{messagebodysource_custom',
		'{messagebodysource_hardcoded',
		'{notificationgroup'
		);

		$main_extension_id = null;
		$extensions_to_del = array();
		foreach ($results as $key=>$value) {
			if ($value->element == 'notifyarticlesubmit') {
				$main_extension_id = $value->extension_id;
			}
			else {
				$extensions_to_del[] = $value->extension_id;
			}
			if (empty ($value->params) || strpos($value->params,'{notificationgroup') !== false ) {
				continue;
			}
			// Convert the params field to a string.
			$parameter = new JRegistry;
			$parameter->loadString($value->params);
			$params = $parameter->toObject();
			$forUsers = array('ausers_','rusers_');
			foreach ($forUsers as $usertype) {
				foreach ($notificationgroup_example as $k=>$name) {
					if ($name == '{notificationgroup'){
						if ($value->element == 'notifyarticlesubmit') {
							$new_params->{'{notificationgroup'}->{$name}[] = 'Notification rules group for '.$usertype;
						}
						else {
							$new_params->{'{notificationgroup'}->{$name}[] = $value->name .' '.$usertype;
						}
						$new_params->{'{notificationgroup'}->{$name}[] = '0';
						$new_params->{'{notificationgroup'}->{$name}[] = 'variablefield::{notificationgroup';
					}
					else if (
						strpos($name,'{') === 0
						|| strpos($name,'{') === (strlen($name)-1)
						|| in_array($name,array('attachdiffinfo','attachpreviousversion','general_spaser1','includediffinfo_html'))
					) {
						$new_params->{'{notificationgroup'}->{$name}[] = 'variablefield::{notificationgroup';
					}
					else if ($name == 'emailformat'){
						$new_params->{'{notificationgroup'}->{$name}[] = 'plaintext';
						$new_params->{'{notificationgroup'}->{$name}[] = 'variablefield::{notificationgroup';
					}
					else if ($name == 'includediffinfo_text'){
						$new_params->{'{notificationgroup'}->{$name}[] = 'none';
						$new_params->{'{notificationgroup'}->{$name}[] = 'variablefield::{notificationgroup';
					}
					else if ($name == 'messagebodycustom'){
						$new_params->{'{notificationgroup'}->{$name}[] = '';
						$new_params->{'{notificationgroup'}->{$name}[] = 'variablefield::{notificationgroup';
					}
					else if ($name == 'messagebodysource'){
						$new_params->{'{notificationgroup'}->{$name}[] = 'hardcoded';
						$new_params->{'{notificationgroup'}->{$name}[] = 'variablefield::{notificationgroup';
					}
					else if ($name == 'messagesubjectcustom'){
						$new_params->{'{notificationgroup'}->{$name}[] = '%SITELINK% : %ACTION% article "%TITLE%", user %MODIFIER%';
						$new_params->{'{notificationgroup'}->{$name}[] = 'variablefield::{notificationgroup';
					}
					else if ($usertype == 'rusers_' && (strpos($name,'author_')===0  || in_array($name, array('ausers_authorincludeintrotext', 'ausers_authorincludefulltext') ) )){
						$new_params->{'{notificationgroup'}->{$name}[] = "0";
						$new_params->{'{notificationgroup'}->{$name}[] = 'variablefield::{notificationgroup';
					}
					else {
						if ($usertype == 'rusers_') {
							$kkey = str_replace ('ausers_',$usertype,$name);
						}
						else {
							$kkey = $name;
						}
						if (isset($params->$kkey)) {
							if (is_string($params->$kkey)) {
								$new_params->{'{notificationgroup'}->{$name}[] = $params->$kkey;
								$new_params->{'{notificationgroup'}->{$name}[] = 'variablefield::{notificationgroup';
							}
							if (is_array($params->$kkey)) {
								foreach ($params->$kkey as $val) {
									$new_params->{'{notificationgroup'}->{$name}[] = array($val);
								}
								$new_params->{'{notificationgroup'}->{$name}[] = array('variablefield::{notificationgroup');
							}
							unset($params->$kkey);
						}
						else {
							if (in_array($kkey, array ($usertype.'allowusergroupsselection',$usertype.'articlegroupsselection',$usertype.'notifyusergroupsselection') ) ) {
								$new_params->{'{notificationgroup'}->{$name}[] = array('variablefield::{notificationgroup');
							}
							else {
								$new_params->{'{notificationgroup'}->{$name}[] = 'variablefield::{notificationgroup';
							}
						}

					}
				}

			}
			foreach ($params as $k=>$v) {
				$new_params->$k = $v;
			}
		}

		// Create a new query object.
		$query = $db->getQuery(true);

		//Build the query
		$query->update("#__extensions");
		$query->set('params = '.$db->quote(json_encode($new_params)));
		$query->where('extension_id = '. $db->quote($main_extension_id));
		$db->setQuery($query);
		//execute db object
		try {
		// Execute the query in Joomla 3.0.
		$result = $db->execute();
		} catch (Exception $e) {
		//print the errors
		print_r($e);
		}

		foreach ($extensions_to_del as $k=>$id) {
			$installer = new JInstaller;
			$installer->uninstall('plugin',(integer)$id,1);
		}


	}



	/**
	 * A small helper class to get extension name from $this class name
	 *
	 * Full description (multiline)
	 *
	 * @author Gruz <arygroup@gmail.com>
	 * @param	type	$name	Description
	 * @return	type			Description
	 */
	static function getExtensionName() {
		$className = get_called_class();
		preg_match('~plg(?:authentication|captcha|content|editors|editors-xtd|extension|finder|jmonitoring|quickicon|search|system|user|xmap)(.*)InstallerScript~Ui',$className,$matches);
		if (isset($matches[1])) {
			return strtolower($matches[1]);
		}
		return false;
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) {
		$manifest = $parent->getParent()->getManifest();
		//$this->plg_name = self::getExtensionName();
		$this->plg_name = $this->getExtensionName();
		$this->plg_type = $manifest['group'];
		$this->plg_full_name = 'plg_'.$this->plg_type.'_'.$this->plg_name;
		$this->langShortCode = null;//is used for building joomfish links
		$this->default_lang = JComponentHelper::getParams('com_languages')->get('admin');
		$language = JFactory::getLanguage();
		$language->load($this->plg_full_name, dirname(__FILE__), 'en-GB', true);
		$language->load($this->plg_full_name, dirname(__FILE__), $this->default_lang, true);

		/*
		if ($type == 'uninstall') {
			//Get the smallest order value
			$db = JFactory::getDbo();

			// Unpublish native SEF
			$query = $db->getQuery(true);
			// Fields to update.
			$fields = array(
				$db->quoteName('enabled').'='.$db->Quote('1')
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('type').'='.$db->Quote('plugin'),
				$db->quoteName('folder').'='.$db->Quote('system'),
				$db->quoteName('element').'='.$db->Quote('sef'),
				$db->quoteName('name').'='.$db->Quote('plg_system_sef')
			);
			$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();
		}
		*/


		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		//echo '<p>' . JText::_('COM_HELLOWORLD_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) {
		$manifest = $parent->getParent()->getManifest();

		//if (false ) {
		if ($type == 'install' || $type == 'update' ) {
			//Get the smallest order value
			$db = JFactory::getDbo();


			// Publish plugin
			$query = $db->getQuery(true);
			// Fields to update.
			$fields = array(
				$db->quoteName('enabled').'='.$db->Quote('1')
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('type').'='.$db->Quote($manifest['type']),
				$db->quoteName('folder').'='.$db->Quote($manifest['group']),
				$db->quoteName('element').'='.$db->Quote($this->plg_name),
				$db->quoteName('name').'='.$db->Quote($this->plg_full_name)
			);
			$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();


			/*
			// Unpublish native SEF
			$query = $db->getQuery(true);
			// Fields to update.
			$fields = array(
				$db->quoteName('enabled').'='.$db->Quote('0')
			);
			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('type').'='.$db->Quote('plugin'),
				$db->quoteName('folder').'='.$db->Quote('system'),
				$db->quoteName('element').'='.$db->Quote('sef'),
				$db->quoteName('name').'='.$db->Quote('plg_system_sef')
			);
			$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();
			$this->messages[] = JText::_('JUNPUBLISHED').' '.'plg_system_sef';
			*/
			$this->messages[] = JText::_('JPUBLISHED').' '.$this->plg_full_name;
		}

		if ($type != 'uninstall') {
			$this->installExtensions($parent);
		}


		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		//echo '<p>' . JText::_('COM_HELLOWORLD_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
		if (!empty($this->messages)) {
			echo '<ul><li>'.implode('</li><li>',$this->messages).'</li></ul>';
		}
	}
	private function installExtensions ($parent) {
		jimport('joomla.filesystem.folder');
		jimport('joomla.installer.installer');

		JLoader::register('LanguagesModelInstalled', JPATH_ADMINISTRATOR.'/components/com_languages/models/installed.php');
		$lang = new LanguagesModelInstalled();
		$current_languages = $lang ->getData();
		$locales = array();
		foreach($current_languages as $lang) {
			$locales[]=$lang->language;
		}


		$extpath = dirname(__FILE__).'/extensions';
		if (!is_dir($extpath)) {
			return;
		}
		$folders = JFolder::folders ($extpath);
		foreach ($folders as $folder) {

			$folder_temp = explode('.',$folder);
			if (isset ($folder_temp[1])) {
				if (!in_array($folder_temp[0],$locales)) {
					continue;
				}
			}

			$installer = new JInstaller();
			if ($installer->install($extpath.'/'.$folder)) {
				$manifest = $installer->getManifest();
				$this->messages[] = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS','<b style="color:#0055BB;">['.$manifest->name.']<span style="color:green;">').'</span></b>';
			}
			else {
				$this->messages[] = '<span style="color:red;">'.$folder . ' '.JText::_('JERROR_AN_ERROR_HAS_OCCURRED') . '</span>';
			}
		}
	}

}
?>

