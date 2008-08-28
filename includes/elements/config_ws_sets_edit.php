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

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_TAGS_CHANGE,BITGROUP_2)) {
	die();
}

if(empty($tsid)) {
	return;
}

// [JAS]: If the tag exists, use a pointer.
if(isset($tags->sets[$tsid])) {
	$set = &$tags->sets[$tsid];
}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="tsid" value="<?php echo $tsid; ?>">
<input type="hidden" name="module" value="ws_tags">
<input type="hidden" name="form_submit" value="ws_tag_sets_edit">
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="0" cellpadding="2" bordercolor="B5B5B5">
  <tr> 
    <td class="boxtitle_orange_glass"><img alt="Bookmark" src="includes/images/icone/16x16/bookmark.gif" width="16" height="16" align="middle"> Set: <?php echo $set->name; ?></td>
  </tr>
  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <td bgcolor="#EEEEEE" class="cer_maintable_text">
		Tags are used to group content (e.g., tickets, articles) by organizational characteristics (e.g., receipts, notices, spam)<br>
    	<br>
    	
	    	<table cellpadding="2" cellspacing="2" border="0" width="100%">
				<tr>
					<td class="cer_maintable_text" colspan="2">
					
						<table width="100%">
							<tr>
				    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap" width="0%">Set Name:</td>
				    			<td class="cer_maintable_text" width="100%"><input type="input" name="ws_set_name" size="45" value="<?php echo htmlspecialchars($set->name); ?>"  style="width:98%;"/></td>
							</tr>
							<tr>
				    			<td class="cer_maintable_heading" align="right" valign="top" nowrap="nowrap" width="0%">Permissions:</td>
				    			<td class="cer_maintable_text" width="100%">
			    					<?php
									include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
									$cerTeams = CerWorkstationTeams::getInstance();
									$team_list = $cerTeams->getTeams();
		
			    					foreach($team_list as $team_id => $team) { /* @var $team CerWorkstationTeam */
			    						$enabled = (isset($set->teams[$team_id])) ? 1 : 0;
			    					?>
			    					<label><input type="checkbox" name="ws_set_teams[]" value="<?php echo $team_id; ?>" <?php if($enabled) echo "checked"; ?>><?php echo $team->name; ?></label><br>
			    					<?php } ?>
				    			</td>
							</tr>
						</table>
						
					</td>
				</tr>
	    	</table>
    	
    </td>
  </td>
  </tr>
  
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
			<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SAVE; ?>">
		</td>
	</tr>
  
</table>

</form>
