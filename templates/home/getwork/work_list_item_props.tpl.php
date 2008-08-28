<form action="#" method="post" id="getworkform{$ticket->id}" style="padding:0px;margin:0px;">
<input type="hidden" name="id" value="{$ticket->id}">
<table border="0" cellpadding="2" cellspacing="0" class="table_green">
      <tr>
        <td class="bg_green"><table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td><span class="text_title_white"><img src="includes/images/icone/16x16/folder_gear.gif" alt="A gear" width="16" height="16" /> Properties
              </span></td>
              </tr>
        </table></td>
      </tr>
      <tr>
        <td bgcolor="#F5FBEE"><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="green_heading">{$smarty.const.LANG_WORD_STATUS}:</td>
              </tr>
              <tr>
                <td>
                <select name="ticket_status">
                	<option value="open" {if !$ticket->is_closed && !$ticket->is_deleted}selected{/if}>open
                	<option value="closed" {if $ticket->is_closed && !$ticket->is_deleted}selected{/if}>closed
                	<option value="deleted" {if $ticket->is_deleted}selected{/if}>deleted
                </select>
                </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">{$smarty.const.LANG_WORD_PRIORITY}:</td>
              </tr>
              <tr>
                <td class="green_heading">
                	<table cellpadding="0" cellspacing="0">
                		<tr>
                			<td align="center"><label for="priority0_{$ticket->id}"><img src="includes/images/icone/16x16/star_alpha.gif" width="16" height="16" border="0" title="None" alt="No Priority"></label></td>
                			<td align="center"><label for="priority1_{$ticket->id}"><img src="includes/images/icone/16x16/star_grey.gif" width="16" height="16" border="0" title="Lowest" alt="Lowest Priority"></label></td>
                			<td align="center"><label for="priority2_{$ticket->id}"><img src="includes/images/icone/16x16/star_blue.gif" width="16" height="16" border="0" title="Low" alt="Low Priority"></label></td>
                			<td align="center"><label for="priority3_{$ticket->id}"><img src="includes/images/icone/16x16/star_green.gif" width="16" height="16" border="0" title="Moderate" alt="Moderate Priority"></label></td>
                			<td align="center"><label for="priority4_{$ticket->id}"><img src="includes/images/icone/16x16/star_yellow.gif" width="16" height="16" border="0" title="High" alt="High Priority"></label></td>
                			<td align="center"><label for="priority5_{$ticket->id}"><img src="includes/images/icone/16x16/star_red.gif" width="16" height="16" border="0" title="Highest" alt="Highest Priority"></label></td>
                		</tr>
                		<tr>
                			<td align="center"><input id="priority0_{$ticket->id}" type="radio" name="ticket_priority" value="0" {if empty($ticket->priority)}checked{/if}></td>
                			<td align="center"><input id="priority1_{$ticket->id}" type="radio" name="ticket_priority" value="25" {if $ticket->priority > 0 && $ticket->priority <= 25}checked{/if}></td>
                			<td align="center"><input id="priority2_{$ticket->id}" type="radio" name="ticket_priority" value="50" {if $ticket->priority > 25 && $ticket->priority <= 50}checked{/if}></td>
                			<td align="center"><input id="priority3_{$ticket->id}" type="radio" name="ticket_priority" value="75" {if $ticket->priority > 50 && $ticket->priority <= 75}checked{/if}></td>
                			<td align="center"><input id="priority4_{$ticket->id}" type="radio" name="ticket_priority" value="90" {if $ticket->priority > 75 && $ticket->priority <= 90}checked{/if}></td>
                			<td align="center"><input id="priority5_{$ticket->id}" type="radio" name="ticket_priority" value="100" {if $ticket->priority > 90 && $ticket->priority <= 100}checked{/if}></td>
                		</tr>
                	</table>
              	 </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">{$smarty.const.LANG_WORD_SPAM_PROBABILITY}:</td>
              </tr>
              {math assign="spam_rating" equation="100*x" x=$ticket->spam_probability}
              <tr>
                <td>
			 			<table border="0" cellpadding="2" cellspacing="0">
				 			<tr>
				 				<td bgcolor="#{if $spam_rating > 90}FF0000{else}00BB00{/if}"><font color="white"><b>{$spam_rating|string_format:"%0.2f"}%</b></font></td>
				 				<td>
				 				{if $ticket->spam_trained == 0}
				 				<select name="ticket_spam" class="cer_footer_text">
		 							<option value="spam" {if $spam_rating >= 90}selected{/if}>{$smarty.const.LANG_TICKET_SPAM_TRAINING_IS}
		 							<option value="notspam" {if $spam_rating < 90}selected{/if}>{$smarty.const.LANG_TICKET_SPAM_TRAINING_NOT}
		 						</select>
		 						{else}
		 							{if $ticket->spam_trained == 1}{$smarty.const.LANG_TICKET_IS_HAM}{else}{$smarty.const.LANG_TICKET_IS_SPAM}{/if}
		 						{/if}
				 				</td>
				 			</tr>
			 			</table>
                </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">Mailbox:</td>
              </tr>
              <tr>
                <td nowrap>
                	<select name="ticket_queue">
                	{foreach from=$queues item=queue key=queueId}
                		<option value="{$queueId}" {if $ticket->queue_id==$queueId}selected{/if}>{$queue->queue_name}
                	{/foreach}
                	</select>
                </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">{$smarty.const.LANG_DISPLAY_DUE}:</td>
              </tr>
              <tr>
                <td nowrap>
		          	<input type="text" name="ticket_due" value="{if $ticket->date_due->mktime_datetime}{$ticket->date_due->getUserDate()}{/if}" size="24">
						<a href="javascript:;" onclick="drawTicketDueCalendar({$ticket->id});"><img src="includes/images/icon_calendar.gif" border="0" align="middle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
						<div id="getworkduecal{$ticket->id}"></div>
                </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">Hide From Quick Assign Until:</td>
              </tr>
              <tr>
                <td nowrap>
		          	<input type="text" name="ticket_delay" value="{if $ticket->date_delay->mktime_datetime}{$ticket->date_delay->getUserDate()}{/if}" size="24">
						<a href="javascript:;" onclick="drawTicketDelayCalendar({$ticket->id});"><img src="includes/images/icon_calendar.gif" border="0" align="middle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
						<div id="getworkdelaycal{$ticket->id}"></div>
                </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">{$smarty.const.LANG_DISPLAY_PROPS_TICKET_SUBJECT}:</td>
              </tr>
              <tr>
                <td nowrap>
						<input type="text" name="ticket_subject" size="24" value="{$ticket->subject|short_escape}">
                </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">Waiting On Customer Reply:</td>
              </tr>
              <tr>
                <td nowrap>
						<label><input type="radio" name="ticket_waiting_on_customer" value="1" {if $ticket->is_waiting_on_customer}checked{/if}>Yes</label>
						<label><input type="radio" name="ticket_waiting_on_customer" value="0" {if !$ticket->is_waiting_on_customer}checked{/if}>No</label>
                </td>
              </tr>
              <tr>
                <td nowrap>
						<input type="button" value="Save Properties" class="cer_button_face" onclick="saveWorkflow({$ticket->id});">
						<input type="button" value="Cancel" class="cer_button_face" onclick="clearGetWorkOpts({$ticket->id});">                
                </td>
              </tr>
          </table></td>
      </tr>
    </table>
</form>
<br>