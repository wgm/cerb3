{if $o_ticket->writeable}
<script type="text/javascript">
tkt = {$o_ticket->ticket_id};

{literal}
	function doClone()
  	{
      if(confirm("This will create an identical copy of this ticket's threads, comments, attachments and properties to a new ticket id.\r\nAfter the ticket is cloned a change to one ticket will not affect the other.\r\nAre you sure you want to clone this ticket?"))
      	{
			document.location=formatURL("display.php?form_submit=clone&ticket=" + tkt);				     	
        }
    }
    
	icon_expand = new Image;
	icon_expand.src = "includes/images/icon_expand.gif";
	
	icon_collapse = new Image;
	icon_collapse.src = "includes/images/icon_collapse.gif";

	function toggleThread(th) {
		if (document.getElementById) {
			if(document.getElementById("thread_" + th).style.display=="block") {
				document.getElementById("thread_" + th).style.display="none";
			}
			else {
				document.getElementById("thread_" + th).style.display="block";
			}
		}
	}
	
	function toggleThreadTime(th) {
		if (document.getElementById) {
			if(document.getElementById("thread_track_time_" + th).style.display=="block") {
				document.getElementById("thread_track_time_" + th).style.display="none";
				document.getElementById("thread_track_time_" + th + "_edit").style.display="block";
			}
			else {
				document.getElementById("thread_track_time_" + th).style.display="block";
				document.getElementById("thread_track_time_" + th + "_edit").style.display="none";
			}
		}
	}
	
	function toggleThreadTimeEntry() {
		if (document.getElementById) {
			if(document.getElementById("thread_add_time_entry").style.display=="block") {
				document.getElementById("thread_add_time_entry").style.display="none";
			}
			else {
				document.getElementById("thread_add_time_entry").style.display="block";
			}
		}
	}

	function doTimeEntryAddHelp(prefix,fld) {
		if (document.getElementById) {
			document.getElementById(prefix + "_0").style.display="none";
			document.getElementById(prefix + "_1").style.display="none";
			document.getElementById(prefix + "_2").style.display="none";
			document.getElementById(prefix + "_3").style.display="none";
			
			document.getElementById(prefix + "_" + fld).style.display="block";
		}
	}
	
	var threads_activity_enabled = 1;
	var threads_time_enabled = 1;
	var threads_hidden_enabled = 0;
	
	function toggleThreadsActivity() {
		
		if(threads_activity_enabled) {
			toggle_to = "none";
			threads_activity_enabled = 0;
		}
		else {
			toggle_to = "block";
			threads_activity_enabled = 1;
		}
		
		if (document.getElementById) {
			{/literal}
				{foreach from=$o_ticket->threads item=thread_ptr}
					{if (($thread_ptr->type == "email" || $thread_ptr->type == "comment") && !$thread_ptr->ptr->is_hidden)}
						document.getElementById("thread_{$thread_ptr->ptr->thread_id}").style.display=toggle_to;
					{/if}	
				{/foreach}
			{literal}
		}
	}
	
	function toggleThreadsHidden() {
		
		if(threads_hidden_enabled) {
			toggle_to = "none";
			threads_hidden_enabled = 0;
		}
		else {
			toggle_to = "block";
			threads_hidden_enabled = 1;
		}
		
		if (document.getElementById) {
			{/literal}
				{foreach from=$o_ticket->threads item=thread_ptr}
					{if $thread_ptr->ptr->is_hidden}
						document.getElementById("thread_{$thread_ptr->ptr->thread_id}").style.display=toggle_to;
					{/if}	
				{/foreach}
			{literal}
		}
	}
	
	function toggleThreadsTime() {
		
		if(threads_time_enabled) {
			toggle_to = "none";
			threads_time_enabled = 0;
		}
		else {
			toggle_to = "block";
			threads_time_enabled = 1;
		}
		
		if (document.getElementById) {
			{/literal}
				{foreach from=$o_ticket->threads item=thread_ptr}
					{if $thread_ptr->type == "time"}
						document.getElementById("thread_track_time_{$thread_ptr->ptr->thread_time_id}").style.display=toggle_to;
						document.getElementById("thread_track_time_{$thread_ptr->ptr->thread_time_id}_edit").style.display="none";
					{/if}	
				{/foreach}
			{literal}
		}
	}
	
	function toggleThreadOptions(th) {
		if (document.getElementById) {
			if(document.getElementById("thread_" + th + "_options").style.display=="block") {
				document.getElementById("thread_" + th + "_options").style.display="none";
			}
			else {
				document.getElementById("thread_" + th + "_options").style.display="block";
			}
		}
	}

	function toggleQuotedText(th) {
		if (document.getElementById) {
			if(document.getElementById("thread_" + th + "_quoted").style.display=="block") {
				document.getElementById("thread_" + th + "_quoted").style.display="none";
			}
			else {
				document.getElementById("thread_" + th + "_quoted").style.display="block";
			}
		}
	}

	
	function calendarPopUp(time,label_id,field_id)
	{
		{/literal}
		url = formatURL("calendar_popup.php?show_time=1&timestamp=" + time + "&label=" + label_id + "&field=" + field_id);
		{literal}
		window.open(url,"calendarWin","width=300,height=220,resizable=1");		
	}
	
{/literal}

</script>
{/if}

<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td width="0%" nowrap valign="top" style="padding-right:5px;">
			{include file="display/boxes/box_properties.tpl.php}
			{include file="display/boxes/box_contact.tpl.php"}
			{include file="display/boxes/box_requesters.tpl.php"}
		</td>
		<td width="100%" valign="top">
			{include file="display/display_ticket_heading.tpl.php"}
			
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			{assign var=uid value=$session->vars.login_handler->user_id}
			{if $o_ticket->writeable}
				<tr>
					<td colspan="2" valign="top" align="right">
					{* Flag *}
					{if !isset($wsticket->flags.$uid) && $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
						[ <a href="{"display.php?form_submit=take&ticket="|cat:$wsticket->id|cer_href}" class="link_navmenu">Take Ticket</a> ]&nbsp;
					{/if}
					
					{* Release *}
					{if isset($wsticket->flags.$uid) && $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
						[ <a href="{"display.php?form_submit=release&ticket="|cat:$wsticket->id|cer_href}" class="link_navmenu">Release Ticket</a> ]&nbsp;
					{/if}
					
					
					{* Merge Into *}
					{if $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE) }
						[ <a href="{$urls.tab_merge}" class="link_navmenu">{$smarty.const.LANG_ACTION_MERGE}</a> ]&nbsp;
					{/if}
					
					{* Clone Link *}
					{if $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE) }
						[ <a href="javascript:doClone();" class="link_navmenu">{$smarty.const.LANG_ACTION_CLONE}</a> ]&nbsp;
					{/if}
					
			 		{* Print Ticket Link *}
			 		[ <a href="javascript: printTicket('{$urls.print_ticket}');" class="link_navmenu">{$smarty.const.LANG_ACTION_PRINT}</a> ]
			    	</td>
				</tr>
			{else}
				<tr>
					<td colspan=2>&nbsp;</td>
				</tr>
			{/if}
			</table>
			
			{include file="display/display_ticket_active_modules.tpl.php"}
		
		</td>
	</tr>
</table>
