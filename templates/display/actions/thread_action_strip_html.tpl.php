<tr>
	<form action="display.php" method="POST">
	<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
	<input type="hidden" name="thread" value="{$oThread->thread_id}">
	<input type="hidden" name="form_submit" value="strip_html">
	<input type="hidden" name="sid" value="{$session_id}">
	
	<td align="left" valign="middle">
			&nbsp;HTML Tags Stripped!  Save changes? 
			<input type="submit" value="Accept">
			<input type="button" value="Reject" OnClick="javascript:document.location='{$urls.tab_display}';">
	</td>
	
	</form>
</tr>