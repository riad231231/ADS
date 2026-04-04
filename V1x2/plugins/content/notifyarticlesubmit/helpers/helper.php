<?php
/**
 * Description
 *
 * @package		VirtuemartUNSO
 * @subpackage	VirtuemartUNSO.Component
 * @author Gruz <arygroup@gmail.com>
 * @copyright	Copyleft - All rights reversed
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class NotifyArticleSubmitHelper {

	/**
	 * This is a debug function. Generates a number of users for testing purposes
	 *
	 * @author Gruz <arygroup@gmail.com>
	 * @param type $name Description
	 * @return type Description
	 */
	static function userGenerator($number = 2, $groups = 'default') {
		$jinput = JFactory::getApplication()->input;
		if ($jinput->get('option') == 'com_plugins' && $jinput->get('task') == 'apply') {
			//ok
		}
		else {
			return null;
		}

		$instance = JUser::getInstance();
		jimport('joomla.application.component.helper');
		$config = JComponentHelper::getParams('com_users');
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id , title');
		$query->from('#__usergroups');
		$query->where('id IN ('.implode(',',$groups).')');
		$db->setQuery((string)$query);

		$defaultUserGroupNames = $db->loadAssocList();
		$defaultUserGroup = $db->loadResultArray();
		$acl = JFactory::getACL();

		//for each group
		for ($k = 0; $k < count($defaultUserGroupNames); $k++) {
			//for each number
			for ($i = 0; $i < $number; $i++) {
				$user = array();
				$hash = uniqid();
				if (!empty($defaultUserGroupNames)) {
					$user['fullname'] = 'Fake '.$defaultUserGroupNames[$k]['title'].' '.$hash;
				}
				else {
					$user['fullname'] = 'Fake User '.$hash;
				}
				$user['username'] = $hash;;
				$user['email'] = $hash."@test.com";
				$user['password_clear'] = $hash;

				$instance->set('id'         , 0);
				$instance->set('name'           , $user['fullname']);
				$instance->set('username'       , $user['username']);
				$instance->set('password_clear' , $user['password_clear']);
				$instance->set('email'          , $user['email']);  // Result should contain an email (check)
				$instance->set('usertype'       , 'deprecated');
				$instance->set('groups'     , array($defaultUserGroupNames[$k]['id']));

				//If autoregister is set let's register the user
				$autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $config->get('autoregister', 1);


				if ($autoregister) {
					if (!$instance->save()) {
						return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
					}
				}
				else {
					// No existing user and autoregister off, this is a temporary user.
					$instance->set('tmp_user', true);
				}
			}
		}


	}

}
