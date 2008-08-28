<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table_reply">
	<tr>
		<td class="text_title_white">Comment</td>
	</tr>
	<tr>
		<td><textarea id="replytext{$threadId}" name="comment" cols="50" rows="10" class="input_reply"></textarea></td>
	</tr>
	<tr>
		<td>
			<input type="button" onclick="form.submit();" class="cer_button_face" value="Send">
			<input type="button" onclick="displayClearThread('{$threadId}');" class="cer_button_face" value="Discard">
		</td>
	</tr>
</table>