<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td><span class="quickworkflow_item"><b>Found:</b></span></td>
	</tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="quickworkflow_results">
	
	{foreach from=$tags item=tag name=tags}
	<tr>
	 <td><table width="100%" border="0" cellpadding="2" cellspacing="0" style="border-bottom:1px solid #DDDDDD;">
	   <tr title="">
	     <td width="0%" align="center" nowrap="nowrap" bgcolor="#FF8000"><img src="includes/images/icone/16x16/folder_network.gif" alt="A folder" width="16" height="16" /></td>
	     <td width="100%" align="left" valign="top" nowrap="nowrap" class="quickworkflow_item"><label><input type="checkbox" name="workflow[]" value="t_{$tag->id}" title="Tag: {$tag->name}" /> {$tag->name} <i>({$tag->parent->name})</i></label></td>
	 </tr>
	 </table></td>
	</tr>
	{/foreach}
	
	{foreach from=$teams item=team name=teams}
	<tr>
	 <td><table width="100%" border="0" cellpadding="2" cellspacing="0" style="border-bottom:1px solid #DDDDDD;">
	   <tr title="">
	     <td width="0%" align="center" nowrap="nowrap" bgcolor="#00DD37"><img src="includes/images/icone/16x16/businessmen.gif" alt="A Team" width="16" height="16" /></td>
	     <td width="100%" align="left" valign="top" nowrap="nowrap" class="quickworkflow_item"><label><input type="checkbox" name="workflow[]" value="g_{$team->getId()}" title="Team: {$team->getName()}" /> {$team->getName()}</label></td>
	 </tr>
	 </table></td>
	</tr>
	{/foreach}
	
	{foreach from=$agents item=agent name=agents}
	<tr>
	 <td><table width="100%" border="0" cellpadding="2" cellspacing="0" style="border-bottom:1px solid #DDDDDD;">
	   <tr title="">
	     <td width="0%" align="center" nowrap="nowrap" bgcolor="#00DD37"><img src="includes/images/icone/16x16/hand_paper.gif" alt="An Agent" width="16" height="16" /></td>
	     <td width="100%" align="left" valign="top" nowrap="nowrap" class="quickworkflow_item"><label><input type="checkbox" name="workflow[]" value="a_{$agent->getId()}" title="Agent: {$agent->getRealName()}" /> {$agent->getRealName()}</label></td>
	 </tr>
	 </table></td>
	</tr>
	{/foreach}

	{foreach from=$flagAgents item=flag name=flags}
	<tr>
	 <td><table width="100%" border="0" cellpadding="2" cellspacing="0" style="border-bottom:1px solid #DDDDDD;">
	   <tr title="">
	     <td width="0%" align="center" nowrap="nowrap" bgcolor="#CCCCCC"><img src="includes/images/icone/16x16/flag_red.gif" alt="A Flag" width="16" height="16" /></td>
	     <td width="100%" align="left" valign="top" nowrap="nowrap" class="quickworkflow_item"><label><input type="checkbox" name="workflow[]" value="f_{$flag->getId()}" title="Flag: {$flag->getRealName()}" /> {$flag->getRealName()}</label></td>
	 </tr>
	 </table></td>
	</tr>
	{/foreach}
	
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td><img src="includes/images/spacer.gif" height="3" width="1" border="0"></td>
	</tr>
	<!---
	{if $actionSet=="article"}
			<tr>
				<td align="right"><input type="button" name="" value="+ Add to Favorites" class="cer_button_face" /><input type="button" onclick="kbQuickWorkflow.addResultsToArticle();" value="+ Add to Article" class="cer_button_face" /></td>
			</tr>	  
	{elseif $actionSet=="search"}
			<tr>
				<td align="right"><input type="button" name="" value="Add &gt;&gt;" class="cer_button_face" onclick="this.form.cmd.value='search_set_criteria';doSearchCriteriaSet(this.form.label.value);" /></td>
			</tr>	  
	{else}
			<tr>
				<td align="right"><input type="button" name="" value="+ Add to Favorites" class="cer_button_face" /><input type="button" onclick="doPostQuickWorkflow({$id});" value="+ Add to Ticket" class="cer_button_face" /></td>
			</tr>	  
	{/if}
	--->
</table>