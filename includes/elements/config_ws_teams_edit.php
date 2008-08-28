<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2006, WebGroup Media LLC 
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_TEAMS_CHANGE,BITGROUP_2) && !$acl->has_priv(PRIV_CFG_TEAMS_DELETE,BITGROUP_2)) {
	die();
}

if(empty($tid)) {
	echo "<span class='cer_maintable_text'>Choose a Team.</span>";
	return;
}

$sched_handler = new cer_ScheduleHandler();

$team = new stdClass();

// [JAS]: If the plan exists, use a pointer.
if(isset($team_list[$tid])) {
	$team = &$team_list[$tid];
}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<script language="javascript" type="text/javascript">
function privsCheck(state) {
	var p = document.getElementById('teamPrivs');
	if(null == p) return;
	
	var boxes = p.getElementsByTagName('input');
	if(null == boxes) return;
	
	for(x=0;x < boxes.length; x++) {
		if(boxes[x].type=="checkbox") {
			boxes[x].checked = state;
		}
	}
}
function mailboxesCheck(state) {
	var p = document.getElementById('teamMailboxes');
	if(null == p) return;
	
	var boxes = p.getElementsByTagName('input');
	if(null == boxes) return;
	
	for(x=0;x < boxes.length; x++) {
		if(boxes[x].type=="checkbox") {
			boxes[x].checked = state;
		}
	}
}
</script>

