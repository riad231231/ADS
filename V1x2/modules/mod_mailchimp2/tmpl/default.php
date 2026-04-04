<?php
defined('_JEXEC') or die('Restricted access');
$css_class_label = "modmc2_label";

$mc_checkemailaddress = $params->get('mc_checkemailaddress');
?>

<div class="module<?php if($params->get('moduleclass_sfx')) echo $params->get('moduleclass_sfx'); ?>" >
	<script type="text/javascript" src="<?php echo JURI::base(); ?>modules/mod_mailchimp2/ajax.js"> </script>

	<script type="text/javascript">
	//<![CDATA[

	// Global Ajax request
	var MCajaxReq = new MCAjaxRequest();

	// Add a new email address on the server using Ajax
	function getInterestGroup(inputgroup){
		var iginputs = document.getElementById(inputgroup);
		if(!iginputs){
			return('');
		}
		var group_id = iginputs.getElementsByTagName('legend');
		ig_values = 'mcig_' + group_id[0].title + ','
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
					      ig_values = ig_values + inputs[i].value + ",";
	      				}
				}
			}
		}
		return(ig_values);
	}	// end of getInterestGroup

	function getInterestGroups(){
		var retstring;
		retstring = '';
		for(i=1; i<10; i++){
			var groupname;
			groupname = 'mciginputs' + i;
			retstring = retstring + getInterestGroup(groupname);
		}
		return(retstring);
	} // end of getInterestGroups
	


	function addEmailAddress() {


	
		var lang_error_invalid_email = '<?php echo modMC2_escapeJavaScriptText(JText::_('MOD_MAILCHIMP2_ERROR_INVALID_EMAIL'));?>';
		var lang_message_adding = '<?php echo modMC2_escapeJavaScriptText(JText::_('MOD_MAILCHIMP2_MESSAGE_ADDING'));?>';

		<?php if($mc_checkemailaddress) : ?>
		// check email address for validity
		// not perfect, and now off by default
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
		
	
		var i_groups;	
		i_groups = getInterestGroups();
		if(i_groups.length){
			postvars = postvars + "&ig=" + i_groups;
		}
		MCajaxReq.send("POST", "<?php echo JURI::base(); ?>modules/mod_mailchimp2/send_to_mc.php", handleRequest, "application/x-www-form-urlencoded; charset=UTF-8", postvars);

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

 
	//require_once('MCAPI.class.php');
	//require_once('mod_mailchimp2_helper.php');
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
    		  //echo "Can't find lists: ";
    		  echo JText::_('MOD_MAILCHIMP2_ERROR_GENERAL');
    		  echo $api->errorMessage;
    		  echo "<br />\n";

    		  //echo " error code: ";
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
	<div class="<?php echo $css_class_label;?>"><?php echo JText::_('MOD_MAILCHIMP2_LABEL_EMAIL_ADDRESS'); ?></div>
		<input type="text" id="mc2_email" name="email" value="" size="<?php echo $textsize;?>" />
	<!--</div>-->
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

		  if($mergevar['public'] != 1) {
		    continue;
		  }
		  
		
		
			if($mergevar['tag'] == 'EMAIL') {
				continue;
			} else {
			
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
				
				
				//handle each type of field
				
				if ($mergevar['field_type'] == "dropdown") {
					//print_r ($mergevar['choices']);
				
					echo '<span class="' . $css_class_label . ' ' . $css_class_required . '">' . $mergeVarLabel . ": </span><br />\n"; //was $mergevar['name']
					echo '<select class="mergevars" type="text" name="' . $mergevar['tag'] . '" id="' . $mergevar['tag'] . '"';
					echo '';
					echo "/ >";
					
					foreach ($mergevar['choices'] as $choice) {
						echo '<option value="' . $choice . '"';
						if ($mergevar['default'] == $choice) {
							echo ' selected="selected" ';
						}
						echo '>';

						echo "$choice";
						echo '</option>';
					}
					
					
					echo '</select>';
					echo "<br />\n";
			
				}	elseif ($mergevar['field_type'] == "radio") {
					echo '<span class="' . $css_class_label . ' ' . $css_class_required . '">' . $mergeVarLabel . ": </span><br />\n"; //was $mergevar['name']
				
					//MC autogenerated form example appends a digit to the ID
					$choiceNumber = 0;
					foreach ($mergevar['choices'] as $choice) {
						$radioId = $mergevar['tag'] . "-" . $choiceNumber;
					
						echo '<input ';
						echo 'id="' . $radioId . '" ';
						echo 'class="mergevars" name="' . $mergevar['tag'] . '" type="radio" value="' . $choice . '"';
						if ($mergevar['default'] == $choice) {
							echo ' checked="checked" ';
						}

						echo ' />';
						echo '<label for="' . $radioId . '">' . $choice . '</label>';
						echo "<br />\n";
						
					$choiceNumber++;
					}
				
				
				}	else {
					echo '<span class="' . $css_class_label . ' ' . $css_class_required . '">' . $mergeVarLabel . ": </span><br />\n"; //was $mergevar['name']
					echo '<input class="mergevars" type="text" name="' . $mergevar['tag'] . '" ';
					echo 'size="' . $textsize . '"';
					echo "/><br />\n";
				}
			}
		}
		?>
		</div>
		<?php
		if($params->get('askinterest')){
			// show interest areas
 			//* @returnf string name Name for the Interest groups
			//* @returnf string form_field Gives the type of interest group: checkbox,radio,select
			//* @returnf array groups Array of the group names
			$int_groups = array();
      $int_groups = $api->listInterestGroupings($list_id);
			
			
      if ($int_groups !== false) { 
        $i = 1;
			foreach($int_groups as $int_group){
			
			// Don't draw hidden fieldsets 
			if ($int_group['form_field'] == "hidden") {
			 continue;
			}
			
			
				$groups = $int_group['groups'];
				echo '<fieldset class="mciginputs" id="mciginputs' . $i . '">';
				$i++;
				echo '<legend title="' . $int_group['id'] . '">' . $int_group['name'] . '</legend>';
				switch ($int_group['form_field']) {
					case "checkboxes":	
						foreach($groups as $group){
							echo '<input type="checkbox" name="ig" value="' . $group['name'] . '" />'. $group['name'] . "<br />\n";
						}
						break;
					case "radio":

						foreach($groups as $group){
							echo '<input type="radio" name="ig" value="' . $group['name'] . '" />' . $group['name'] . "<br />\n";
						}
						break;
					case "dropdown":
						echo '<select name="ig" id="mciginputs" style="width: 180px">';
						foreach($groups as $group){
							echo '<option>' . $group['name'] . "</option>\n";
						}
						echo '</select><br />';
			
						break;
					
					case "hidden":
					  continue;
					break;
										
					default:
            continue; 
						//echo "can't happen";
						break;
					} // end of case
					echo '</fieldset>';
				} // end of foreach
			
			} //end of gropus test
		}

	?>
	<input type="submit" id="mc2_add" value="<?php echo JText::_('MOD_MAILCHIMP2_LABEL_SUBMIT_BUTTON'); ?>" onclick="addEmailAddress(); return false;" /></form><br />
</div>

