{if $view->show_mass || $view->show_modify}
	<tr valign="middle">
		<td align="left" colspan="{$col_span}" nowrap>
		
			<script type="text/javascript">
				var massWorkflow{$view->view_slot} = new CerQuickWorkflow('{$view->view_slot}','viewform_{$view->view_slot}');
				
				massWorkflow{$view->view_slot}.post = function() {literal}{{/literal}
				{literal}}{/literal}
			</script>
		
			<table cellpadding="3" cellspacing="0" border="0">
			
			<tr>
				<td valign="top" nowrap>
					<select name="mass_action" class="cer_footer_text" onchange="changeMassAction('{$view->view_slot}',this.value);">
						<option value="">- Perform action? -
						<option value="status">Set status:
						<option value="priority">Set priority:
						<option value="queue">Set mailbox:
						<option value="due">Set due date:
						<option value="spam">Set spam training:
						<option value="waiting">Set waiting on customer:
						<option value="blocked">Set blocked sender:
						<option value="merge">Merge tickets into:
						<option value="flag">Take/release:
						<option value="tag">Apply tags:
						<option value="workflow">Assign/suggest agents:
					</select>	
				</td>
				
				<td valign="top" nowrap>
					<span id="{$view->view_slot}_mass_status" style="display:none;">
						<select name="ma_status" class="cer_footer_text">
						  <option value="open">open
						  <option value="closed">closed
						  {if $acl->has_priv($smarty.const.PRIV_TICKET_DELETE)}
						  	<option value="deleted">deleted
						  {/if}
						</select>
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,this.form.ma_status);">
					</span>
					
					<span id="{$view->view_slot}_mass_priority" style="display:none;">
						<select name="ma_priority" class="cer_footer_text">
						  <option value="0">none
						  <option value="25">lowest
						  <option value="50">low
						  <option value="75">moderate
						  <option value="90">high
						  <option value="100">highest
						</select>
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,this.form.ma_priority);">
					</span>
					
					<span id="{$view->view_slot}_mass_due" style="display:none;">
						<input type="text" name="ma_due" value="+24 hours" size="24">
						<!---<span id="mass_due_cal">[[ calendar ]]</span>--->
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,this.form.ma_due);">
					</span>
					
					<span id="{$view->view_slot}_mass_spam" style="display:none;">
						<select name="ma_spam" class="cer_footer_text">
						  <option value="1">spam
						  <option value="0">not spam
						</select>
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,this.form.ma_spam);">
					</span>
					
					<span id="{$view->view_slot}_mass_waiting" style="display:none;">
						<select name="ma_waiting" class="cer_footer_text">
						  <option value="1">yes
						  <option value="0">no
						</select>
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,this.form.ma_waiting);">
					</span>
					
					<span id="{$view->view_slot}_mass_blocked" style="display:none;">
						<select name="ma_blocked" class="cer_footer_text">
						  <option value="1">yes
						  <option value="0">no
						</select>
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,this.form.ma_blocked);">
					</span>
					
					<span id="{$view->view_slot}_mass_merge" style="display:none;">
						<input type="text" name="ma_merge" value="" size="24">
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,this.form.ma_merge);">
					</span>
					
					<span id="{$view->view_slot}_mass_queue" style="display:none;">
						<select name="ma_queue" class="cer_footer_text">
						{foreach from=$queues item=queue key=queueId}
						  <option value="{$queueId}">{$queue->queue_name}
					   {/foreach}
						</select>
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,this.form.ma_queue);">
					</span>
					
					<span id="{$view->view_slot}_mass_flag" style="display:none;">
						<select name="ma_flag" class="cer_footer_text">
						  <option value="1">Take
						  <option value="0">Release
						</select>
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,this.form.ma_flag);">
					</span>

					<span id="{$view->view_slot}_mass_workflow" style="display:none;">
						{include file="widgets/quickworkflow/quickworkflow.tpl.php" jvar="massWorkflow"|cat:$view->view_slot label=$view->view_slot hide_submit=true no_tags=true}
						<label><input type="radio" name="workflow_mode" value="1" checked> Add</label> 
						<label><input type="radio" name="workflow_mode" value="0"> Remove</label>
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,null);">
					</span>
					
					<span id="{$view->view_slot}_mass_tag" style="display:none;">
						<b>Enter tags separated by commas:</b><br>
			            <div class="searchdiv">
		                    <div class="searchautocomplete">
		                        <input name="ma_tags" id="tag_input{$view->view_slot}" size="45" />
		                        <div id="searchcontainer{$view->view_slot}" class="searchcontainer"></div>
		                    </div>
			            </div>
						<input type="button" value=" &gt;&gt " onclick="javascript:addMassAction('{$view->view_slot}',this.form,this.form.ma_tags);">
					</span>
				</td>
				
				<td valign="top">
					<span id="{$view->view_slot}_mass_commit" style="display:none;">
						<b>With selected tickets:</b><br>
						<select size="5" name="mass_commit"></select><br>
						<!---<input type="button" class="cer_button_face" value="Echo" onclick="echoMassAction(this.form);">--->
						<input type="button" class="cer_button_face" value="Cancel" onclick="cancelMassAction(this.form,'{$view->view_slot}');">
						<input type="button" class="cer_button_face" value="Remove Selected" onclick="removeMassAction(this.form);">
						<input type="hidden" name="mass_commit_list" value="">
						<input type="button" class="cer_button_face" value="{$smarty.const.LANG_WORD_COMMIT}" onclick="commitMassActions(this.form);">
					</span>
				</td>
				
			</tr>
			
			</table>
			
		</td>
	</tr>
	
	<script>
		YAHOO.util.Event.addListener(document.body, "load", autoTags("tag_input{$view->view_slot}","searchcontainer{$view->view_slot}"));
	</script>
{/if}