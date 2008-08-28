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
| File: config_queues.php
|
| Purpose: The configuration include for configuring and deleting queue 
| 		e-mail addresses and properties.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");

//require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationRouting.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_QUEUES_CHANGE,BITGROUP_2) && !$acl->has_priv(PRIV_CFG_QUEUES_DELETE,BITGROUP_2)) {
	die("Permission denied.");
}

$sql = "SELECT q.queue_id, q.queue_name, count(qa.queue_addresses_id) as num_addresses, q.queue_reply_to, q.queue_mode, q.queue_default_schedule, q.queue_default_response_time ".
	"FROM queue q ".
  	"LEFT JOIN queue_addresses qa ON (q.queue_id = qa.queue_id) ".
  	"GROUP BY q.queue_id ".
	"ORDER BY q.queue_name ASC";
$result = $cerberus_db->query($sql);

$wstags = new CerWorkstationTags();
//$wsteams = new CerWorkstationTeams();
$agents = CerAgents::getInstance();
$routing = new CerWorkstationRouting();

//$teams = $wsteams->getTeams();
$agentList = $agents->getList("RealName");

$license = new CerWorkstationLicense();
$qh = new cer_QueueHandler();
$queueList = $qh->getQueues();

$sched_handler = new cer_ScheduleHandler();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="qid" value="<?php echo  @$queue_data["queue_id"]; ?>">
<input type="hidden" name="module" value="queues">
<input type="hidden" name="form_submit" value="queues_delete_confirm">
<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr> 
    <td class="boxtitle_orange_glass"><span class="text_title_white">Mailbox Configuration</span></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text">
    <?php if(!$license->hasLicense() && count($queueList)>=1) { ?>
    	No license uploaded.  Limited to one mailbox.
    <?php } else { ?>
			<?php if($acl->has_priv(PRIV_CFG_QUEUES_CHANGE,BITGROUP_2)) { ?><a href="<?php echo cer_href("configuration.php?module=queues&pqid=0"); ?>" class="cer_maintable_subjectLink">Create a New Mailbox</a><br><br><?php } ?>
	<?php } ?>
			<table border="0" cellpadding="3" cellspacing="1" width="100%" bgcolor="#FFFFFF">
			
				<tr bgcolor="#666666">
					<td align="center" valign="middle" class="cer_maintable_header">Delete</td>
					<td class="cer_maintable_header">Mailbox</td>
				</tr>
			
			<?php
			$i = 0;
			while($row = $cerberus_db->fetch_row($result))
				{
					$qid = intval($row['queue_id']);
					
					$sql = sprintf("SELECT qa.queue_addresses_id, qa.queue_address, qa.queue_domain ".
						"FROM queue_addresses qa ".
						"WHERE qa.queue_id = %d " .
						"ORDER BY qa.queue_address, qa.queue_domain;",
							$qid
					);
					$queue_boxes_res = $cerberus_db->query($sql);
				?>
				
				<tr bgcolor="#DDDDDD">
					<td valign="top" align="center"><?php if($acl->has_priv(PRIV_CFG_QUEUES_DELETE,BITGROUP_2)) { echo "<input type=\"checkbox\" name=\"qids[" . $row["queue_id"] . "]\" value=\"" . $row["queue_id"] . "\"> "; } ?></td>
					<td>
						
						<table border="0" cellspacing="1" cellpadding="3" bgcolor="#000000" width="100%">
							<tr>
								<td class="boxtitle_green_glass" colspan="3"><a href="<?php echo cer_href("configuration.php?module=queues&pqid=" . $row["queue_id"]); ?>" class="text_title_white"><?php echo @htmlspecialchars(stripslashes($row["queue_name"])); ?></a></td>
							</tr>
							<tr bgcolor="#CCCCCC">
								<td width="10%" class="cer_maintable_headingSM">Mode</td>
								<td width="60%" class="cer_maintable_headingSM">Schedule (default)</td>
								<td width="30%" align="center" class="cer_maintable_headingSM">Response Target (default)</td>
							</tr>
							<tr bgcolor="#EEEEEE">
								<td class="cer_maintable_text"><?php echo (($row["queue_mode"]) ? "Gated" : "Open"); ?></td>
								<td class="cer_maintable_text"><?php echo (($row["queue_default_schedule"]) ? $sched_handler->schedules[$row["queue_default_schedule"]]->schedule_name : "not set" ); ?></td>
								<td align="center" class="cer_maintable_text"><?php echo (($row["queue_default_response_time"]) ? $row["queue_default_response_time"] . " hrs" : "not set"); ?></td>
							</tr>
							<tr></tr>
							<tr bgcolor="#FFFFFF">
								<td colspan="3" class="cer_footer_text"><b>Reply As:</b> <?php echo stripslashes($row['queue_reply_to']); ?></td>
							</tr>
							<tr bgcolor="#FFFFFF">
								<td colspan="3" class="cer_footer_text"><b>Incoming Address<?php echo (($row["num_addresses"] == 1) ? "" : "es") . " (" . $row["num_addresses"] . ")"; ?>:</b> 
								
									<?php
										  echo "<span class=\"cer_footer_text\">";
								          if($cerberus_db->num_rows($queue_boxes_res) > 0)
								          {
								            $x = 1;
								            while($queue_box = $cerberus_db->fetch_row($queue_boxes_res)) {
								            	echo $queue_box["queue_address"] . "@" . $queue_box["queue_domain"];
								              if($x != $cerberus_db->num_rows($queue_boxes_res)) echo ", ";
								              $x++;
								            }
								            echo "</span>";
								          }
									?>
								</td>
							</tr>
							<tr bgcolor="#EEEEEE">
								<td colspan="3" class="cer_footer_text">
									
									<table border="0" cellpadding="0" cellspacing="0" width="100%">
										<tr>
											<td><b>Mailbox Workflow:</b></td>
										</tr>
									   <tr>
									     <td bgcolor="#F0F0FF"><span class="box_text">
												<table border="0" cellspacing="1" cellpadding="0" bgcolor="#ffffff" width="100%">
													<?php if(empty($routing->queues[$qid]->tags) && empty($routing->queues[$qid]->agents)) { ?>
														<tr>
															<td bgcolor="#F0F0FF">No workflow applied to incoming tickets.</td>
														</tr>
													<?php } ?>
													<?php
													if(is_array($routing->queues[$qid]->tags))
													 foreach($routing->queues[$qid]->tags as $tagId => $t) { ?>
											        <tr>
											          <td><table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#F0F0FF">
											            <tr title="">
											              <td width="0%" align="center" nowrap="nowrap" bgcolor="#FF8000"><img alt="Folder" src="includes/images/icone/16x16/folder_network.gif" alt="_" width="16" height="16" /></td>
											              <td width="100%" align="left" nowrap="nowrap" class="workflow_item"><?php echo $wstags->tags[$tagId]->name; ?></td>
											          </tr>
											          </table></td>
											        </tr>
											      <?php } ?>
													<?php 
													if(is_array($routing->queues[$qid]->suggested_agents))
													foreach($routing->queues[$qid]->suggested_agents as $agentId => $t) {
														if(!isset($agentList[$agentId])) continue;	
													?>
											        <tr>
											          <td><table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#F0F0FF">
											            <tr>
											              <td width="0%" align="center" nowrap="nowrap" bgcolor="AD5BFF"><img alt="Suggested" src="includes/images/icone/16x16/hand_paper.gif" alt="_" width="16" height="16" /></td>
											              <td width="100%" align="left" nowrap="nowrap" class="workflow_item"><?php echo $agentList[$agentId]->getRealName(); ?></td>
											          </tr>
											          </table></td>
											        </tr>
													<?php } ?>
													<?php 
													if(is_array($routing->queues[$qid]->flagged_agents))
													foreach($routing->queues[$qid]->flagged_agents as $agentId => $t) {
														if(!isset($agentList[$agentId])) continue;	
													?>
											        <tr>
											          <td><table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#F0F0FF">
											            <tr>
											              <td width="0%" align="center" nowrap="nowrap" bgcolor="AD5BFF"><img alt="Flagged" src="includes/images/icone/16x16/flag_red.gif" alt="_" width="16" height="16" /></td>
											              <td width="100%" align="left" nowrap="nowrap" class="workflow_item"><?php echo $agentList[$agentId]->getRealName(); ?></td>
											          </tr>
											          </table></td>
											        </tr>
													<?php } ?>
											   </table>
											</td>
										</tr>
									</table>
									<br>								
								
								</td>
							</tr>
						</table>
						
					</td>
				</tr>
					
				<?php
					$i++;	
				}
				?>
				
			</table>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="left">
				<?php if($acl->has_priv(PRIV_CFG_QUEUES_DELETE,BITGROUP_2)) {?><input type="submit" class="cer_button_face" value="<?php echo  LANG_WORD_DELETE ?>"><?php } ?>&nbsp;
		</td>
	</tr>
</table>
<br>
