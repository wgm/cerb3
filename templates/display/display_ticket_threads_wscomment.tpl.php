<div id="thread_wscomment_{$oStep->getId()}" style="display:block;">

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="ticket_display_comment">
  <tr>
	<td>
		<img src="includes/images/icone/16x16/note_pinned.gif" alt="Note" width="16" height="16" border="0"><b>{$oStep->getCreatedByAgentName()}</b> comments:<br>
		Date: {$oStep->getDateCreated()|date_format:$smarty.const.LANG_DATE_FORMAT_STANDARD}<br>
		<br>
	</td>
  </tr>
	
	<tr>
		<td>
			<table width="100%" cellspacing="1" cellpadding="2" border="0">
				<tr>
					<td width="100%">
					{$oStep->getNote()|short_escape|nl2br}
					</td>
				</tr>
			</table>
		</td>
	</tr>
	
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td align="right"><a href="#top" class="link_ticket_cmd">{$smarty.const.LANG_DISPLAY_BACK_TO_TOP|lower}</a></td></tr>
</table>

<br>
</div>