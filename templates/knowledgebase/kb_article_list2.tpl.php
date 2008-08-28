{if count($articles) > 0}
	<table width="100%" border="0" cellspacing="1" cellpadding="1" class="ticket_display_table">
    		<tr> 
	        <td width="0%" nowrap></td>
	        <td width="100%"><b>{$smarty.const.LANG_KB_SUMMARY}</b></td>
	        <td width="0%" nowrap><b>Relevance</b></td>
       	</tr>
	    	
 			{foreach from=$articles item=article name=articles}
        <tr>
          <td width="0%" align="right" nowrap>&nbsp;</td>
          <td width="100%"> 
          	<table cellpadding="0" cellspacing="0" border="0">
          		<tr>
          			<td valign="middle" align="center"><img alt="" src="includes/images/spacer.gif" width="4" height="1"><img alt="A document" src="includes/images/icone/16x16/document{if !$article->public}_info{/if}.gif" width="16" height="16" align="middle"><img alt="" src="includes/images/spacer.gif" width="6" height="1"></td>
          			<td valign="top">
						<a href="javascript:popupResource({$article->article_id});" class="link_ticket_cmd">{$article->article_title}</a><br>
			         		{$articles[article]->article_brief|strip_tags|short_escape|truncate:150}
         			</td>
         		</tr>
         	</table>
			</td>
			<td width="0%" valign="middle" align="center" nowrap>{$article->article_relevance}%</td>
        </tr>
			{/foreach}
			
	</table>

{else}
		<br>
		<i>{$smarty.const.LANG_KB_ARTICLE_NO_ARTICLES}</i>
		<br>
{/if}
