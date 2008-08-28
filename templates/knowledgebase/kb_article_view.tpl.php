<script language="javascript" type="text/javascript">
{literal}

function doArticleDelete()
{
	{/literal}if(confirm("{$smarty.const.LANG_KB_ARTICLE_DEL_CONFIRM}")){literal}
	{
		{/literal}document.location = "{"knowledgebase.php?form_submit=kb_delete&kbid="|cat:$article->article_id|cat:"&root="|cat:$root|cer_href}";{literal}
	}
}

//function doCommentRemove(comment_id)
//{
//	if(confirm("Are you sure you want to remove this knowledgebase article comment?"))
//	{
//		{/literal}url = "{$kb->focus_article->url_comment_delete}";{literal}
//		url = formatURL(url);
//		document.location = url;
//	}
//}

{/literal}
</script>

<br>

<table cellpadding="2" cellspacing="2" border="0" width="100%">
<tr>
	<td width="100%" valign="top">
		<table cellpadding="4" cellspacing="1" border="0" bgcolor="#BABABA" width='100%'>
			<form method="post" action="knowledgebase.php" name="frmTagRemove">
			<input type="hidden" name="sid" value="{$session_id}">
			<input type="hidden" name="form_submit" value="kb_modify_tags">
			<input type="hidden" name="mode" value="view_article">
			<input type="hidden" name="kb_tag_mode" value="0">
			<input type="hidden" name="kbid" value="{$article->article_id}">
			<tr>
				<td bgcolor="#FFFFFF" {if $acl->has_priv($smarty.const.PRIV_KB_EDIT)}ondblclick="document.location='{"knowledgebase.php?mode=edit_entry&kbid="|cat:$article->article_id|cer_href}'"{/if}>
					<span class="text_title">{$article->article_title|escape:"htmlall"}</span><br>
					{$article->article_content|escape:"script"}

					<span class="cer_footer_text">
					<br>
					<br>
					______________________<br>
					<b>Views: </b> {$article->article_views} (Support Center)<br>
					<b>Access: </b> {if $article->public}public{else}private{/if}<br>
					{if $article->article_votes != 0}
						{math assign="percent" equation="100*(x/5)" x=$article->article_rating format="%d"}
						{math assign="percent_i" equation="100-x" x=$percent format="%d"}
						<table cellpadding="0" cellspacing="0">
							<tr>
								<td class="cer_footer_text" width="0%" nowrap valign="middle">
									<b>User Rating:</b>&nbsp;
								</td>
								<td width="0%" nowrap>
				            	<table cellpadding="0" cellspacing="0" width="100%">
				            		<tr>
				            			<td colspan="2" nowrap class="cer_footer_text">
				            				{$article->article_rating} / 5.0 ({$article->article_votes} votes)
				            			</td>
				            		</tr>
				            		<tr>
				            			<td width="{$percent}%" bgcolor="#EE0000"><img alt="" src="includes/images/spacer.gif" height="3" width="1"></td>
				            			<td width="{$percent_i}%" bgcolor="#AEAEAE"></td>
				            		</tr>
				            	</table>
								</td>
							</tr>
						</table>
	        		{/if}
					</span>					
					
					{if !empty($tag_breadcrumbs)}
					<br>
					<span class="cer_footer_text">
					{foreach from=$tag_breadcrumbs name=tagsets item=tagSet key=setId}
						{foreach from=$tagSet name=tagsubset item=tagSubSet key=tagSubSetId}
							<a href="{"knowledgebase.php?root="|cat:$tagSubSetId|cer_href}" class="cer_footer_text">{$tagSubSet}</a>{if $smarty.foreach.tagsubset.last}{else} :{/if}
						{/foreach}
						<br>
					{/foreach}
					</span>
					{/if}
				</td>
			</tr>
			</form>
		</table>
		
		<form method="post" action="knowledgebase.php" style="margin:0px;">		
		<input type="button" class="cer_button_face" value="&lt;&lt Back" onClick="javascript:document.location='{"knowledgebase.php?root="|cat:$root}';">
		{if $acl->has_priv($smarty.const.PRIV_KB_EDIT)}<input type="button" class="cer_button_face" value="{$smarty.const.LANG_KB_EDIT}" OnClick="document.location='{"knowledgebase.php?mode=edit_entry&kbid="|cat:$article->article_id|cat:"&root="|cat:$root|cer_href}';">&nbsp;{/if}
		{if $acl->has_priv($smarty.const.PRIV_KB_DELETE)}<input type="button" class="cer_button_face" value="{$smarty.const.LANG_KB_DELETE}" OnClick="javascript:doArticleDelete();">&nbsp;{/if}
		<br>
		</form>
		
		<BR>

		{if $acl->has_priv($smarty.const.PRIV_KB_EDIT)}
		<form method="post" id="kbResourceCategoryManager" onsubmit="return false;">Loading...</form>
		<script>YAHOO.util.Event.addListener(window, "load", getFnrResourceCategoryManager({$article->article_id},'kbResourceCategoryManager'));</script>
	    <br>
	    {/if}
		
	    {if $acl->has_priv($smarty.const.PRIV_KB_EDIT)}
	    <span class="text_title">Tags:</span><br>
		<form style="margin:0px;" id="fnrResourceForm">
			<input type="hidden" name="id" value="{$resource->id}">
			<span id="kbResourceTagManager">{include file="knowledgebase/rpc/fnr_resource_tag_manager.tpl.php"}</span>
		</form>
		<script type="text/javascript" language="javascript">
			YAHOO.util.Event.addListener(document.body, "load", autoTags('tag_input','searchcontainer'));
		</script>
		<br>
		{/if}
		
		{if !empty($related_articles)}
			<span class="link_ticket">Similar Articles</span>
			{include file="knowledgebase/kb_article_list.tpl.php" articles=$related_articles}		
			<br>
		{/if}
		
		{*
		<table cellpadding="4" cellspacing="1" border="0" bgcolor="#BABABA" width='100%'>
			<tr>
				<td class="boxtitle_gray_glass">User Contributed Comments:</td>
			</tr>
		</table>
		( <a href="{$kb->focus_article->url_add_comment}" class="cer_maintable_heading">add comment</a> )<br>
		
		{include file="knowledgebase/kb_article_comment_list.tpl.php"}
		
		{if !empty($kb_comment) }
			{include file="knowledgebase/kb_article_comment_add.tpl.php"}
		{/if}
		*}
		
	</td>
</tr>
</table>

