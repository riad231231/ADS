<?php
/**
* @Copyright Copyright (C) 2014 3by400, Inc.
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
//params is now a stdObj created by parsing module params
//and not a JParameters object
function get_list($api, $params){

  $list_return = $api->lists();
    
  if($api->errorCode){
    if ($params->show_errors == 1 ) {
      //echo "can't find lists: ";
	  echo JText::_('MOD_MAILCHIMP2_ERROR_GENERAL');
      echo $api->errorMessage;
	  echo "<br />\n";

      //echo " error code: ";
	  echo JText::_('MOD_MAILCHIMP2_ERROR_CODE');
      echo $api->errorCode;
    }
    echo "</div>";
    return null;
  }
  
  $foundit = false;
  $list_count = $list_return['total'];
  $lists = $list_return['data'];
  
  foreach($lists as $list){
    if($list['id'] == $params->mc_unique_id){
       return($list);
    }
  }
  
  if(!$foundit){
    return($lists[0]);
  }

}	// end of get_list

// old function took a Joomla API object
function get_list_legacy($api, $params){

  $list_return = $api->lists();
    
  if($api->errorCode){
    if ($params->get('show_errors') == 1 ) {
      //echo "can't find lists: ";
	  echo JText::_('MOD_MAILCHIMP2_ERROR_GENERAL');
      echo $api->errorMessage;
	  echo "<br />\n";

      //echo " error code: ";
	  echo JText::_('MOD_MAILCHIMP2_ERROR_CODE');
      echo $api->errorCode;
    }
    echo "</div>";
    return null;
  }
  
  $foundit = false;
  $list_count = $list_return['total'];
  $lists = $list_return['data'];
  
  foreach($lists as $list){
    if($list['id'] == $params->get('mc_unique_id')){
       return($list);
    }
  }
  
  if(!$foundit){
    return($lists[0]);
  }

}	// end of get_list




