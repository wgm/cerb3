<form action="index.php" method="post" name="formGetWork">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="cmd" value="getwork_teams">
<input type="hidden" name="form_submit" value="getwork_pull">
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="table_green">
	<!---<input type="hidden" name="getwork_team" value="">--->
   <tr>
     <td nowrap="nowrap" class="bg_green"><img alt="A folder" src="includes/images/icone/16x16/folder_gear.gif" width="16" height="16" /><span class="text_title_white">My Teams</span> <a href="javascript:;" onclick="getTeamWorkloads();" class="link_box_edit">reload</a></td>
   </tr>
   <tr>
     <td nowrap="nowrap" bgcolor="#F0F0FF" class="orange_heading">Assign from these teams:  </td>
   </tr>
   <tr>
     <td nowrap="nowrap" bgcolor="#F0F0FF">
		{foreach from=$teams name=teams item=team key=teamId}
			{assign var=uid value=$session->vars.login_handler->user_id}
			{if isset($team->agents.$user_id)} <!--- <input type="checkbox" name="getwork_teams[]" value="{$teamId}" /> --->
				<label><input type="checkbox" name="getwork_teams[]" value="{$teamId}"> <img alt="A team" src="includes/images/icone/16x16/businessmen.gif" width="16" height="16" />&nbsp;<strong>{$team->name}</strong> ({$team->workload_hits})</label>
				<br />
			{/if}
      {/foreach}
      
      {*
      {if $numUnassigned}
      <label><input type="checkbox" name="include_unassigned" value="1"><img alt="A folder" src="includes/images/icone/16x16/folder_information.gif" width="16" height="16" />&nbsp;<strong>Unassigned</strong> ({$numUnassigned})</label>
		<br />
		{/if}
		*}
   </tr>
   
   <!---
   <tr>
     <td nowrap="nowrap" bgcolor="#F0F0FF" class="orange_heading">Filters: </td>
   </tr>
   <tr>
     <td nowrap="nowrap" bgcolor="#F0F0FF" class="box_text">
     	<label><input type="checkbox" name="getwork_show_flagged" value="1">Show Flagged Tickets</label>
     </td>
   </tr>
   --->
   
   <tr>
     <td nowrap="nowrap" bgcolor="#F0F0FF" class="orange_heading">How many tickets? </td>
   </tr>
   <tr>
     <td nowrap="nowrap" bgcolor="#F0F0FF"><!---<select name="getwork_order" class="box_text">
       <option value="priority" {if $order=="priority"}selected="selected"{/if}>Highest Priority</option>
       <option value="due" {if $order=="due"}selected="selected"{/if}>Most Overdue</option>
       <option value="latest" {if $order=="latest"}selected="selected"{/if}>Most Recent Reply</option>
     </select>---><select name="getwork_limit" class="box_text">
       <option value="1" {if $limit=="1"}selected="selected"{/if}>1</option>
       <option value="5" {if $limit=="5"}selected="selected"{/if}>5</option>
       <option value="10" {if $limit=="10"}selected="selected"{/if}>10</option>
       <option value="25" {if $limit=="25"}selected="selected"{/if}>25</option>
     </select><input type="button" value="Assign" onclick="getWork();">
     <!---<span class="box_text"><label><input type="checkbox" name="getwork_show_flagged" value="1" {if $show_flagged}checked{/if}>Include Flagged Tickets</label></span>--->
     </td>
   </tr>

<!---
   <tr>
     <td align="right" nowrap="nowrap" bgcolor="#F0F0FF"><input type="button" value="Get Work" onclick="javascript:getWork();" class="cer_button_face" /></td>
   </tr>
--->
   
</table>
</form>