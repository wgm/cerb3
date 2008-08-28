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
|		Daniel Hildebrandt  (hildy@webgroupmedia.com)  [DDH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/status/CerStatuses.class.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_HD_SETTINGS,BITGROUP_2)) {
	die();
}

if(!empty($_REQUEST["ticket_status_id"])) {
	@$ticket_status_id = $_REQUEST["ticket_status_id"];
}

$cerStatuses = CerStatuses::getInstance(); /* @var $cerStatuses CerStatuses */
$statuses = $cerStatuses->getList();

?>

<script>
	function nukeStatus(id) {
		if(confirm("Are you sure you want to delete this custom status?")) {
			document.location = formatURL("configuration.php?module=statuses&form_submit=custom_status_delete&ticket_status_id="+id);
		}
	}
</script>

<table width="100%" cellpadding="0" cellspacing="1">
	<tr>
		<td width="1%" nowrap="nowrap" valign="top">
			<table border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
			  <tr> 
			  	<td class="boxtitle_green_glass" nowrap="nowrap" colspan="2" style="padding-left:5px;">Custom Statuses</td>
			  </tr>
			  		<?php
			  		if(is_array($statuses)) {
			  		foreach($statuses as $ticket_status_id => $status) { /* @var $status cerStatus */
			  		?>
					  <tr bgcolor="#EEEEEE">
					  	<td nowrap="nowrap" class="cer_footer_text" style="padding-left:2px;padding-top:2px;"">
							<a href="<?php echo cer_href("configuration.php?module=statuses&ticket_status_id=" . intval($ticket_status_id)); ?>" class="cer_footer_text"><?php echo $status->getText(); ?></a><BR>
						</td>
						<td align="center"><a href="javascript:nukeStatus(<?php echo $ticket_status_id; ?>);"><img src="includes/images/crystal/16x16/button_cancel.gif" border="0" height="16" width="16" alt="Delete"></a></td>
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
					<tr>
						<form action="configuration.php" method="post">
						<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
						<input type="hidden" name="module" value="statuses">
						<input type="hidden" name="form_submit" value="custom_status_edit">
						<td nowrap="nowrap" class="cer_footer_text" colspan="2">
							<b>Add:</b> 
							<input type="text" name="add_custom_status_text" size="32" value="">
							<input type="submit" value="+">
						</td>
						</form>
					</tr>
			</table>
		</td>
		<td width="0%" nowrap="nowrap"><img alt="" src="includes/images/spacer.gif" width="5" height="1"></td>
		<td width="99%" valign="top">
			<?php
			include(FILESYSTEM_PATH . "includes/elements/config_custom_statuses_edit.php");
			?>
		</td>
	</tr>
</table>
