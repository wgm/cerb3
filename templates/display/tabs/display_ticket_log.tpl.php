<img src="includes/images/icone/16x16/document_info.gif" width="16" height="16" alt="Document">
<span class="link_ticket">{$smarty.const.LANG_AUDIT_LOG_TITLE_LATEST_5}</span><br>
<table cellspacing="0" cellpadding="0" border="0" width="100%" style="padding-bottom:0px;"><tr><td bgcolor="#DDDDDD"><img src="includes/images/spacer.gif" height="1" width="1" alt=""></td></tr></table>
<form style="margin:0px;">
	<table cellspacing="0" cellpadding="0" width="100%" border="0" class="ticket_display_table">
	{section name=item loop=$o_ticket->log->entries}
		<tr> 
		  <td class="box_text" width="5%" align="right" nowrap style="padding-left: 2px; padding-right: 2px;">
		     {$o_ticket->log->entries[item]->log_timestamp}:
		  </td>
		  <td width="95%" style="padding-left: 2px; padding-right: 2px;">
			{$o_ticket->log->entries[item]->log_text}
		  </td>
		</tr>
	{sectionelse}
		<tr><td class="cer_footer_text"><b>No log entries</b></td></tr>
	{/section}
	</table>
</form>