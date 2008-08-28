<table border="0" cellpadding="2" cellspacing="2" width="100%">
<tbody>
  <tr>
    <td align="right">Edit Category:</td>
    <td><input type="hidden" name="category_id" value="{$category->id}" />{$category->name}</td>
  </tr>
  <tr>
    <td align="right">Label:</td>
    <td><input size="32" name="category_name" value="{$category->name|escape:"htmlall"}" /></td>
  </tr>
  <tr>
    <td align="right">Parent:</td>
    <td>
    <select name="category_parent">
      <option value="0">None (Top Level Category)
      
      {foreach from=$root->getDescendents() item=catId}
      {if $catId > 0}
      	{assign var=parent value=$kb->flat_categories.$catId}
        <option value="{$parent->id}" {if $category->parent_id==$parent->id}selected{/if}>
      	{if null != $parent->id}
	      	{if $parent->level > 2}
	      		{* Repeat spacer *}
	      		{math assign=levels equation="x-2" x=$parent->level}
	      		{str_repeat string="&nbsp; &nbsp;" mult=$levels}
	      	{/if}
	      	{if $parent->level > 1}
	      		---&nbsp;
	      	{/if}
			{$parent->name}
		{/if}
	  {/if}
      {/foreach}
    </select>
    </td>
  </tr>
  <tr>
    <td align="right"></td>
    <td><input name="save" value="Save Changes" type="button" onclick="javascript:doFnrCategoryEdit();">
	{assign var=childcount value=$category->getChildCount()}
    {if $childcount == 0}
    	<input name="delete" value="Delete" type="button" onclick="javascript:doFnrCategoryDelete();">
    {/if}</td>
  </tr>
</tbody>
</table>
