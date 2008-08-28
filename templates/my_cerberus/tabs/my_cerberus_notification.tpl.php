{assign var=uid value=$session->vars.login_handler->user_id}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form action="my_cerberus.php" method="post">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="mode" value="notification">
<input type="hidden" name="form_submit" value="notification">

  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr class="boxtitle_blue_dk"> 
    <td>&nbsp;{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_HEADER}</td>
  </tr>
  <tr><td colspan="{$col_span}" class="cer_table_row_line"><img alt="" src="includes/images/spacer.gif" width="1" height="1"></td></tr>
  <tr bgcolor="#DDDDDD"> 
    <td bgcolor="#DDDDDD">
    
    	<table width="100%" cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF">
    	
    		<tr bgcolor="#DDDDDD">
	    		<td colspan="2" class="cer_maintable_text">
	    			{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_INSTRUCTIONS}</span><br>
					<br>
					{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_TOKENS_INSTRUCTIONS}<br>
					
					<B>##ticket_id##</B> - Ticket ID or Mask<br>
					<B>##ticket_subject##</B> - Ticket Subject<br>
					<B>##ticket_status##</B> - Ticket Status (new, closed, deleted)<br>
					<B>##first_email##</B> - The Body of the Original Ticket Email<br>
					<B>##latest_email##</B> - The Body of the Latest Ticket Email<br>
					<B>##queue_name##</B> - Ticket Queue Name (Support, etc.)<br>
					<B>##requester_address##</B> - The Email Address that Opened the Ticket.<br>
					<br>
					You can also create a direct link to a ticket by using the following url (modify if necessary to match your browser):<br>
					<b>{$tsurl}/display.php?ticket=##ticket_id##</b><br>
				</td>
	    	</tr>
	    	
    		<tr class="boxtitle_blue_glass">
	    		<td colspan="2">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EVENT_NEWTICKET_HEADER}</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" class="cer_maintable_heading" valign="top">
	    			Teams:
	    			<span class="cer_footer_text">(notify me of tickets created for these teams)</span>
	    		</td>
	    		<td width="99%">
	    		
	    			<table cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF">
	    				<tr bgcolor="#666666">
	    					<td class="cer_maintable_header">Team</td>
	    					<td class="cer_maintable_header">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO} {$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO_COMMADELIMITED}</td>
	    				</tr>
	    				
	    				{foreach key=teamId from=$teams item=team name=teams}
		    				{if $acl->teams.$teamId}
		    				<tr bgcolor="#DDDDDD">
		    					<td class="cer_maintable_heading">
		    						<label><input type="checkbox" name="notify_new_enabled[]" value="{$teamId}" {if isset($notification->users.$uid->n_new_ticket->teams_send_to.$teamId)}checked{/if}> <b>{$team->name}</b></label>
		    					</td>
		    					<td>
		    						<input type="text" name="notify_new_emails[]" size="45" maxlength="255" value="{$notification->users.$uid->n_new_ticket->teams_send_to.$teamId}">
		    						<input type="hidden" name="notify_new_teams[]" value="{$teamId}">
		    					</td>
		    				</tr>
		    				{/if}
	    				{/foreach}
	    				
	    			</table>
	    		</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EMAILTEMPLATE}</td>
	    		<td width="99%"><textarea name="notify_new_template" rows="10" cols="100%">{$notification->users.$uid->n_new_ticket->template}</textarea></td>
	    	</tr>
	    	
	    	
    		<tr class="boxtitle_blue_glass">
	    		<td colspan="2">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EVENT_ASSIGNMENT_HEADER}</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_ENABLED}</td>
	    		<td width="99%"><input name="notify_assigned_enabled" type="checkbox" value="1" {if $notification->users.$uid->n_assignment->enabled}checked{/if}></td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO}</td>
	    		<td width="99%">
	    			<input name="notify_assigned_emails" type="text" size="64" maxlength="255" value="{$notification->users.$uid->n_assignment->send_to}">
	    			<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO_COMMADELIMITED}</span>
	    		</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EMAILTEMPLATE}</td>
	    		<td width="99%"><textarea name="notify_assigned_template" rows="10" cols="100%">{$notification->users.$uid->n_assignment->template}</textarea></td>
	    	</tr>

    		<tr class="boxtitle_blue_glass">
	    		<td colspan="2">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EVENT_CLIENTREPLY_HEADER}</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_ENABLED}</td>
	    		<td width="99%"><input name="notify_client_reply_enabled" type="checkbox" value="1" {if $notification->users.$uid->n_client_reply->enabled}checked{/if}></td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO}</td>
	    		<td width="99%">
	    			<input name="notify_client_reply_emails" type="text" size="64" maxlength="255" value="{$notification->users.$uid->n_client_reply->send_to}">
	    			<span class="cer_footer_text">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_SENDTO_COMMADELIMITED}</span>
	    		</td>
	    	</tr>
    		<tr bgcolor="#DDDDDD">
	    		<td width="1%" nowrap class="cer_maintable_heading" valign="top">{$smarty.const.LANG_MYCERBERUS_NOTIFICATIONS_EMAILTEMPLATE}:</td>
	    		<td width="99%"><textarea name="notify_client_reply_template" rows="10" cols="100%">{$notification->users.$uid->n_client_reply->template}</textarea></td>
	    	</tr>

    	</table>

	</td>
  </tr>
  
  <tr>
  	<td style="padding-right:2px;padding-top:2px;padding-bottom:2px" bgcolor="#BBBBBB" align="right"><input type="submit" value="{$smarty.const.LANG_BUTTON_SUBMIT}" class="cer_button_face"></td>
  </tr>
  
</form>
  
</table>

<br>