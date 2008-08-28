<table cellpadding="2" cellspacing="0" border="0" class="table_green" bgcolor="#FFFFFF">
	<tr>
		<td colspan="2" class="bg_green" nowrap> <img src="includes/images/icone/16x16/find.gif" width="16" height="16" alt="Find"> <span class="text_title_white">Search Criteria </span></td>
	</tr>
	
	<tr>
		<td colspan="2">
		&nbsp; <a href="javascript:;" onclick="doSearchCriteriaReset('{$label}');" class="cer_footer_text">reset criteria</a>
		| <a href="javascript:;" onclick="doSearchCriteriaGetSave('{$label}');" class="cer_footer_text">save</a>
		| <a href="javascript:;" onclick="doSearchCriteriaGetLoad('{$label}');" class="cer_footer_text">load</a>
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

		{if $criteriaName=="status"}
			<tr><td nowrap="nowrap">
			<img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Ticket State is <b>
			{if $criteria.status==0}
				Any Status
			{elseif $criteria.status==1}
				Any Active Status
			{elseif $criteria.status==2}
				Open
			{elseif $criteria.status==3}
				Closed
			{elseif $criteria.status==4}
				Deleted
			{/if}
			</b>
			</td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			
		{elseif $criteriaName=="waiting"}
			<tr><td nowrap="nowrap">
			<img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Waiting on Customer is <b>
			{if $criteria.waiting==0}
				false
			{elseif $criteria.waiting==1}
				true
			{/if}
			</b>
			</td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			
		{*
		{elseif $criteriaName=="has_teams"}
			<tr><td nowrap="nowrap">
			<img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Has Teams Assigned is <b>
			{if $criteria.show==0}
				false
			{elseif $criteria.show==1}
				true
			{/if}
			</b>
			</td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
		*}
			
		{elseif $criteriaName=="requester"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Requester contains:</td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			<tr><td valign="top" colspan="2"><b>{$criteria.requester}</b></td></tr>
			
		{elseif $criteriaName=="mask"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Ticket ID/Mask: <b>{$criteria.mask}</b></td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			
		{elseif $criteriaName=="subject"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Subject matches:</td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			<tr><td valign="top" colspan="2"><b>{$criteria.subject}</b></td></tr>
			
		{elseif $criteriaName=="content"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Content matches:</td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			<tr><td valign="top" colspan="2"><b>{$criteria.content}</b></td></tr>
			
		{elseif $criteriaName=="company"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Company contains '<b>{$criteria.company}</b>'</td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
		
		{elseif $criteriaName=="queue"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Mailbox is any:</td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			
			{if count($criteria.queues)}
				{foreach from=$criteria.queues item=queue name=queues key=queueId}
					<tr><td nowrap="nowrap">&nbsp; &nbsp;- <b>{$queue}</b></td>
					<td valign="top">
						<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','queues','{$queueId}');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
					</td>
					</tr>
				{/foreach}
			{/if}
			
		{elseif $criteriaName=="ticket_status"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Ticket Status is any:</td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			
			{if count($criteria.statuses)}
				{foreach from=$criteria.statuses item=status name=statuses key=ticket_status_id}
					<tr><td nowrap="nowrap">&nbsp; &nbsp;- <b>{$status}</b></td>
					<td valign="top">
						<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','statuses','{$ticket_status_id}');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
					</td>
					</tr>
				{/foreach}
			{/if}
			
		{elseif $criteriaName=="flag"}
			{if $criteria.flag_mode}
				<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Flagged By:</td>
				<td valign="top">
					<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
				</td>
				</tr>
				{if count($criteria.flags)}
					{foreach from=$criteria.flags item=flag name=flags key=flagId}
						<tr><td nowrap="nowrap">&nbsp; &nbsp;- <b>{$flag}</b></td>
						<td valign="top">
							<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','flags','{$flagId}');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
						</td>
						</tr>
					{/foreach}
				{/if}
			{else}
				<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Flagged By: <b>Nobody</b></td>
				<td valign="top">
					<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
				</td>
				</tr>
			{/if}
			
		{elseif $criteriaName=="priority"}
				<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Priority in any:</b></td>
				<td valign="top">
					<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
				</td>
				</tr>
				<tr><td valign="top" colspan="2">
					{foreach from=$criteria.priorities item=priority key=priorityId}
						&nbsp; &nbsp;- <b>{$priority}</b><br>
					{/foreach}
				</td></tr>
			

		{elseif $criteriaName=="tags"}
				
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
				
		{elseif $criteriaName=="workflow"}
<!--			<tr><td nowrap="nowrap">Workflow matches (any|all) <b>{$criteria.workflow}</b></td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
-->
			
			{*
			{if count($criteria.teams)}
				<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Teams <a href="javascript:;" onclick="doSearchCriteriaToggle('{$label}','{$criteriaName}','teams_match');" class="workflow_item">{if !$criteria.teams_match}match any{elseif $criteria.teams_match==1}match all{else}match none{/if}</a>:</td>
				<td valign="top">
					<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','teams','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
				</td>
				</tr>
			
				{foreach from=$criteria.teams item=team name=teams key=teamId}
					<tr><td nowrap="nowrap">&nbsp; &nbsp;- <b>{$team}</b></td>
					<td valign="top">
						<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','teams','{$teamId}');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
					</td>
					</tr>
				{/foreach}
			{/if}
			*}

			{if count($criteria.agents)}
				<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Agents <a href="javascript:;" onclick="doSearchCriteriaToggle('{$label}','{$criteriaName}','agents_match');" class="workflow_item">{if !$criteria.agents_match}match any{elseif $criteria.agents_match==1}match all{else}match none{/if}</a>:</td>
				<td valign="top">
					<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','agents','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
				</td>
				</tr>
			
				{foreach from=$criteria.agents item=agent name=agents key=agentId}
					<tr><td nowrap="nowrap">&nbsp; &nbsp;- <b>{$agent}</b></td>
					<td valign="top">
						<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','agents','{$agentId}');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
					</td>
					</tr>
				{/foreach}
			{/if}
			
		{elseif $criteriaName=="created"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Created from <b>{$criteria.from}</b></td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			<tr><td valign="top" colspan="2"> to <b>{$criteria.to}</b></td></tr>
			
		{elseif $criteriaName=="last_updated"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Updated from <b>{$criteria.from}</b></td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			<tr><td valign="top" colspan="2"> to <b>{$criteria.to}</b></td></tr>
			
		{elseif $criteriaName=="due"}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> Due from <b>{$criteria.from}</b></td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			<tr><td valign="top" colspan="2"> to <b>{$criteria.to}</b></td></tr>
			
		{elseif substr($criteriaName,0,12)=="custom_field"}
			{if $criteria.type == "D"} {* Dropdown *}
				<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> {$criteria.name} in any:</b></td>
				<td valign="top">
					<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
				</td>
				</tr>
				<tr><td valign="top" colspan="2">
					{foreach from=$criteria.options item=opt key=optId}
						&nbsp; &nbsp;- <b>{$opt}</b><br>
					{/foreach}
				</td></tr>
			{elseif $criteria.type == "E"} {* Date *}
			<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> {$criteria.name} from <b>{$criteria.from}</b></td>
			<td valign="top">
				<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
			</td>
			</tr>
			<tr><td valign="top" colspan="2"> to <b>{$criteria.to}</b></td></tr>
			{else} {* Textline/Textbox *}
				<tr><td nowrap="nowrap"><img src="includes/images/icone/16x16/data_find.gif" width="16" height="16" border="0" alt="Criteria" align="absmiddle"> {$criteria.name} contains </b></td>
				<td valign="top">
					<a href="javascript:;" onclick="doSearchCriteriaRemove('{$label}','{$criteriaName}','','');" title="Remove"><b><img src="includes/images/icone/16x16/data_error.gif" width="16" height="16" border="0" alt="Remove"></b></a>
				</td>
				</tr>
				<tr><td valign="top" colspan="2">'<b>{$criteria.value}</b>'</td></tr>
			{/if}
			
		{/if}
		
	</tr>
	{/foreach}
	
	{if $label == "search"}
	<tr>
		<td colspan="2" align="right" valign="middle" bgcolor="#DDDDDD" nowrap="nowrap">
			<form action="ticket_list.php" style="margin:0px;">
			<input type="hidden" name="search_submit" value="yes">
			<span class="cer_footer_text">Results per page:</span> <input type="text" name="search_limit" value="{if $filter_rows}{$filter_rows}{else}50{/if}" size="3" maxlength="4"><input type="submit" value="Search" onclick="">
			</form>
		</td>
	</tr>
	{else}{* Views *}
	<tr>
		<td colspan="2" align="right" bgcolor="#DDDDDD">
			<form action="index.php" style="margin:0px;">
			<input type="hidden" name="view_submit" value="yes">
			<input type="hidden" name="view_submit_mode" value="2">
			<input type="hidden" name="label" value="{$label}">
			<input type="submit" value="Set Criteria" onclick="">
			</form>
		</td>
	</tr>
	{/if}
	
</table>