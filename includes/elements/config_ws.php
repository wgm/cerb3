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

require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationLicense.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationSettings.class.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CONFIG,BITGROUP_1)) {
	die("Permission denied.");
}

$license = new CerWorkstationLicense();
$settings = new CerWorkstationSettings();
	
if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="ws_config">
<input type="hidden" name="form_submit" value="ws_config">
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="0" cellpadding="2" bordercolor="B5B5B5">
  <tr class="boxtitle_orange_glass"> 
    <td class="cer_maintable_header">Cerberus Helpdesk Workstation&trade; - Settings</td>
  </tr>
  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <td bgcolor="#EEEEEE" class="cer_maintable_text">
    	<b>Workstation&trade;</b> provides your team with a blazing-fast desktop interface to your Cerberus Helpdesk installation,
    	supporting enhanced workflow and team-based functionality.
    	<br>
    	
    	<?php if($license->hasLicense()) { ?>
	    	<br>
	    	<table cellpadding="2" cellspacing="2" border="0">
				<tr class="boxtitle_green_glass"> 
					<td class="cer_maintable_header" colspan="2">Gateway Security</td>
				</tr>
	    		<tr>
	    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap">Disable IP Security:</td>
	    			<td class="cer_maintable_text">
	    				<input type="checkbox" name="ws_ip_disable" value="1" <?php echo $settings->hasIpSecurityDisabled() ? "CHECKED" : ""; ?>><br>
	    				<span class="cer_footer_text">This disables IP validation of users connecting through the Workstation&trade; desktop interface.  Keeping this at the default of enabled is <b>*highly*</b> recommended.  Disable in the rare instance where a proxy or dynamic IP addressing prohibits IP matching from functioning.</span>
	    			</td>
	    		</tr>
	    		<tr>
	    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap">Valid IP Masks:</td>
	    			<td class="cer_maintable_text">
	    				<textarea name="ws_valid_ips" rows="8" cols="24"><?php
	    				$ips = $settings->getValidIps();
	    				if(is_array($ips))
	    				foreach($ips as $ip) {
	    					echo $ip . "\n";
	    				}
	    				?></textarea><br>
	    				<span class="cer_footer_text">Valid IP masks, one per line.  You can enter full or partial IPs, such as: 12.34.56.78 or 12.34.56 (the latter permits 12.34.56.*).  When possible it is recommended you enter full IP addresses for the best security, such as your office router IP.<br>
	    				<b>Your current IP is: <?php echo $_SERVER['REMOTE_ADDR']; ?></b>
	    				</span>
	    			</td>
	    		</tr>
	    		
	    	</table>
	   <?php } else { ?>
	   	<br>
	   	No Workstation&trade; license is currently installed.  Please upload a license first.
    	<?php } ?>
    	
    </td>
  </td>
  </tr>
</table>
<br>
<?php if($license->hasLicense()) { ?>
	<input type="submit" value="Save Changes">
<?php } ?>

</form>
