<?php
/**
* @Copyright Copyright (C) 2015 3by400, Inc.
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/
defined('_JEXEC') or die('Restricted Access');
define('DEFAULT_FIELD_SIZE', 25);
define('MIN_FIELD_SIZE', 10);

$css_class_label = "modmc2_label";

// Pass params to session.
// AJAX call will retrieve params from session if it can't read the module params

$session = JFactory::getSession();
$session->set('params', $params, "mod_mailchimp2");


// AJAX	Type
//
// Determines which of two mutually-exclusive AJAX calls to run
//
// MC_AJAX_TYPE_SELF = Call another copy of itself
// Incompatible with some frameworks
//
// MC_AJAX_TYPE_DIRECT = Call send_to_mc.php directly
// Incompatible with security frameworks that prevent direct access
// add to your site's whitelist if necessary
//

$ajaxtype = $params->get('mc_ajax_type', 'MC_AJAX_TYPE_SELF');
?>


	<?php
	/* NEW:
	The page the user is facing will call another copy of itself
	to allow a dedicated AJAX call but still avoid having to install and maintian a component.
	This copy will be called with the mode=send query parameter.
	
	Send a new header and echo a plain HTML response.
	*/
	$mode = JRequest::getCmd('mode');
	if($mode == "send") {
		header( "Content-Type: text/html; charset=utf-8" );
		include ('send_to_mc.php');
		exit();
	}
	?>

	

<div class="module<?php if($params->get('moduleclass_sfx')) echo $params->get('moduleclass_sfx'); ?>" >
	<script type="text/javascript" src="<?php echo JURI::base(); ?>modules/mod_mailchimp2/url.js"> </script>
	<script type="text/javascript" src="<?php echo JURI::base(); ?>modules/mod_mailchimp2/ajax.js"> </script>


	<script type="text/javascript">
	//<![CDATA[

	// Global Ajax request
	var MCajaxReq = new MCAjaxRequest();

	// Add a new email address on the server using Ajax

	function addEmailAddress() {


	
		var lang_error_invalid_email = '<?php echo modMC2_escapeJavaScriptText(JText::_('MOD_MAILCHIMP2_ERROR_INVALID_EMAIL'));?>';
		var lang_message_adding = '<?php echo modMC2_escapeJavaScriptText(JText::_('MOD_MAILCHIMP2_MESSAGE_ADDING'));?>';

		<?php if($params->get('mc_checkemailaddress')): ?>
		// check email address for validity.
		// disabled by default.
		var regex = /^[\w\.\-_\+]+@[\w-]+(\.\w{2,6})+$/;

		if(!regex.test(document.getElementById("mc2_email").value)){
			// document.getElementById("status").innerHTML = "Invalid email address";
			document.getElementById("mc2_status").innerHTML = lang_error_invalid_email;
			return;
		}
		<?php endif; ?>


		// Disable the Add button and set the status to busy
		document.getElementById("mc2_add").disabled = true;
		document.getElementById("mc2_status").innerHTML = lang_message_adding;

		// Send the new email entry and our module id data as an Ajax request
		postvars = "ajax=true" + "&mid=<?php echo $module->id;?>" + "&email=" + document.getElementById("mc2_email").value;
		var mergeVarInputs = document.getElementById('mcmergevars');
		mvinputs = mergeVarInputs.getElementsByTagName('input'); //does not get selects.  

		maxj = mvinputs.length;
		for(j=0; j < maxj; j++){
			if(mvinputs[j].getAttribute("type") == "radio") { //don't automatically add all radio inputs
				
				if (mvinputs[j].checked && mvinputs[j].value) { 
					postvars = postvars + "&" + mvinputs[j].getAttribute('name') + 
						"=" + mvinputs[j].value;
				}
				
			} else if(mvinputs[j].value){ //input but not radiobutton
				postvars = postvars + "&" + mvinputs[j].getAttribute('name') + 
					"=" + mvinputs[j].value;
			}

		}
		
		
		mvselects = mergeVarInputs.getElementsByTagName('select');
		
		maxj = mvselects.length;
		for(j=0; j < maxj; j++){
			if(mvselects[j].value){
				postvars = postvars + "&" + mvselects[j].getAttribute('name') + 
					"=" + mvselects[j].value;
			}

		}
		
		
		//SEND

		<?php
		switch($ajaxtype) {

			case 'MC_AJAX_TYPE_DIRECT':
		?>
				//Add ajax parameter to send_to_mc call
				
				MCajaxReq.send("POST", "<?php echo JURI::base(); ?>modules/mod_mailchimp2/send_to_mc.php?ajax=1", handleRequest, "application/x-www-form-urlencoded; charset=UTF-8", postvars);
		<?php
			break;
			

			case 'MC_AJAX_TYPE_SELF':
			default:
		?>
				<?php
				$thisUrl = JURI::getInstance();
				$thisUrl->delVar('mode');
				$thisUrl->setVar('mode', 'send');
				?>
				
				//Add ajax parameter to send_to_mc call
				MCajaxReq.send("POST", "<?php echo JURI::base(); ?>modules/mod_mailchimp2/send_to_mc.php?ajax=1", handleRequest, "application/x-www-form-urlencoded; charset=UTF-8", postvars);	
				
			
		<?php
			break;
		}
		?>


	}	// end of addEmailAddress

	// Handle the Ajax request
	function handleRequest() {
		if (MCajaxReq.getReadyState() == 4 && MCajaxReq.getStatus() == 200) {
			// Confirm the addition of the email entry
			document.getElementById("mc2_status").innerHTML = MCajaxReq.getResponseText();
			document.getElementById("mc2_add").disabled = false;
			document.getElementById("mc2_email").value = "";
			
			var mvinputs = document.getElementById('mcmergevars');	// clear mergevars
			if(mvinputs.getElementsByTagName('input').length){
				var inputs = mvinputs.getElementsByTagName('input');
				if(inputs){
					for (var i=0; i < inputs.length; i++) {	
						inputs[i].value = '';		
					}
				}
			}
			/* clear checkboxes and radios */
			var iginputs = document.getElementById('mciginputs');
			ig_values = "";
			if(iginputs){
				if(iginputs.getElementsByTagName('input').length){
					var inputs = iginputs.getElementsByTagName('input'); 	// radio or checkbox
				} else {
					var inputs = iginputs.getElementsByTagName('option'); 	// select list
				}
				if(inputs){
					for (var i=0; i < inputs.length; i++) {
	 					// checked for radio or checkbox, selected for select list
						if (inputs[i].checked || inputs[i].selected) { 
							inputs[i].checked = false;
		      				}
					}
				}
			}
		}
	}
	//]]>
	</script>
