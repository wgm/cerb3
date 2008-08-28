<table cellpadding="2" cellspacing="0" border="0" class="table_green" bgcolor="#FFFFFF">
	<tr>
		<td colspan="2" class="bg_green" nowrap> <img src="includes/images/icone/16x16/find.gif" width="16" height="16" alt="Find"> <span class="text_title_white">Search Criteria </span></td>
	</tr>
	
	<tr>
		<td colspan="2">
		&nbsp; <a href="javascript:;" onclick="doSearchCriteriaReset('{$label}');" class="cer_footer_text">reset criteria</a>
		</td>
	</tr>
	
	<tr>
		<td colspan="2">
		<form name="{$label}_searchCriteriaIOForm" id="{$label}_searchCriteriaIOForm" style="margin:0px;">
			<input type="hidden" name="label" value="{$label}">
			<span id="{$label}_searchCriteriaIO"></span>
		</form>
		</td>
	</tr>
	
	{foreach from=$search_builder->criteria item=criteria name=criterion key=criteriaName}

		{if $criteriaName=="keyword"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Text:</td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			<tr><td valign="top" colspan="2"><b>{$criteria.keyword}</b></td></tr>
		
		{elseif $criteriaName=="workflow"}

			{if count($criteria.tags)}
				<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Tags <a href="javascript:;" onclick="doSearchCriteriaToggle('{$label}','{$criteriaName}','tags_match');" class="workflow_item">{if !$criteria.tags_match}match any{elseif $criteria.tags_match==1}match all{else}match none{/if}</a>:</td>
				<td valign="top">
					<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','tags','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
				</td>
				</tr>
			
				{foreach from=$criteria.tags item=tag name=tags key=tagId}
					<tr><td nowrap="nowrap">&nbsp; &nbsp;- <b>{$tag}</b></td>
					<td valign="top">
						<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','tags','{$tagId}');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
					</td>
					</tr>
				{/foreach}
			{/if}
			
		{/if}
		
	</tr>
	{/foreach}
	
	<tr>
		<td colspan="2" align="right" bgcolor="#DDDDDD">
			<form action="knowledgebase.php" style="margin:0px;">
			<input type="hidden" name="form_submit" value="kb_search">
			<input type="submit" value="Search" onclick="">
			</form>
		</td>
	</tr>
	
</table>