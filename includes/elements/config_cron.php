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

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/cron/CerCron.class.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_SCHED_TASKS,BITGROUP_2)) {
	die("Permission denied.");
}

$cron = new CerCron();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<script>
function nukeCronTask(id) {
	var url = formatURL("configuration.php?module=cron_config&form_submit=cron_task_delete&tid=" + id);
	if(confirm("Are you sure you want to delete this scheduled task?")) {
		document.location = url;
	}
}
</script>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="cron_config">
<input type="hidden" name="form_submit" value="cron_config">
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="0" cellpadding="2" bordercolor="B5B5B5">
  <tr> 
    <td class="boxtitle_orange_glass">Scheduled Tasks</td>
  </tr>
  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <td bgcolor="#EEEEEE" class="cer_maintable_text">
    	Scheduled tasks allow your helpdesk to perform timed behavior, such as checking POP3 mailboxes or sending daily reports.<BR>
    	<br>
    	<table cellpadding="2" cellspacing="2" border="0" width="100%">
			<tr class="boxtitle_green_glass"> 
				<td class="boxtitle_green_glass" colspan="2">Settings</td>
			</tr>
    		<tr>
    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap" width="0%">Timer:</td>
    			<td class="cer_maintable_text" width="100%">
    				<label><input type="radio" name="poll_mode" value="2" <?php echo ($cron->getPollMode()==CER_CRON_MODE_EXTERNAL) ? "CHECKED" : "" ; ?>> External Timer</label>
    				<label><input type="radio" name="poll_mode" value="1" <?php echo ($cron->getPollMode()==CER_CRON_MODE_INTERNAL) ? "CHECKED" : "" ; ?>> Internal Timer</label>
    				<label><input type="radio" name="poll_mode" value="0" <?php echo ($cron->getPollMode()==CER_CRON_MODE_MANUAL) ? "CHECKED" : "" ; ?>> Manual</label>
    				<br>
    				<span class="cer_footer_text">
    				<b>External: </b> An external cronjob/task will run scheduled tasks. (recommended)<br>
    				<b>Internal: </b> Active helpdesk sessions will prompt scheduled tasks. (default)<br>
    				<b>Manual: </b> Clicking a button in the GUI will run scheduled tasks.<br>
    				</span>
    			</td>
    		</tr>
    		<tr>
    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap">Valid IP Masks:</td>
    			<td class="cer_maintable_text">
    				<textarea name="cron_valid_ips" rows="5" cols="24"><?php
    				$ips = $cron->getValidIps();
    				if(is_array($ips))
    				foreach($ips as $ip) {
    					echo $ip . "\n";
    				}
    				?></textarea><br>
    				<span class="cer_footer_text">Valid IP masks that can run scheduled tasks, one per line.  The IPs of logged in users are automatically added to the authorized list.  You can enter full or partial IPs, such as: 12.34.56.78 or 12.34.56 (the latter permits 12.34.56.*).  When possible it is recommended you enter full IP addresses for the best security, such as your server or office router IP.<br>
    				<b>Your current IP is: <?php echo $_SERVER['REMOTE_ADDR']; ?></b>
    				</span>
    			</td>
    		</tr>
    		
			<tr> 
				<td class="boxtitle_blue_glass" colspan="2">Tasks</td>
			</tr>
			<tr>
				<td colspan="2">
					<a href="<?php echo cer_href("configuration.php?module=cron_tasks&ptid=0"); ?>" class="cer_maintable_subjectLink">Add a New Scheduled Task</a>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<table cellpadding="1" cellspacing="1" border="0" width="100%">
						<tr>
							<td class="boxtitle_gray_glass" width="0%" nowrap="nowrap" align="center">Enabled</td>
							<td class="boxtitle_gray_glass" width="0%" nowrap="nowrap" align="center">Min</td>
							<td class="boxtitle_gray_glass" width="0%" nowrap="nowrap" align="center">Hour</td>
							<td class="boxtitle_gray_glass" width="0%" nowrap="nowrap" align="center">Day</td>
							<td class="boxtitle_gray_glass" width="0%" nowrap="nowrap" align="center">DoW</td>
							<td class="boxtitle_gray_glass" width="100%">Title</td>
							<td class="boxtitle_gray_glass" width="0%" nowrap="nowrap">Script</td>
							<td class="boxtitle_gray_glass" width="0%" nowrap="nowrap">Actions</td>
						</tr>
						
						<?php
						if(is_array($cron->tasks))
						foreach($cron->tasks as $task) { /* @var $task CerCronTask */
							if($task->getLastRuntime() == 0) {
								$last_ran_string = "Never";
							} else {
								$date = new cer_DateTime($task->getLastRuntime());
								$last_ran_string = $date->getUserDate();
							}
							if($task->getNextRuntime() == 0) {
								$next_run_string = "ASAP";
							} else {
								$date = new cer_DateTime($task->getNextRuntime());
								$next_run_string = $date->getUserDate();
							}
						?>
						<tr class="cer_maintable_text">
							<td width="0%" nowrap="nowrap" align="center" rowspan="2" valign="top"><B><?php echo ($task->getEnabled()) ? "Yes" : "No"; ?></B></td>
							<td width="0%" nowrap="nowrap" align="center"><?php echo $task->getMinute(); ?></td>
							<td width="0%" nowrap="nowrap" align="center"><?php echo $task->getHour(); ?></td>
							<td width="0%" nowrap="nowrap" align="center"><?php echo $task->getDayOfMonth(); ?></td>
							<td width="0%" nowrap="nowrap" align="center"><?php echo $task->getDayOfWeek(); ?></td>
							<td width="100%"><a href="<?php echo cer_href("configuration.php?module=cron_tasks&ptid=" . $task->getId()); ?>" class="cer_maintable_subjectLink"><?php echo $task->getTitle(); ?></a></td>
							<td width="0%" nowrap="nowrap"><?php echo $task->getScript(); ?></td>
							<td width="0%" nowrap="nowrap" class="cer_footer_text" align="center">
								<a href="javascript:nukeCronTask(<?php echo $task->getId(); ?>);" class="cer_footer_text"><img alt="Cancel" src="includes/images/crystal/16x16/button_cancel.gif" border="0" height="16" width="16" alt="Delete"></a>
							</td>
						</tr>
						<tr>
							<td colspan="7">
								<table border="0" cellspacing="0" cellpadding="2" width="100%">
									<tr>
										<td width="50%" class="cer_footer_text"><b>Last Ran:</b> <?php echo $last_ran_string; ?></td>
										<td width="50%" class="cer_footer_text"><b>Next Run:</b> <?php echo $next_run_string; ?></td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="8">
								<table width="100%" cellspacing="0" cellpadding="0"><tr bgcolor="#333333"><td><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td></tr></table>
							</td>
						</tr>
						<?php } ?>
						
					</table>
				</td>
			</tr>
    		
    	</table>
    	
    </td>
  </td>
  </tr>
</table>
<br>
<input type="submit" value="Save Changes">

</form>
