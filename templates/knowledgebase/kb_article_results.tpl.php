<span class="cer_display_header">Matching Resources:</span><br>

{if count($articles) > 0}
	<table width="100%" border="0" cellspacing="1" cellpadding="1" class="ticket_display_table">
    	<tr> 
	        <td width="100%"><b>{$smarty.const.LANG_KB_SUMMARY}</b></td>
	        <td width="0%" align="center" nowrap style="border-right:1px dotted #BBBBBB;"><b>Public</b></td>
	        <td width="0%" align="center" nowrap style="border-right:1px dotted #BBBBBB;"><b>Rating</b></td>
	        <td width="0%" nowrap><b>Relevance</b></td>
       	</tr>
	    	
 		{foreach from=$articles item=article name=articles}
        <tr {if 0 != $smarty.foreach.articles.iteration % 2}class="ticket_display_table_odd"{/if}>
  			<td valign="top">
				<img alt="" src="includes/images/spacer.gif" width="4" height="1"><img alt="A document" src="includes/images/icone/16x16/document{if !$article->public}_info{/if}.gif" width="16" height="16" align="middle" title="An article"><img alt="" src="includes/images/spacer.gif" width="6" height="1"><a href="javascript:popupResource({$article->article_id});" class="link_ticket_cmd">{$article->article_title}</a><br>
 			</td>
			<td width="0%" valign="middle" align="center" class="cer_maintable_heading" nowrap>{if $article->article_public}X{/if}</td>
			<td width="0%" align="center" nowrap>
				{if $article->article_votes != 0}
				{math assign="percent" equation="100*(x/5)" x=$article->article_rating format="%d"}
				{math assign="percent_i" equation="100-x" x=$percent format="%d"}
            	<table cellpadding="0" cellspacing="0" width="50">
            		<tr>
            			<td width="{$percent}%" bgcolor="#EE0000" title="{$article->article_rating} / 5.0"><img alt="" src="includes/images/spacer.gif" height="3" width="1"></td>
            			<td width="{$percent_i}%" bgcolor="#AEAEAE" title="{$article->article_rating} / 5.0"></td>
            		</tr>
            	</table>
	         	{else}
	         		<i>n/a</i>
        		{/if}
	         </td>
			<td width="0%" valign="middle" align="center" nowrap>{$article->article_relevance}%</td>
        </tr>
		{/foreach}
			
	</table>

{else}
		<br>
		<i class="cer_knowledgebase_link">{$smarty.const.LANG_KB_ARTICLE_NO_ARTICLES}</i>
		<br>
{/if}