<form action="configuration.php" method="post" name="team_edit">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="tid" value="<?php echo $tid; ?>">
<input type="hidden" name="module" value="ws_teams">
<input type="hidden" name="form_submit" value="ws_teams_edit">
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="0" cellpadding="2" bordercolor="B5B5B5">
  <tr> 
    <td class="boxtitle_orange_glass">Team: <?php echo htmlentities($team->name); ?></td>
  </tr>
  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <td bgcolor="#EEEEEE" class="cer_maintable_text">
    
	    	<table cellpadding="2" cellspacing="2" border="0" width="100%">
	    	
				<tr>
	    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap" width="0%">Team Name:</td>
	    			<td class="cer_maintable_text" width="100%"><input type="input" name="ws_team_name" size="45" value="<?php echo htmlspecialchars($team->name); ?>"/></td>
				</tr>
				<tr>
	    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap" width="0%">Members:</td>
	    			<td class="cer_maintable_text" width="100%">
	    					<?php
							include_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
							$agents = CerAgents::getInstance();
							$user_list = $agents->getList("RealName");

	    					foreach($user_list as $user_id => $user) { /* @var $user CerAgent */
	    						$enabled = (isset($team->agents[$user_id])) ? 1 : 0;
	    					?>
	    					<label><input type="checkbox" name="ws_team_members[]" value="<?php echo $user_id; ?>" <?php if($enabled) echo "checked"; ?>><?php echo $user->getRealName(); ?></label><br>
	    					<?php } ?>
	    			</td>
				</tr>
				<tr>
	    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap" width="0%">Team Mailboxes:<br>
	    			<a href="javascript:;" onclick="mailboxesCheck(true);" class="link_navmenu">check all</a><br>
	    			<a href="javascript:;" onclick="mailboxesCheck(false);" class="link_navmenu">check none</a>
	    			</td>
	    			<td class="cer_maintable_text" width="100%">
						<table cellpadding="0" cellspacing="1" id="teamMailboxes">
							<tr class="boxtitle_gray_glass">
								<td class="cer_maintable_heading">Mailbox</td>
								<td class="cer_maintable_heading">Quick Assign</td>
							</tr>
	    					<?php
							include_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");
							$qh = cer_QueueHandler::getInstance();
							$queue_list = $qh->getQueues();

	    					foreach($queue_list as $queue_id => $queue) { /* @var $queue cer_Queue */
	    						$mailbox_enabled = (isset($team->queues[$queue_id])) ? 1 : 0;
	    						$assign_enabled = (isset($team->quick_assign[$queue_id])) ? 1 : 0;
	    					?>
	    					<tr>
	    						<td align="left"><label><input type="checkbox" name="ws_team_queues[]" value="<?php echo $queue_id; ?>" <?php if($mailbox_enabled) echo "checked"; ?>><?php echo $queue->queue_name; ?></label></td>
	    						<td align="center"><input type="checkbox" name="ws_team_quickassign[]" value="<?php echo $queue_id; ?>" <?php if($assign_enabled) echo "checked"; ?>></td>
	    					</tr>
	    					<?php } ?>
	    				</table><br>
	    				<span class="cer_footer_text">(Mailboxes marked 'Quick Assign' will distribute available tickets to this team's members)</span>
	    			</td>
				</tr>
				<tr>
	    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap" width="0%">
	    			Permissions:<br>
	    			<a href="javascript:;" onclick="privsCheck(true);" class="link_navmenu">check all</a><br>
	    			<a href="javascript:;" onclick="privsCheck(false);" class="link_navmenu">check none</a>
	    			</td>
	    			<td class="cer_maintable_text" width="100%" id="teamPrivs">
	    					<i>Helpdesk - Tickets</i></br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_TICKET_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_TICKET_CHANGE,$team->acl1)) echo "checked"; ?>>Can Modify Tickets</label><br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_TICKET_DELETE; ?>" <?php if(cer_bitflag_is_set(PRIV_TICKET_DELETE,$team->acl1)) echo "checked"; ?>>Can Delete Tickets</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_REMOVE_ANY_FLAGS; ?>" <?php if(cer_bitflag_is_set(PRIV_REMOVE_ANY_FLAGS,$team->acl2)) echo "checked"; ?>>Can Modify Other Agent's Ticket Flags</label><br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_BLOCK_SENDER; ?>" <?php if(cer_bitflag_is_set(PRIV_BLOCK_SENDER,$team->acl1)) echo "checked"; ?>>Can Ban Senders</label><br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_VIEW_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_VIEW_CHANGE,$team->acl1)) echo "checked"; ?>>Can Modify Ticket Views</label><br>
	    					<br>
	    					<i>Helpdesk - Address Book</i></br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_COMPANY_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_COMPANY_CHANGE,$team->acl1)) echo "checked"; ?>>Can Modify Company Records</label><br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_CONTACT_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_CONTACT_CHANGE,$team->acl1)) echo "checked"; ?>>Can Modify Contact Records</label><br>
	    					<br>
	    					<i>Helpdesk - Knowledgebase</i></br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_KB; ?>" <?php if(cer_bitflag_is_set(PRIV_KB,$team->acl1)) echo "checked"; ?>>Can View Knowledgebase</label><br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_KB_EDIT; ?>" <?php if(cer_bitflag_is_set(PRIV_KB_EDIT,$team->acl1)) echo "checked"; ?>>Can Modify Knowledgebase Articles</label><br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_KB_DELETE; ?>" <?php if(cer_bitflag_is_set(PRIV_KB_DELETE,$team->acl1)) echo "checked"; ?>>Can Delete Knowledgebase Articles</label><br>
	    					<br>
	    					<i>Helpdesk - Reports</i></br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_REPORTS; ?>" <?php if(cer_bitflag_is_set(PRIV_REPORTS,$team->acl1)) echo "checked"; ?>>Can Run Reports</label><br>
	    					<label><input type="checkbox" name="ws_team_acl3[]" value="<?php echo PRIV_REPORTS_INSTALL; ?>" <?php if(cer_bitflag_is_set(PRIV_REPORTS_INSTALL,$team->acl3)) echo "checked"; ?>>Can Install Reports</label><br>
	    					<br>
	    					<i>Configuration - General</i></br>
	    					<label><input type="checkbox" name="ws_team_acl1[]" value="<?php echo PRIV_CONFIG; ?>" <?php if(cer_bitflag_is_set(PRIV_CONFIG,$team->acl1)) echo "checked"; ?>>Can Enter Configuration Menu</label><br>
	    					<br>
	    					<i>Configuration - Helpdesk</i></br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_HD_SETTINGS; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_HD_SETTINGS,$team->acl2)) echo "checked"; ?>>Can Modify Global Settings</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_AGENTS_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_AGENTS_CHANGE,$team->acl2)) echo "checked"; ?>>Can Modify Agents</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_AGENTS_DELETE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_AGENTS_DELETE,$team->acl2)) echo "checked"; ?>>Can Delete Agents</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_TEAMS_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_TEAMS_CHANGE,$team->acl2)) echo "checked"; ?>>Can Modify Teams</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_TEAMS_DELETE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_TEAMS_DELETE,$team->acl2)) echo "checked"; ?>>Can Delete Teams</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_TAGS_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_TAGS_CHANGE,$team->acl2)) echo "checked"; ?>>Can Modify Tags</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_TAGS_DELETE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_TAGS_DELETE,$team->acl2)) echo "checked"; ?>>Can Delete Tags</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_MAINT_PURGE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_MAINT_PURGE,$team->acl2)) echo "checked"; ?>>Can Purge Deleted Tickets/Files</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_MAINT_REPAIR; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_MAINT_REPAIR,$team->acl2)) echo "checked"; ?>>Can Optimize/Repair Database</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_MAINT_ATTACH; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_MAINT_ATTACH,$team->acl2)) echo "checked"; ?>>Can Purge Attachments</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_DATA_IO; ?>" <?php if(cer_bitflag_is_set(PRIV_DATA_IO,$team->acl2)) echo "checked"; ?>>Can Import/Export Data</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_SCHED_TASKS; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_SCHED_TASKS,$team->acl2)) echo "checked"; ?>>Can Modify Scheduled Tasks</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_CUSTOM_FIELDS; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_CUSTOM_FIELDS,$team->acl2)) echo "checked"; ?>>Can Modify Custom Field Definitions</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_SLA_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_SLA_CHANGE,$team->acl2)) echo "checked"; ?>>Can Modify SLA Plans</label><br>
	    					<label><input type="checkbox" name="ws_team_acl3[]" value="<?php echo PRIV_CFG_SCHEDULES; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_SCHEDULES,$team->acl3)) echo "checked"; ?>>Can Modify Schedules</label><br>
	    					<label><input type="checkbox" name="ws_team_acl3[]" value="<?php echo PRIV_CFG_INDEXES; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_INDEXES,$team->acl3)) echo "checked"; ?>>Can Reindex Search Indexes</label><br>
	    					<label><input type="checkbox" name="ws_team_acl3[]" value="<?php echo PRIV_CFG_WORKSTATION; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_WORKSTATION,$team->acl3)) echo "checked"; ?>>Can Configure Workstation&trade; (Desktop UI)</label><br>
	    					<br>
	    					<i>Configuration - Parser</i></br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_POP3_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_POP3_CHANGE,$team->acl2)) echo "checked"; ?>>Can Modify POP3 Accounts</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_POP3_DELETE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_POP3_DELETE,$team->acl2)) echo "checked"; ?>>Can Delete POP3 Accounts</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_PARSER_IMPORT; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_PARSER_IMPORT,$team->acl2)) echo "checked"; ?>>Can Import Raw E-mail Messages</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_PARSER_FAILED; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_PARSER_FAILED,$team->acl2)) echo "checked"; ?>>Can Manage Failed Parser Messages</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_QUEUES_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_QUEUES_CHANGE,$team->acl2)) echo "checked"; ?>>Can Modify Queues</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_QUEUES_DELETE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_QUEUES_DELETE,$team->acl2)) echo "checked"; ?>>Can Delete Queues</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_QUEUES_CATCHALL; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_QUEUES_CATCHALL,$team->acl2)) echo "checked"; ?>>Can Manage Queue Catchall Rules</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_RULES_CHANGE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_RULES_CHANGE,$team->acl2)) echo "checked"; ?>>Can Modify Mail Rules</label><br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_RULES_DELETE; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_RULES_DELETE,$team->acl2)) echo "checked"; ?>>Can Delete Mail Rules</label><br>
	    					<br>
	    					<i>Configuration - Support Center</i></br>
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo PRIV_CFG_SC_PROFILES; ?>" <?php if(cer_bitflag_is_set(PRIV_CFG_SC_PROFILES,$team->acl2)) echo "checked"; ?>>Can Modify Support Center Profiles</label><br>
	    			</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
	    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap" width="0%">Restrictions:</td>
	    			<td class="cer_maintable_text" width="100%">
	    					<label><input type="checkbox" name="ws_team_acl2[]" value="<?php echo REST_EMAIL_ADDY; ?>" <?php if(cer_bitflag_is_set(REST_EMAIL_ADDY,$team->acl2)) echo "checked"; ?>>Can't See Customer E-mail Addresses</label><br>
	    			</td>
				</tr>

	    	</table>
    	
    </td>
  </td>
  </tr>
  
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
			<td align="right">
				<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SAVE; ?>">
			</td>
		</tr>
  
</table>
<br>
</form>
