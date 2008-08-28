<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: config_parser_rules_edit.php
|
| Purpose: The configuration include for creating and editing parser mail rules.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/mail_rules/mail_rules.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/general.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_RULES_CHANGE,BITGROUP_2)) {
	die("");
}
	
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationRouting.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");

$wstags = new CerWorkstationTags();
$wsteams = new CerWorkstationTeams();
$wsagents = CerAgents::getInstance();

$agents = $wsagents->getList("RealName");
$teams = $wsteams->getTeams();
	
$cerberus_format = new cer_formatting_obj;
$type = isset($_REQUEST["type"]) ? $_REQUEST["type"] : "post";

if($rid!=0) {	
    $o_rule_handler = new CER_MAIL_RULE_HANDLER($rid);
    $o_rule = &$o_rule_handler->mail_rules[0]; // [JAS]: Pointer to the single mail rule we've loaded.
}
else {
	$o_rule = new CER_MAIL_RULE_STRUCT();
	$o_rule->rule_pre_parse = ($type=="pre") ? 1 : 0;
	$o_rule->rule_title = sprintf("%s-Parse Mail Rule",
			($type=="pre") ? "Pre" : "Post"
		);
}
?>

<form action="configuration.php" method="post" name="ruleedit">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="prid" value="<?php echo $rid ?>">
<input type="hidden" name="module" value="rules">
<input type="hidden" name="form_submit" value="rules_edit">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . "Parser Mail Rule Updated!" . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="#B5B5B5">
<?php
if(0==$rid) {
?>
  <tr> 
    <td class="boxtitle_orange_glass">Create <?php echo $o_rule->rule_title; ?></td>
  </tr>
<?php
}
else {
?>
  <tr> 
    <td class="boxtitle_orange_glass">Edit <?php echo $o_rule->rule_title; ?> '<?php echo stripslashes($o_rule->rule_name); ?>'</td>
  </tr>
<?php
}
?>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
      <div align="right" class="cer_maintable_text"></div>
      <div align="left">
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td colspan=3><span class="cer_maintable_heading">Rule Name: </span>
              <input type="text" name="rule_name" size="32" maxlength="128" value="<?php echo cer_dbc($o_rule->rule_name); ?>">
              <span class="cer_footer_text">Choose a name for this rule.</span>
            </td>
          </tr>
          <tr> 
            <td colspan=3><span class="cer_maintable_heading">Rule Type: </span>
            	<span class="cer_maintable_text"><?php echo $o_rule->rule_title; ?></span>
            	<input type="hidden" name="rule_pre_parse" value="<?php echo $o_rule->rule_pre_parse; ?>">
            </td>
          </tr>
                    
          <tr> 
            <td class="boxtitle_gray_glass_dk" colspan="3">Rule Criteria:</td>
          </tr>
          
          <?php
          if(isset($o_rule)) {$f = $o_rule->is_enabled_fov(RULE_FIELD_SENDER); }
          ?>
          <tr> 
            <td class="cer_maintable_heading" width="30%">
              <input type="checkbox" name="rule_crit_sender" value="1" <?php echo ((!empty($f))?"CHECKED":""); ?>> <b>Sender Address</b></td> 
              <td width="20%%"><select name="rule_crit_sender_oper">
              	<option value="3" <?php echo((@$f->fov_oper==RULE_OPER_CONTAINS)?"SELECTED":""); ?>>contains
              	<option value="4" <?php echo((@$f->fov_oper==RULE_OPER_NOT_CONTAINS)?"SELECTED":""); ?>>does not contain
              	<option value="1" <?php echo((@$f->fov_oper==RULE_OPER_EQUAL)?"SELECTED":""); ?>>equal to
              	<option value="2" <?php echo((@$f->fov_oper==RULE_OPER_NOT_EQUAL)?"SELECTED":""); ?>>not equal to
              	<option value="5" <?php echo((@$f->fov_oper==RULE_OPER_REGEXP)?"SELECTED":""); ?>>regular expression
              </select></td>
              <td width="50%"><input type="text" name="rule_crit_sender_value" size="32" maxlength="128" value="<?php echo cer_dbc(@$f->fov_value); ?>"></td>
          </tr>
          <?php
          if(isset($o_rule)) {$f = $o_rule->is_enabled_fov(RULE_FIELD_SUBJECT); }
          ?>
          <tr> 
            <td class="cer_maintable_heading">
              <input type="checkbox" name="rule_crit_subject" value="2" <?php echo ((!empty($f))?"CHECKED":""); ?>> <b>E-mail Subject</b></td> 
              <td><select name="rule_crit_subject_oper">
              	<option value="3" <?php echo((@$f->fov_oper==RULE_OPER_CONTAINS)?"SELECTED":""); ?>>contains
              	<option value="4" <?php echo((@$f->fov_oper==RULE_OPER_NOT_CONTAINS)?"SELECTED":""); ?>>does not contain
              	<option value="1" <?php echo((@$f->fov_oper==RULE_OPER_EQUAL)?"SELECTED":""); ?>>equal to
              	<option value="2" <?php echo((@$f->fov_oper==RULE_OPER_NOT_EQUAL)?"SELECTED":""); ?>>not equal to
              	<option value="5" <?php echo((@$f->fov_oper==RULE_OPER_REGEXP)?"SELECTED":""); ?>>regular expression
              </select></td>
              <td><input type="text" name="rule_crit_subject_value" size="32" maxlength="128" value="<?php echo cer_dbc(@$f->fov_value); ?>"></td>
          </tr>
          <?php
          if(isset($o_rule)) {$f = $o_rule->is_enabled_fov(RULE_FIELD_BODY); }
          ?>
          <tr> 
            <td class="cer_maintable_heading">
              <input type="checkbox" name="rule_crit_body" value="8" <?php echo ((!empty($f))?"CHECKED":""); ?>> <b>E-mail Body</b></td> 
              <td><select name="rule_crit_body_oper">
              	<option value="3" <?php echo((@$f->fov_oper==RULE_OPER_CONTAINS)?"SELECTED":""); ?>>contains
              	<option value="4" <?php echo((@$f->fov_oper==RULE_OPER_NOT_CONTAINS)?"SELECTED":""); ?>>does not contain
              	<option value="1" <?php echo((@$f->fov_oper==RULE_OPER_EQUAL)?"SELECTED":""); ?>>equal to
              	<option value="2" <?php echo((@$f->fov_oper==RULE_OPER_NOT_EQUAL)?"SELECTED":""); ?>>not equal to
              	<option value="5" <?php echo((@$f->fov_oper==RULE_OPER_REGEXP)?"SELECTED":""); ?>>regular expression
              </select></td>
              <td><input type="text" name="rule_crit_body_value" size="32" maxlength="128" value="<?php echo cer_dbc(@$f->fov_value); ?>"></td>
          </tr>
          <?php
          if(isset($o_rule)) {$f = $o_rule->is_enabled_fov(RULE_FIELD_QUEUE); }
          ?>
          <tr> 
            <td class="cer_maintable_heading">
              <input type="checkbox" name="rule_crit_queue" value="3" <?php echo ((!empty($f))?"CHECKED":""); ?>> <b>Destination Mailbox</b></td> 
              <td><select name="rule_crit_queue_oper">
              	<option value="1" <?php echo((@$f->fov_oper==RULE_OPER_EQUAL)?"SELECTED":""); ?>>equal to
              	<option value="2" <?php echo((@$f->fov_oper==RULE_OPER_NOT_EQUAL)?"SELECTED":""); ?>>not equal to
              </select></td>
							<td><?php $cerberus_disp->draw_queue_select("rule_crit_queue_value",@$f->fov_value,"","","","read"); ?></td>
          </tr>
          <?php
          if(isset($o_rule)) {$f = $o_rule->is_enabled_fov(RULE_FIELD_SLA); }
          ?>
          <tr> 
            <td class="cer_maintable_heading">
              <input type="checkbox" name="rule_crit_sla" value="9" <?php echo ((!empty($f))?"CHECKED":""); ?>> <b><?php echo LANG_CONTACTS_REGISTRED_COMPANY_SLAPLAN; ?></b></td> 
              <td><select name="rule_crit_sla_oper">
              	<option value="1" <?php echo((@$f->fov_oper==RULE_OPER_EQUAL)?"SELECTED":""); ?>>equal to
              	<option value="2" <?php echo((@$f->fov_oper==RULE_OPER_NOT_EQUAL)?"SELECTED":""); ?>>not equal to
              </select></td>
							<td><?php $cerberus_disp->draw_sla_select("rule_crit_sla_value",@$f->fov_value,"","",""); ?></td>
          </tr>
          
          <?php
          if(isset($o_rule)) {$f = $o_rule->is_enabled_fov(RULE_FIELD_NEW_TICKET); }
          if(@$f->fov_value==1) $tf=1; else $tf=0;
          ?>
          <tr> 
            <td class="cer_maintable_heading">
              <input type="checkbox" name="rule_crit_new" value="4" <?php echo ((!empty($f))?"CHECKED":""); ?>> <b>E-mail is New (Not a Reply)</b></td> 
              <td><select name="rule_crit_new_oper">
              	<option value="1">equal to
              </select></td>
              <td><select name="rule_crit_new_value">
              	<option value="1" <?php echo (($tf)?"SELECTED":""); ?>>true
              	<option value="0" <?php echo ((!$tf)?"SELECTED":""); ?>>false
              </select></td>
          </tr>
          <?php
          if(isset($o_rule)) {$f = $o_rule->is_enabled_fov(RULE_FIELD_REOPENED_TICKET); }
          if(@$f->fov_value==1) $tf=1; else $tf=0;
          ?>
          <tr> 
            <td class="cer_maintable_heading">
              <input type="checkbox" name="rule_crit_reopened" value="5" <?php echo ((!empty($f))?"CHECKED":""); ?>> <b>E-mail Re-opens a Ticket</b></td> 
              <td><select name="rule_crit_reopened_oper">
              	<option value="1">equal to
              </select></td>
              <td><select name="rule_crit_reopened_value">
              	<option value="1" <?php echo (($tf)?"SELECTED":""); ?>>true
              	<option value="0" <?php echo ((!$tf)?"SELECTED":""); ?>>false
              </select></td>
          </tr>
           <?php
           if(isset($o_rule)) {$f = $o_rule->is_enabled_fov(RULE_FIELD_ATTACHMENT_NAME); }
           ?>
           <tr> 
             <td class="cer_maintable_heading" width="30%">
               <input type="checkbox" name="rule_crit_attachment_name" value="6" <?php echo ((!empty($f))?"CHECKED":""); ?>> <b>Attachment Name</b></td> 
               <td width="20%"><select name="rule_crit_attachment_name_oper">
               	<option value="3" <?php echo((@$f->fov_oper==RULE_OPER_CONTAINS)?"SELECTED":""); ?>>contains
               	<option value="4" <?php echo((@$f->fov_oper==RULE_OPER_NOT_CONTAINS)?"SELECTED":""); ?>>does not contain
               	<option value="1" <?php echo((@$f->fov_oper==RULE_OPER_EQUAL)?"SELECTED":""); ?>>equal to
               	<option value="2" <?php echo((@$f->fov_oper==RULE_OPER_NOT_EQUAL)?"SELECTED":""); ?>>not equal to
              	<option value="5" <?php echo((@$f->fov_oper==RULE_OPER_REGEXP)?"SELECTED":""); ?>>regular expression
               </select></td>
               <td width="50%"><input type="text" name="rule_crit_attachment_name_value" size="32" maxlength="128" value="<?php echo cer_dbc(@$f->fov_value); ?>"></td>
           </tr>
           <?php
           if(isset($o_rule)) {$f = $o_rule->is_enabled_fov(RULE_FIELD_SPAM_PROBABILITY); }
           ?>
           <tr> 
             <td class="cer_maintable_heading" width="30%">
               <input type="checkbox" name="rule_crit_spam_probability" value="7" <?php echo ((!empty($f))?"CHECKED":""); ?>> <b><?php echo LANG_WORD_SPAM_PROBABILITY; ?></b></td> 
               <td width="20%"><select name="rule_crit_spam_probability_oper">
               	<option value="1" <?php echo((@$f->fov_oper==RULE_OPER_EQUAL)?"SELECTED":""); ?>>equal to
               	<option value="2" <?php echo((@$f->fov_oper==RULE_OPER_NOT_EQUAL)?"SELECTED":""); ?>>not equal to
               	<option value="6" <?php echo((@$f->fov_oper==RULE_OPER_LTE)?"SELECTED":""); ?>>less than or equal to
               	<option value="7" <?php echo((@$f->fov_oper==RULE_OPER_GTE)?"SELECTED":""); ?>>greater than or equal to
               </select></td>
               <td width="50%"><input type="text" name="rule_crit_spam_probability_value" size="5" maxlength="5" value="<?php echo cer_dbc(@$f->fov_value); ?>"><span class="cer_maintable_text">%</a></td>
           </tr>
           
          <tr>
          	<td colspan="3">
          	
		    	<table cellpadding="2" cellspacing="0" border="0" bgcolor="#FFFFFF" width="75%">
	    			<tr>
		    			<td class="boxtitle_green_glass" width="99%">Quick Regular Expression Tester</td>
		    			<td class="boxtitle_green_glass" width="1%" nowrap valign="middle" align="center"><img alt="Expand" id="rule_regexp_tester_icon" src="includes/images/icon_expand.gif" width="16" height="16" onclick="javascript:toggleRuleRegexpTester();" onmouseover="javascript:this.style.cursor='hand';"></td>
	    			</tr>
	    		</table>
	    		
	    		<div id="rule_regexp_tester" style="display:none;">
	    		<table cellpadding="2" cellspacing="1" border="0" width="75%">
	    			<tr>
	    				<td bgcolor="#EEEEEE" colspan="2">
	    					 <span class="cer_maintable_heading">Regexp: </span>
	    					 <input type="text" name="regexp_tester_pattern" size="35" value="/find/" onfocus="javascript:clearRegexpResult(this.form);">
	    					 <br>
	    					 <span class="cer_maintable_heading">Text to Test: </span>
	    					 <input type="text" name="regexp_tester_subject" size="45" value="This should *find* a word in text." onfocus="javascript:clearRegexpResult(this.form);">
	    					 <br>
	    					 <input type="button" value="Test!" onclick="javascript:testRegexp(this.form);" class="cer_button_face">
	    					 <span class="cer_maintable_heading">Result: </span>
	    					 <input type="text" name="regexp_tester_result" size="5" value="">
	    				</td>
	    			</tr>
	    		</table>
		    	</div>

			<script>
			
			icon_expand = new Image;
			icon_expand.src = "includes/images/icon_expand.gif";
	
			icon_collapse = new Image;
			icon_collapse.src = "includes/images/icon_collapse.gif";

			function toggleRuleRegexpTester() {
				if (document.getElementById) {
					if(document.getElementById("rule_regexp_tester").style.display=="block") {
						document.getElementById("rule_regexp_tester").style.display="none";
						document.getElementById("rule_regexp_tester_icon").src=icon_expand.src;
					}
					else {
						document.getElementById("rule_regexp_tester").style.display="block";
						document.getElementById("rule_regexp_tester_icon").src=icon_collapse.src;
					}
				}
			}
			
			function clearRegexpResult(f) {
				f.regexp_tester_result.value = "";
			}
			
			function testRegexp(f) {
				// [JAS]: We want to strip the leading + trailing slashes (/) since JScript freaks.
				regexp_string = f.regexp_tester_pattern.value;
				regexp_string = regexp_string.substr(1,regexp_string.length);
				last_slash = regexp_string.indexOf("/");
				regexp_string = regexp_string.substr(0,last_slash);
				
				var regexp = new RegExp("" + regexp_string);
				var strr = f.regexp_tester_subject.value;
				
				var matches = strr.match(regexp);
				
				if(matches == null) {
					f.regexp_tester_result.value = "Fail!";
				}
				else {
					f.regexp_tester_result.value = "Pass!";
				}
			}
			</script>
          	
          	</td>
          </tr>           
           
          <tr> 
            <td class="boxtitle_gray_glass_dk" colspan="3">Rule Actions:</td>
          </tr>
          <?php
          	// [JAS]: Only pre-parse actions
            if($o_rule->rule_pre_parse) {
          ?>
          
	          <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_PRE_REDIRECT); }
			  ?>
	          <tr> 
	            <td colspan="1" class="cer_maintable_heading">
	              <input type="checkbox" name="rule_act_pre_redirect" value="<?php echo RULE_ACTION_PRE_REDIRECT; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Redirect E-mail to</b>
	            </td>
	            <td colspan="2">
	            	<input type="input" name="rule_act_pre_redirect_value" size="32" maxlength="128" value="<?php echo stripslashes($a->action_value); ?>"> <span class="cer_footer_text">(e-mail address)</span>
	            </td>
	          </tr>
          
	          <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_PRE_BOUNCE); }
			  ?>
	          <tr> 
	            <td colspan="1" class="cer_maintable_heading" valign="top">
	              <input type="checkbox" name="rule_act_pre_bounce" value="<?php echo RULE_ACTION_PRE_BOUNCE; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Reply to E-mail with Message</b>
	            </td>
				<td colspan="2">
					<textarea name="rule_act_pre_bounce_value" rows="5" cols="32"><?php echo stripslashes($a->action_value); ?></textarea>
				</td>
	          </tr>
          
	          <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_PRE_NO_AUTOREPLY); }
			  ?>
	          <tr> 
	            <td colspan="3" class="cer_maintable_heading">
	              <input type="checkbox" name="rule_act_pre_no_autoreply" value="<?php echo RULE_ACTION_PRE_NO_AUTOREPLY; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Don't Send Mailbox Auto-Reply</b>
	            </td>
	          </tr>
	          
	          <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_PRE_NO_NOTIFICATION); }
			  ?>
	          <tr> 
	            <td colspan="3" class="cer_maintable_heading">
	              <input type="checkbox" name="rule_act_pre_no_notification" value="<?php echo RULE_ACTION_PRE_NO_NOTIFICATION; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Don't Send Notifications</b>
	            </td>
	          </tr>
	          
	          <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_PRE_IGNORE); }
			  ?>
	          <tr> 
	            <td colspan="3" class="cer_maintable_heading">
	              <input type="checkbox" name="rule_act_pre_ignore" value="<?php echo RULE_ACTION_PRE_IGNORE; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Don't Create a Ticket From E-mail</b>
	            </td>
	          </tr>
          
          <?php
            // [JAS]: Only post-parse actions
            } else {
          ?>

	         <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_CHANGE_PRIORITY); }
			  	?>
	          <tr> 
	            <td class="cer_maintable_heading">
	              <label><input type="checkbox" name="rule_act_chpriority" value="<?php echo RULE_ACTION_CHANGE_PRIORITY; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Change Priority to</b></label></td>   
						<td colspan="2"><?php $cerberus_disp->draw_priority_select($priority_options,"rule_act_chpriority_value",@$a->action_value); ?>
	            </td>
	          </tr>
	          
	         <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_CHANGE_STATUS); }
			  	?>
	          <tr> 
	            <td class="cer_maintable_heading">
	              <label><input type="checkbox" name="rule_act_chstatus" value="<?php echo RULE_ACTION_CHANGE_STATUS; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Change Status to</b></label></td>   
						<td colspan="2">
						<select name="rule_act_chstatus_value">
							<option value="open" <?php if($a->action_value=="open") echo "SELECTED"; ?>>open
							<option value="closed" <?php if($a->action_value=="closed") echo "SELECTED"; ?>>closed
							<option value="deleted" <?php if($a->action_value=="deleted") echo "SELECTED"; ?>>deleted
						</select>
	            </td>
	          </tr>

	         <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_CUSTOMER_WAITING); }
			  	?>
	          <tr> 
	            <td class="cer_maintable_heading">
	              <label><input type="checkbox" name="rule_act_custwait" value="<?php echo RULE_ACTION_CUSTOMER_WAITING; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Set Waiting On Customer Reply to</b></label></td>   
						<td colspan="2">
						<select name="rule_act_custwait_value">
							<option value="yes" <?php if($a->action_value=="yes") echo "SELECTED"; ?>>yes
							<option value="no" <?php if($a->action_value=="no") echo "SELECTED"; ?>>no
						</select>
	            </td>
	          </tr>

          <?php
          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_CHANGE_QUEUE); }
          ?>
          <tr> 
            <td class="cer_maintable_heading">
              <label><input type="checkbox" name="rule_act_chqueue" value="<?php echo RULE_ACTION_CHANGE_QUEUE; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Destination Mailbox</b></label></td> 
              <td colspan="2">
					<?php $cerberus_disp->draw_queue_select("rule_act_chqueue_value",@$a->action_value,"","","","read"); ?>
				  </td>
          </tr>
	          
	         <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_ADD_TAGS); }
	          
	          if($a && !empty($a->action_value)) {
//	          	$tmp = explode(',', $a->action_value);
//	          	$tagList = array();
//	          	if(is_array($tmp))
//	          	foreach($tmp as $t) {
//	          		$tagList[$t] = $t;
//	          	}
	          }
			  	?>
	          <tr> 
	            <td class="cer_maintable_heading" valign="top">
	              <label><input type="checkbox" name="rule_act_tags" onclick="toggleDiv('action_tags');" value="<?php echo RULE_ACTION_ADD_TAGS; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Apply Tags</b></label></td>
					<td colspan="2"><div id="action_tags" style="display:<?php echo (($a)?"block":"none"); ?>;">
						<table border="0" cellpadding="0" cellspacing="0" class="table_orange">
			            <tr>
			              <td><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FF8000">
			                  <tr>
			                    <td style="padding:2px;" nowrap="nowrap"><span class="text_title_white"><img alt="Folder" src="includes/images/icone/16x16/folder_network.gif" alt="find" width="16" height="16" /> Tags </span></td>
			                  </tr>
			              </table></td>
			            </tr>
			            <tr>
								<td bgcolor="#FFF0D9" style="padding:2px;" class="workflow_item">
									<?php
