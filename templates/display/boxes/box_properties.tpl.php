{if  $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
<form id="workflowForm" name="workflowForm" action="display.php" style="margin:0px;">
<input type="hidden" name="ticket" value="{$ticket}">
<input type="hidden" name="sid" value="{$session_id}">
<input type="hidden" name="form_submit" value="ticket_modify_workflow">

<table border="0" cellpadding="2" cellspacing="0" class="table_green" width="100%">
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
                	<option value="open" {if !$wsticket->is_closed && !$wsticket->is_deleted}selected{/if}>open
                	<option value="closed" {if $wsticket->is_closed && !$wsticket->is_deleted}selected{/if}>closed
                	<option value="deleted" {if $wsticket->is_deleted}selected{/if}>deleted
                </select>
                </td>
              </tr>
              {if !empty($statuses)}
              <tr>
                <td class="green_heading">{$smarty.const.LANG_WORD_NEW_STATUS}:</td>
              </tr>
              <tr>
                <td>
                <select name="ticket_new_status">
                	{foreach from=$statuses item=status}
	                	<option value="{$status->getId()}" {if $wsticket->ticket_status_id == $status->getId()}selected{/if}>{$status->getText()}
	                {/foreach}
                </select>
                </td>
              </tr>
              {/if}
              <tr>
                <td style="padding-top:3px;" class="green_heading">{$smarty.const.LANG_WORD_PRIORITY}:</td>
              </tr>
              <tr>
                <td class="green_heading">
                	<table cellpadding="0" cellspacing="0">
                		<tr>
                			<td align="center"><label for="priority0"><img src="includes/images/icone/16x16/star_alpha.gif" width="16" height="16" border="0" title="None" alt="No Priority"></label></td>
                			<td align="center"><label for="priority1"><img src="includes/images/icone/16x16/star_grey.gif" width="16" height="16" border="0" title="Lowest" alt="Lowest Priority"></label></td>
                			<td align="center"><label for="priority2"><img src="includes/images/icone/16x16/star_blue.gif" width="16" height="16" border="0" title="Low" alt="Low Priority"></label></td>
                			<td align="center"><label for="priority3"><img src="includes/images/icone/16x16/star_green.gif" width="16" height="16" border="0" title="Moderate" alt="Moderate Priority"></label></td>
                			<td align="center"><label for="priority4"><img src="includes/images/icone/16x16/star_yellow.gif" width="16" height="16" border="0" title="High" alt="High Priority"></label></td>
                			<td align="center"><label for="priority5"><img src="includes/images/icone/16x16/star_red.gif" width="16" height="16" border="0" title="Highest" alt="Highest Priority"></label></td>
                		</tr>
                		<tr>
                			<td align="center"><input id="priority0" type="radio" name="ticket_priority" value="0" {if empty($wsticket->priority)}checked{/if}></td>
                			<td align="center"><input id="priority1" type="radio" name="ticket_priority" value="25" {if $wsticket->priority > 0 && $wsticket->priority <= 25}checked{/if}></td>
                			<td align="center"><input id="priority2" type="radio" name="ticket_priority" value="50" {if $wsticket->priority > 25 && $wsticket->priority <= 50}checked{/if}></td>
                			<td align="center"><input id="priority3" type="radio" name="ticket_priority" value="75" {if $wsticket->priority > 50 && $wsticket->priority <= 75}checked{/if}></td>
                			<td align="center"><input id="priority4" type="radio" name="ticket_priority" value="90" {if $wsticket->priority > 75 && $wsticket->priority <= 90}checked{/if}></td>
                			<td align="center"><input id="priority5" type="radio" name="ticket_priority" value="100" {if $wsticket->priority > 90 && $wsticket->priority <= 100}checked{/if}></td>
                		</tr>
                	</table>
              	 </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">{$smarty.const.LANG_WORD_SPAM_PROBABILITY}:</td>
              </tr>
              {if $wsticket && $wsticket->spam_probability}
              		{math assign="spam_rating" equation="100*x" x=$wsticket->spam_probability}
              	{else}
              		{assign var=spam_rating value="0"}
              {/if}
              <tr>
                <td>
			 			<table border="0" cellpadding="2" cellspacing="0">
				 			<tr>
				 				{if $wsticket->spam_trained == 0}
				 				<td bgcolor="#{if $spam_rating > 90}FF0000{else}00BB00{/if}"><font color="white"><b>{$spam_rating|string_format:"%0.2f"}%</b></font></td>
				 				<td>
				 				<select name="ticket_spam" class="cer_footer_text">
		 							<option value="spam" {if $spam_rating >= 90}selected{/if}>{$smarty.const.LANG_TICKET_SPAM_TRAINING_IS}
		 							<option value="notspam" {if $spam_rating < 90}selected{/if}>{$smarty.const.LANG_TICKET_SPAM_TRAINING_NOT}
		 						</select>
				 				</td>
		 						{else}
		 							{if $wsticket->spam_trained == 1}
		 							  <td bgcolor="#00BB00"><font color="white"><b>
		 							    {$smarty.const.LANG_TICKET_IS_HAM}
		 							  </b></font></td>
		 							{else}
		 							  <td bgcolor="#FF0000"><font color="white"><b>
		 							    {$smarty.const.LANG_TICKET_IS_SPAM}
		 							  </b></font></td>
		 							{/if}
		 						{/if}
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
                		<option value="{$queueId}" {if $wsticket->queue_id==$queueId}selected{/if}>{$queue->queue_name}
                	{/foreach}
                	</select>
                </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">{$smarty.const.LANG_DISPLAY_DUE}:</td>
              </tr>
              <tr>
                <td nowrap>
		          	<input type="text" name="ticket_due" value="{if $wsticket->date_due->mktime_datetime}{$wsticket->date_due->getUserDate()}{/if}" size="24">
						<a href="javascript:;" onclick="drawTicketDueCalendar({$wsticket->id});"><img src="includes/images/icon_calendar.gif" border="0" align="middle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
						<div id="duecal"></div>
                </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">Hide From Quick Assign Until:</td>
              </tr>
              <tr>
                <td nowrap>
		          	<input type="text" name="ticket_delay" value="{if $wsticket->date_delay->mktime_datetime}{$wsticket->date_delay->getUserDate()}{/if}" size="24">
						<a href="javascript:;" onclick="drawTicketDelayCalendar({$wsticket->id});"><img src="includes/images/icon_calendar.gif" border="0" align="middle" alt="{$smarty.const.LANG_DISPLAY_SHOW_CALENDAR}"></a>
						<div id="delaycal"></div>
                </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">{$smarty.const.LANG_DISPLAY_PROPS_TICKET_SUBJECT}:</td>
              </tr>
              <tr>
                <td nowrap>
						<input type="text" name="ticket_subject" size="24" value="{$wsticket->subject|short_escape}">
                </td>
              </tr>
              <tr>
                <td style="padding-top:3px;" class="green_heading">Waiting On Customer Reply:</td>
              </tr>
              <tr>
                <td nowrap>
						<label><input type="radio" name="ticket_waiting_on_customer" value="1" {if $wsticket->is_waiting_on_customer}checked{/if}>Yes</label>
						<label><input type="radio" name="ticket_waiting_on_customer" value="0" {if !$wsticket->is_waiting_on_customer}checked{/if}>No</label>
                </td>
              </tr>
              <tr>
              	<td align="right"><input type="submit" value="Save Changes" class='cer_button_face'></td>
              </tr>
          </table></td>
      </tr>
    </table>
    
</form>
{/if}
<br>