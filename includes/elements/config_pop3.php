<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC 
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

require_once(FILESYSTEM_PATH . "cerberus-api/pop3/CerPop3Accounts.class.php");

/* [JAS]: Variable typing */
/* @var $pop3 CerPop3Account */

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_POP3_CHANGE,BITGROUP_2) && !$acl->has_priv(PRIV_CFG_POP3_DELETE,BITGROUP_2)) {
	die("Permission denied.");
}

$pop3accts = new CerPop3Accounts();
$pop3list = $pop3accts->getList("Name","DESC");

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="pop3_delete">
<input type="hidden" name="module" value="pop3">

<table width="99%" border="0" cellspacing="0" cellpadding="2" bgcolor="#FFFFFF">
  <tr> 
    <td class="boxtitle_orange_glass">POP3 Accounts</td>
	</td>
  </tr>
  <tr class="cer_maintable_text"> 
    <td align="left" bgcolor="#EEEEEE" class="cer_maintable_text"> 
  		<a href="<?php echo cer_href("configuration.php?module=pop3&pgid=0"); ?>" class="cer_maintable_subjectLink">Create POP3 Account</a><br>
  		<br>
  	</td>
  </tr>
</table>
  
<table width="99%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
	<tr bgcolor="#666666">
	 <td width="1%" class="cer_maintable_header" nowrap="nowrap">Del</td>
    <td width="64%" class="cer_maintable_header">Account</td>
    <td width="10%" class="cer_maintable_header" nowrap>Host
    <td width="10%" class="cer_maintable_header" nowrap>Login
    <td width="10%" class="cer_maintable_header" nowrap>Last Polled
    <td width="5%" class="cer_maintable_header" nowrap>Active
	</tr>
  <?php
	foreach($pop3list as $id => $pop3) {
  ?>
  <tr bgcolor="#EAEAEA">
   <td>
   	<input type="checkbox" name="pgids[]" value="<?php echo $id; ?>" />
   </td>
  	<td>
  		<a href="<?php echo cer_href("configuration.php?module=pop3&pgid=" . $id); ?>" class="cer_maintable_heading"><?php echo $pop3->getName(); ?></a> 
	</td>
	<td class="cer_maintable_text">
		<?php echo $pop3->getHost(); ?>
	</td>
	<td class="cer_maintable_text">
		<?php echo $pop3->getLogin(); ?>
	</td>
	<td class="cer_maintable_text">
		<?php 
		$pollSecs = $pop3->getLastPolled();
		if(0 < $pollSecs) {
			$cerDate = new cer_DateTime($pollSecs);
			echo $cerDate->getUserDate();
		} else {
			echo "Never";
		}
		?>
	</td>
	<td>
		<?php if(!$pop3->getDisabled()) { ?>
			<span class="cer_footer_green">Enabled</span>
		<?php } else { ?>
			<span class="cer_footer_red">Disabled</span>
		<?php } ?>
	</td>
  </tr>
  <?php } ?>
  
  <?php if($acl->has_priv(PRIV_CFG_POP3_DELETE,BITGROUP_2)) { ?>
  <tr bgcolor="#BBBBBB">
  	<td colspan="6" align="left"><input type="submit" value="Delete Checked" /></td>
  </tr>
  <?php } ?>
</table>
