<table border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td valign="top">{include file="home/getwork/work_list_item_props.tpl.php"}</td>
    <td valign="top"><span id="workflowSnapshot_{$ticket->id}"></span></td>
    <td valign="top">
		<form id="frmQuickWorkflowSearch_{$ticket->id}" name="frmQuickWorkflowSearch_{$ticket->id}" action="#" method="POST" style="margin:0px;">
			<input type="hidden" name="id" value="{$ticket->id}">
			<input type="hidden" name="cmd" value="workflow_set">
			{include file="widgets/quickworkflow/quickworkflow.tpl.php" jvar="aryWorkflow["|cat:$ticket->id|cat:"]" label=$ticket->id}
		</form>
	<script>
		YAHOO.util.Event.addListener(window, "load", autoTags('tag_input_{$ticket->id}','searchcontainer_{$ticket->id}'));
	</script>
    </td>
  </tr>
</table>