<?php

 
	require_once('MCAPI.class.php');
	require_once('mod_mailchimp2_helper.php');
	$api = new MODMC2_MCAPI($params->get('mc_api_key'));
	$api->setTimeout(60);
	$list_id = ($params->get('mc_unique_id'));
	$list_return = $api->lists();
	
	if($params->get('mc_secure') == 1) {
	 $api->useSecure(true);
	}
	
	if($api->errorCode){

		if ($params->get('show_errors') == 1 ) {
			switch ($api->errorCode) {

				case -50:
				echo JText::_('MOD_MAILCHIMP2_ERROR_BUSY');
				break;

				default:
				//"Can't find lists: ";
				echo JText::_('MOD_MAILCHIMP2_ERROR_GENERAL');
				echo $api->errorMessage;
				echo "<br />\n";

				//" error code: ";
				echo JText::_('MOD_MAILCHIMP2_ERROR_CODE');
				echo $api->errorCode;
			}
		}
		echo "</div>";
		return null;
	}
	
	
	$foundit=FALSE;
	$list_count = $list_return['total'];
	$lists = $list_return['data'];
	foreach($lists as $list){
		if($list['id'] == $list_id){
			$foundit=TRUE;
			break;
		}
	}
	if(!$foundit){
    if ($params->get('show_errors') == 1 ) {
		  echo JText::_('MOD_MAILCHIMP2_ERROR_NO_ID') . ": $list_id";
		}
		echo "</div>";
		return null;
	}

	if($params->get('showlist')){
		echo $list['name'];
		echo '<br />';
	}

	$textsize = DEFAULT_FIELD_SIZE;
	if(is_numeric($params->get('textsize'))){
		$textsize = $params->get('textsize');
	}
	if($textsize < MIN_FIELD_SIZE){
		$textsize = MIN_FIELD_SIZE;
	}
		
?>

	<div id="mc2_status"></div>
		
	
	<form name="mailchimp2" action="">
	<span class="<?php echo $css_class_label;?>"><?php echo JText::_('MOD_MAILCHIMP2_LABEL_EMAIL_ADDRESS'); ?></span><br />
	<input type="text" id="mc2_email" name="email" value="" size="<?php echo $textsize;?>"/><br />
	<?php if($params->get('askname')){ 
		$showname = "inline";
	} else {
		$showname = "none";
	} ?>
	<div id="mcmergevars" style="display: <?php echo $showname;?>">
	<?php   
	$mergevars = $api->listMergeVars($list_id);
		// show extra signup fields
		// returns name req tag


		foreach($mergevars as $mergevar){


		if ( $mergevar['tag'] == 'FNAME'  || $mergevar['tag'] == "LNAME") {

				/** Special cases for Mailchimp-provided fields.
				Use translations for first and last name.
				*/
				
				$mergeVarLabel = $mergevar['name'];
				
				
				// TODO: make this a user-editable list in the module options.
				// For now, we hardcode the 'default' MailChimp fields,
				// First Name and Last Name
				
				$translatedFieldNameList = array( "First Name" => 'MOD_MAILCHIMP2_LABEL_FIRST_NAME', 
												 "Last Name" => 'MOD_MAILCHIMP2_LABEL_LAST_NAME' );
				foreach ( $translatedFieldNameList as $translatedFieldName => $fieldTranslation ) {
					if ( $mergevar['name'] == $translatedFieldName ) {
						$mergeVarLabel = JText::_($fieldTranslation);
					}
				}
			
			
				// prompt
				$css_class_required="notreq";
				if($mergevar['req']){
					$css_class_required="req";
				}
				
					echo '<span class="' . $css_class_label . ' ' . $css_class_required . '">' . $mergeVarLabel . ": </span><br />\n"; //was $mergevar['name']
					echo '<input class="mergevars" type="text" name="' . $mergevar['tag'] . '" ';
					echo 'size="' . $textsize . '"';
					echo "/><br />\n";
				}
			}


	?>

	</div>


	<?php
	/*
	Default form submit action is empty.  Instead, trigger the addEmailAddress action which sends to the MC API.
	*/
	?>
	<input type="submit" id="mc2_add" value="<?php echo JText::_('MOD_MAILCHIMP2_LABEL_SUBMIT_BUTTON'); ?>" onclick="addEmailAddress(); return false;" /></form><br />
	
	
	
</div>

<?php
// Micah Wittman, wittman.org
function modMC2_escapeJavaScriptText($string) { 
	return str_replace("\n", '\n', str_replace('"', '\"', addcslashes(str_replace("\r", '', (string)$string), "\0..\37'\\"))); 
} 
//EOF