<form id="getworkcomment{$id}">
<input type="hidden" name="id" value="{$id}">
<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table_reply">
	<tr>
		<td class="text_title_white">Comment</td>
	</tr>
	<tr><!--- &gt; On {$date}, {$sender} wrote: --->
		<td><textarea id="comment{$id}" name="comment" cols="50" rows="10" class="input_reply"></textarea></td>
	</tr>
	<tr>
		<td>
			<input type="button" onclick="quickCommentSend('{$id}');" class="cer_button_face" value="Save">
			<input type="button" onclick="clearGetWorkPreview('{$id}');" class="cer_button_face" value="Discard">
		</td>
	</tr>
</table>
</form>
