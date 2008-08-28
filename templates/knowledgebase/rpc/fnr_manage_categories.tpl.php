<table border="0" cellpadding="2" cellspacing="2" width="100%">
  <tbody>
    <tr>
      <td><img src="includes/images/icone/16x16/folder_add.gif" align="absmiddle">&nbsp;<a href="javascript:;" class="cer_knowledgebase_link" onclick="getFnrCategoryNew();">add category</a><br>
      <div style="height:200px;overflow:auto;border:1px solid #aaa;background:#fff">
      {foreach from=$root->getDescendents() item=catId name=manageDescendents}
      {if $catId > 0}
      	{assign var=category value=$kb->flat_categories.$catId}
      	{if $category->level > 2}
      		{* Repeat spacer *}
      		{math assign=levels equation="x-2" x=$category->level}
      		{str_repeat string="<img src=\"includes/images/tree_gap.gif\" align=\"absmiddle\">" mult=$levels}
      	{/if}
      	{if $category->level > 1}
      		<img src="includes/images/tree_cap.gif" align="absmiddle">
      	{/if}
      	{* Break up top level categories for readability *}
      	{if $category->level == 1 && $smarty.foreach.manageDescendents.iteration > 2}
      		<br>
      	{/if}
		<img src="includes/images/icone/16x16/folder.gif" align="absmiddle"><img src="includes/images/spacer.gif" width="5" height="1"><a href="javascript:;" onclick="getFnrCategoryEdit({$catId});">{if empty($category->name)}[no label]{else}{$category->name}{/if}</a> ({$category->hits})<br>
	  {/if}
      {/foreach}
      </div>
</td>
    </tr>
    <tr>
      <td>
      <form style="margin:0px;" onsubmit="return false;" id="fnrCategoryForm">
      </form>
      </td>
    </tr>
  </tbody>
</table>
