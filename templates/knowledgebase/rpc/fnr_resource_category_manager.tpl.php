{assign var=descendents value=$kb_root->getDescendents()}
<span class="text_title">Categories:</span>
{if $acl->has_priv($smarty.const.PRIV_KB_EDIT)}<a href="javascript:popupResourceCategories();" class="cer_knowledgebase_link">manage</a> | {/if}
<a href="javascript:getFnrResourceCategoryManager({$resource->id},'{$div}');" class="cer_knowledgebase_link">reload</a>
<br>
<input type="hidden" name="resource_id" value="{$resource->id}">
<table cellspacing="0" cellpadding="2">
	<tr>
		<td>
		<b>Available Categories</b><br>
	    <select multiple="multiple" size="15" name="set_categories[]" style="font-size:10px;">
{foreach from=$descendents item=catId}
{if $catId > 0}
{assign var=parent value=$kb->flat_categories.$catId}
{if null != $parent->id && !isset($resource->categories.$catId)}
<option value="{$catId}">{assign var=trail value=$parent->getAncestors(1)}{foreach from=$trail item=t name=trail}{assign var=tcat value=$kb->flat_categories.$t}{if $tcat->id}{$tcat->name}{if !$smarty.foreach.trail.last} : {/if}{/if}{/foreach}</option>
{/if}
{/if}
{/foreach}
</select>
		</td>
		<td>
			<input type="button" value="&gt;&gt;" onclick="setFnrResourceCategories({$resource->id},'{$div}');">
			<input type="button" value="&lt;&lt;" onclick="unsetFnrResourceCategories({$resource->id},'{$div}');">
		</td>
		<td>
		<b>Assigned Categories</b><br>
<select multiple="multiple" size="15" name="unset_categories[]" style="font-size:10px;">
{foreach from=$descendents item=catId}
{if $catId > 0}
{assign var=parent value=$kb->flat_categories.$catId}
{if null != $parent->id && isset($resource->categories.$catId)}
<option value="{$catId}">{assign var=trail value=$parent->getAncestors(1)}{foreach from=$trail item=t name=trail}{assign var=tcat value=$kb->flat_categories.$t}{if $tcat->id}{$tcat->name}{if !$smarty.foreach.trail.last} : {/if}{/if}{/foreach}</option>
{/if}
{/if}
{/foreach}
</select>
		</td>
	</tr>
</table>
