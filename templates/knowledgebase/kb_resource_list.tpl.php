<span class="cer_display_header">{if isset($kb_root) && 0 == $kb_root->id}Uncategorized {/if}Resources:</span><br>

{if count($resources) > 0}
	<table width="100%" border="0" cellspacing="1" cellpadding="1" class="ticket_display_table">
    	<tr> 
	        <td width="100%"><b>{$smarty.const.LANG_KB_SUMMARY}</b></td>
	        <td width="0%" align="center" nowrap><B>User Rating</B></td>
       	</tr>
	    	
 		{foreach from=$resources item=resource name=resources}
        <tr {if 0 != $smarty.foreach.resources.iteration % 2}class="ticket_display_table_odd"{/if}>
          <td width="100%"> 
          	<table cellpadding="0" cellspacing="0" border="0">
          		<tr>
          			<td valign="middle" align="center"><img alt="" src="includes/images/spacer.gif" width="4" height="1"><img alt="A document" src="includes/images/icone/16x16/document{if !$resource->public}_info{/if}.gif" width="16" height="16" align="middle" title="An article"><img alt="" src="includes/images/spacer.gif" width="6" height="1"></td>
          			<td valign="top">
						<a href="javascript:popupResource({$resource->id});" class="link_ticket_cmd">{$resource->name}</a><br>
			         		{*$resources[resource]->resource_brief|strip_tags|short_escape|truncate:150*}
         			</td>
         		</tr>
         	</table>
			</td>
			<td width="0%" valign="middle" align="center" nowrap>
				{if $resource->votes != 0}
				{math assign="percent" equation="100*(x/5)" x=$resource->rating format="%d"}
				{math assign="percent_i" equation="100-x" x=$percent format="%d"}
	         	<table cellpadding="0" cellspacing="0" width="50">
	         		<tr>
	         			<td width="{$percent}%" bgcolor="#EE0000" title="{$resource->rating} / 5.0"><img alt="" src="includes/images/spacer.gif" height="3" width="1"></td>
	         			<td width="{$percent_i}%" bgcolor="#AEAEAE" title="{$resource->rating} / 5.0"></td>
	         		</tr>
	         	</table>
	         	{else}
	         		<i>n/a</i>
	     		{/if}
			</td>
        </tr>
		{/foreach}
			
	</table>

{else}
		<br>
		<i class="cer_knowledgebase_link">{$smarty.const.LANG_KB_ARTICLE_NO_ARTICLES}</i>
		<br>
{/if}
