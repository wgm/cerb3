<form id="frmQuickWorkflowSearch_{$ticketId}" name="frmQuickWorkflowSearch_{$ticketId}" action="#" method="POST" style="margin:0px;">
<input type="hidden" name="id" value="{$ticketId}">
<input type="hidden" name="cmd" value="workflow_set">
{include file="widgets/quickworkflow/quickworkflow.tpl.php" jvar="ticketWorkflow" label=$ticketId}
</form>