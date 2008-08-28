<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC. 
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2007, WebGroup Media LLC 
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
|		Daniel Hildebrandt  (hildy@webgroupmedia.com)  [DDH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_HD_SETTINGS,BITGROUP_2)) {
	die();
}

$cerStatuses = CerStatuses::getInstance(); /* @var $cerStatuses CerStatuses */
$status = $cerStatuses->getById($_REQUEST["ticket_status_id"]);

if(empty($_REQUEST["ticket_status_id"]) || empty($status)) {
	echo "<span class='cer_maintable_text'>Select a Custom Status to edit it.</span>";
	return;
}

?>

<form action="configuration.php" method="post" name="custom_status_edit">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="ticket_status_id" value="<?php echo $_REQUEST["ticket_status_id"]; ?>">
<input type="hidden" name="module" value="statuses">
<input type="hidden" name="form_submit" value="custom_status_edit">
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="0" cellpadding="2" bordercolor="B5B5B5">
  <tr> 
    <td class="boxtitle_orange_glass" colspan="2">Update Custom Status Text</td>
  </tr>
  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
	<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap" width="0%">Custom Status Text:</td>
	<td class="cer_maintable_text" width="100%"><input type="input" name="ticket_status_text" size="45" value="<?php echo htmlspecialchars($status->getText()); ?>"/></td>
  </tr>
  
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
			<td colspan="2" align="right">
				<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SAVE; ?>">
			</td>
		</tr>
  
</table>
<br>
</form>
