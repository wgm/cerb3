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

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CONFIG,BITGROUP_1)) {
	die("Permission denied.");
}

$license = new CerWorkstationLicense();
	
if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<?php if(isset($form_submit) && $form_submit=="ws_key") echo "<span class=\"cer_configuration_updated\">" . LANG_CONFIG_KEY_SUCCESS . "</span><br>"; ?>

<?php if($license->hasLicense()) { ?>
<?php if(isset($form_submit) && $form_submit=="ws_key_users") echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>

<form action="configuration.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="ws_key">
<input type="hidden" name="form_submit" value="ws_key_users">
<table width="98%" border="0" cellspacing="0" cellpadding="2" bordercolor="B5B5B5">
  <tr> 
    <td class="boxtitle_green_glass" class="cer_maintable_header">Workstation&trade;-enabled Users</td>
  </tr>
  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <td bgcolor="#EEEEEE" class="cer_maintable_text">
	    	<table>
					<td class="cer_maintable_header" colspan="2"></td>
				</tr>
				<tr> 
					<td class="cer_maintable_text" colspan="2">Your license allows <b><?php echo $license->getMaxDesktopUsers(); ?></b> Agents from your helpdesk to run the Desktop software.<br><br></td>
				</tr>
	    		<tr>
	    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap">Permitted Users:</td>
	    			<td class="cer_maintable_text">
	    				<?php
	    				$agents = new CerAgents();
	    				$list = $agents->getList("RealName");
	    				if(is_array($list))
	    				foreach($list as $agent) { ?>
	    					<label><input type="checkbox" name="ws_users[]" value="<?php echo $agent->getId(); ?>" <?php echo (($agent->getWsEnabled()) ? "CHECKED" : ""); ?>> <?php echo $agent->getRealName(); ?> (<?php echo $agent->getLogin(); ?>)</label><br>
	    				<?php
	    				}
	    				?>
	    				<br>
						<input type="submit" value="Save Changes" />	    				
	    			</td>
	    		</tr>
	    	</table>
    </td>
  </td>
  </tr>
</table>
</form>
<br>
<?php } else { ?>
	No Workstation&trade; license is currently installed.  Please upload a license first.
<?php } ?>
