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

require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CONFIG,BITGROUP_1)) {
	die("Permission denied.");
}

if(empty($slid)) {
	echo "<span class='cer_maintable_text'>Choose an SLA Plan.</span>";
	return;
}

$teams = new CerWorkstationTeams();
$teamList = $teams->getTeams();
$sla_plan = new stdClass();

// [JAS]: If the plan exists, use a pointer.
if(isset($ws_sla->plans[$slid])) {
	$sla_plan = &$ws_sla->plans[$slid];
}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pslid" value="<?php echo $pslid; ?>">
<input type="hidden" name="module" value="ws_sla">
<input type="hidden" name="form_submit" value="ws_sla_edit">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
  <tr> 
	<td class="boxtitle_orange_glass">SLA Plan: <?php echo @htmlspecialchars($sla_plan->name); ?></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td colspan="2">
            	
            	<table border="0" cellspacing="1" cellpadding="1" width="100%" bgcolor="#FFFFFF">
            		<tr>
            			<td class="boxtitle_green_glass" colspan="4">Plan Includes:</td>
            		</tr>
            		
            		<tr>
            			<td class="boxtitle_gray_glass_dk" align="center" nowrap>Include</td>
            			<td class="boxtitle_gray_glass_dk">Team</td>
            			<td class="boxtitle_gray_glass_dk">SLA Response Schedule</td>
            			<td class="boxtitle_gray_glass_dk" align="center" nowrap>Response (hrs)*</td>
            		</tr>
            		
            		<?php
            		$row = 0;
            		
            	if(is_array($teamList))
					foreach($teamList as $team_id => $team) /* @var $team stdClass */
            		{
            			if($row % 2) $row_bgcolor = "#DFDFDF";
            			else $row_bgcolor = "#EAEAEA";
            		?>
            		
            		<tr bgcolor="<?php echo $row_bgcolor; ?>">
            			<td align="center" nowrap><input type="checkbox" name="team_ids[]" value="<?php echo $team_id; ?>" <?php if(isset($sla_plan->teams[$team_id])) echo "CHECKED"; ?>></td>
            			<td class="cer_maintable_heading"><?php echo $team->name; ?></td>
            			<td>
            				<select name="t<?php echo $team_id; ?>_schedule">
            					<option value="0"> - none - 
            					<?php foreach($sched_handler->schedules as $sched) { ?>
            						<option value="<?php echo $sched->schedule_id; ?>" <?php if(@$sla_plan->teams[$team_id]->schedule_id == $sched->schedule_id) echo "SELECTED"; ?>><?php echo $sched->schedule_name; ?>
            					<?php } ?>
            				</select>
            			</td>
            			<td align="center" nowrap><input type="text" name="t<?php echo $team_id; ?>_response_time" size="3" maxlength="3" value="<?php echo @$sla_plan->teams[$team_id]->response_time; ?>"></td>
            		</tr>

            		<?php
            			$row++;
            		}
            		?>
            		
            		<tr>
            			<td colspan="5" class="cer_maintable_header" bgcolor="#999999">* SLA Guaranteed Response Time (in business hours using Schedule).</td>
            		</tr>
            		
            		<tr>
            			<td colspan="5" class="cer_maintable_text" bgcolor="#DDDDDD">
            				<b>Note:</b> Teams not included in the plan will be handled by their default settings.
            			</td>
            		</tr>
            		
            	</table>
            	
			</td>
          </tr>
        </table>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
			<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SAVE; ?>">
		</td>
	</tr>
</table>
</form>