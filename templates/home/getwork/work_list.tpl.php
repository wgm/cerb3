{if count($tickets)}
{foreach from=$tickets item=ticket name=tickets}
<div id="getwork{$ticket->id}" style="opacity:1.0;">
	{include file="home/getwork/work_list_item.tpl.php" ticket=$ticket}
</div>
{/foreach}
{/if}