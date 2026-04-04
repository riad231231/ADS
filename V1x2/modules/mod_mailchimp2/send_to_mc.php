<?php
    define( '_JEXEC', 1 );
    define( 'JPATH_BASE', realpath(dirname(__FILE__).'/../../' ));  
    require_once ( JPATH_BASE .'/includes/defines.php' );
    require_once ( JPATH_BASE .'/includes/framework.php' );

    $mainframe = JFactory::getApplication('site');
//error_reporting(E_ALL);
//ini_set('display_errors', '1');	

/**
* @Copyright Copyright (C) 2015 3by400, Inc.
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/

// the work's all done here, getting the info and making the call to the MailChimp API
function send_to_mc(){
	
	$session = JFactory::getSession();
	
	$timeoutMin = 10; //seconds to wait for API call
	$timeoutDefault = 15;
	$timeoutMax = 500; 
	

	$email = isset($_POST['email'])?$_POST['email']:'';
	$mid =  isset($_POST['mid'])?$_POST['mid']:'';
	$first =  isset($_POST['first'])?$_POST['first']:'';
	$last =  isset($_POST['last'])?$_POST['last']:'';
	$ig =  isset($_POST['ig'])?$_POST['ig']:'';
	$ig_struct = make_ig($ig);

	defined('_JEXEC') or define( '_JEXEC', 1 );
	defined ('JPATH_BASE') or define('JPATH_BASE', $_SERVER['DOCUMENT_ROOT'] );
	defined('DS') or define( 'DS', DIRECTORY_SEPARATOR );

	require_once (JPATH_BASE .DS.'includes'.DS.'defines.php' );
	require_once (JPATH_BASE .DS.'includes'.DS.'framework.php' );

	$mainframe = JFactory::getApplication('site'); //was by reference
	$mainframe->initialise();
	
	//language substitution
	$lang = JFactory::getLanguage(); //was by reference
	$lang->load('mod_mailchimp2',JPATH_ROOT);
	
	global $mainframe;

	$db = JFactory::getDBO(); //was by reference


	// Validation
	// MOD_MAILCHIMP2_NO_EMAIL_PROVIDED would also work
	if(!$email){
		$retval = JText::_('MOD_MAILCHIMP2_ERROR_INVALID_EMAIL');
		return $retval;
	} 

	
	//
	// Try to read the module parameters directly
	//
	
	jimport( 'joomla.application.module.helper' );
	$module = JModuleHelper::getModule( 'mod_mailchimp2' ); //FIXME: should choose module title to get the right module

		
	//
	// If we can't read module params, grab the params object from the session
	//
	
	if ( !isset($module->params) || empty($module->params) ) {

		$session = JFactory::getSession();
		$sessionParams = $session->get('params', "", "mod_mailchimp2");	
		$paramsCatcher = json_decode($sessionParams);

	} else {
	
		$paramsCatcher = json_decode($module->params);
	}
	
	$apikey = $paramsCatcher->mc_api_key;

	if(!$apikey){
	$retval = JText::_('MOD_MAILCHIMP2_ERROR_NO_API_KEY');
		return($retval);
	}
	
	// Timeout sanity check
	$timeout = $timeoutDefault;
	if(property_exists($paramsCatcher, "mc_timeout")) {
		if (is_numeric($paramsCatcher->mc_timeout) && $paramsCatcher->mc_timeout >= $timeoutMin && $paramsCatcher->mc_timeout <= $timeoutMax) {
			$timeout = $paramsCatcher->mc_timeout;
		}
	}
	

	// pull in the MailChimp API
	require_once('MCAPI.class.php');
	require_once('mod_mailchimp2_helper.php');
	$api = new MODMC2_MCAPI($apikey);
	$api->setTimeout($timeout);
	
	
	$list = get_list($api, $paramsCatcher);

	if(!$list['id']){
    $retval = JText::_('MOD_MAILCHIMP2_ERROR_NO_LIST');
		return $retval;
	}

	$mergevars = $api->listMergeVars($list['id']);

	// finally! the Subscribe
	$merge_vars_togo = array();
	$err_tags = array();
	$err_names = array();
	foreach($mergevars as $mergevar){
		$tag = $mergevar['tag'];
		$err_tags[] = "/$tag/";
		$err_names[] = $mergevar['name'];
		if($tag == 'EMAIL') {
			continue;
		} else {
			if(  isset($_POST[$tag])  ){
				$merge_vars_togo[$tag]=$_POST[$tag];
			}
		}
	}
	if($ig_struct){
		$merge_vars_togo['GROUPINGS']=$ig_struct;
	}
	if(!count($merge_vars_togo)) {
		$merge_vars_togo = '';
	}
	if($api->listSubscribe($list['id'], $email, $merge_vars_togo) === true) {
		// It worked!
		if($paramsCatcher->lang_override_success) {
			$retval = $paramsCatcher->lang_override_success;
		} else {
			$retval = JText::_('MOD_MAILCHIMP2_MESSAGE_SUCCESS');
		}
	return $retval;
	} else {



		switch($api->errorCode) { // An error ocurred, return error message
			case 250: // List_MergeFieldRequired

				$retval = JText::_('MOD_MAILCHIMP2_ERROR_SUBSCRIBE');
				
				if($paramsCatcher->show_errors) {
					$retval .= "<br />\n" . preg_replace($err_tags, $err_names, $api->errorMessage);
					$retval .= "<br />\n" . "MailChimp2 Pro can accomodate merge fields that are Required.";
				}
				
				
				return $retval;
			break;
			case 502: // Invalid_Email

				$retval = JText::_('MOD_MAILCHIMP2_ERROR_SUBSCRIBE');	
				$retval .= JText::_('MOD_MAILCHIMP2_ERROR_INVALID_EMAIL');
				return $retval;
			break;
			
			default:

				if($paramsCatcher->lang_override_failure) {
					$retval = $paramsCatcher->lang_override_failure;
				} else {
					$retval = JText::_('MOD_MAILCHIMP2_ERROR_SUBSCRIBE');
				}
				
				if($paramsCatcher->show_errors) {
					$retval .=  "<br />\n" . preg_replace($err_tags, $err_names, $api->errorMessage);
				}

				return $retval;
			break;
		}
	}
	
}

function make_ig($all_igs){

	/* need to end up with:
	Set Interest Groups by Grouping. 
	Each element in this array should be an array containing the "groups" parameter which contains 
	a comma delimited list of Interest Groups to add. 
	Commas in Interest Group names should be escaped with a backslash. ie, "," => "\," 
	and either an "id" or "name" parameter to specify the Grouping - get from listInterestGroupings()

	merge_vars = array ('GROUPINGS' => array('id'=> group_id1, 'groups'=>'ecommerce,marketing,joomla 1.8',
						'id'=> group_id2, 'groups'=>'male');
				.......
	*/

	$igs = "";
	
	$tmp=preg_replace('/,$/', '', $all_igs);

	$parts = explode(',', $tmp);

	$parts[] = 'mcig_0';	// end of file 

	$ret = array();

	$group = array();
	$group_id = "";
	
	for($i = 0; $i<count($parts); $i++){
		$this_part = $parts[$i];
		if(preg_match('/^mcig_/', $this_part)){
			// finish previous array
			if($igs and $group_id){
				$group['id'] = $group_id;
				$igs = preg_replace('/,$/', '', $igs);
				$group['groups'] = $igs;
				$ret[] = $group;
				$group = array();
				$id = '';
				$igs = '';
			}
			$group_id = preg_replace('/^mcig_/', '', $this_part);
		} else {
			$igs .= $this_part . ',';
		}
	}
	return($ret);
}	// end of make_ig 

// If being called via ajax, autorun the function with email address and module id
//if($_POST['ajax']){ echo send_to_mc(); }
if($_REQUEST['ajax']){ echo send_to_mc(); }


//EOF