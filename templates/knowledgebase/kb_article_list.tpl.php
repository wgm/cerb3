{if count($articles) > 0}
	<table width="100%" border="0" cellspacing="1" cellpadding="1" class="ticket_display_table">
    		<tr> 
	        <td width="100%" style="border-right:1px dotted #BBBBBB;"><B>{$smarty.const.LANG_KB_SUMMARY}</B></td>
	        <td width="0%" align="center" nowrap style="border-right:1px dotted #BBBBBB;"><B>Public</B></td>
	        <td width="0%" align="center" nowrap><B>User Rating</B></td>
       	</tr>
	    	
 			{foreach from=$articles item=article name=articles}
        <tr>
          <td width="100%"> 
          	<table cellpadding="0" cellspacing="0" border="0">
          		<tr>
          			<td valign="middle" align="center"><img alt="" src="includes/images/spacer.gif" width="4" height="1"><img alt="An article" src="includes/images/icon_article{if !$article->public}_private{/if}.gif" align="middle"><img src="includes/images/spacer.gif" width="6" height="1" alt=""></td>
          			<td valign="top">
						<a href="{"knowledgebase.php?mode=view_entry&kbid="|cat:$article->article_id|cat:"&root="|cat:$root|cer_href}" class="link_ticket_cmd">{$article->article_title}</a><br>
			         		{$articles[article]->article_brief|strip_tags|short_escape|truncate:150}
         			</td>
         		</tr>
         	</table>
			</td>
			<td width="0%" valign="middle" align="center" class="cer_maintable_heading" nowrap>{if $article->article_public}X{/if}</td>
         <td width="0%" align="center" nowrap>
			{if $article->votes != 0}
			{math assign="percent" equation="100*(x/5)" x=$article->rating format="%d"}
			{math assign="percent_i" equation="100-x" x=$percent format="%d"}
         	<table cellpadding="0" cellspacing="0" width="50">
         		<tr>
         			<td width="{$percent}%" bgcolor="#EE0000"><img alt="" src="includes/images/spacer.gif" height="3" width="1"></td>
         			<td width="{$percent_i}%" bgcolor="#AEAEAE"></td>
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
		<i>{$smarty.const.LANG_KB_ARTICLE_NO_ARTICLES}</i>
		<br>
{/if}
