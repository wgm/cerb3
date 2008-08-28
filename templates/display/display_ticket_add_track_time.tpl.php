<div id="thread_add_time_entry" style="display:{if isset($form_submit) && $form_submit == "thread_create_time_entry"}block{else}none{/if};">

<a name="thread_track_time"></a>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1" alt=""></td></tr>
  <tr bgcolor="#6600cc">
	<td class="cer_display_thread_header">
		&nbsp;Create New Time Tracking Entry
	</td>
  </tr>
  <tr><td class="cer_table_row_line"><img src="includes/images/spacer.gif" width="1" height="1" alt=""></td></tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	
	<form action="display.php" method="post" name="add_time_tracking_entry_form">
	<input type="hidden" name="form_submit" value="thread_time_add">
	<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
	<input type="hidden" name="sid" value="{$session_id}">

	<tr bgcolor="#FFFFFF">
		<td class="cer_maintable_text">
			<table width="100%" cellspacing="1" cellpadding="2" border="0">
				
				<tr>
					<td colspan="2" bgcolor="#D0D0D0">Dates can be entered absolutely ('mm/dd/yy') or relatively ('now', '+2 days', '+1 week')</td>
				</tr>
			
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top"  nowrap>
						{$smarty.const.LANG_DISPLAY_TIME_TRACKING_WORK_DATE}:
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						<input type="text" name="thread_time_date" size="24" value="{$time_entry_defaults.mdy}">
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
						{$smarty.const.LANG_DISPLAY_TIME_TRACKING_DATE_BILLED}:
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						<input type="text" name="thread_time_date_billed" size="24" value="">
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top"  nowrap>
						{$smarty.const.LANG_DISPLAY_TIME_TRACKING_WORK_AGENT}:
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						<select name="thread_time_working_agent_id">
						{foreach from=$agents key=agentId item=agent name=agents}
							<option value="{$agentId}" {if $agentId==$session->vars.login_handler->user_id}selected{/if}>{$agent->getRealName()}
						{/foreach}
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top"  nowrap>
						{$smarty.const.LANG_WORD_HOURS}:<br>
						<span class="cer_footer_text">(for example: <B>1.5</B>)</span>
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						<table border="0" cellpadding="1" cellspacing="0">
							<tr>
								<td><span class="cer_custom_field_heading">{$smarty.const.LANG_WORD_WORKED}:</span> <input name="thread_time_hrs_spent" type="text" size="5" onfocus="javascript:doTimeEntryAddHelp('time_add_help',0);">&nbsp;</td>
								<td><span class="cer_custom_field_heading">{$smarty.const.LANG_WORD_CHARGEABLE}:</span> <input name="thread_time_hrs_chargeable" type="text" size="5" onfocus="javascript:doTimeEntryAddHelp('time_add_help',1);">&nbsp;</td>
								<td><span class="cer_custom_field_heading">{$smarty.const.LANG_WORD_BILLABLE}:</span> <input name="thread_time_hrs_billable" type="text" size="5" onfocus="javascript:doTimeEntryAddHelp('time_add_help',2);">&nbsp;</td>
								<td><span class="cer_custom_field_heading">{$smarty.const.LANG_WORD_PAYABLE}:</span> <input name="thread_time_hrs_payable" type="text" size="5" onfocus="javascript:doTimeEntryAddHelp('time_add_help',3);">&nbsp;</td>
							</tr>
						</table>
						
						{* Time Entry Help Section *}
						<div id="time_add_help_0" style="display:none;"><B>{$smarty.const.LANG_WORD_WORKED}:</B> The actual hours worked by the agent.</div>
						<div id="time_add_help_1" style="display:none;"><B>{$smarty.const.LANG_WORD_CHARGEABLE}:</B>The amount of hours charged to the client. (e.g.: minus lunch breaks, etc.)</div>
						<div id="time_add_help_2" style="display:none;"><B>{$smarty.const.LANG_WORD_BILLABLE}:</B> The amount of hours originally quoted to the client (if any).</div>
						<div id="time_add_help_3" style="display:none;"><B>{$smarty.const.LANG_WORD_PAYABLE}:</B> The amount of hours payable to the agent.</div>
					</td>
				</tr>
				
				<tr>
					<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
						{$smarty.const.LANG_DISPLAY_TIME_TRACKING_WORK_SUMMARY}:
					</td>
					<td bgcolor="#E0E0E0" width="99%">
						<textarea name="thread_time_summary" width="100%" rows="5">{$time_entry_defaults.summary|short_escape}</textarea>
					</td>
				</tr>
				
				{if $time_entry_defaults.custom_gid}
				
					<input type="hidden" name="thread_time_custom_gid" value="{$time_entry_defaults.custom_gid}">
				
					{foreach from=$time_entry_defaults.custom_fields->fields name=custom item=field}
						<tr>
							<td class="cer_custom_field_heading" bgcolor="#D0D0D0" width="1%" valign="top" nowrap>
								{$field->field_name}:
							</td>
							<td bgcolor="#E0E0E0" width="99%">
			                  	{if $field->field_type == "S"}
			                    	<input type="text" name="thread_time_custom_{$field->field_id}" size="65" value="" class="cer_custom_field_text">
			                    {/if}
			
			                  	{if $field->field_type == "T"}
			                    	<textarea cols="65" rows="3" name="thread_time_custom_{$field->field_id}" wrap="virtual" class="cer_custom_field_text"></textarea><br>
			                    	<span class="cer_footer_text">(maximum 255 characters)</span>
			                    {/if}
			                    
			                  	{if $field->field_type == "D"}
			                    	<select name="thread_time_custom_{$field->field_id}" class="cer_custom_field_text">
				                      <option value="">
				                      {html_options options=$field->field_options}
			                        </select>
			                    {/if}
							</td>
						</tr>
					{/foreach}
				{/if}
				
				
				<tr>
					<td colspan="2" bgcolor="#BBBBBB" align="right">
						<input type="submit" value="Update" class="cer_footer_text">
						<input type="button" value="Cancel" OnClick="javascript:toggleThreadTimeEntry();" class="cer_footer_text">
					</td>
				</tr>
				
			</table>
		</td>
	</tr>
	
	</form>
	
</table>

<br>
</div>