<form id="getworkreply{$id}">
<input type="hidden" name="id" value="{$id}">
<input type="hidden" name="threadId" value="{$threadId}">
<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table_reply">
	<tr>
		<td colspan="2" class="text_title_white">Reply</td>
	</tr>
	<tr>
		<td align="right" width="0%" nowrap="nowrap" valign="top"><b>From:&nbsp;</b></td>
		<td width="100%">{$wsticket->queue_reply_to}</td>
	</tr>
	<tr>
		<td align="right" width="0%" nowrap="nowrap" valign="top"><b>To:&nbsp;</b></td>
		<td width="100%">
		{foreach from=$wsticket->getRequesters() item=req key=reqId name=reqs}
			{$req}{if !$smarty.foreach.reqs.last}, {/if}
		{/foreach}
		</td>
	</tr>
	<tr>
		<td align="right" width="0%" nowrap="nowrap" valign="top"><b>Cc:&nbsp</b></td>
		<td width="100%"><textarea name="reply_cc" cols="64" rows="2"></textarea></td>
	</tr>
	<tr>
		<td align="right" width="0%" nowrap="nowrap" valign="top"><b>Bcc:&nbsp</b></td>
		<td width="100%"><textarea name="reply_bcc" cols="64" rows="2"></textarea></td>
	</tr>
	<tr><!--- &gt; On {$date}, {$sender} wrote: --->
		<td colspan="2"><textarea id="reply{$id}" name="reply" cols="50" rows="10" class="input_reply">
{$text|quote}
{$sig}
</textarea></td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="top" width="0%" nowrap><b><label>Actions:</label> </b></td>
					<td valign="top" width="100%"><div id="replyaction{$threadId}" style="display:block;">
					<label>Set priority to </label>
						<label><input type="radio" name="reply_action_priority" value="0" {if empty($wsticket->priority)}checked{/if}><img src="includes/images/icone/16x16/star_alpha.gif" width="16" height="16" border="0" title="None" alt="No Priority"></label>
						<label><input type="radio" name="reply_action_priority" value="25" {if $wsticket->priority > 0 && $wsticket->priority <= 25}checked{/if}><img src="includes/images/icone/16x16/star_grey.gif" width="16" height="16" border="0" title="Lowest" alt="Lowest Priority"></label>
						<label><input type="radio" name="reply_action_priority" value="50" {if $wsticket->priority > 25 && $wsticket->priority <= 50}checked{/if}><img src="includes/images/icone/16x16/star_blue.gif" width="16" height="16" border="0" title="Low" alt="Low Priority"></label>
						<label><input type="radio" name="reply_action_priority" value="75" {if $wsticket->priority > 50 && $wsticket->priority <= 75}checked{/if}><img src="includes/images/icone/16x16/star_green.gif" width="16" height="16" border="0" title="Moderate" alt="Moderate Priority"></label>
						<label><input type="radio" name="reply_action_priority" value="90" {if $wsticket->priority > 75 && $wsticket->priority <= 90}checked{/if}><img src="includes/images/icone/16x16/star_yellow.gif" width="16" height="16" border="0" title="High" alt="High Priority"></label>
						<label><input type="radio" name="reply_action_priority" value="100" {if $wsticket->priority > 90 && $wsticket->priority <= 100}checked{/if}><img src="includes/images/icone/16x16/star_red.gif" width="16" height="16" border="0" title="Highest" alt="Highest Priority"></label>
					<br>
					<label>Set ticket state to </label>
						<label><input type="radio" name="reply_action_status" value="open" {if $wsticket->is_deleted==0 && $wsticket->is_closed==0}checked{/if}>open</label>
						<label><input type="radio" name="reply_action_status" value="closed" {if $wsticket->is_closed==1 && $wsticket->is_deleted==0}checked{/if}>closed</label>
						{if $acl->has_priv($smarty.const.PRIV_TICKET_DELETE)}
						<label><input type="radio" name="reply_action_status" value="deleted" {if $wsticket->is_deleted==1}checked{/if}>deleted</label>
						{/if}
					<br>
					{if !empty($statuses)}
					<label>Set ticket status to </label>
		                <select name="reply_action_new_status">
		                	{foreach from=$statuses item=status}
			                	<option value="{$status->getId()}" {if $wsticket->ticket_status_id == $status->getId()}selected{/if}>{$status->getText()}
			                {/foreach}
		                </select>
              	    <br>
              	    {/if}
					<label><input type="checkbox" value="1" name="reply_action_release"> Release flag on ticket</label><br>
					<label><input type="checkbox" value="1" name="reply_action_waiting"> Set Waiting on Customer</label><br>
					</div></td>
				</tr>
			</table>
		</td>
	</tr>
	{if $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
	<tr>
		<td colspan="2">
			<input type="button" onclick="quickReplySend('{$id}');" class="cer_button_face" value="Send">
			<input type="button" onclick="doGetWorkSpellCheck('reply{$id}');" class="cer_button_face" value="Spellcheck">
			<input type="button" onclick="clearGetWorkPreview('{$id}');" class="cer_button_face" value="Discard">
		</td>
	</tr>
	{/if}
</table>
</form>
