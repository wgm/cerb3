<input type="hidden" name="id" value="{$id}">
<img src="includes/images/icone/16x16/document{if !$public}_info{/if}.gif" width="16" height="16" border="0" align="absmiddle"> <a href="javascript:;" class="cer_knowledgebase_link" onclick="fnrShowTab(0);">view</a>
{if $acl->has_priv($smarty.const.PRIV_KB_EDIT)}
	| <img src="includes/images/icone/16x16/document_edit.gif" width="16" height="16" border="0" align="absmiddle"> <a href="javascript:;" class="cer_knowledgebase_link" onclick="fnrShowTab(1);">edit</a>
	| <img src="includes/images/icone/16x16/folder.gif" width="16" height="16" border="0" align="absmiddle"> <a href="javascript:;" class="cer_knowledgebase_link" onclick="fnrShowTab(2);">categories</a> 
	| <img src="includes/images/icone/16x16/bookmark.gif" width="16" height="16" border="0" align="absmiddle"> <a href="javascript:;" class="cer_knowledgebase_link" onclick="fnrShowTab(3);">tags</a> 
{/if}
| <img src="includes/images/icone/16x16/document_into.gif" width="16" height="16" border="0" align="absmiddle"> <a href="javascript:;" class="cer_knowledgebase_link" onclick="fnrShowTab(4);">permalinks</a> 
<span id="fnr_tab_view" style="display:block;">
	<div style="height:350px;background:#fff;overflow:auto;border:1px solid #aaa"><span class="text_title">{$title}</span><br><br>{$content}</div>
</span>
<span id="fnr_tab_edit" style="display:none;">
	<input type="text" name="title" size="50" value="{$title|escape:"htmlall"}">
	<label><input type="checkbox" name="private" value="1" {if !$public}checked{/if}> Private</label>
	<br>
	<textarea id="elm1" name="content" rows="15" cols="80" style="width:98%;">{$content}</textarea><br>
	<input type="button" value="Save Changes" onclick="fnrResourceSave({$id});">
</span>
<span id="fnr_tab_categories" style="display:none;overflow:auto;">
	<span id="kbResourceCategoryManager"></span>
</span>
<span id="fnr_tab_tags" style="display:none;">
	<span id="kbResourceTagManager"></span>
</span>
<span id="fnr_tab_permalinks" style="display:none;overflow:auto;">
	{include file="knowledgebase/rpc/fnr_get_permalinks.tpl.php"}
</span>