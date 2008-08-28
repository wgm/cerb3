{foreach from=$o_ticket->threads item=thread_ptr name=thread}

	{if $thread_ptr->type == "ws_comment"}
		{include file="display/display_ticket_threads_wscomment.tpl.php" oStep=$thread_ptr->ptr}
	{elseif $thread_ptr->type == "time"}
		{if $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE) || $thread_ptr->ptr->working_agent_id == $session->vars.login_handler->user_id }
				{include file="display/display_ticket_threads_time.tpl.php" oThread=$thread_ptr->ptr}
				{include file="display/display_ticket_threads_time_editable.tpl.php" oThread=$thread_ptr->ptr}
		{/if}
	{else} 
		{include file="display/display_ticket_threads_activity.tpl.php" oThread=$thread_ptr->ptr}
	{/if}
  
{/foreach}