//										$sql = sprintf("SELECT t.tag_name ".
//											"FROM `workstation_routing_tags` r ".
//											"INNER JOIN workstation_tags t ON ( r.tag_id = t.tag_id ) ".
//											"WHERE r.queue_id = %d ".
//											"ORDER BY t.tag_name ASC",
//												$qid
//										);
//										$res = $cerberus_db->query($sql);
//										
//										$appliedTags = array();
//										if($cerberus_db->num_rows($res))
//										while($row = $cerberus_db->fetch_row($res)) {
//											$tag_name = stripslashes($row['tag_name']);
//											$appliedTags[] = $tag_name;
//										}
//									?>
								<b>Enter tags separated by commas:</b><br>
						            <div class="searchdiv">
					                    <div class="searchautocomplete">
											<textarea name="rule_act_tags_value" id="tag_input" rows="5" cols="24"><?php echo $a->action_value; ?></textarea>
					                        <div id="searchcontainer" class="searchcontainer"></div>
					                    </div>
						            </div>
									<script>
										YAHOO.util.Event.addListener(document.body, "load", autoTags('tag_input','searchcontainer'));
									</script>
								
									<?php
//									if(is_array($wstags->sets))
//									foreach($wstags->sets as $setId => $set) {
//										echo "<b>",$set->name,"</b><br>";
//										if(is_array($set->tags))
//										foreach($set->tags as $tag) {
//											$sel = (isset($tagList[$tag->id])) ? 1 : 0;
										?>
