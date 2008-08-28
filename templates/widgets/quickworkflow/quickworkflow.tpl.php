<input type="hidden" name="quickworkflow_string" value="">
<table border="0" cellpadding="2" cellspacing="0" class="quickworkflow_table">
	<tr>
		<td bgcolor="#FF8000"><span class="quickworkflow_title"> <img src="includes/images/icone/16x16/bookmark.gif" alt="Workflow" width="16" height="16" />&nbsp;Quick Workflow</span></td>
	</tr>
	<tr>
		<td>
			<span id="searchmodes_{$label}">
			{if !$no_tags}
				<label><input name="category" type="radio" value="tag" onclick="{$jvar}.setMode(0);">Add Tags</label>
			{/if}
			
			{if !$no_flags}
				{if $acl->has_priv($smarty.const.PRIV_REMOVE_ANY_FLAGS,$smarty.const.BITGROUP_2)}
					<label><input name="category" type="radio" value="flag" onclick="{$jvar}.setMode(1);">Assign Agents</label>
				{/if}
			{/if}
			
			{if !$no_suggestions}
				<label><input name="category" type="radio" value="agent" onclick="{$jvar}.setMode(1);">Suggest Agents</label>
			{/if}
			</span>
		</td>
	</tr>
	<tr>
		<td nowrap="nowrap">
			<span id="quickWorkflowMode0_{$label}" style="display:none;">
			<table cellpadding="0" cellspacing="0" width="100%" border="0">
				<tr>
					<td>
					<b>Enter tags separated by commas:</b><br>
			            <div class="searchdiv">
		                    <div class="searchautocomplete">
		                        <input name="tag_input" id="tag_input_{$label}" size="45" />
		                        <div id="searchcontainer_{$label}" class="searchcontainer"></div>
		                    </div>
			            </div>
					</td>
				</tr>
				{if !$hide_submit}
				<tr>
					<td align="right"><input type="button" onclick="{$jvar}.tagAction();" value="+ Apply" class="cer_button_face" /></td>
				</tr>
				{/if}
			</table>
			</span>
			
			<span id="quickWorkflowMode1_{$label}" style="display:none;">
			<table cellpadding="0" cellspacing="0" width="100%" border="0">
				<tr>
					<td nowrap="nowrap"><img src="includes/images/icone/16x16/find.gif" alt="Find text" title="Find text" width="16" height="16" />
					<input name="keyword" type="text" value="" size="35" onkeypress="return {$jvar}.handleEnter(this, event);" /><input type="button" name="find" onclick="{$jvar}.search();" value="Find" title="Search" /></td>
				</tr>
				<tr>
					<td>
						<span id="quickWorkflowResults_{$label}">You can find workflow items by category or keywords.<br>
						Clicking 'Find' with no search criteria will quickly list all items.</span>
					</td>
				</tr>
				{if !$hide_submit}
				<tr>
					<td align="right"><input type="button" onclick="{$jvar}.resultsAction();" value="+ Add" class="cer_button_face" /></td>
				</tr>
				{/if}
			</table>
			</span>
		</td>
	</tr>
</table>
