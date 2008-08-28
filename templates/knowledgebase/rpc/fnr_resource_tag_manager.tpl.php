<!---<span class="text_title">Tags:</span><br>--->
<input type="hidden" name="resource_id" value="{$resource->id}">
<table cellspacing="0" cellpadding="2" width="100%">
	<tr>
		<td valign="top" width="0%" nowrap="nowrap">
		<b>Current Tags</b><br>
	    <select multiple="multiple" size="15" name="unset_tags[]" style="font-size:10px;">
	    	{foreach from=$tags item=tag name=tags key=tagId}
	    		<option value="{$tagId}">{$tag}</option>
	    	{/foreach}
	    </select>
		</td>
		<td valign="top" width="0%" nowrap="nowrap">
			&nbsp;<br>
			<input type="button" value="&gt;&gt;" onclick="fnrResourceUntag({$resource->id});">
			<input type="button" value="&lt;&lt;" onclick="fnrResourceTag({$resource->id});">
		</td>
		<td valign="top" width="100%">
		<b>Enter tags separated by commas:</b><br>

        <div class="searchdiv">
            <div class="searchautocomplete">
                <input name="tag_input" id="float_tag_input" size="45" />
                <div id="float_searchcontainer" class="searchcontainer"></div>
            </div>
        </div>
		</td>
	</tr>
</table>
