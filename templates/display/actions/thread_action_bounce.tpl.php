<tr>
	
<form action="display.php" method="post">
	<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
	<input type="hidden" name="thread" value="{$oThread->thread_id}">
	<input type="hidden" name="form_submit" value="bounce">
	<input type="hidden" name="sid" value="{$session_id}">
	
	<td align="left" valign="middle">
			&nbsp;>Bounce to address: 
			<input type="input" name="forward_to" size="35" value=""> 
			<input type="checkbox" name="add_to_req" value="1"> Add to Ticket Requesters 
			<input type="submit" value="Send">
	</td>
	
	</form>
</tr>
