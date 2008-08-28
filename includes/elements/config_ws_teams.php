<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC 
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

require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationLicense.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationSettings.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");

/*
 * Important Licensing Note from the Cerberus Helpdesk Team:
 * 
 * Yes, it would be really easy for you to to just cheat and edit this file to 
 * use the software without paying for it.  We're trusting the community to be
 * honest and understand that quality software backed by a dedicated team takes
 * money to develop.  We aren't volunteers over here, and we aren't working 
 * from our bedrooms -- we do this for a living.  This pays our rent, health
 * insurance, and keeps the lights on at the office.  If you're using the 
 * software in a commercial or government environment, please be honest and
 * buy a license.  We aren't asking for much. ;)
 * 
 * Encoding/obfuscating our source code simply to get paid is something we've
 * never believed in -- any copy protection mechanism will inevitably be worked
 * around.  Cerberus development thrives on community involvement, and the 
 * ability of users to adapt the software to their needs.
 * 
 * A legitimate license entitles you to support, access to the developer 
 * mailing list, the ability to participate in betas, the ability to
 * purchase add-on tools (e.g., Workstation, Standalone Parser) and the 
 * warm-fuzzy feeling of doing the right thing.
 *
 * Thanks!
 * -the Cerberus Helpdesk dev team (Jeff, Mike, Jerry, Darren, Brenan)
 * and Cerberus Core team (Luke, Alasdair, Vision, Philipp, Jeremy, Ben)
 *
 * http://www.cerberusweb.com/
 * support@cerberusweb.com
 */

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_TEAMS_CHANGE,BITGROUP_2) && !$acl->has_priv(PRIV_CFG_TEAMS_DELETE,BITGROUP_2)) {
	die();
}

if(!empty($_REQUEST["tid"])) {
	@$tid = $_REQUEST["tid"];
}

$license = new CerWorkstationLicense();
$settings = new CerWorkstationSettings();
$teams = new CerWorkstationTeams();
	
$team_list = $teams->getTeams();
?>

<script>
	function nukeTeam(id) {
		if(confirm("Are you sure you want to delete this team?")) {
			document.location = formatURL("configuration.php?module=ws_teams&form_submit=ws_teams_delete&tid="+id);
		}
	}
</script>

<table width="100%" cellpadding="0" cellspacing="1">
	<tr>
		<td width="1%" nowrap="nowrap" valign="top">
			<table border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
			  <tr> 
			  	<td class="boxtitle_green_glass" nowrap="nowrap" colspan="2" style="padding-left:5px;">Teams</td>
			  </tr>
			  		<?php
			  		if(is_array($team_list)) {
			  		foreach($team_list as $team_id => $team) { /* @var $team stdClass */
			  		?>
					  <tr bgcolor="#EEEEEE">
					  	<td nowrap="nowrap" class="cer_footer_text" style="padding-left:2px;padding-top:2px;"">
							<img alt="Team" src="includes/images/icone/16x16/businessmen.gif" width="16" height="16" align="middle"> 
							<a href="<?php echo cer_href("configuration.php?module=ws_teams&tid=" . intval($team_id)); ?>" class="cer_footer_text"><?php echo $team->name; ?></a><BR>
						</td>
						<td align="center"><?php if($acl->has_priv(PRIV_CFG_TEAMS_DELETE,BITGROUP_2)) { ?><a href="javascript:nukeTeam(<?php echo $team_id; ?>);"><img alt="Cancel" src="includes/images/crystal/16x16/button_cancel.gif" border="0" height="16" width="16" alt="Delete"></a><?php } ?></td>
						</tr>
						<tr>
							<td bgcolor="#FFFFFF"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td>
						</tr>
					<?php }} else { ?>
					  <tr bgcolor="#EEEEEE">
						  	<td nowrap="nowrap" class="cer_footer_text" colspan="2">
								No teams defined.
							</td>
						</tr>
					<?php } ?>
					<?php if($acl->has_priv(PRIV_CFG_TEAMS_CHANGE,BITGROUP_2)) { ?>
					<tr>
						<form action="configuration.php" method="post">
						<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
						<input type="hidden" name="module" value="ws_teams">
						<input type="hidden" name="form_submit" value="ws_teams_edit">
						<td nowrap="nowrap" class="cer_footer_text" colspan="2">
							<b>Add:</b> 
							<input type="text" name="ws_add_team_name" size="15" value=""><!--
							--><input type="submit" value="+">
						</td>
						</form>
					</tr>
					<?php } ?>
			</table>
		</td>
		<td width="0%" nowrap="nowrap"><img alt="" src="includes/images/spacer.gif" width="5" height="1"></td>
		<td width="99%" valign="top">
			<?php
			if($acl->has_priv(PRIV_CFG_TEAMS_CHANGE,BITGROUP_2))
			include(FILESYSTEM_PATH . "includes/elements/config_ws_teams_edit.php");
			?>
		</td>
	</tr>
</table>
