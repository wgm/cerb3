<input type="hidden" name="cmd" value="{$cmd}">
<input type="hidden" name="criteria" value="{$criteria}">
In any:<br>
{foreach from=$statuses key=ticket_status_id item=status}
	<label><input type="checkbox" name="statuses[]" value="{$ticket_status_id}">{$status->getText()}</label><br>
{/foreach}
<div align="right"><input type="button" value="Add &gt;&gt;" onclick="doSearchCriteriaSet('{$label}');"></div>