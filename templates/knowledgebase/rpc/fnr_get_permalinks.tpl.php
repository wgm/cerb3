<br>
<b>Internal Helpdesk:</b><br>
<a href='{$cfg->settings.http_server|cat:$cfg->settings.cerberus_gui_path|cat:"/knowledgebase.php?mode=view_entry&kbid="|cat:$id}' class='cer_knowledgebase_link' style="font-size:80%;">{$cfg->settings.http_server|cat:$cfg->settings.cerberus_gui_path|cat:"/knowledgebase.php?mode=view_entry&kbid="|cat:$id}</a><br>
<br>
{if is_array($permalinks)}
{foreach from=$permalinks item=link}
{if is_array($link) && !empty($link.1)}
	<b>{$link.0}:</b><br>
	<a href='{$link.1|cat:"?mod_id=2&id="|cat:$id}' class='cer_knowledgebase_link' style="font-size:80%;">{$link.1|cat:"?mod_id=2&id="|cat:$id}</a><br>
	<br>
{/if}
{/foreach}
{/if}
