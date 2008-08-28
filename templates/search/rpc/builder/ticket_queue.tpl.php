<input type="hidden" name="cmd" value="{$cmd}">
<input type="hidden" name="criteria" value="{$criteria}">
In any:<br>
{foreach from=$queues key=queueId item=queue}
	{if isset($acl->queues.$queueId)}
		<label><input type="checkbox" name="queues[]" value="{$queueId}">{$queue->queue_name}</label><br>
	{/if}
{/foreach}
<div align="right"><input type="button" value="Add &gt;&gt;" onclick="doSearchCriteriaSet('{$label}');"></div>