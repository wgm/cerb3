<table border="0" cellpadding="2" cellspacing="2" width="100%">
<tbody>
  <tr>
    <td align="right">New Category Label:</td>
    <td><input size="32" name="category_name" value="" /></td>
  </tr>
  <tr>
    <td align="right">Parent:</td>
    <td>
    <select name="category_parent">
      <option value="0">None (Top Level Category)
      
      {foreach from=$root->getDescendents() item=catId}
      {if $catId > 0}
      	{assign var=parent value=$kb->flat_categories.$catId}
        <option value="{$parent->id}">
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
    <td><input name="save" value="Save Changes" type="button" onclick="javascript:doFnrCategoryNew();"></td>
  </tr>
</tbody>
</table>
