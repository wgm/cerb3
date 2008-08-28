<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: config_queues_edit.php
|
| Purpose: The configuration include that facilitates the queue creation and
|			modification of properties.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationRouting.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");

$wstags = new CerWorkstationTags();
$wsteams = new CerWorkstationTeams();
$agents = CerAgents::getInstance();
$routing = new CerWorkstationRouting();

$teams = $wsteams->getTeams();
$agentList = $agents->getList("RealName");

$license = new CerWorkstationLicense();
$sched_handler = new cer_ScheduleHandler();

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_QUEUES_CHANGE,BITGROUP_2)) {
	die("Permission denied.");
}

if(!isset($qid))
{ echo LANG_CONFIG_QUEUE_EDIT_NOID; exit(); }	
	
if($qid != 0) { 
	
	$sql = sprintf("SELECT q.queue_id, q.queue_name, q.queue_reply_to, q.queue_email_display_name, q.queue_prefix, q.queue_response_open, q.queue_response_close,".
	" q.queue_response_gated, q.queue_send_open, q.queue_send_closed, q.queue_core_update, q.queue_mode, q.queue_default_response_time, q.queue_default_schedule " .
  	" FROM queue q ".
  	" WHERE q.queue_id = %d ".
  	" ORDER BY q.queue_name ASC",
  		$qid
  );
  $result = $cerberus_db->query($sql);
  if($cerberus_db->num_rows($result)==0)
  	{	echo LANG_CERB_ERROR_ACCESS;
  	exit(); }
	$queue_data = $cerberus_db->fetch_row($result);
}

$queue_handler = new cer_QueueHandler();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pqid" value="<?php echo  @$queue_data["queue_id"]; ?>">
<input type="hidden" name="module" value="queues">
<input type="hidden" name="form_submit" value="queues_edit">
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="B5B5B5">
  <tr> 
