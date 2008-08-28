<span class="cer_display_header">Categories:</span> 
{if $acl->has_priv($smarty.const.PRIV_KB_EDIT)}<span class=""><a href="javascript:popupResourceCategories();">manage</a>{/if}
<br>

{* Breadcrumb trail *}
{assign var=ancestors value=$kb_root->getAncestors(1)}
{foreach from=$ancestors item=ans key=ans_id}
	{if $ans_id}
		<a href="{"knowledgebase.php?root="|cat:$ans_id|cer_href}" class="cer_knowledgebase_link">{if empty($kb->flat_categories.$ans_id->name)}[no label]{else}{$kb->flat_categories.$ans_id->name}{/if}</a> : 
	{else}
		<a href="knowledgebase.php?root=0" class="cer_knowledgebase_link">Top</a> ::  
	{/if}
{/foreach}

<table cellpadding="0" cellspacing="10" border="0" width="100%">
	<tr>
		<td width="50%" valign="top">
			<table cellpadding="1" cellspacing="0" border="0" style="padding-right:2px;border-right:1px dashed #dddddd">
				{math assign=middle equation="ceil(x/2)" x=$kb_root->getChildCount()}
				{if 0 != count($kb_root->children)}
				{foreach from=$kb_root->children item=category key=category_id name=categories}
					<tr>
						<td colspan="2"><img src="includes/images/icone/16x16/folder.gif" align="absmiddle"><img src="includes/images/spacer.gif" width="5" height="1" align="absmiddle"><a href="{"knowledgebase.php?root="|cat:$category->id|cer_href}" class="kb_category"><b>{if empty($category->name)}[no label]{else}{$category->name}{/if}</b></a> ({$category->hits})</td>
					</tr>
					
					{* Most popular articles per category *}
					{assign var=articles value=$category->getMostViewedArticles(5)}
					{foreach from=$articles item=article key=article_id}
					<tr>
						<td width="100%"><img src="includes/images/icone/16x16/document{if !$article->public}_info{/if}.gif" align="absmiddle" alt="article"> <a href="javascript:popupResource({$article->id});" class="cer_knowledgebase_link">{$article->name}</a></td>
						<td align="right" width="0%" nowrap="nowrap">&nbsp;</td>
					</tr>
					{/foreach}
					
					<tr>
						<td width="100%">&nbsp;</td>
						<td width="0%">&nbsp;</td>
					</tr>
					
					{* Split columns at the halfway point *}
					{if $smarty.foreach.categories.iteration == $middle}
							</table>
						</td>
						<td width="50%" valign="top">
							<table cellpadding="1" cellspacing="0" border="0" style="padding-right:2px;border-right:1px dashed #dddddd">
					{/if}
				{/foreach}
				
				{else}
					<tr>
						<td width="100%"><span class="cer_knowledgebase_link"><i>Category <b>{$kb_root->name}</b> has no subcategories.</i></span></td>
						<td width="0%">&nbsp;</td>
					</tr>
				
				{/if}
			
			</table>
		</td>
	</tr>
</table>
