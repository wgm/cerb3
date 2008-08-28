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
| File: config_users.php
|
| Purpose: The configuration include for configuring and deleting users.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationLicense.class.php");

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

// [JAS]: Verify that the connecting user has access to modify configuration/
//		queue values
$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_AGENTS_CHANGE,BITGROUP_2) && !$acl->has_priv(PRIV_CFG_AGENTS_DELETE,BITGROUP_2)) {
	die();
}

$license = new CerWorkstationLicense();

/* @var $agent CerAgent */
$agents = new CerAgents();
$agentList = $agents->getList("RealName");

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php?module=users" method="post" onsubmit="return confirm('<?php echo  LANG_CONFIG_USER_CONFIRM ?>');">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="users_delete">

<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#FFFFFF">
  <tr> 
    <td class="boxtitle_orange_glass"><?php echo  LANG_CONFIG_USER_TITLE ?></td>
  </tr>
  
	<?php if($acl->has_priv(PRIV_CFG_AGENTS_CHANGE,BITGROUP_2)) { ?>
	<tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
		<td>
			<?php if(!$license->hasLicense() && count($agentList) >= 3) { echo "No license uploaded.  Limited to 3 agents."; 
			} elseif ($license->hasLicense() && $license->getMaxWebUsers() && count($agentList) >= $license->getMaxWebUsers()) { ?>
			Your license is limited to <?php echo $license->getMaxWebUsers(); ?> web users.
			<?php } else { ?>
			<a href="<?php echo cer_href("configuration.php?module=users&puid=0"); ?>" class="cer_maintable_subjectLink"><?php echo  LANG_CONFIG_USER_CREATE ?></a><br>
			<?php } ?>
		</td>
	</tr>
	<?php } ?>

	<?php
	if(is_array($agentList))
	foreach($agentList as $agent)
	{
		// [JAS]: If we're not a superuser, don't list superusers.
		if($agent->getSuperuser() && $session->vars["login_handler"]->user_superuser == 0)
			continue;
			
		echo '<tr bgcolor="#DDDDDD" class="cer_maintable_text">';
 		echo '<td align="left" bgcolor="#DDDDDD" class="cer_maintable_text">';
				
 		if($acl->has_priv(PRIV_CFG_AGENTS_DELETE,BITGROUP_2)) { 
				echo "<input type=\"checkbox\" name=\"uids[]\" value=\"" . $agent->getId() . "\">&nbsp;";
			}
		
			echo "<a href=\"" . cer_href("configuration.php?module=users&puid=" . $agent->getId()) . "\" class=\"cer_maintable_subjectLink\">" . cer_dbc($agent->getRealName()) . "</a><br>";
    	echo "</td>";
  		echo "</tr>";
	}
	?>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="left">
			<?php if($acl->has_priv(PRIV_CFG_AGENTS_DELETE,BITGROUP_2)) { ?><input type="submit" class="cer_button_face" value="<?php echo  LANG_WORD_DELETE ?>"><?php } ?>&nbsp;
		</td>
	</tr>
</table>

</form>
<br>
