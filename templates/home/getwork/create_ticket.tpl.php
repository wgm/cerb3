<form name="spellform" method="POST" target="spellWindow" action="includes/elements/spellcheck/spellcheck.php" style="margin:0px;">
<input type="hidden" name="caller" value="">
<input type="hidden" name="spellstring" value="">
</form>
<br>
<form id="reply_0" action="index.php" style="margin:0px;" onsubmit="return createTicketSend();" enctype="multipart/form-data" method="post">
<input type="hidden" name="sid" value="{$session->id}">
<input type="hidden" name="form_submit" value="create_ticket">
<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table_reply">
	<tr>
		<td class="text_title_white">Create Ticket</td>
	</tr>
	<tr>
		<td>
			<a href="javascript:;" onclick="ticketSpellCheck('nt_body');"><img src="includes/images/icone/16x16/document_check.gif" width="16" height="16" border="0" align="middle" onmouseover="ticketReplyToolTip({$threadId},'Spellcheck');" onmouseout="ticketReplyToolTip({$threadId},'');" alt="Spellcheck"></a>
			<!---<a href="javascript:;" onclick="ticketReplyTemplate('0','replytext{$threadId}');"><img src="includes/images/icone/16x16/document.gif" width="16" height="16" border="0" align="middle" onmouseover="ticketReplyToolTip({$threadId},'Insert E-mail Template');" onmouseout="ticketReplyToolTip({$threadId},'');" alt="Document"></a>--->
			<a href="javascript:;" onclick="doTemplate('0','nt_body');"><img src="includes/images/icone/16x16/document.gif" width="16" height="16" border="0" align="middle" onmouseover="ticketReplyToolTip({$threadId},'Insert E-mail Template');" onmouseout="ticketReplyToolTip({$threadId},'');" alt="Document"></a>
			<a href="javascript:;" onclick="ticketGetSig('nt_body');"><img src="includes/images/icone/16x16/mail_write.gif" width="16" height="16" border="0" align="middle" onmouseover="ticketReplyToolTip({$threadId},'Append Signature');" onmouseout="ticketReplyToolTip({$threadId},'');" alt="Envelope with Pen"></a>
			<!---<a href="javascript:;"><img src="includes/images/icone/16x16/book_blue_view.gif" width="16" height="16" border="0" align="middle" onmouseover="ticketReplyToolTip({$threadId},'Spellcheck');" onmouseout="ticketReplyToolTip({$threadId},'');"></a>--->
			<a href="javascript:;" onclick="ticketAddAttach({$threadId});"><img src="includes/images/icone/16x16/document_attachment.gif" width="16" height="16" border="0" align="middle" onmouseover="ticketReplyToolTip({$threadId},'Add Attachment');" onmouseout="ticketReplyToolTip({$threadId},'');" alt="Attachment"></a>
			<span id="replytip{$threadId}"class="box_text" style="border:1px solid #f5f5f5;padding:2px;color:#ffffff;background-color:#0000b5;visibility:hidden;"></span>
		</td>
	</tr>
	<tr>
		<td>
		<table width="100%" cellpadding="2" cellspacing="0" border="0">
			<tr>
				<td nowrap="nowrap" width="0%"><b>To:</b></td>
				<td width="100%">
				<span class="searchdiv">
					<input id="nt_to" name="nt_to" size="64" class='search_input'>
					<div class="searchshadow"><div id="searchcontainer" class="searchcontainer"></div></div>
				</span> (helpdesk addresses only)
				</td>
			</tr>
			<tr>
				<td nowrap="nowrap"><b>From:</b></td>
				<td><input type="input" name="nt_from" value="{if $email_address}{$email_address}{/if}" size="64"></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><b>Subject:</b></td>
				<td><input type="input" name="nt_subject" value="" size="64"></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr><!--- &gt; On {$date}, {$sender} wrote: --->
		<td><textarea name="nt_body" cols="50" rows="10" class="input_reply" id="nt_body"></textarea></td>
	</tr>
	<tr>
		<td>
			<label><input type="checkbox" name="nt_send_copy" value="1"> Mail a copy of this ticket to the requester</label><br>
			<label><input type="checkbox" name="nt_no_autoreply" value="1"> Don't send an autoresponse to the requester</label><br>
			<label><input type="checkbox" name="nt_no_notifications" value="1"> Don't send notification and/or watcher emails</label><br>
		</td>
	</tr>
	<tr>
		<td>
			<input type="submit"  class="cer_button_face" value="Send">
			<input type="button" onclick="clearCreateTicket();" class="cer_button_face" value="Discard">
		</td>
	</tr>
</table>
</form>
<br>