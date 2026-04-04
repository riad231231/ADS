<?php
/**
 * A plugin which sends notifications when an article is added or modified at a Joomla web-site
 *
 * @package		NotifyArticleSubmit
 * @author Gruz <arygroup@gmail.com>
 * @copyright	Copyleft - All rights reversed
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

$com_path = JPATH_SITE.'/components/com_content/';
require_once $com_path.'router.php';
require_once $com_path.'helpers/route.php';
if (!class_exists ('NotifyArticleSubmitHelper') ) { require_once (dirname(__FILE__).'/helpers/helper.php'); }

jimport( 'gjfields.helper.plugin' );

if (!class_exists('JPluginGJFields')) {
	JFactory::getApplication()->enqueueMessage('Strange, but missing GJFields library for <span style="color:black;">'.__FILE__.'</span><br> The library should be installed together with the extension... Anyway, reinstall it: <a href="http://www.gruz.org.ua/en/extensions/gjfields-sefl-reproducing-joomla-jform-fields.html">GJFields</a>', 'error');
}
else {
	class plgContentNotifyarticlesubmitCore extends JPluginGJFields {

		protected $previous_state;
		protected $broken_sends;
		protected $isNew;
		protected $publish_state;
		protected $article;
		protected $sitename;
		protected $article_info_ausers;
		protected $introtext;
		protected $fulltext;
		protected $config;
		protected $plg_type;
		protected $plg_name;
		protected $plg_full_name;
		protected $langShortCode;
		protected $diffTypes = array ('Text/Unified','Text/Context','Html/SideBySide','Html/Inline');

		public function __construct(& $subject, $config) {
			parent::__construct($subject, $config);
			$jinput =  JFactory::getApplication()->input;
			//if ($jinput->get('option',null) == 'com_dump') { return; }

			$this->getGroupParams('{notificationgroup');// Get variable fields params parsed in a nice way, stored to $this->pparams
			// Determine if at least according to one rule the article notification is on
			$this->prepare_previous_versions_flag = array();
			$this->includeDiffInBody = false;

			foreach ($this->pparams as $rule_number=>$rule) {
				$this->pparams[$rule_number] = (object) $rule;
				if (isset($this->pparams[$rule_number]->attachpreviousversion) ) {
					foreach ($this->pparams[$rule_number]->attachpreviousversion as $k=>$v) {
						$this->prepare_previous_versions_flag[$v] = $v;
					}
				}
				$this->prepare_diff_flag = array();
				if (isset($this->pparams[$rule_number]->attachdiffinfo) ) {
					foreach ($this->pparams[$rule_number]->attachdiffinfo as $k=>$v) {
						$this->prepare_diff_flag[$v] = $v;
					}
				}
				if ($this->pparams[$rule_number]->emailformat == 'plaintext' && $this->pparams[$rule_number]->includediffinfo_text != 'none' ) {
					$this->includeDiffInBody = $this->pparams[$rule_number]->includediffinfo_text;
				}
				else if ($this->pparams[$rule_number]->emailformat == 'html' && $this->pparams[$rule_number]->includediffinfo_html != 'none' ) {
					$this->includeDiffInBody = $this->pparams[$rule_number]->includediffinfo_html;
				}

				if ($this->pparams[$rule_number]->messagebodysource == 'custom') {
					foreach ($this->diffTypes as $diffType) {
						if (strpos($this->pparams[$rule_number]->messagebodycustom,'%DIFF '.$diffType.'%') !== false) {
							$this->prepare_diff_flag[$diffType] = $diffType;
						}
					}
				}
			}

			//define some variables
			$this->broken_sends = array();
			$this->isNew = false;
			$this->publish_state_change = 'nochange';
			$this->config = $config;


			//generate users
			while(true) {
				if (!$this->paramGet( 'debug' )) { break; }
				if (!$this->paramGet( 'generatefakeusers' )) { break; }

				$usergroups = $this->paramGet( 'fakeusersgroups' );
				if ($usergroups == 'custom') {
					$usergroups = explode (',',$this->paramGet( 'customfakeusersgroups' ));
				}
				$usernum = (integer) $this->paramGet( 'fakeusersnumber' );


				if ($this->isFirstRun('userGenerator')) {
					NotifyArticleSubmitHelper::userGenerator($usernum,$usergroups);
				}
				break;

			}


			//Do not run this block on other pages except the article edit form
			if ($this->isArticlePage() ) {

				//prepare the object to be passed to $this->_checkAllowed
				$article = new stdClass;
				$article->id = null;
				$article->catid = null;

				$app = JFactory::getApplication();
				$jinput = JFactory::getApplication()->input;

				if ( $app->isAdmin() ) {
					$article->id = $jinput->get('id');
				}
				else {
					$article->id = $jinput->get('a_id');;
				}


				if (!empty($article->id) ) {
					$db = JFactory::getDBO();
					$query = $db->getQuery(true);
					$query->select('catid');
					$query->select('created_by');
					$query->from('#__content');
					$query->where($db->quoteName('id') .' = '. $db->Quote($article->id));
					$db->setQuery((string)$query);
					$res = $db->loadObject();
					$article = (object) array_merge((array) $article, (array) $res);
				}

				$user =  JFactory::getUser();


				$articleAllowedAusers = $UserAmongAllowedAusers = $notifyAuthor = $notifyModifier = false;
				foreach ($this->pparams as $rule_number=>$rule) {
					//do not notify if ausers if set  never to notify
					if ($rule->ausers_notifyon == 3) {
						unset($this->pparams[$rule_number]);
						continue;
					}

					$this->rule = $rule;

					$UserAmongAllowedAusers = $this->_checkAllowed ($user, $paramName = 'allowuser');
					if ($UserAmongAllowedAusers) { break; }

					// The NSwitch must be shown anyway, is the author or modifier has to be notified
					// Check if the author has to be modified
					$notifyAuthor = false;
					// If authorn has to be notified is some case (not NEVER value)
					if ($this->rule->author_notifyonaction != 0 ) {
						// if the author must be modified for every modifier
						if ($this->rule->author_foranyuserchanges == 1 )  {
							$notifyAuthor = true;
						}
						// If author has to be modified for allowed users and the user is allowed, then
						elseif ($UserAmongAllowedAusers) {
								$notifyAuthor = true;
						}

					}
					if ($notifyAuthor) { break; }

					$notifyModifier = false;
					if ($this->rule->ausers_notifymodifier == 1 && $article->created_by != $user->id) {
						$notifyModifier = true;
					}
					if ($notifyModifier) { break; }


					$articleAllowedAusers = $this->_checkAllowed ($article, $paramName = 'article');
					if ($articleAllowedAusers) { break; }
				}



				$session = JFactory::getSession();
				// If notification is allowed in the current instance plugin, then set the flag value to true.
				// By default we consider the flag is false, it's checked in $this->showSwitchCheck()
				if ( $articleAllowedAusers || $UserAmongAllowedAusers || $notifyAuthor || $notifyModifier) {
					$session->set('NotificationIsAllowedInTheArticle',true,'NotifyArticleSubmit');
				}
			}

		}


		/**
		 * Checks if current page is an aritcle edit page
		 *
		 * @author Gruz <arygroup@gmail.com>
		 * @return void
		 */
		function isArticlePage() { // TODO Why not just check context ???
			$jinput = JFactory::getApplication()->input;
			$option = $jinput->get('option');

			if ($option != 'com_content') {
				return false;
			}

			$layout = $jinput->get('layout');
			$view = $jinput->get('view');
			$task = $jinput->get('task');
			$app = JFactory::getApplication();

			if ( $app->isAdmin() ) {
				if ($view == 'article' && $layout == 'edit') {
					return true;
				}
				return false;
			}
			else {
				if ($view == 'form' && $layout == 'edit') {
					return true;
				}
				return false;
			}

		}

		/**
		 * Run plugin on change article state
		 */
		public function onContentChangeState($context, $pks, $value) {
			if ($context != 'com_content.article' || ($value != '1' && $value != '0')) {
				return;
			}
			$this->previous_state = 1 - $value;
			foreach ($pks as $id) {
				$article = JTable::getInstance( 'content' );
				$article->load( $id );
				$this->onContentAfterSave($context, $article, false);
			}

			return true;
		}

		/**
		* Save previous state to a variable to check if it has been changed after content save
		*/
		public function onContentBeforeSave($context, $article, $isNew) {

			$this->previous_article = JTable::getInstance('content');
			$this->previous_article->load($article->id);
			$this->previous_state = $this->previous_article->state;


			$confObject = JFactory::getApplication();
			$tmpPath = $confObject->getCfg('tmp_path');

			foreach ($this->prepare_previous_versions_flag as $k=>$v) {
				$this->attachments[$v] = $tmpPath.'/prev_version_id_'.$this->previous_article->id.'.'.$v;
				switch ($v) {
					case 'html':
					case 'txt':
						$text = '';
						$text .= '<h1>'.$this->previous_article->title.'</h1>'.PHP_EOL;
						$text .= '<br />'.$this->previous_article->introtext.PHP_EOL;
						if (!empty($this->previous_article->fulltext)) {
							$text .= '<hr id="system-readmore" />'.PHP_EOL.PHP_EOL.$this->previous_article->fulltext;
						}
						if ($v == 'txt') {
							if (!class_exists ('html2text') ) { require_once (dirname(__FILE__).'/helpers/html2text.php'); }
							// Instantiate a new instance of the class. Passing the string
							// variable automatically loads the HTML for you.
							$h2t = new html2text($text);
							$h2t->width = 120;
							// Simply call the get_text() method for the class to convert
							// the HTML to the plain text. Store it into the variable.
							$text = $h2t->get_text();
							unset ($h2t);
						}
						break;
					case 'sql':
						$db = JFactory::getDBO();
						$db->getPrefix();
						$text = 'UPDATE '.$db->getPrefix().'content SET ';
						$parts = array();
						foreach ($this->previous_article as $field=>$value) {
							if (is_string($value)) {
								$parts[] = $db->quoteName($field).'='.$db->quote($value);
							}
						}
						$text .= implode (',',$parts);
						$text .= ' WHERE '.$db->quoteName('id').'='.$db->quote($this->previous_article->id);
						break;
					default :
						$this->attachments[$v] = null;
						break;
				}
				if (!empty($this->attachments[$v])) {
					JFile::write($this->attachments[$v],$text);
				}

			}
			$this->noDiffFound = false;
			if (!empty($this->prepare_diff_flag) || $this->includeDiffInBody !== false ) {
				if (!class_exists ('Diff') ) { require_once (dirname(__FILE__).'/helpers/Diff.php'); }
				$options = array(
					//'ignoreWhitespace' => true,
					//'ignoreCase' => true,
				);
				$old = array();
				$old[] = '<h1>'.$this->previous_article->title.'</h1>';
				$introtext = explode (PHP_EOL,JString::trim($this->previous_article->introtext));
				$old = array_merge($old,$introtext);
				if (!empty($this->previous_article->fulltext)) {
					$old[] = '<hr id="system-readmore" />';
					$fulltext = explode (PHP_EOL,JString::trim($this->previous_article->fulltext));
					$old = array_merge($old,$fulltext);
				}

				$new = array();
				$new[] = '<h1>'.$article->title.'</h1>';
				$introtext = explode (PHP_EOL,JString::trim($article->introtext));
				$new = array_merge($new,$introtext);
				if (!empty($article->fulltext)) {
					$new[] = '<hr id="system-readmore" />';
					$fulltext = explode (PHP_EOL,JString::trim($article->fulltext));
					$new = array_merge($new,$fulltext);
				}
				// Initialize the diff class
				$diff = new Diff($old, $new, $options);

				$css = JFile::read(dirname(__FILE__).'/helpers/Diff/styles.css');
			}

			$path = $tmpPath.'/diff_id_'.$this->previous_article->id;
			if ($this->includeDiffInBody !== false && !isset($this->prepare_diff_flag[$this->includeDiffInBody])) {
				$this->prepare_diff_flag[$this->includeDiffInBody] = $this->includeDiffInBody;
			}
			foreach ($this->prepare_diff_flag as $k=>$v) {
				$useCSS = false;
				switch ($v) {
					case 'Text/Unified':
					case 'Text/Context':
						$fileNamePart = explode ('/',$v);
						$this->attachments[$v] = $path.'_'.$fileNamePart[1].'.txt';
						break;
					case 'Html/SideBySide':
					case 'Html/Inline':
						$useCSS = true;
						$fileNamePart = explode ('/',$v);
						$this->attachments[$v] = $path.'_'.$fileNamePart[1].'.html';
						break;
					default :
						$this->attachments[$v] = null;
						break;
				}
				$className = 'Diff_Renderer_'.str_replace('/','_',$v);
				if (!class_exists($className)) {
					require_once (dirname(__FILE__).'/helpers/Diff/Renderer/'.$v.'.php');
				}
				// Generate a side by side diff
				$renderer = new $className;
				$text = $diff->Render($renderer);
				if (empty($text)) {
					unset($this->attachments[$v]);
					$this->noDiffFound = true;
					break;
				}
				$this->diffs[$v] = $text;
				if ($useCSS) {
					$this->diffs[$v] = '<style>'.$css.'</style>'.PHP_EOL.$text;
				}
				if ($useCSS) {
					$text = '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><style>'.$css.'</style></head><body>'.PHP_EOL.$text.'</body></html>';
				}
				if (!empty($this->attachments[$v])) {
					JFile::write($this->attachments[$v],$text);
				}
			}
			$session = JFactory::getSession();
			if (!empty($this->attachments) ) {
				$session->set('Attachments',$this->attachments,'NotifyArticleSubmit');
			}
		}


		//public function onContentAfterSave($context, &$article, $isNew) {
		public function onContentAfterSave($context, $article, $isNew) {
			$this->showSwitchCheck();
			// Blocks executing the plugin in no-articles
			if ($this->paramGet( 'shownotificationswitch') ){
				if ($this->showSwitchCheckFlag === true && json_decode($article->attribs)->runnotifyarticlesubmit != 1 ) {
					return;
				}
			}
			if ($context != 'com_content.article' && $context != 'com_content.form') {
				return;
			}
			$this->article = &$article;
			$this->isNew = $isNew;
			$config = JFactory::getConfig();

			$this->sitename = $config->get('sitename');
			if (trim ($this->sitename) == '') {
				$this->sitename = JURI::root();
			}
			$user =  JFactory::getUser();
			$app = JFactory::getApplication();

			$ShowSuccessMessage = $this->paramGet( 'showsuccessmessage' );
			$this->SuccessMessage = '';
			if ($ShowSuccessMessage == 1) {
				$this->SuccessMessage = $this->paramGet( 'successmessage');
			}
			$ShowErrorMessage = $this->paramGet( 'showerrormessage');
			$this->ErrorMessage = '';
			if ($ShowErrorMessage == 1) {
				$this->ErrorMessage = $this->paramGet( 'errormessage');
			}

			// If Notification has to be sent
			if ($this->previous_state == $article->state) {
				$this->publish_state_change = 'nochange';
			}
			else {
				switch ($article->state) {
					case '1':
						$this->publish_state_change = 'published';
						break;
					case '0':
						$this->publish_state_change = 'unpublished';
						break;
					case '2':
						$this->publish_state_change = 'archived';
						break;
					case '-2':
						$this->publish_state_change = 'trashed';
						break;
				}
			}

			$this->author = JFactory::getUser( $article->created_by );
			if ($article->modified_by > 0) {
				$this->modifier = JFactory::getUser( $article->modified_by );
			}
			else {
				$this->modifier = JFactory::getUser();
			}

			foreach ($this->pparams as $rule_number=>$rule) {
				// TODO Add compatibility with FaLang Load joomfish translated params if ( $this->j15_loadjoomfishparams() == false ) { return; }
				//do not notify if ausers if set  never to notify
				if ($rule->ausers_notifyon == 3) {
					unset($this->pparams[$rule_number]);
					continue;
				}

				$this->rule = $rule;

				$AUsers_to_send = $this->_users_to_send();
				$users_to_send_helper = $this->addAuthorModifier();
				$AUsers_to_send = array_merge($AUsers_to_send,$users_to_send_helper);
				$AUsers_to_send = $this->_add_remove_mails ($AUsers_to_send);

				if ($this->paramGet( 'debug' ) ) {
					//if jdump extension is installed and enabled
					$debugmsg = 'No messages are sent in the debug mode. You can check the users to be notified.';
					if  (function_exists('dump') ) {
						dumpMessage ($debugmsg);
						dump ($AUsers_to_send,'$AUsers_to_send');
					}
					else {
						ob_start();
							echo '<div style="color:red;">'.$debugmsg.'</div>';
							echo '<pre>$AUsers_to_send = ';
							print_r($AUsers_to_send);
							echo '</pre>';
						$buffer = ob_get_contents();
						ob_end_clean();
						JFactory::getApplication()->enqueueMessage( $buffer, 'notice');
						unset($buffer);
					}
					// DO NOT SEND ANY MAILS ON DEBUG
					return;
				}
				// ** Notify admin users **
				// Отримати з функцій відправки листів масиви, зклеїти їх. Якщо результуючий масив не пустий, то вивести повідомлення про помилку. Якщо пустий, то про успіх.
				$this->_send_mails ($AUsers_to_send);
				// ** Notify regular users **

				if (!empty ($this->broken_sends) && !empty($this->ErrorMessage)  ) {
					// User has back-end access
					$canLoginBackend =  $user->authorise('core.login.admin');
					if ($canLoginBackend ) {
						$email = " ".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_EMAILS').implode(" , ",$this->broken_sends);
					}
					$app->enqueueMessage( JText::_($this->ErrorMessage).' '.$email, 'error' );

				}
				else if (empty ($this->broken_sends) && !empty($this->SuccessMessage) ) {
					if (!empty($AUsers_to_send) ) {
						$app->enqueueMessage( JText::_($this->SuccessMessage ));
					}
				}

			}


		}

		protected function _send_mails (&$Users_to_send) {
			if (empty($Users_to_send) ) { return; }
			$app = JFactory::getApplication();
			foreach ($Users_to_send as $key=>$value)  {
				if (!empty($value['id'])) {
					$user = JFactory::getUser($value['id']);
				}
				else {
					$user = JFactory::getUser(0);
					$user->set('email',$value['email']);
				}
				if ($this->rule->messagebodysource == 'hardcoded') {
					$mail = $this->buildMailHardcoded($user);
				}
				else {
					$mail = $this->buildMailCustom($user);
				}
				if (!$mail) {
					continue;
				}

				$mailer = JFactory::getMailer();
				if ($this->rule->emailformat != 'plaintext') {
					$mailer->isHTML(true);
					$mailer->Encoding = 'base64';
				}
				if ($this->rule->emailformat != 'plaintext') {

				}

				$mailer->setSubject($mail['subj'] );
				$mailer->setSender(array($app->getCfg('mailfrom'), $app->getCfg('fromname')));
				$mailer->addRecipient($mail['email']);

				if (isset($this->rule->attachpreviousversion) ) {
					foreach ($this->rule->attachpreviousversion as $k=>$v) {
						if (isset($this->attachments[$v])) {
							$mailer->addAttachment($this->attachments[$v]);
						}
					}
				}
				if (isset($this->rule->attachdiffinfo) ) {
					foreach ($this->rule->attachdiffinfo as $k=>$v) {
						if (isset($this->attachments[$v])) {
							$mailer->addAttachment($this->attachments[$v]);
						}
					}
				}



				$mailer->setBody($mail['body']);
				$send = $mailer->Send();

				if ( $send !== true ) {
					$this->broken_sends[] = $mail['email'];
				}
			}
		}//function

		protected function _users_to_send () {
			$nofityOn = $this->rule->ausers_notifyon;

			$onAction = $this->rule->ausers_notifyonaction;
			//if never notify
			if ($nofityOn ==  3 ) {
				return array ();
			}
			//if noify only at New, but the article is not new
			if ($nofityOn ==  1 && !$this->isNew ) {
				return array ();
			}
			//if notify only at Updated, but the article is New
			if ($nofityOn ==  2 && $this->isNew ) {
				return array ();
			}
			//If on Publish or Unpublish only but article state was not changed
			if (($onAction == 1 || $onAction == 2 || $onAction == 6) && $this->publish_state_change == 'nochange' ) {
				return array ();
			}
			//If on changes in Published only, but the article is unpublished
			if ($onAction == 3 && $this->article->state == '0' ) {
				return array ();
			}
			//If on changes in Unpublished only, but the article is published
			if ($onAction == 4 && $this->article->state == '1' ) {
				return array ();
			}
			//If on Publish only, but the article was unpublished
			if ($onAction == 1 && $this->publish_state_change == 'unpublished'  ) {
				return array ();
			}
			//If on Unpublish only, but the article was published
			if ($onAction == 2 && $this->publish_state_change == 'published'  ) {
				return array ();
			}
			$user =  JFactory::getUser();

			//check if notifications turned on for current user
			if (!$this->_checkAllowed ($user, $paramName = 'allowuser')) {
				return array ();
			}
			//check if notifications turned on for current article
			if (!$this->_checkAllowed ($this->article, $paramName = 'article')) {
				return array ();
			}
			static $prevent_from_sending = array();
			$users_to_send = array();
			$UserIds = array ();

			$paramName = 'notifyuser';
			$groupName = 'ausers_'.$paramName.'groups';
			$itemName = 'ausers_'.$paramName.'s';

			$onGroupLevels = $this->rule->{$groupName};
			$onItems = $this->rule->{$itemName};

			$db = JFactory::getDBO();
			//create WHERE conditions
			$GroupLevels = array();
			while (true) {
				//if no limitation set
				if (($onGroupLevels == 0  && $onItems == 0) || $onGroupLevels == 0  && $onItems == 1 ) {
					break;
				}

				if ($onGroupLevels != 0) {
					$GroupLevels = (array)$this->rule->{$groupName.'selection'};
					foreach ($GroupLevels as $k=>$v) {
						$GroupLevels[$k] = $db->Quote((int)$GroupLevels[$k]);
					}
					$GroupWhere = 'AND';

					if ($onGroupLevels == 1) {
						$GroupWhere = 'AND';
					}
					else if ($onGroupLevels == 2) {
						$GroupWhere = 'NOT';
					}
				}
				if ($onItems != 0) {
					$UserIds = explode (',',$this->rule->{$itemName.'selection'} );
					foreach ($UserIds as $k=>$v) {
						$UserIds[$k] = $db->Quote((int)$UserIds[$k]);
					}

					$UserWhere = 'AND';
					if ($onItems == 1) {
						$UserWhere = 'AND';
					}
					else if ($onItems == 2) {
						$UserWhere = 'NOT';
					}
				}
				break;
			}

			$GroupLevels = array_filter($GroupLevels);
			$UserIds = array_filter($UserIds);
			$prevent_from_sending = array_filter($prevent_from_sending);
			$query = $db->getQuery(true);
			$query->select('name, username, email, id, group_id as gid ');
			$query->from('#__users AS users');
			$query->leftJoin('#__user_usergroup_map AS map ON users.id = map.user_id');
			$query->where('block = 0');
			$query->where($db->quoteName('id')." != ".$db->Quote($this->article->created_by));
			if (!empty($this->article->modified_by) && $this->article->modified_by != $this->article->created_by) {
				$query->where(" id != ".$db->Quote($this->article->modified_by));
			}

			if (!empty($GroupLevels)) {
				$where = '';
				if ($GroupWhere == 'NOT') {
					$where .= $GroupWhere;
				}
				$where .= ' ( group_id = '.implode(' OR group_id = ',$GroupLevels).')';
				$query->where($where);
			}

			if (!empty($UserIds)) {
				$where = '';
				if ($UserWhere == 'NOT') {
					$where .= $UserWhere;
				}
				else {
					$where .= 'TRUE OR';
				}

				$where .= ' ( id = '.implode(' OR id=',$UserIds).')';
				$query->where($where);
			}

			if (!empty($prevent_from_sending)) {
				$where = ' NOT ';

				$where .= ' ( id = '.implode(' OR id=',$prevent_from_sending).')';
				$query->where($where);
			}
			$query->group('id');

			$db->setQuery((string)$query);
			$users_to_send = $db->loadAssocList();
			$users_to_send = $this->_add_remove_mails ($users_to_send);
			if (!empty($users_to_send) ) {
				foreach ($users_to_send as $k=>$v) {
					if (!empty($v['id'])) {
						$prevent_from_sending[] = $v['id'];
					}

				}
			}

			$notifyonlyifcanview = $this->rule->ausers_notifyonlyifcanview;
			if ($notifyonlyifcanview) {
				foreach ($users_to_send as $k=>$value) {
					if (!empty($value['id'])) {
						$user = JFactory::getUser($value['id']);
					}
					else {
						$user = JFactory::getUser(0);
						$user->set('email',$value['email']);
					}
					$canView = false;
					//$canEdit = $user->authorise('core.edit', 'com_content.article.'.$this->article->id);
					//$canLoginBackend =  $user->authorise('core.login.admin');
					if (in_array($this->article->access, $user->getAuthorisedViewLevels())) {
						$canView = true;
					}
					if (!$canView) {
						unset($users_to_send[$k]);
					}
				}
			}

			return (array) $users_to_send;

		}

		protected function addAuthorModifier () {

			$users_to_send_helper = array();
			//add modifier and author emails
			while(true) {
				//check if to add modifier
				while (true) {
					if ($this->author->id == $this->modifier->id) { break; }
					if ($this->rule->ausers_notifymodifier == '0') {break;}
					$users_to_send_helper[] = array ('id'=>$this->modifier->id,'email'=>$this->modifier->email,'name'=>$this->modifier->name,'username'=>$this->modifier->username);
					break;
				}
				if ($this->rule->author_foranyuserchanges == '0' && !$this->_checkAllowed ($this->modifier, $paramName = 'allowuser') ) {	break;}
				//if always notify author
				$nauthor = $this->rule->author_notifyonaction;

				if ($nauthor == '5') {
					$users_to_send_helper[] = array ('id'=>$this->author->id,'email'=>$this->author->email,'name'=>$this->author->name,'username'=>$this->author->username);
					break;
				}


				//if never to notify author
				if ($nauthor == '0') {break;}

				//if notify on `publish only` or on `unpublish only`, but the state was not changed
				if ($this->publish_state_change == 'nochange' && ($nauthor == '1' || $nauthor == '2') ) {break;}

				//if notify on `publish or on unpublish` , but the state was not changed
				if ($this->publish_state_change == 'nochange' && $nauthor == '6' ) {break;}

				//if article is unpublished but is set to notify only in published articles
				if ($this->article->state ='0' && $nauthor == '3' ) {break;}

				//if article is published but is set to notify only in unpublished articles
				if ($this->article->state == '1' && $nauthor == '4' ) {break;}


				//add author to the list of receivers
				$users_to_send_helper[] = array ('id'=>$this->author->id,'email'=>$this->author->email);

				break;
			}
			return $users_to_send_helper;
		}

		function getP ($name, $forUsers) {
			if ($forUsers == 'ausers') {
				return $this->rule->{$name};
			}
			else {
				return $this->paramGet($name);
			}
		}

		protected function _checkAllowed (&$object, $paramName, $forUsers='ausers' ) {

			//If notifyon is set to Never, then return false
			if ($this->getP($forUsers.'_notifyon',$forUsers) == 3) {
				return false;
			}
			$groupName = $forUsers.'_'.$paramName.'groups';
			$itemName = $forUsers.'_'.$paramName.'s';
			$onGroupLevels = $this->getP($groupName,$forUsers);
			$onItems = $this->getP($itemName,$forUsers);

			//allowed for all
			if ($onGroupLevels == 0  && $onItems == 0) {
				return true;
			}
			switch ($paramName) {
				// if means &object is user, not article
				case "allowuser":
					$object->temp_gid = $object->get('groups');
					break;
				// if means &object is article, not user
				default:
					$object->temp_gid = (array) $object->catid;
					break;
			}

			//If not all grouplevels allowed then check if current user is allowed
			$isOk = false;

			if ($onGroupLevels != 0 ) {
				$GroupLevels = (array) $this->getP( $groupName.'selection',$forUsers );
				//Check only categories, as there are no sections
				$gid_in_array = false;
				foreach ($object->temp_gid as $gid)  {
					if (in_array($gid,$GroupLevels)) {
						$gid_in_array = true;
					}
				}

				if ($onGroupLevels == 1 && $gid_in_array) {
					$isOk = true;
				}
				else if ($onGroupLevels == 2 && !$gid_in_array) {
					$isOk = true;
				}
			}
			else {
				$isOk = true;
			}
			$groupCheck = $isOk;

			$isOk = false;

			//If not all user allowed then check if current user is allowed
			if ($onItems != 0 ) {
				$Items = $this->getP( $itemName.'selection',$forUsers );
				$Items = explode (",",$Items );
				$Items = (array) $Items;

				$item_in_array = in_array($object->id,$Items);
				if ($onItems == 1 && $item_in_array) {
					$isOk = true;
				}
				else if ($onItems == 2 && !$item_in_array) {
					$isOk = true;
				}
			}
			else {
				$isOk = true;
			}
			$itemCheck = $isOk;

			//If the item is allowed
			if ( ($itemCheck && $onItems != 0  && !$groupCheck ) || ($itemCheck && $groupCheck)) {
				return true;
			}
			else {
				return false;
			}
		}

		protected function _add_remove_mails ($Users_to_send) {
			$Users_Add_emails = $this->rule->ausers_additionalmailadresses;
			$Users_Add_emails = explode ("\n",$Users_Add_emails);

			foreach ($Users_Add_emails as $cur_email) {
				$cur_email = JString::trim($cur_email);
				if ($cur_email == "") {
					continue;
				}
				$add_mail_flag = true;
				foreach ($Users_to_send as $v=>$k) {
					if ($k['email'] == $cur_email )  {
						$add_mail_flag == false;
						break;
					}
				}
				if ($add_mail_flag) {
					$Users_to_send[]['email'] = $cur_email;
				}
			}

			$Users_Exclude_emails = $this->rule->ausers_excludeusers;
			$Users_Exclude_emails = explode ("\n",$Users_Exclude_emails);

			foreach ($Users_Exclude_emails as $cur_email) {
				$cur_email = JString::trim($cur_email);
				if ($cur_email == "") {
					continue;
				}
				foreach ($Users_to_send as $v=>$k) {
					if ($k['email'] == $cur_email) {
						unset ($Users_to_send[$v]);
						break;
					}
				}
			}
			return $Users_to_send;
		}

		//prepare some article info
		protected function _article_info ( $lang_code) {
			if (isset($this->rule->article_info[$lang_code])) {
				return $this->rule->article_info[$lang_code];
			}

			$IncludeArticleTitle = $this->rule->ausers_includearticletitle;
			$IncludeCategoryTree = $this->rule->ausers_includecategorytree;
			$IncludeAuthorName = $this->rule->ausers_includeauthorname;
			$IncludeModifierName = $this->rule->ausers_includemodifiername;
			$IncludeCreatedDate = $this->rule->ausers_includecreateddate;
			$IncludeModifiedDate = $this->rule->ausers_includemodifieddate;

			$article_info =array();
			if ($IncludeArticleTitle) {
				$article_info []= "<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_ARTICLE_TITLE').'</b>: '.$this->article->title;
			}
			if ($IncludeCategoryTree) {
				$this->buildCategoryTree();
				$text = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_CATEGORY');
				if ($text == 'PLG_CONTENT_NOTIFYARTICLESUBMIT_CATEGORY') {
					$text = JText::_('JCATEGORY');
				}
				$article_info []= "<b>".$text.'</b>: '.implode (' > ',$this->categoryTree);
			}

			if ($this->publish_state_change == 'nochange') {
				if ($this->article->state) {
					$article_info []= "<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_THIS_ARTICLE_IS_ALREADY_PUBLISHED')."</b>.";
				}
				else 	{
					$article_info []= "<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_THIS_ARTICLE_IS_NOT_YET_PUBLISHED')."</b>.";
				}
			}
			else if ($this->publish_state_change == 'published') {
				$article_info []= "<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_THIS_ARTICLE_WAS_PUBLISHED')."</b>.";
			}
			else if ($this->publish_state_change == 'unpublished') {
				$article_info []= "<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_THIS_ARTICLE_WAS_UNPUBLISHED')."</b>.";
			}

			$article_info []= "";

			if ($IncludeAuthorName) {
				$article_info []= "<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_AUTHOR').'</b>: '.$this->author->username;
			}
			if ($IncludeModifierName) {
				$article_info []= "<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_MODIFIER').'</b>: '.$this->modifier->username;
			}


			if ($IncludeCreatedDate && !empty($this->article->created) ) {
				$article_info []= "<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_CREATED').'</b>: '.$this->getCorrectDate($this->article->created);
			}
			if ($IncludeModifiedDate && !$this->isNew) {
				$article_info []= "<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_MODIFIED').'</b>: '.$this->getCorrectDate($this->article->modified);
			}

			$this->rule->article_info[$lang_code] = $article_info;
			return $this->rule->article_info[$lang_code];
		}

		protected function j15_loadjoomfishparams() { // TODO Add compatibility with FaLang
			if(version_compare(JVERSION,'1.6.0','ge')) {
			// Joomla! 1.6 code here
				return true;
			}

			$this->lang = JFactory::getLanguage();
			//load default lang
			$this->lang->load('plg_content_notifyarticlesubmit',JPATH_ADMINISTRATOR,$this->default_lang, true);
			$config = JFactory::getConfig();
			$this->defaultlang = $config->getValue( 'config.defaultlang' );
			$this->multilingual_support = $config->getValue( 'config.multilingual_support' );
			$this->lang_site = $config->getValue( 'config.lang_site' );
			$this->lang_id = $config->getValue( 'joomfish.language.id', null );
			$this->langShortCode = $config->getValue( 'joomfish.language.shortcode', null );

			$OnlyForMainLanguage = $this->paramGet( 'onlyformainlanguage' );
			if ($OnlyForMainLanguage ==1 && $this->defaultlang != $this->lang_site ) {
				return false ;
			}

			if ($this->multilingual_support && $this->defaultlang != $this->lang_site ) {
				$db = JFactory::getDBO();
				$query = "SELECT ".$db->quoteName('jf.value')." FROM ".$db->quoteName('#__jf_content')." AS jf ".
					" LEFT JOIN ".$db->quoteName('#__plugins')." AS plg ".
					" ON ".$db->quoteName('plg.id')." = ".$db->quoteName('jf.reference_id').
					" WHERE ".$db->quoteName('plg.element')." = ".$db->Quote('notifyarticlesubmit').
					" AND ".$db->quoteName('jf.reference_table')." = ".$db->Quote('plugins').
					" AND ".$db->quoteName('jf.language_id ')." = ".$db->Quote($this->lang_id ).
					" AND ".$db->quoteName('jf.reference_field')." = ".$db->Quote('params');


				$db->setQuery($query);
				$params = $db->loadResult();
				if (trim($params) != '') {
					$plugin->params = $params;
					$this->params = new JParameter( $plugin->params );
				}
			}

			return true;
		}


		// $zone possible values site/admin
		// $task possible values edit/view
		protected function buildLink($zone='site',$task='edit',$lang=null) {
			if ($lang == null && $this->langShortCode != null ) {
				$lang = '&lang='.$this->langShortCode;
			}
			$link = '';
			switch ($task) {
				case 'edit':
					if ($zone == 'site') {
						$link = "index.php?option=com_content&task=article.edit&a_id=".$this->article->id;
					}
					else if ($zone == 'admin') {
						$link = "administrator/index.php?option=com_content&task=article.edit&id=".$this->article->id;
					}
					break;
				case 'view':
					$this->article->slug = $this->article->id.':'.$this->article->alias;
					$link	= ContentHelperRoute::getArticleRoute($this->article->slug, $this->article->catid);
					break;

			}
			return JRoute::_(JURI::root().$link,false);
			//return JURI::root().$link;
		}


		/*
		 *
			foreach ($this->mailCache as $mail) {

				$mailer =& JFactory::getMailer();
				$mailer->setSubject($mail['subj'] );
				$mailer->setBody($mail['body']);
				foreach ($mail['recepients'] as $email) {
					$mailer->addRecipient($email);
				}

		 * Builds a mail message for a particulat user
		 * @param &user object
		 * @return $mail array which contains subject and body
		 */
		protected function buildMailHardcoded (&$user) {
			static $user_language_loaded = false;
			$app = JFactory::getApplication();
			if ( $app->isAdmin() ) {
				$lang_code = $user->getParam('admin_language');
			}
			else {
				$lang_code = $user->getParam('language');
			}
			$language = JFactory::getLanguage();
			if (!empty($lang_code) && $lang_code != $this->default_lang) {
				$language->load($this->plg_base_name, JPATH_ADMINISTRATOR, $lang_code, true);
				$language->load($this->plg_full_name, JPATH_ADMINISTRATOR, $lang_code, true);
				$user_language_loaded = true;
			}
			else if ($user_language_loaded) {
				$language->load($this->plg_base_name, JPATH_ADMINISTRATOR, 'en-GB', true);
				$language->load($this->plg_full_name, JPATH_ADMINISTRATOR, 'en-GB', true);
				$language->load($this->plg_base_name, JPATH_ADMINISTRATOR, $this->default_lang, true);
				$language->load($this->plg_full_name, JPATH_ADMINISTRATOR, $this->default_lang, true);
				$user_language_loaded = false;
				$lang_code = $this->default_lang;
			}
			else {
				$user_language_loaded = false;
				$lang_code = $this->default_lang;
			}

			//need this for authors and modifiers as they are not checked anywhere else
			if ($user->block == 1) {
				return false;
			}

			static $authorinformed = false;
			//if modifier and author are the same, then inform only once as author
			if ($user->id == $this->author->id && $authorinformed) {
				return false;
			}
			else if ($user->id == $this->author->id) {
				$authorinformed = true;
			}

			$canView = false;
			// User has back-end access
			$canEdit = $user->authorise('core.edit', 'com_content.article.'.$this->article->id);
			$canLoginBackend =  $user->authorise('core.login.admin');
			if (in_array($this->article->access, $user->getAuthorisedViewLevels())) {
				$canView = true;
			}
			$canEditOwn = $user->authorise('core.edit', 'com_content.article.'.$this->article->id);
			$notifyonlyifcanview = $this->rule->ausers_notifyonlyifcanview;

			if ($notifyonlyifcanview == 1 && !$canView) { return false; }


			if ($this->rule->emailformat == 'plaintext') {
				$glue = PHP_EOL;
			}
			else {
				$glue = '<br />'.PHP_EOL;
			}

			$include = '';
			if ($user->id == $this->author->id) {
				$include = 'author';
			}
			else if ($user->id == $this->modifier->id) {
				$include = 'modifier';
			}

			$IncludeIntroText = $this->rule->{'ausers_'.$include.'includeintrotext'};
			$IncludeFullText = $this->rule->{'ausers_'.$include.'includefulltext'};


			$IncludeFrontendViewLink = $this->rule->ausers_includefrontendviewlink;
			$IncludeFrontendEditLink = $this->rule->ausers_includefrontendeditlink;
			$IncludeBackendEditLink = $this->rule->ausers_includebackendeditlink;

			$body_include = '';
			if ($this->isNew) {
				if ($user->id == $this->article->created_by) {
					if ($this->article->created_by != $this->article->modified_by) {
						$subj = $this->modifier->username.' '.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_HAS_ADDED_AN_ARTICLE_WITH_YOU_SET_AS_AUTHOR_AT');
					}
					else {
						$subj = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOU_HAVE_JUST_ADDED_AN_ARTICLE_AT');
					}
				}
				else if ($user->id == $this->article->modified_by) {
					$subj = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_AN_ARTICLE_WITH_YOU_SET_AS_AUTHOR_HAS_BEEN_JUST_ADDED_AT');
				}

				$subj = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_NEW_ARTICLE_AT');
			}
			else {
				if ($this->publish_state_change == 'published') {
					if ($user->id == $this->article->modified_by) {
						$subj =  JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOU_HAVE_JUST_PUBLISHED_AN_ARTICLE_AT').".";
					}
					else if ($user->id == $this->article->created_by && $user->id != $this->article->modified_by) {
						$subj = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOUR_ARTICLE_HAS_BEEN_PUBLISHED_AT');
						$body_include = "\n\n".$this->modifier->username.' '.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_HAS_PUBLISHED_YOUR_ARTICLE_AT');
					}
					else {
						$subj =  JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_AN_ARTICLE_HAS_BEEN_PUBLISHED_AT').".";
					}
				}
				else if ($this->publish_state_change == 'unpublished') {
					if ($user->id == $this->article->modified_by) {
						$subj =  JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOU_HAVE_JUST_UNPUBLISHED_AN_ARTICLE_AT').".";
					}
					else if ($user->id == $this->article->created_by && $user->id != $this->article->modified_by) {
							$subj = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOUR_ARTICLE_HAS_BEEN_UNPUBLISHED_AT');
							$body_include = "\n\n".$this->modifier->username.' '.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_HAS_UNPUBLISHED_YOUR_ARTICLE_AT');
					}
					else {
						$subj =  JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_AN_ARTICLE_HAS_BEEN_UNPUBLISHED_AT').".";
					}
				}
				else {
					if ($user->id == $this->article->modified_by) {
						$subj = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOU_HAVE_JUST_UPDATED_AN_ARTICLE_AT');
					}
					else if ($user->id == $this->article->created_by && $user->id != $this->article->modified_by) {
							$subj = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOUR_ARTICLE_HAS_BEEN_MODIFIED_AT');
							$body_include = "\n\n".$this->modifier->username.' '.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_HAS_MODIFIED_YOUR_ARTICLE_AT');
					}
					else {
						$subj = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_AN_ARTICLE_HAS_BEEN_CHANGED_AT');
					}
				}
			}
			$body = array();
			if (empty ($body_include)) {
				$body[] = "\n".$subj.' <b>'.$this->sitename .'</b> (<a href="'.JURI::root().'">'.JURI::root().'</a>)';
			}
			else {
				$body[] = "\n".$body_include.' <b>'.$this->sitename .'</b> (<a href="'.JURI::root().'">'.JURI::root().'</a>)';
			}

			$article_info = $this->_article_info ($lang_code);
			if ($this->rule->emailformat == 'plaintext') {
				$article_info = array_map('strip_tags',$article_info);
			}

			$body[]= implode($glue,$article_info);

			if ($IncludeFrontendViewLink && $this->article->state == 1 && $canView) {
				$body[]="<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_VIEW_ARTICLE')."</b>: ";
				$body[]= '<a href="'.$this->buildLink($zone='site',$task='view').'">'.$this->buildLink($zone='site',$task='view').'</a>';
			}
			else if ($IncludeFrontendViewLink && $this->article->state == 1 && !$canView) {
				$body[]="<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_VIEW_ARTICLE')."</b>: ";
				$body[]= JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_ALERTNOAUTHOR').' ';
				$body[]= '<a href="'.$this->buildLink($zone='site',$task='view').'">'.$this->buildLink($zone='site',$task='view').'</a>';
			}
			if ($this->article->state != 1) {
				$body[]= "<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_THIS_ARTICLE_MUST_BE_REVIEWED_AND_MAY_BE_PUBLISHED_BY_AN_ADMINISTRATOR_USER')."</b>.";
			}

			if ($user->id == $this->article->modified_by || $user->id == $this->article->created_by) {
				$body[]= "\n<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOUR_ARTICLE_ID_FOR_FUTHER_REFERENCE_IS')."</b> ".$this->article->id;
			}

			//If current user is Author or Modifier or Inclider Edit links are enabled
			if ($user->id == $this->article->modified_by || $user->id == $this->article->created_by || $IncludeFrontendEditLink || $IncludeBackendEditLink) {
				//add FE edit link
				if ( ($user->id == $this->article->created_by && $canEditOwn)	||	($IncludeFrontendEditLink && $canEdit)	) {
					$body[]="\n<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_IF_YOU_ARE_LOGGED_IN_TO_FRONTEND_USE_THIS_LINK_TO_EDIT_THE_ARTICLE').'</b>';
					$body[]='<a href="'.$this->buildLink($zone='site',$task='edit').'">'.$this->buildLink($zone='site',$task='edit').'</a>';
				}
				//add BE edit link
				if ( $canLoginBackend && ($user->id == $this->article->created_by	||	($IncludeBackendEditLink && $canEdit)	) ) {
					$body[]="\n<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_IF_YOU_ARE_LOGGED_IN_TO_BACKEND_USE_THIS_LINK_TO_EDIT_THE_ARTICLE').'</b>';
					$body[]="".'<a href="'.$this->buildLink($zone='admin',$task='edit').'">'.$this->buildLink($zone='admin',$task='edit').'</a>';
				}
			}

			$subj .= ' '.$this->sitename.": ".$this->article->title;
			// *** prepare introtext and fulltext {
			// Instantiate a new instance of the class. Passing the string
			// variable automatically loads the HTML for you.
			if ($this->rule->emailformat == 'plaintext') {
				if (!class_exists ('html2text') ) { require_once (dirname(__FILE__).'/helpers/html2text.php'); }
			}

			if ($IncludeIntroText && empty($this->rule->introtext)) {
				if ($this->rule->emailformat == 'plaintext') {
					$h2t = new html2text($this->article->introtext);
					$h2t->width = 120;
					// Simply call the get_text() method for the class to convert
					// the HTML to the plain text. Store it into the variable.
					$this->rule->introtext = $h2t->get_text();
					unset ($h2t);
				}
				else {
					$this->rule->introtext = $this->article->introtext;
				}
			}

			if ($IncludeFullText && empty($this->rule->fulltext)) {
				if ($this->rule->emailformat == 'plaintext') {
					// Instantiate a new instance of the class. Passing the string
					// variable automatically loads the HTML for you.
					$h2t = new html2text($this->article->fulltext);
					$h2t->width = 120;
					// Simply call the get_text() method for the class to convert
					// the HTML to the plain text. Store it into the variable.
					$this->rule->fulltext = $h2t->get_text();
					unset ($h2t);
				}
				else {
					$this->rule->fulltext = $this->article->fulltext;
				}
			}
			// *** prepare introtext and fulltext }

			if ($IncludeIntroText == 1 ) {
					$body[]= "\n\n<br/><br/>...........<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_INTROTEXT_OVERVIEW')."</b>:...........\n<br/>".$this->rule->introtext;
			}
			if (empty($this->rule->fulltext)) {
				$fulltext = '['.JText::_('COM_CONTENT_NONE').']';
			}
			else {
				$fulltext = $this->rule->fulltext;
			}
			if ($IncludeFullText == 1 ) {
				$body[]= "\n\n<br/>...........<b>".JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_FULLTEXT_OVERVIEW')."</b>:...........\n<br/>".$fulltext;
			}

			if (isset($this->rule->attachdiffinfo) && $this->noDiffFound) {
				$body[] = PHP_EOL.'<br /><span style="color:red;">'.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_NO_DIFF_FOUND').'</span><br/>'.PHP_EOL;
			}

			$diffType = 'none';
			if ($this->rule->emailformat == 'plaintext') {
				$diffType = $this->rule->includediffinfo_text;
			}
			else {
				$diffType = $this->rule->includediffinfo_html;
			}
			if ($diffType != 'none' && isset($this->diffs[$diffType])) {
				$body[] = PHP_EOL.'<hr><center>					.....Diff.......</center>';
				$body[] = $this->diffs[$diffType];
			}

			$mail['subj'] = $subj;

			if ($this->rule->emailformat == 'plaintext') {
				$body = array_map('strip_tags',$body);
			}
			$mail['body'] = implode($glue,$body);

			$mail['email'] = $user->email;

			return $mail;
		}

		protected function buildMailCustom (&$user) {
			static $user_language_loaded = false;
			$app = JFactory::getApplication();
			if ( $app->isAdmin() ) {
				$lang_code = $user->getParam('admin_language');
			}
			else {
				$lang_code = $user->getParam('language');
			}
			$language = JFactory::getLanguage();
			if (!empty($lang_code) && $lang_code != $this->default_lang) {
				$language->load($this->plg_base_name, JPATH_ADMINISTRATOR, $lang_code, true);
				$language->load($this->plg_full_name, JPATH_ADMINISTRATOR, $lang_code, true);
				$user_language_loaded = true;
			}
			else if ($user_language_loaded) {
				$language->load($this->plg_base_name, JPATH_ADMINISTRATOR, 'en-GB', true);
				$language->load($this->plg_full_name, JPATH_ADMINISTRATOR, 'en-GB', true);
				$language->load($this->plg_base_name, JPATH_ADMINISTRATOR, $this->default_lang, true);
				$language->load($this->plg_full_name, JPATH_ADMINISTRATOR, $this->default_lang, true);
				$user_language_loaded = false;
				$lang_code = $this->default_lang;
			}
			else {
				$user_language_loaded = false;
				$lang_code = $this->default_lang;
			}

			//need this for authors and modifiers as they are not checked anywhere else
			if ($user->block == 1) {
				return false;
			}

			static $authorinformed = false;
			//if modifier and author are the same, then inform only once as author
			if ($user->id == $this->author->id && $authorinformed) {
				return false;
			}
			else if ($user->id == $this->author->id) {
				$authorinformed = true;
			}

			$canView = false;
			// User has back-end access
			$canEdit = $user->authorise('core.edit', 'com_content.article.'.$this->article->id);
			$canLoginBackend =  $user->authorise('core.login.admin');
			if (in_array($this->article->access, $user->getAuthorisedViewLevels())) {
				$canView = true;
			}
			$canEditOwn = $user->authorise('core.edit', 'com_content.article.'.$this->article->id);
			$notifyonlyifcanview = $this->rule->ausers_notifyonlyifcanview;
			if ($notifyonlyifcanview == 1 && !$canView) { return false; }

			$place_holders_subject = array(
				'%SITENAME%' => null,
				'%SITELINK%' => null,
				'%ACTION%' => null,
				'%STATE%' => null,
				'%TITLE%' => null,
				'%MODIFIER%' => null
			);

			$place_holders_body = array(
				'%ARTICLE ID%' => null,
				'%AUTHOR%' => null,
				'%CREATED DATE%' => null,
				'%MODIFIED DATE%' => null,
				'%FRONT VIEW LINK%' => null,
				'%FRONT EDIT LINK%' => null,
				'%BACKEND EDIT LINK%' => null,
				'%CATEGORY PATH%' => null,
				'%INTRO TEXT%' => null,
				'%FULL TEXT%' => null,
				'%DIFF Text/Unified%' => null,
				'%DIFF Text/Context%' => null,
			);
			if ($this->rule->emailformat == 'html') {
				$place_holders_body ['%DIFF Html/SideBySide%'] = null;
				$place_holders_body ['%DIFF Html/Inline%'] = null;
			}

			$place_holders_subject['%SITENAME%'] = $this->sitename;
			$place_holders_subject['%SITELINK%'] = JURI::root();

			if ($this->isNew) {
				$place_holders_subject['%ACTION%'] = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_NEW_ARTICLE_AT');
				if ($user->id == $this->article->created_by) {
					if ($this->article->created_by != $this->article->modified_by) {
						$place_holders_subject['%ACTION%'] = $this->modifier->username.' '.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_HAS_ADDED_AN_ARTICLE_WITH_YOU_SET_AS_AUTHOR_AT');
					}
					else {
						$place_holders_subject['%ACTION%'] = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOU_HAVE_JUST_ADDED_AN_ARTICLE_AT');
					}
				}
				else if ($user->id == $this->article->modified_by) {
					$place_holders_subject['%ACTION%'] = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_AN_ARTICLE_WITH_YOU_SET_AS_AUTHOR_HAS_BEEN_JUST_ADDED_AT');
				}

			}
			else {
				if ($this->publish_state_change == 'published') {
					if ($user->id == $this->article->modified_by) {
						$place_holders_subject['%ACTION%'] =  JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOU_HAVE_JUST_PUBLISHED_AN_ARTICLE_AT').".";
					}
					else if ($user->id == $this->article->created_by && $user->id != $this->article->modified_by) {
						$place_holders_subject['%ACTION%'] = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOUR_ARTICLE_HAS_BEEN_PUBLISHED_AT');
						$place_holders_subject['%ACTION BODY%'] = "\n\n".$this->modifier->username.' '.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_HAS_PUBLISHED_YOUR_ARTICLE_AT');
					}
					else {
						$place_holders_subject['%ACTION%'] =  JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_AN_ARTICLE_HAS_BEEN_PUBLISHED_AT').".";
					}
				}
				else if ($this->publish_state_change == 'unpublished') {
					if ($user->id == $this->article->modified_by) {
						$place_holders_subject['%ACTION%'] =  JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOU_HAVE_JUST_UNPUBLISHED_AN_ARTICLE_AT').".";
					}
					else if ($user->id == $this->article->created_by && $user->id != $this->article->modified_by) {
							$place_holders_subject['%ACTION%'] = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOUR_ARTICLE_HAS_BEEN_UNPUBLISHED_AT');
							$place_holders_subject['%ACTION BODY%'] = "\n\n".$this->modifier->username.' '.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_HAS_UNPUBLISHED_YOUR_ARTICLE_AT');
					}
					else {
						$place_holders_subject['%ACTION%'] =  JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_AN_ARTICLE_HAS_BEEN_UNPUBLISHED_AT').".";
					}
				}
				else {
					if ($user->id == $this->article->modified_by) {
						$place_holders_subject['%ACTION%'] = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOU_HAVE_JUST_UPDATED_AN_ARTICLE_AT');
					}
					else if ($user->id == $this->article->created_by && $user->id != $this->article->modified_by) {
							$place_holders_subject['%ACTION%'] = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_YOUR_ARTICLE_HAS_BEEN_MODIFIED_AT');
							$place_holders_subject['%ACTION BODY%'] = "\n\n".$this->modifier->username.' '.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_HAS_MODIFIED_YOUR_ARTICLE_AT');
					}
					else {
						$place_holders_subject['%ACTION%'] = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_AN_ARTICLE_HAS_BEEN_CHANGED_AT');
					}
				}
			}

			switch ($this->article->state) {
				case '1':
					$place_holders_subject['%STATE%']  = JText::_('JPUBLUSHED');
					break;
				case '0':
					$place_holders_subject['%STATE%']  = JText::_('JUNPUBLUSHED');
					break;
				case '2':
					$place_holders_subject['%STATE%']  = JText::_('JARCHIVED');
					break;
				case '-2':
					$place_holders_subject['%STATE%']  = JText::_('JTRASHED');
					break;
			}

			$place_holders_subject['%TITLE%']  = $this->article->title;
			$place_holders_subject['%MODIFIER%']  = $this->modifier->username;

			// ---------------- body ---------------- //
			$place_holders_body['%ARTICLE ID%']  = $this->article->id;
			$place_holders_subject['%AUTHOR%']  = $this->author->username;

			$place_holders_subject['%CREATED DATE%']  = $this->article->created;
			$place_holders_subject['%MODIFIED DATE%']  = $this->article->modified;

			$place_holders_subject['%FRONT VIEW LINK%'] = $this->buildLink($zone='site',$task='view') ;
			$place_holders_subject['%FRONT EDIT LINK%'] = $this->buildLink($zone='site',$task='edit');
			$place_holders_subject['%BACKEND EDIT LINK%'] = $this->buildLink($zone='admin',$task='edit');

			$this->buildCategoryTree();
			$place_holders_subject['%CATEGORY PATH%'] = implode (' > ',$this->categoryTree);

			// *** prepare introtext and fulltext {
			// Instantiate a new instance of the class. Passing the string
			// variable automatically loads the HTML for you.
			if ($this->rule->emailformat == 'plaintext') {
				if (!class_exists ('html2text') ) { require_once (dirname(__FILE__).'/helpers/html2text.php'); }
			}

			if (empty($this->rule->introtext)) {
				if ($this->rule->emailformat == 'plaintext') {
					$h2t = new html2text($this->article->introtext);
					$h2t->width = 120;
					// Simply call the get_text() method for the class to convert
					// the HTML to the plain text. Store it into the variable.
					$this->rule->introtext = $h2t->get_text();
					unset ($h2t);
				}
				else {
					$this->rule->introtext = $this->article->introtext;
				}
			}

			if (empty($this->rule->fulltext)) {
				if ($this->rule->emailformat == 'plaintext') {
					// Instantiate a new instance of the class. Passing the string
					// variable automatically loads the HTML for you.
					$h2t = new html2text($this->article->fulltext);
					$h2t->width = 120;
					// Simply call the get_text() method for the class to convert
					// the HTML to the plain text. Store it into the variable.
					$this->rule->fulltext = $h2t->get_text();
					unset ($h2t);
				}
				else {
					$this->rule->fulltext = $this->article->fulltext;
				}
			}
			// *** prepare introtext and fulltext }


			if (empty($this->rule->fulltext)) {
				$fulltext = '['.JText::_('COM_CONTENT_NONE').']';
			}
			else {
				$fulltext = $this->rule->fulltext;
			}
			$place_holders_subject['%INTRO TEXT%'] = $this->rule->introtext;
			$place_holders_subject['%FULL TEXT%'] = $fulltext;



			$noDiffEchoed = false;
			foreach ($this->diffTypes as $diffType) {
				if (strpos($this->rule->messagebodycustom,'%DIFF '.$diffType.'%') !== false) {
					if ($this->noDiffFound) {
						if (!$noDiffEchoed) {
							$place_holders_subject['%DIFF '.$diffType.'%'] = JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_NO_DIFF_FOUND');
							$noDiffEchoed = true;
						}
						else {
							$place_holders_subject['%DIFF '.$diffType.'%'] = '';
						}
					}
					else {
						$place_holders_subject['%DIFF '.$diffType.'%'] = $this->diffs[$diffType];
					}
				}
			}

			foreach ($place_holders_subject as $k=>$v) {
				$this->rule->messagesubjectcustom = str_replace ($k,$v,$this->rule->messagesubjectcustom);
				$this->rule->messagebodycustom = str_replace ($k,$v,$this->rule->messagebodycustom);
			}
			$mail['subj'] = $this->rule->messagesubjectcustom;

			foreach ($place_holders_body as $k=>$v) {
				$this->rule->messagebodycustom = str_replace ($k,$v,$this->rule->messagebodycustom);
			}
			$mail['body'] = $this->rule->messagebodycustom;
			$mail['email'] = $user->email;

			return $mail;

/*********************************************************/
			$include = '';
			if ($user->id == $this->author->id) {
				$include = 'author';
			}
			else if ($user->id == $this->modifier->id) {
				$include = 'modifier';
			}

			$body_include = '';

			$body = array();
			if (empty ($body_include)) {
				$body[] = "\n".$subj.' <b>'.$this->sitename .'</b> (<a href="'.JURI::root().'">'.JURI::root().'</a>)';
			}
			else {
				$body[] = "\n".$body_include.' <b>'.$this->sitename .'</b> (<a href="'.JURI::root().'">'.JURI::root().'</a>)';
			}

			$article_info = $this->_article_info ($lang_code);
			if ($this->rule->emailformat == 'plaintext') {
				$article_info = array_map('strip_tags',$article_info);
			}

			$body[]= implode($glue,$article_info);


			$subj .= ' '.$this->sitename.": ".$this->article->title;

			$mail['subj'] = $subj;

			if ($this->rule->emailformat == 'plaintext') {
				$body = array_map('strip_tags',$body);
			}
			$mail['body'] = implode($glue,$body);

			$mail['email'] = $user->email;

			return $mail;
		}

		protected function buildCategoryTree () {
			if (!empty($this->categoryTree)) {
				return;
			}
			//need to pass the $options array with access false to get categroies with all access levels
			$options = array ();
			$options['access'] = false;
			$cat = JCategories::getInstance('Content',$options)->get($this->article->catid);

			$this->categoryTree = array();
			while ($cat->hasParent()) {
				array_unshift($this->categoryTree,$cat->title);
				$cat = $cat->getParent();
			}
		}

		/**
		 * Checks if the plugin has to be run at this page
		 *
		 * The function is needed because of growing request to use the plugin in other extensions.
		 * Besides, some this class methods are needed only at certain pages. So I need the function to avoid
		 * code check duplication.
		 *
		 * @author Gruz <arygroup@gmail.com>
		 * @return boolean
		 */
		function properPage() {
			static $first = true;
			$jinput = JFactory::getApplication()->input;
			$option = $jinput->get('option');
			$layout = $jinput->get('layout');
			$view = $jinput->get('view');
			$task = $jinput->get('task');
			$cid = $jinput->get('cid',null);

			if ($option != 'com_content' && $first ) {
				$first = false;
				return true;
			}

			if ($layout == 'edit' || ($view =='articles' && in_array($task,array ('publish','unpublish') ) ) || $task == 'save') {
				return true;
			}
			//return true;
			return false;
		}


		/**
		 * Adds CSS and JS for the notification switch at article view.
		 *
		 * @author Gruz <arygroup@gmail.com>
		 * @return void
		 */
		function onBeforeRender() {
			if (!$this->isArticlePage() ) {
				return;
			}
			if (!$this->isFirstRun('onBeforeRender')) {
				return;
			}
			if (!$this->showSwitchCheck()) {
				return;
			}
			$app = JFactory::getApplication();
			$document = JFactory::getDocument();
			$js = $css = '';

			$css = '
			#jform_attribs_runnotifyarticlesubmit-lbl{
				padding:0 4px;
			}
			#jform_attribs_runnotifyarticlesubmit {
				padding:0 10px 0 4px;
			}
			';

			if($app->isAdmin()) {

			}
			else {
				$css .= ".notifyarticlesubmitformfix .control-label {float:left;}".PHP_EOL;

				$js = '
					jQuery(document).ready(function($){
						$(".btn-group input:checked").each(function()  {
							//console.log (this.id);
							var label = $("label[for="+this.id+"]");
							//console.log (label);
							label.toggleClass(\'active\');

						});
						$(".btn-group input").each(function()  {
							$(this).change(function(){
								//console.log ($(this).parent());
								$(this).parent().find("label").toggleClass(\'active\');

							});
						});
					});
					';
			}


			$document->addStyleDeclaration($css);
			$document->addScriptDeclaration($js);

		}
		/**
		 * Adds the notification switch at an article view
		 *
		 * @author Gruz <arygroup@gmail.com>
		 * @return void
		 */
		function onAfterRender() {
			$session = JFactory::getSession();
			$attachments = $session->get('Attachments',null,'NotifyArticleSubmit');
			if (!empty($attachments)) {
				foreach ($attachments as $k=>$v) {
					JFile::delete($v);
				}
			}
			$session->set('Attachments',null,'NotifyArticleSubmit');
			$session->set('Diffs',null,'NotifyArticleSubmit');
			if (!$this->isArticlePage() ) {
				return;
			}
			if (!$this->isFirstRun('onAfterRender')) {
				return;
			}
			if (!$this->showSwitchCheck()) {
				return;
			}

			$body = JResponse::getBody();
			$app = JFactory::getApplication();

			$checkedyes = $checkedno = 'checked="checked"';
			$active_no = $active_yes = '';
			if ($this->runnotifyarticlesubmit == 1) {
				$checkedno = '';
				//$active_yes='active btn-success';
			}
			else {
				$checkedyes = '';
				//$active_no=' active btn-danger';
			}

			$replacement_label = '
						<label title="::'.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_NOTIFY_DESC').'" class="hasTip" for="jform_runnotifyarticlesubmit" id="jform_attribs_runnotifyarticlesubmit-lbl">'.JText::_('PLG_CONTENT_NOTIFYARTICLESUBMIT_NOTIFY').'</label>
			';
			$replacement_fieldset = '
						<fieldset id="jform_attribs_runnotifyarticlesubmit" class="radio btn-group nswitch" >
							<input type="radio" '.$checkedno.' value="0" name="jform[attribs][runnotifyarticlesubmit]" id="jform_attribs_runnotifyarticlesubmit0">
							<label for="jform_attribs_runnotifyarticlesubmit0" class="btn'.$active_no.'">'.JText::_('JNO').'</label>
							<input type="radio" '.$checkedyes.' value="1" name="jform[attribs][runnotifyarticlesubmit]" id="jform_attribs_runnotifyarticlesubmit1">
							<label for="jform_attribs_runnotifyarticlesubmit1" class="btn '.$active_yes.'">'.JText::_('JYES').'</label>
						</fieldset>
			';
			if($app->isAdmin() ) {
				$replacement = $replacement_label.$replacement_fieldset;
				$nswitch_placeholder = $this->getHTMLElementById($body,'jform_catid-lbl','label');
				$body = str_replace($nswitch_placeholder,$nswitch_placeholder.$replacement,$body);
			}
			else {
				$replacement = '
				<div class="control-group notifyarticlesubmitformfix">
					<div class="control-label">
						'.$replacement_label.'
					</div>
					<div class="controls">
						'.$replacement_fieldset.'
					</div>
				</div>
				';
				$nswitch_placeholder = $this->getHTMLElementById($body,'jform_articletext','label','for');
				if (empty($nswitch_placeholder)) {
					$nswitch_placeholder = $this->getHTMLElementById($body,'formelm-buttons','div','class');
				}
				$body = str_replace($nswitch_placeholder,$nswitch_placeholder.$replacement,$body);
			}


			JResponse::setBody($body);
			return;

		}



		/**
		 * Prepares from data at the article edit view.
		 *
		 * It's a must function. I tried to remove it, but the in-article switch refuses
		 * to be saved without this function. I though before it can be removed.
		 *
		 * @param	JForm	$form	The form to be altered.
		 * @param	array	$data	The associated data for the form.
		 *
		 * @return	boolean
		 * @since	1.6
		 */
		function onContentPrepareForm	($form, $data)	{
			if (!$this->isFirstRun('onContentPrepareForm')) {
				return;
			}
			// Check we are manipulating a valid form.
			if (!($form instanceof JForm)) 	{
				$this->_subject->setError('JERROR_NOT_A_FORM');
				return false;
			}

			$name = $form->getName();
			//if (!in_array($name, array('com_admin.profile', 'com_users.user', 'com_users.profile', 'com_users.registration'))) {
			if (!in_array($name, array('com_content.article'))) {
				return true;
			}

			$app = JFactory::getApplication();
			if(!$app->isAdmin()) {
				$data->attribs = (array)json_decode($data->attribs);
			}

			$this->runnotifyarticlesubmit = 0;
			if (isset ($data->attribs['runnotifyarticlesubmit'])) {
				$this->runnotifyarticlesubmit = $data->attribs['runnotifyarticlesubmit'];
			}
			else {
				$this->runnotifyarticlesubmit = $this->paramGet( 'notificationswitchdefault');
			}

			$string = '
						<form>
							<fields name="attribs">
								<fieldset name="basic" >
									<field
										label="PLG_CONTENT_NOTIFYARTICLESUBMIT_NOTIFY"
										description="PLG_CONTENT_NOTIFYARTICLESUBMIT_NOTIFY_DESC"
										name="runnotifyarticlesubmit"
										type="radio"
										class="btn-group nswitch"
										default="'.$this->runnotifyarticlesubmit.'"
										>
										<option value="0">JNO</option>
										<option value="1">JYES</option>
									</field>
								</fieldset>
							</fields>
						</form>';
			$form->load($string,true);
			return true;
		}



		/**
		 * A helper function to parse HTML to get an element by an attribute
		 *
		 * Doesn't work for one-tag tags like <input />,
		 * only works for open and closed tags
		 *
		 * @author Gruz <arygroup@gmail.com>
		 * @param	string	$html					String of HTML code to be parsed
		 * @param	string	$attibuteValue		Attribute value to be found, i.e. element id value
		 * @param	string	$tagname				Tag to be found. I.e. div, span, p
		 * @param	string	$attributeName		Which attribute to look for. I.e. id, for, class
		 * @return	string							Returns HTML of the element found in $html
		 */
		function getHTMLElementById($html,$attributeValue,$tagname = 'div', $attributeName = 'id') {
			$re = '% # Match a DIV element having id="content".
				 <'.$tagname.'\b             # Start of outer DIV start tag.
				 [^>]*?             # Lazily match up to id attrib.
				 \b'.$attributeName.'\s*+=\s*+      # id attribute name and =
				 ([\'"]?+)          # $1: Optional quote delimiter.
				 \b'.$attributeValue.'\b        # specific ID to be matched.
				 (?(1)\1)           # If open quote, match same closing quote
				 [^>]*+>            # remaining outer DIV start tag.
				 (                  # $2: DIV contents. (may be called recursively!)
					(?:              # Non-capture group for DIV contents alternatives.
					# DIV contents option 1: All non-DIV, non-comment stuff...
					  [^<]++         # One or more non-tag, non-comment characters.
					# DIV contents option 2: Start of a non-DIV tag...
					| <            # Match a "<", but only if it
					  (?!          # is not the beginning of either
						 /?'.$tagname.'\b    # a DIV start or end tag,
					  | !--        # or an HTML comment.
					  )            # Ok, that < was not a DIV or comment.
					# DIV contents Option 3: an HTML comment.
					| <!--.*?-->     # A non-SGML compliant HTML comment.
					# DIV contents Option 4: a nested DIV element!
					| <'.$tagname.'\b[^>]*+>  # Inner DIV element start tag.
					  (?2)           # Recurse group 2 as a nested subroutine.
					  </'.$tagname.'\s*>      # Inner DIV element end tag.
					)*+              # Zero or more of these contents alternatives.
				 )                  # End 2$: DIV contents.
				 </'.$tagname.'\s*>          # Outer DIV end tag.
				 %isx';
			if (preg_match($re,$html, $matches)) {
				return $matches[0];
				 //printf("Match found:\n%s\n", $matches[0]);
			}
			return null;
		}


		/**
		 * Converts DB date to the date including joomla offset
		 *
		 * @author Gruz <arygroup@gmail.com>
		 * @param string $value Date in format 0000-00-00 00:00:00
		 * @return string Date in format 0000-00-00 00:00:00
		 */
		function getCorrectDate($value) {
			$config = JFactory::getConfig();
			//$date = JFactory::getDate($value, 'UTC');
			//$date->setTimezone(new DateTimeZone($config->get('timezone', $config->get('offset'))));
			//$value = $date->format('Y-m-d H:i:s', true, false);
			$value = JHTML::_('date',  $value, 'Y-m-d h:i:s');
			return $value;
		}



		/**
		 * Checks if it's the first run of the function. The plugin is executed twice  - as system and as content.
		 *
		 * Is needed i.e. to properly add notification switch, to run the add routine only once
		 *
		 * @author Gruz <arygroup@gmail.com>
		 * @return boolean True is the functions is rub not the first time
		 */
		protected function isFirstRun($name = 'unknown') { // TODO Maybe not needed, maybe it's a J1.5 code. Check,
			global  $NotifyArticleSubmitFirstRunCheck;
			if (empty($NotifyArticleSubmitFirstRunCheck[$name])) {
				$NotifyArticleSubmitFirstRunCheck[$name] = 'not first';
				return true;
			}
			return false;
		}


		/**
		 * Checks if notification switch show is allowed
		 *
		 * @author Gruz <arygroup@gmail.com>
		 * @return boolean
		 */
		function showSwitchCheck() {

			// If the check was already done return the old value. It hase to be determined only once
			if (isset ($this->showSwitchCheckFlag) ) {
				return $this->showSwitchCheckFlag;
			}
			// If NSwitch is off - FALSE
			if ($this->paramGet( 'shownotificationswitch') == 0) {
				$this->showSwitchCheckFlag = false;
				return $this->showSwitchCheckFlag;
			}
			// If NSwitch is allowed for this article, then continue, but if not then FALSE
			$session = JFactory::getSession();
			$NotificationIsAllowedInTheArticle = $session->get('NotificationIsAllowedInTheArticle',false,'NotifyArticleSubmit');
			if (!$NotificationIsAllowedInTheArticle) {
				$this->showSwitchCheckFlag = false;
				return $this->showSwitchCheckFlag;
			}
			// If NSwitch is allowed for this article, then check if it's allowed for this user
			// TODO Make _checkAllowed work only once for the user and the article!!!! Now it's run 2 times
			$user =  JFactory::getUser();
			//$this->showSwitchCheckFlag = $this->checkUserAllowed ($user,$prefix = 'nswitch');
			//check if notifications turned on for current user
			$NotificationIsAllowedForThisUser =  $this->_checkAllowed ($user, $paramName = 'allowuser', 'nswitch');

			if ($NotificationIsAllowedForThisUser && $NotificationIsAllowedInTheArticle) {
				$this->showSwitchCheckFlag = true;
			}
			else {
				$this->showSwitchCheckFlag = false;
			}

			return $this->showSwitchCheckFlag;
		}
	}
	class plgContentNotifyarticlesubmit extends plgContentNotifyarticlesubmitCore {
		public function __construct(& $subject, $config) {
			parent::__construct($subject, $config);
		}
	}

}

