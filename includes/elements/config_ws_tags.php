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

require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationSettings.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_TAGS_CHANGE,BITGROUP_2) && !$acl->has_priv(PRIV_CFG_TAGS_DELETE,BITGROUP_2)) {
	die();
}

@$tid = $_REQUEST["tid"];
@$tsid = $_REQUEST["tsid"];

$settings = new CerWorkstationSettings();
$teams = new CerWorkstationTeams();
$tags = new CerWorkstationTags();
	
$team_list = $teams->getTeams();
$tag_list = $tags->getTags();
?>

<script>
	function nukeTag(id) {
		if(confirm("Are you sure you want to delete this tag?")) {
			document.location = formatURL("configuration.php?module=ws_tags&form_submit=ws_tags_delete&tid="+id);
		}
	}
	function nukeSet(id) {
		if(confirm("Are you sure you want to delete this set?")) {
			document.location = formatURL("configuration.php?module=ws_tags&form_submit=ws_tag_sets_delete&tid="+id);
		}
	}
</script>

<table width="100%" cellpadding="0" cellspacing="1">
	<tr>
		<td width="1%" nowrap="nowrap" valign="top">
			<table border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
				  <?php
				  if(!empty($tag_list)) {
//					foreach($tags->sets as $set_id => $set) {
////						list($level,$rTag) = $rTagPtr;
					?>

				  <tr> 
				  	<td class="boxtitle_green_glass" nowrap="nowrap" style="padding-left:5px;">Tags</td>
				  	<td class="boxtitle_green_glass" nowrap="nowrap" align="right">
				  	</td>
				  </tr>

					<?php
						if(is_array($tag_list))
						foreach($tag_list as $rTag) {
				  ?>
					<tr bgcolor="#EEEEEE">
						<td nowrap="nowrap" style="padding-left:2px;padding-top:2px;"">
							&nbsp;&nbsp;<a href="<?php echo cer_href("configuration.php?module=ws_tags&tid=" . intval($rTag->id)); ?>" class="box_text"><?php echo $rTag->name; ?></a>&nbsp;&nbsp;
						</td>
						<td align="right" class="box_text" style="padding-right:5px;">
							<?php if($acl->has_priv(PRIV_CFG_TAGS_DELETE,BITGROUP_2) && empty($rTag->children)) { ?>
							<a href="javascript:nukeTag(<?php echo intval($rTag->id); ?>);" class="text_navmenu"><b>X</b></a>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td bgcolor="#FFFFFF"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td>
					</tr>
				  	<?php
				  		}
				  	} else { ?>
					  <tr bgcolor="#EEEEEE">
						  	<td nowrap="nowrap" class="cer_footer_text" colspan="2">
								No tags defined.
							</td>
						</tr>
				  	<?php } ?>
					<tr>
						<td>&nbsp;</td>
					</tr>
			</table>
		</td>
		<td width="0%" nowrap="nowrap"><img alt="" src="includes/images/spacer.gif" width="5" height="1"></td>
		<td width="99%" valign="top">
			<?php
			if($acl->has_priv(PRIV_CFG_TAGS_CHANGE,BITGROUP_2)) {
				if(!empty($tsid)) {
					include(FILESYSTEM_PATH . "includes/elements/config_ws_sets_edit.php");
				} elseif(!empty($tid)) {
					include(FILESYSTEM_PATH . "includes/elements/config_ws_tags_edit.php");
				}
			}
			?>
		</td>
	</tr>
</table>
