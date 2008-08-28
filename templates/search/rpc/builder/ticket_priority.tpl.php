<input type="hidden" name="cmd" value="{$cmd}">
<input type="hidden" name="criteria" value="{$criteria}">In any:<br>
{foreach from=$priorities key=priorityId item=priority}
	<label><input type="checkbox" name="priorities[]" value="{$priorityId}">{$priority}</label><br>
{/foreach}
<div align="right"><input type="button" value="Add &gt;&gt;" onclick="doSearchCriteriaSet('{$label}');"></div>