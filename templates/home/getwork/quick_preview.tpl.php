<form>
<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table_comment">
	<tr>
		<td>
		<table cellspacing="0" cellpadding="0" width="100%" border="0">
			<tr>
				<td width="100%" align="left">
					{if $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
						<input type="button" onclick="preQuickReply('{$id}','{$thread_id}');" class="cer_button_face" value="Reply">
						<input type="button" onclick="quickComment('{$id}');" class="cer_button_face" value="Comment">
					{/if}
					<input type="button" onclick="clearGetWorkPreview('{$id}');" class="cer_button_face" value="Hide">
				</td>
				<td width="0%" nowrap="nowrap" align="right">
					
					{if $current.prev}
						{assign var=prev_id value=$current.prev}
						<input type="button" value="&lt;" onclick="getWorkShowPreview('{$id}','{$prev_id}');" title="{$threads.$prev_id.date}: {$threads.$prev_id.address}">
					{/if}
					
					Message <b>{$current.pos}</b> of <b>{$num_threads}</b>
					
					{if $current.next}
						{assign var=next_id value=$current.next}
						<input type="button" value="&gt;" onclick="getWorkShowPreview('{$id}','{$next_id}');" title="{$threads.$next_id.date}: {$threads.$next_id.address}">
					{/if}
					&nbsp;
				</td>
			</tr>
		</table>
		<br>
		</td>
	</tr>

	<tr>
		<td>
		On {$date}, {if !$acl->has_restriction($smarty.const.REST_EMAIL_ADDY,$smarty.const.BITGROUP_2)}{$sender}{else}sender{/if} wrote:<br>
		{$text|escape|nl2br}
		</td>
	</tr>
	<tr>
		<td>
		<br>
		<table cellspacing="0" cellpadding="0" width="100%" border="0">
			<tr>
				<td width="100%" align="left">
					{if $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
						<input type="button" onclick="preQuickReply('{$id}','{$thread_id}');" class="cer_button_face" value="Reply">
						<input type="button" onclick="quickComment('{$id}');" class="cer_button_face" value="Comment">
					{/if}
					<input type="button" onclick="clearGetWorkPreview('{$id}');" class="cer_button_face" value="Hide">
				</td>
				<td width="0%" nowrap="nowrap" align="right">
					
					{if $current.prev}
						{assign var=prev_id value=$current.prev}
						<input type="button" value="&lt;" onclick="getWorkShowPreview('{$id}','{$prev_id}');" title="{$threads.$prev_id.date}: {$threads.$prev_id.address}">
					{/if}
					
					Message <b>{$current.pos}</b> of <b>{$num_threads}</b>
					
					{if $current.next}
						{assign var=next_id value=$current.next}
						<input type="button" value="&gt;" onclick="getWorkShowPreview('{$id}','{$next_id}');" title="{$threads.$next_id.date}: {$threads.$next_id.address}">
					{/if}
					&nbsp;
				</td>
			</tr>
		</table>
		</td>
	</tr>
</table>
</form>