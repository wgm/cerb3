<table border="0" cellpadding="2" cellspacing="0" class="table_orange" width="100%">
	<tr>
	  <td class="bg_orange"><table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
	      <td width="100%"><span class="text_title_white"> Search Builder</span></td>
	  </tr>
	  </table></td>
	</tr>
</table>

<script type="text/javascript">
	var searchWorkflow{$label} = new CerQuickWorkflow('{$label}','{$label}_searchCriteriaForm');
	
	searchWorkflow{$label}.tagAction = function() {literal}{{/literal}
//		this.post();
		var tagDiv = document.getElementById("tag_input_" + this.label);
		if(null == tagDiv) return;

		{literal}
//		YAHOO.util.Connect.setForm(this.frm);
		var cObj = YAHOO.util.Connect.asyncRequest('GET', 'rpc.php?cmd=search_set_criteria&label=' + this.label + '&criteria=tags&tags=' + escape(tagDiv.value), {
			success: function(o) {
//				o.argument.caller.postResultsAction();
				o.argument.tagDiv.value = '';
				o.argument.tagDiv.focus();
				o.argument.caller.refresh();
				o.argument.caller.postAddTagAction();
			},
			failure: handleWorkflowFailure,
			argument: {caller:this,tagDiv:tagDiv}
		});
		{/literal}
		
	{literal}}{/literal}
	
	searchWorkflow{$label}.postResultsAction = function() {literal}{{/literal}
		doSearchCriteriaList(this.label);
	{literal}}{/literal}
	
	searchWorkflow{$label}.postAddTagAction = function() {literal}{{/literal}
		doSearchCriteriaList(this.label);
	{literal}}{/literal}
</script>

<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr bgcolor="#F0F0FF">
		<td valign="top" width="0%" nowrap="nowrap">
			<form action="#" name="{$label}_searchBuilderForm" id="{$label}_searchBuilderForm" style="margin:0px;">
			<input type="hidden" name="label" value="{$label}">
			<input type="hidden" name="cmd" value="search_show_criteria">
			Criteria: <select name="criteria" onchange="doGetCriteria('{$label}');">
				<option value="">-- select a criteria --
				<optgroup label="Ticket Fields">
					<option value="mask">Ticket ID/Mask
					<option value="status">Ticket State
					<option value="ticket_status">Ticket Status
					<option value="waiting">Waiting on Customer
					<option value="requester">Requester
					<option value="subject">Subject
					<option value="content">Content
					<option value="company">Company
					<option value="queue">Mailbox
					<option value="flag">Flagged by
					<option value="priority">Priority
					<option value="tags">Tags
					<option value="workflow">Suggested Agents
					<!---<option value="has_teams">Has Teams Assigned--->
					<option value="created">Created
					<option value="last_updated">Last Updated
					<option value="due">Due
				</optgroup>
				{if !empty($customfields->group_templates)}
					{foreach from=$customfields->group_templates item=group}
					<optgroup label="{$group->group_name}">
						{foreach from=$group->fields item=field}
							<option value="custom_field{$field->field_id}">{$field->field_name}
						{/foreach}
					</optgroup>
					{/foreach}
				{/if}
			</select>
			<br>
			</form>
		</td>
		<td valign="top" width="0%" nowrap="nowrap">
			<form action="#" name="{$label}_searchCriteriaForm" id="{$label}_searchCriteriaForm" style="margin:0px;">
			<input type="hidden" name="label" value="{$label}">
			<span id="{$label}_searchCriteriaOptions"></span>
			</form>
		</td>
		<td width="100%"></td>
	</tr>
</table>