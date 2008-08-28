{if count($o_ticket->support_history->history)}

{literal}
<script type="text/javascript">
	function toggleDisplayHistory() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_history").style.display=="block") {
				document.getElementById("ticket_display_history").style.display="none";
				document.getElementById("ticket_display_history_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_history.value = 0;
			}
			else {
				document.getElementById("ticket_display_history").style.display="block";
				document.getElementById("ticket_display_history_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_history.value = 1;
			}
		}
	}
</script>
{/literal}

<a href="#" onclick="javascript:toggleDisplayHistory();"><img alt="Toggle" id="ticket_display_history_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_history}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" border="0"></a>
<img alt="A scroll" src="includes/images/icone/16x16/scroll_view.gif" width="16" height="16">
<span class="link_ticket">{$smarty.const.LANG_DISPLAY_CUST_HISTORY}</span><br>
<table cellspacing="0" cellpadding="0" width="100%"><tr><td bgcolor="#DDDDDD"><img src="includes/images/spacer.gif" height="1" width="1" alt=""></td></tr></table>
<div id="ticket_display_history" style="display:{if !empty($hp) || $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_history}block{else}none{/if};">
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="ticket_display_table">
  	{section name=item loop=$o_ticket->support_history->history}
	<tr>
		<td width="0%" nowrap class="td_small" align="right"><img alt="An envelope" src="includes/images/icone/16x16/mail2.gif" width="16" height="16" align="middle"></td>
		<td width="0%" nowrap class="td_small" align="right">{$o_ticket->support_history->history[item]->ticket_mask}</td>
		<td class="td_small" width="100%" style="padding-left: 2px; padding-right: 2px;">
			<a href="{$o_ticket->support_history->history[item]->ticket_url}" class="link_ticket_cmd">{$o_ticket->support_history->history[item]->ticket_subject|short_escape}</a> 
			({$o_ticket->support_history->history[item]->ticket_status})
		</td>
		<td class="td_small" width="0%" nowrap align="right">{$o_ticket->support_history->history[item]->ticket_date}</td>
	</tr>
 	{/section}
  
	<tr>
		<td colspan="4" align="right" class="td_small">
			{if $o_ticket->support_history->url_prev}
				<a href="{$o_ticket->support_history->url_prev}" class="link_ticket_cmd">&lt;&lt; {$smarty.const.LANG_WORD_PREV} </a>
			{/if}
			( {$smarty.const.LANG_WORD_SHOWING} {$o_ticket->support_history->history_from}-{$o_ticket->support_history->history_to}
			  {$smarty.const.LANG_WORD_OF} {$o_ticket->support_history->history_total} )
			{if $o_ticket->support_history->url_next}
				<a href="{$o_ticket->support_history->url_next}" class="link_ticket_cmd">{$smarty.const.LANG_WORD_NEXT} &gt;&gt;</a>
			{/if}
		</td>
	</tr>
</table>
</div>
  
<br>
{/if}

