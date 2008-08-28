<table border="0" cellpadding="0" cellspacing="0" class="table_orange" width="100%">
   <tr>
     <td class="bg_orange"><table width="100%" border="0" cellspacing="0" cellpadding="0">
         <tr>
           <td><span class="text_title_white"><img src="includes/images/icone/16x16/bookmark.gif" alt="A tag" width="16" height="16" /> Workflow </span></td>
         </tr>
     </table></td>
   </tr>
   <tr>
     <td bgcolor="#F0F0FF"><span class="box_text">
			<table border="0" cellspacing="1" cellpadding="0" bgcolor="#FFFFFF" width="100%">
				{if empty($wsticket->tags) && empty($wsticket->teams) && empty($wsticket->agents) && empty($wsticket->flags)}
					<tr>
						<td bgcolor="#F0F0FF">No workflow applied.</td>
					</tr>
				{/if}
				{foreach from=$wsticket->tags key=tagId item=tag name=tags}
		        <tr>
		          <td><table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#F0F0FF">
		            <tr title="">
		              <td width="0%" align="center" nowrap="nowrap" bgcolor="#FF8000"><img src="includes/images/icone/16x16/folder_network.gif" alt="A folder" width="16" height="16" /></td>
		              <td width="100%" align="left" nowrap="nowrap" class="quickworkflow_item">{$tag.name}</td>
		              <td width="0%" align="right" nowrap="nowrap"><a href="javascript:;" onclick="doDisplayRemoveWorkflow('{$wsticket->id}','t','{$tagId}');" class="text_ticket_links" title="Remove tag from ticket"><b>X</b></a></td>
		          </tr>
		          </table></td>
		        </tr>
				{/foreach}
				
				{*
				{foreach from=$wsticket->teams key=teamId item=team name=teams}
		        <tr>
		          <td><table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#F0F0FF">
		            <tr>
		              <td width="0%" align="center" nowrap="nowrap" bgcolor="#00DD37"><img src="includes/images/icone/16x16/businessmen.gif" alt="A group of businesspeople" width="16" height="16" /></td>
		              <td width="100%" align="left" nowrap="nowrap" class="quickworkflow_item">{$team}</td>
		              <td width="0%" align="right" nowrap="nowrap"><a href="javascript:;" onclick="doDisplayRemoveWorkflow('{$wsticket->id}','g','{$teamId}');" class="text_ticket_links" title="Remove team from ticket"><b>X</b></a></td>
		          </tr>
		          </table></td>
		        </tr>
				{/foreach}
				*}
				
				{foreach from=$wsticket->agents key=agentId item=agent name=agents}
		        <tr>
		          <td><table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#F0F0FF">
		            <tr>
		              <td width="0%" align="center" nowrap="nowrap" bgcolor="#00DD37"><img src="includes/images/icone/16x16/hand_paper.gif" alt="A suggested ticket" width="16" height="16" /></td>
		              <td width="100%" align="left" nowrap="nowrap" class="quickworkflow_item">{$agent}</td>
		              <td width="0%" align="right" nowrap="nowrap"><a href="javascript:;" onclick="doDisplayRemoveWorkflow('{$wsticket->id}','a','{$agentId}');" class="text_ticket_links" title="Remove agent from ticket"><b>X</b></a></td>
		          </tr>
		          </table></td>
		        </tr>
				{/foreach}
				{foreach from=$wsticket->flags item=flag name=flags key=flagId}
		        <tr>
		          <td><table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#F0F0FF">
		            <tr>
		              <td width="0%" align="center" nowrap="nowrap" bgcolor="#CCCCCC"><img src="includes/images/icone/16x16/flag_red.gif" alt="A red flag" width="16" height="16" /></td>
		              <td width="100%" align="left" nowrap="nowrap" class="quickworkflow_item">{$flag}</td>
		              {if $acl->has_priv($smarty.const.PRIV_REMOVE_ANY_FLAGS,$smarty.const.BITGROUP_2) || $flagId == $user_id}
			              <td width="0%" align="right" nowrap="nowrap"><a href="javascript:;" onclick="doDisplayRemoveWorkflow('{$wsticket->id}','f','{$flagId}');" class="text_ticket_links" title="Remove flag from ticket"><b>X</b></a></td>
		              {else}
			              <td width="0%" align="right" nowrap="nowrap"></td>
		              {/if}
		          </tr>
		          </table></td>
		        </tr>
				{/foreach}
				{if $wsticket->is_waiting_on_customer}
		        <tr>
		          <td><table width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#F0F0FF">
		            <tr>
		              <td width="0%" align="center" nowrap="nowrap" bgcolor="#9EDBFE"><img src="includes/images/icone/16x16/alarmclock_pause.gif" width="16" height="16" border="0" alt="Waiting on Customer" /></td>
		              <td width="100%" align="left" nowrap="nowrap" class="quickworkflow_item">Waiting On Customer Reply</td>
		          </tr>
		          </table></td>
		        </tr>
				{/if}
		   </table>
		</td>
	</tr>
</table>

<br>