<!---										<label><input type="checkbox" name="rule_act_tags_value[]" value="<?php echo $tag->id; ?>" <?php if($sel) echo "CHECKED";?>> <?php echo str_repeat("&nbsp;",$level) . (($level)?"-":"") . $tag->name; ?></label><br>--->
										<?php
//										 } 
//									}
									?>
								</td>
			            </tr>
			          </table></div>
					</td>
	          </tr>
	          
	         <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_ADD_AGENTS); }

	          if($a && !empty($a->action_value)) {
	          	$tmp = explode(',', $a->action_value);
	          	$agentList = array();
	          	if(is_array($tmp))
	          	foreach($tmp as $t) {
	          		$agentList[$t] = $t;
	          	}
	          }
			  	?>
	          <tr> 
	            <td class="cer_maintable_heading" valign="top">
	              <label><input type="checkbox" name="rule_act_agents" onclick="toggleDiv('action_agents');" value="<?php echo RULE_ACTION_ADD_AGENTS; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Suggest Agents</b></label></td>
					<td colspan="2"><div id="action_agents" style="display:<?php echo (($a)?"block":"none"); ?>">
						<table border="0" cellpadding="0" cellspacing="0" class="table_green">
			            <tr>
			              <td><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#AD5BFF">
			                  <tr>
			                    <td style="padding:2px;" nowrap="nowrap"><span class="text_title_white"><img alt="Agent" src="includes/images/icone/16x16/user_headset.gif" width="16" height="16" /> Agents </span></td>
			                  </tr>
			              </table></td>
			            </tr>
			            <tr>
			              <td bgcolor="#F3E8FF" style="padding:2px;" class="workflow_item">
									<?php
									if(is_array($agents))
									foreach($agents as $agentId => $agent) {
										$sel = (isset($agentList[$agentId])) ? 1 : 0;
									?>
									<label><input type="checkbox" name="rule_act_agents_value[]" value="<?php echo $agentId; ?>" <?php if($sel) echo "CHECKED";?>> <?php echo $agent->getRealName(); ?></label><br>
									<?php } ?>
			              </td>
			            </tr>
			          </table></div>				
					</td>
	          </tr>
	          
	         <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_FLAG_AGENTS); }

	          if($a && !empty($a->action_value)) {
	          	$tmp = explode(',', $a->action_value);
	          	$agentList = array();
	          	if(is_array($tmp))
	          	foreach($tmp as $t) {
	          		$agentList[$t] = $t;
	          	}
	          }
			  	?>
	          <tr> 
	            <td class="cer_maintable_heading" valign="top">
	              <label><input type="checkbox" name="rule_act_flags" onclick="toggleDiv('action_flags');" value="<?php echo RULE_ACTION_FLAG_AGENTS; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Flag Agents</b></label></td>
					<td colspan="2"><div id="action_flags" style="display:<?php echo (($a)?"block":"none"); ?>">
						<table border="0" cellpadding="0" cellspacing="0" class="table_green">
			            <tr>
			              <td><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#AD5BFF">
			                  <tr>
			                    <td style="padding:2px;" nowrap="nowrap"><span class="text_title_white"><img alt="Agent" src="includes/images/icone/16x16/user_headset.gif" width="16" height="16" /> Agents </span></td>
			                  </tr>
			              </table></td>
			            </tr>
			            <tr>
			              <td bgcolor="#F3E8FF" style="padding:2px;" class="workflow_item">
									<?php
									if(is_array($agents))
									foreach($agents as $agentId => $agent) {
										$sel = (isset($agentList[$agentId])) ? 1 : 0;
									?>
									<label><input type="checkbox" name="rule_act_flags_value[]" value="<?php echo $agentId; ?>" <?php if($sel) echo "CHECKED";?>> <?php echo $agent->getRealName(); ?></label><br>
									<?php } ?>
			              </td>
			            </tr>
			          </table></div>				
					</td>
	          </tr>
	          
	         <?php
	          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_CLEAR_WORKFLOW); }
			  	?>
	          <tr> 
	            <td class="cer_maintable_heading" colspan="3">
	              <label><input type="checkbox" name="rule_act_clearworkflow" value="<?php echo RULE_ACTION_CLEAR_WORKFLOW; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Clear Existing Workflow</b></label></td>   
	            </td>
	          </tr>
	          
          <?php
            } // end post-parse actions
          ?>
          
          <?php
          if(isset($o_rule)) {$a = $o_rule->is_enabled_action(RULE_ACTION_STOP_PROCESSING); }
		  ?>
          <tr> 
            <td colspan="3" class="cer_maintable_heading">
              <label><input type="checkbox" name="rule_act_break" value="<?php echo RULE_ACTION_STOP_PROCESSING; ?>" <?php echo ((!empty($a))?"CHECKED":""); ?>> <b>Stop Processing Remaining Rules</b></label>
            </td>
          </tr>
          <tr> 
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="2" class="cer_maintable_text"><b>Note:</b> Enter regular expressions using: <i>/expression/flags</i><br>
            	<a href="http://us3.php.net/manual/en/function.preg-match-all.php" target="_blank" class="cer_maintable_text">More on regular expressions from the PHP website.</a>
            	</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
			<tr>
				<td align="left" width="50%">
					<input type="button" value="&lt;&lt; Back to Parser Mail Rules (Don't Save)" onclick="javascript:document.location='configuration.php?module=rules&sid=<?php echo $session->session_id; ?>';" class="cer_button_face">
				</td>
				<td align="right" width="50%">
					<input type="submit" value="<?php echo LANG_BUTTON_SAVE_CHANGES; ?>" class="cer_button_face">
				</td>
			</tr>
			</table>
		</td>
	</tr>
</table>
</form>
<br>
