{if count($o_ticket->log->entries) }

{literal}
<script type="text/javascript">
	function toggleDisplayLog() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_log").style.display=="block") {
				document.getElementById("ticket_display_log").style.display="none";
				document.getElementById("ticket_display_log_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_log.value = 0;
			}
			else {
				document.getElementById("ticket_display_log").style.display="block";
				document.getElementById("ticket_display_log_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_log.value = 1;
			}
		}
	}
</script>
{/literal}

<a href="javascript:toggleDisplayLog();"><img id="ticket_display_log_icon" border="0" alt="Display Log" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_log}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16"></a>
<img src="includes/images/icone/16x16/document_info.gif" width="16" height="16" alt="Document">
<span class="link_ticket">{$smarty.const.LANG_AUDIT_LOG_TITLE_LATEST_5}</span><br>
<table cellspacing="0" cellpadding="0" width="100%"><tr><td bgcolor="#DDDDDD"><img src="includes/images/spacer.gif" height="1" width="1" alt=""></td></tr></table>
<div id="ticket_display_log" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_log}block{else}none{/if};">
<table cellspacing="1" cellpadding="1" width="100%" border="0" class="ticket_display_table">
	{section name=item loop=$o_ticket->log->entries max=5}
	    <tr>
	      <td width="0%" align="right" style="padding-left: 2px; padding-right: 2px;" nowrap class="box_text">
	      	{$o_ticket->log->entries[item]->log_timestamp}:
	      </td>
	      <td width="100%" style="padding-left: 2px;">{$o_ticket->log->entries[item]->log_text}</td>
	    </tr>
  	{/section}
</table>
</div>
<br>
{/if}