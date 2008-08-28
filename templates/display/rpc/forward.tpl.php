<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table_reply">
	<tr>
		<td class="text_title_white">Forward</td>
	</tr>
	<tr>
		<td>
			<a href="javascript:;" onclick="doDisplaySpellCheck('replytext{$threadId}');"><img src="includes/images/icone/16x16/document_check.gif" width="16" height="16" border="0" align="middle" onmouseover="displayReplyToolTip({$threadId},'Spellcheck');" onmouseout="displayReplyToolTip({$threadId},'');" alt="Spellcheck"></a>
			<a href="javascript:;" onclick="doTemplate({$ticketId},'replytext{$threadId}');"><img src="includes/images/icone/16x16/document.gif" width="16" height="16" border="0" align="middle" onmouseover="displayReplyToolTip({$threadId},'Insert E-mail Template');" onmouseout="displayReplyToolTip({$threadId},'');" alt="Document"></a>
			<a href="javascript:;" onclick="displayGetSig('replytext{$threadId}');"><img src="includes/images/icone/16x16/mail_write.gif" width="16" height="16" border="0" align="middle" onmouseover="displayReplyToolTip({$threadId},'Append Signature');" onmouseout="displayReplyToolTip({$threadId},'');" alt="Envelope with Pen"></a>
			<!---<a href="javascript:;"><img src="includes/images/icone/16x16/book_blue_view.gif" width="16" height="16" border="0" align="middle" onmouseover="displayReplyToolTip({$threadId},'Spellcheck');" onmouseout="displayReplyToolTip({$threadId},'');"></a>--->
			<a href="javascript:;" onclick="displayReplyAddAttach({$threadId});"><img src="includes/images/icone/16x16/document_attachment.gif" width="16" height="16" border="0" align="middle" onmouseover="displayReplyToolTip({$threadId},'Add Attachment');" onmouseout="displayReplyToolTip({$threadId},'');" alt="Attachment"></a>
			<span id="displayreplytip{$threadId}"class="box_text" style="border:1px solid #f5f5f5;padding:2px;color:#ffffff;background-color:#0000b5;visibility:hidden;"></span>
		</td>
	</tr>
	<tr>
		<td nowrap="nowrap"><b>To:</b><br><input type="text" name="reply_to" size="64" style="width:98%;" /></td>
	</tr>
	<tr><!--- &gt; On {$date}, {$sender} wrote: --->
		<td><textarea id="replytext{$threadId}" name="reply" cols="50" rows="10" class="input_reply">
{if $sig_auto && $sig_pos == 1}{$sig}{/if}
{if $quote_previous}{$text|quote}{/if}
{if $sig_auto && $sig_pos == 0}{$sig}{/if}
</textarea></td>
	</tr>
	<tr>
		<td>
			<input type="button" onclick="form.submit();" class="cer_button_face" value="Send">
			<input type="button" onclick="displayClearThread('{$threadId}');" class="cer_button_face" value="Discard">
		</td>
	</tr>
</table>