<?php
if($qid==0) {
    ?><td class="boxtitle_orange_glass"><span class="text_title_white">Create Mailbox</span></td><?php
}
else {
    ?><td class="boxtitle_orange_glass"><span class="text_title_white">Edit Mailbox '<?php echo  $queue_data["queue_name"] ?>'</span></td><?php
}
?>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td  bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td width="19%" class="cer_maintable_heading" valign="top">Mailbox Name:</td>
            <td width="81%">
              <input type="text" name="queue_name" size="55" maxlength="32" value="<?php echo cer_dbc(@$queue_data["queue_name"]); ?>"><br>
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_QUEUE_EDIT_NAME_IE ?></span></td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading" valign="top">Reply As Address: </td>
            <td width="81%">
              <input type="text" name="queue_reply_to" size="55" maxlength="128" value="<?php echo cer_dbc(@$queue_data["queue_reply_to"]); ?>"><br>
              <span class="cer_footer_text">(The e-mail address your customer will see in their e-mail client.  For example: support@company.com)<br>
              <b>IMPORTANT:</b> For the helpdesk to function properly this should be an e-mail address that routes back to the helpdesk on reply (i.e. an incoming e-mail address on a mailbox).  Multiple mailboxes can share the same 'reply as' address and have different incoming addresses.</span></td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading" valign="top">Reply As Personal: </td>
            <td width="81%">
              <input type="text" name="queue_email_display_name" size="55" maxlength="64" value="<?php echo cer_dbc(@$queue_data["queue_email_display_name"]); ?>"><br>
              <span class="cer_footer_text">(for example: &quot;XYZ, Inc. Support&quot; or &quot;XYZ, LLC. Sales&quot;.  Leave blank for normal email address display)<?php /* echo  LANG_CONFIG_QUEUE_EMAIL_DISPLAY_NAME_IE */ ?></span></td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading" valign="top">Subject Line Prefix:</td>
            <td width="81%">
              <input type="text" name="queue_prefix" size="20" maxlength="32" value="<?php echo  cer_dbc(@$queue_data["queue_prefix"]); ?>"><br>
              <span class="cer_footer_text"> <?php echo  LANG_CONFIG_QUEUE_EDIT_PREFIX_IE ?></span></td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading">&nbsp;</td>
            <td width="81%">&nbsp;</td>
          </tr>
          
          <tr> 
            <td class="boxtitle_gray_glass_dk" colspan="2">Autoresponse Template Tokens:</td>
          </tr>
          <tr> 
            <td colspan="2">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_text">
			              You can use the following tokens in both the auto-open and auto-close response templates:<br>
			              <B>##ticket_id##</B> - Ticket ID or Mask<br>
			              <B>##ticket_subject##</B> - Ticket Subject<br>
			              <B>##ticket_due##</B> - Initial Ticket Due Date according to SLA plan/Mailbox &amp; Schedule<br>
			              <B>##ticket_time_worked##</B> - The Amount of Agent/Technician Time Spent on this Ticket<br>
			              <B>##ticket_email##</B> - The Email Body of the Original Ticket Message<br>
			              <br>
			              <B>##queue_name##</B> - Ticket Mailbox Name (Support, etc.)<br>
			              <B>##queue_hours##</B> - Weekly Business Hours this Mailbox is Staffed (default or SLA override) <br>
			              <B>##queue_response_time##</B> - Mailbox's Target Response Time in Business Hours (default or SLA override) <br>
			              <br>
			              <B>##sla_name##</B> - The Sender's SLA plan (if any, otherwise returns "none").<br>
			              <B>##contact_name##</B> - The Sender's Full Name (if stored in Contacts, otherwise blank).<br>
			              <B>##requester_address##</B> - The Sender's E-mail Address that Opened the Ticket.<br>
			              <B>##company_name##</B> - The Sender's Company Name (if stored in Contacts, otherwise blank).<br>
			              <B>##company_acct_num##</B> - Company Account Number (if stored in Contacts, otherwise blank).<br>
			            </td>
					</tr>
				</table>
            </td>
          </tr>
          </tr>
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          
          <tr> 
            <td colspan="2">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA" width="100%">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_heading">
			            	<?php echo  LANG_CONFIG_QUEUE_EDIT_NEW ?>: <!--- (queue default, sender as no SLA coverage): --->
			            </td>
			        </tr>
			    </table>
            </td>
          </tr>
          <tr> 
            <td colspan="2">
              <label><input type="checkbox" name="queue_send_open" value="1"<?php if(@$queue_data["queue_send_open"]) { echo " checked"; } ?>>
              <span class="cer_maintable_heading"> Enable New Ticket Auto Response</span></label>
            </td>
          </tr>
          <tr> 
            <td colspan="2">
              <textarea name="queue_response_open" cols="80" rows="10"><?php echo  cer_dbc(@$queue_data["queue_response_open"]); ?></textarea><br>
            </td>
          </tr>
          
          </tr>
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          
          <tr> 
            <td colspan="2">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA" width="100%">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_heading">
			            	<?php echo  LANG_CONFIG_QUEUE_EDIT_CLOSED ?>:
			            </td>
			        </tr>
			    </table>
            </td>
          </tr>
          <tr> 
            <td colspan="2">
              <label><input type="checkbox" name="queue_send_closed" value="1"<?php if(@$queue_data["queue_send_closed"]) { echo " checked"; } ?>>
              <span class="cer_maintable_heading"> Enable Ticket Resolved Auto Response</span></label>
            </td>
          </tr>
          <tr> 
            <td colspan="2" >
              <textarea name="queue_response_close" cols="80" rows="10"><?php echo cer_dbc(@$queue_data["queue_response_close"]); ?></textarea><br>
            </td>
            
          </tr>
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          
          <tr> 
            <td class="boxtitle_blue_glass" colspan="2">Public Mailbox Access:</td>
          </tr>
          
          <tr> 
            <td colspan="2">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA" width="100%">
		        	<tr>
			        	<td bgcolor="#EEEEEE" class="cer_maintable_heading">
			            	Mailbox Mode:
			            </td>
			        </tr>
			    </table>
	            <table border="0" cellspacing="0" cellpadding="2" width="100%">
		            <tr>
			            <td bgcolor="#DDDDDD">
			              <select name="queue_mode">
			              	<option value="0" <?php if (@$queue_data["queue_mode"]==0) echo "SELECTED"; ?>>Open (Open to all Clients)
			              	<option value="1" <?php if (@$queue_data["queue_mode"]==1) echo "SELECTED"; ?>>Gated (Restricted by SLA)
			              </select>
			              <br>
			              <span class="cer_footer_text">
			              <b>Open</b> mailboxes are available for use by any requester.<br>
			              <b>Gated</b> mailboxes are restricted by Service Level Agreement (SLA) plans that enable access by company (e.g., Priority/Paid Support, etc.)</span><br>
			              <br>
			            </td>
					</tr>
				</table>
				
            </td>
          </tr>

          <tr>
          	<td colspan="2" bgcolor="#DDDDDD">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA" width="100%">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_heading">
			            	Mailbox Service Level Defaults:
			            </td>
			        </tr>
			    </table>
	            <table border="0" cellspacing="0" cellpadding="2">
		            <tr>
			            <td bgcolor="#DDDDDD" class="cer_maintable_text">
			            	If a client (requester) does not have a Service Level Agreement (SLA) plan covering this mailbox 
			            	with a guaranteed response time and defined hours, the following defaults will be used to 
			            	automatically manage due dates on new tickets and replies.
			            </td>
			        </tr>
		            <tr>
			            <td bgcolor="#DDDDDD">
    		            	<span class="cer_maintable_heading">Default Schedule:</span>
	        				<select name="queue_default_schedule">
            					<option value="0"> - none - 
            					<?php foreach($sched_handler->schedules as $sched) { ?>
            						<option value="<?php echo $sched->schedule_id; ?>" <?php if(@$queue_data["queue_default_schedule"] == $sched->schedule_id) echo "SELECTED"; ?>><?php echo $sched->schedule_name; ?>
            					<?php } ?>
            				</select>
			            </td>
			         </tr>
			         <tr>
			            <td bgcolor="#DDDDDD">
			            	<span class="cer_maintable_heading">Default Response Time Target:</span>
			            	<input type="text" name="queue_default_response_time" value="<?php echo @$queue_data["queue_default_response_time"]; ?>" size="2" maxlength="3"> business hours
			            </td>
			        </tr>
			    </table>
          	</td>
          </tr>
          <tr> 
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          
          <tr>
          	<td colspan="2" bgcolor="#DDDDDD">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA" width="100%">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_heading">
			            	E-mail Template (if 'Gated'):
			            </td>
			        </tr>
			    </table>
          	</td>
          </tr>
          
          <tr> 
            <td colspan="2" class="cer_maintable_text" bgcolor="#DDDDDD">The following template is only used if the Mailbox Mode is 'Gated'.  If Cerberus receives 
            e-mail from a sender who isn't authorized by a service-level agreement (SLA) plan to use this mailbox, this e-mail template 
            will be sent back to the sender and no ticket will be created.  This template should include details on how to enable 
            (e.g., purchase/renew) an appropriate SLA plan.<br>
            <br>
            </td>
          </tr>
          
          <tr> 
            <td colspan="2" bgcolor="#DDDDDD">
	            <table border="0" cellspacing="1" cellpadding="2" bgcolor="#BABABA">
		            <tr>
			            <td bgcolor="#EEEEEE" class="cer_maintable_text">
			              You can use the following tokens in your Access Denied response:<br>
			              <B>##email_subject##</B> - Subject of the Unauthorized E-mail<br>
			              <B>##email_to##</B> - Destination E-mail Address of the Unauthorized E-mail<br>
			              <B>##email_sender##</B> - Sender E-mail Address of the Unauthorized E-mail<br>
			              <B>##email_date##</B> - Date of the Unauthorized E-mail<br>
			              <B>##email_body##</B> - Content of the Unauthorized E-mail<br>
			            </td>
					</tr>
				</table>
            </td>
          </tr>
          
          <tr> 
            <td colspan="2">
              <textarea name="queue_response_gated" cols="80" rows="10"><?php echo  cer_dbc(@$queue_data["queue_response_gated"]); ?></textarea><br>
            </td>
          </tr>
          <tr> 
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
          
          
        </table>
    </td>
  </tr>
  
  <tr>
  	<td class="boxtitle_green_glass">
    	Unique inbound e-mail addresses assigned to this mailbox:
    </td>
  </tr>
	<?php
	// [JAS]: \todo This should really be using cer_Queue.
  if(!empty($queue_data["queue_id"]))
  	{
  	$sql = sprintf("SELECT qa.queue_addresses_id, qa.queue_address, qa.queue_domain ".
    	"FROM queue_addresses qa ".
      "WHERE qa.queue_id = %d " .
      "ORDER BY qa.queue_domain, qa.queue_address;",
      	$queue_data["queue_id"]
     );
    $queue_boxes_res = $cerberus_db->query($sql);
    }
  ?>
  <tr bgcolor="#DDDDDD">
  	<td>
    	<table cellpadding="2" cellspacing="1" border="0">
      	<tr bgcolor="#888888">
        	<td class="cer_maintable_header">Address</td>
        	<td class="cer_maintable_header" align="center">Delete</td>
        </tr>
      	<?php
      	$total_queues = $cerberus_db->num_rows($queue_boxes_res);
        if(!empty($queue_data["queue_id"]) && $total_queues > 0)
          {
          while($queue_box = $cerberus_db->fetch_row($queue_boxes_res))
          	{
            ?>
            <tr>
            	<td class="cer_maintable_heading"><?php echo $queue_box["queue_address"]; ?>@<?php echo $queue_box["queue_domain"]; ?></td>
            	<td align="center"><input type="checkbox" name="queue_addresses[]" value="<?php echo $queue_box["queue_addresses_id"]; ?>"></td>
            </tr>
            <?php
	        }
          }
        ?>
      	<tr>
        	<td>
        	<?php
			if(!$license->hasLicense() && $total_queues >= 1) {
				echo "No Cerberus Helpdesk license uploaded.  Limited to one incoming e-mail address on one mailbox.";
			} else {
        	?>
          <span class="cer_maintable_heading">Add: </span><input type="text" name="queue_address" size="15" maxlength="128"><span class="cer_maintable_heading">@</span><input type="text" name="queue_domain" size="25" maxlength="128">
          <span class="cer_footer_text"> <?php echo  LANG_CONFIG_QUEUE_EDIT_ADDRESS_IE ?></span>
          <?php } ?>
          <br>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  
  <tr>
  	<td class="boxtitle_gray_glass_dk">
    	This mailbox is the responsibility of the following teams:
    </td>
  </tr>
  
  <tr bgcolor="#DDDDDD">
  	<td>
    	<table cellpadding="0" cellspacing="1" border="0">
			<tr class="boxtitle_gray_glass">
				<td class="cer_maintable_heading">Teams</td>
				<td class="cer_maintable_heading">Quick Assign</td>
			</tr>

    		<?php
			if(is_array($teams))
			foreach($teams as $teamId=>$team) {
            ?>
            <tr>
            	<td class="cer_maintable_heading">
            		<label><input type="checkbox" name="queue_to_teams[]" value="<?php echo $teamId; ?>" <?php echo ($team->queues[$queue_data["queue_id"]]) ? "CHECKED" : ""; ?>> 
            		<?php echo $team->name; ?></label>
            	</td>
            	<td align="center"><input type="checkbox" name="quick_assign_teams[]" value="<?php echo $teamId; ?>" <?php echo ($team->quick_assign[$queue_data["queue_id"]]) ? "CHECKED" : ""; ?>></td>
            </tr>
            <?php
	        }
        ?>
      </table>
    </td>
  </tr>
  
	<tr> 
		<td class="boxtitle_gray_glass_dk" colspan="2">Automatically add the following workflow to any tickets entering this mailbox:</td>
	</tr>
	<tr bgcolor="#DDDDDD">
		<td colspan="2">
			<table border="0" cellspacing="2" cellpadding="0">
			        <tr>
			          <td valign="top"><table border="0" cellpadding="0" cellspacing="0" class="table_orange">
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
										$sql = sprintf("SELECT t.tag_name ".
											"FROM `workstation_routing_tags` r ".
											"INNER JOIN workstation_tags t ON ( r.tag_id = t.tag_id ) ".
											"WHERE r.queue_id = %d ".
											"ORDER BY t.tag_name ASC",
												$qid
										);
										$res = $cerberus_db->query($sql);
										
										$appliedTags = array();
										if($cerberus_db->num_rows($res))
										while($row = $cerberus_db->fetch_row($res)) {
											$tag_name = stripslashes($row['tag_name']);
											$appliedTags[] = $tag_name;
										}
									?>
								<b>Enter tags separated by commas:</b><br>
						            <div class="searchdiv">
					                    <div class="searchautocomplete">
											<textarea name="queue_tags" id="tag_input" rows="5" cols="24"><?php echo implode(', ', $appliedTags); ?></textarea>
					                        <div id="searchcontainer" class="searchcontainer"></div>
					                    </div>
						            </div>
									<script>
										YAHOO.util.Event.addListener(document.body, "load", autoTags('tag_input','searchcontainer'));
									</script>
								</td>
			            </tr>
			          </table></td>
			          <td valign="top"><table border="0" cellpadding="0" cellspacing="0" class="table_blue">
			            <tr>
			              <td><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#AD5BFF">
			                  <tr>
			                    <td style="padding:2px;" nowrap="nowrap"><span class="text_title_white"><img alt="Headset" src="includes/images/icone/16x16/hand_paper.gif" alt="find" width="16" height="16" /> Suggest Agents </span></td>
			                  </tr>
			              </table></td>
			            </tr>
			            <tr>
			              <td bgcolor="#F3E8FF" style="padding:2px;" class="workflow_item">
									<?php
									if(is_array($agentList))
									foreach($agentList as $agentId => $agent) { /* @var $agent CerAgent */ 
										$sel = ($routing->queueHasAgent($qid,$agentId)) ? 1 : 0;
									?>
									<label><input type="checkbox" name="queue_suggested_agents[]" value="<?php echo $agent->getId(); ?>" <?php if($sel) echo "CHECKED";?>> <?php echo $agent->getRealName(); ?></label><br>
									<?php } ?>
			              </td>
			            </tr>
			          </table></td>
			          <td valign="top"><table border="0" cellpadding="0" cellspacing="0" class="table_blue">
			            <tr>
			              <td><table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#AD5BFF">
			                  <tr>
			                    <td style="padding:2px;" nowrap="nowrap"><span class="text_title_white"><img alt="Headset" src="includes/images/icone/16x16/flag_red.gif" alt="find" width="16" height="16" /> Assign Agents </span></td>
			                  </tr>
			              </table></td>
			            </tr>
			            <tr>
			              <td bgcolor="#F3E8FF" style="padding:2px;" class="workflow_item">
									<?php
									if(is_array($agentList))
									foreach($agentList as $agentId => $agent) { /* @var $agent CerAgent */ 
										$sel = ($routing->queueHasAgent($qid,$agentId,1)) ? 1 : 0;
									?>
									<label><input type="checkbox" name="queue_flagged_agents[]" value="<?php echo $agent->getId(); ?>" <?php if($sel) echo "CHECKED";?>> <?php echo $agent->getRealName(); ?></label><br>
									<?php } ?>
			              </td>
			            </tr>
			          </table></td>
			        </tr>
			</table>
		<br>
	</td>
	</tr>
  
  <?php
  if($session->vars["login_handler"]->is_xsp_user)
  {
  ?>
  <tr>
  	<td bgcolor="#E93700" class="cer_maintable_header">
    	XSP Settings (xsp user only)
    </td>
  </tr>
  <tr> 
  	<td bgcolor="#DDDDDD">
	   	<table cellpadding="2" cellspacing="1" border="0">
			<tr>
			    <td width="19%" class="cer_maintable_heading">Send XSP Ticket Summaries:</td>
			    <td width="81%">
			      <input type="checkbox" name="queue_core_update" value="1"<?php if(@$queue_data["queue_core_update"]) { echo " checked"; } ?>>
			      <br>
			      <span class="cer_footer_text">Checking this box allows the Cerberus XSP GUI (if enabled) to receive 
			      updates about the tickets in this mailbox.</span></td>
			</tr>
		</table>
	</td>
  <?php
  }
  else 
  	echo "<input type=\"hidden\" name=\"queue_core_update\" value=\"".((@$queue_data["queue_core_update"])?"1":"0")."\">";
  ?>
  
  
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
				<input type="submit" class="cer_button_face" value="<?php echo  LANG_BUTTON_SUBMIT ?>">
		</td>
	</tr>
</table>
</form>
<br>
