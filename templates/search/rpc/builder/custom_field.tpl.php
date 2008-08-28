<input type="hidden" name="cmd" value="{$cmd}">
<input type="hidden" name="criteria" value="{$criteria}">
<input type="hidden" name="field_id" value="{$field_id}">
<input type="hidden" name="type" value="{$type}">

{if $type == "S" || $type == "T"}
	<input type="text" name="crit_custom_field" value="" size="20" onkeydown="doSearchEnterKiller(this.form);"><br>
{elseif $type == "E"}
	<table>
		<tr>
			<td>From:</td>
			<td><input type="text" name="from" value="" size="20" onkeydown="doSearchEnterKiller(this.form);"></td>
		</tr>
		<tr>
			<td>To:</td>
			<td><input type="text" name="to" value="" size="20" onkeydown="doSearchEnterKiller(this.form);"></td>
		</tr>
	</table>
	Dates can be entered relatively (e.g. "-1 day", "+1 week", "now")<br>
	or absolutely (e.g. "12/31/06 08:00:00")
{elseif $type == "D"}
	In any:<br>
	{foreach from=$options item=option key=optionId}
		<label><input type="checkbox" name="crit_custom_field_opts[]" value="{$optionId}">{$option}</label><br>
	{/foreach}
{/if}

<div align="right"><input type="button" value="Add &gt;&gt;" onclick="doSearchCriteriaSet('{$label}');"></div>