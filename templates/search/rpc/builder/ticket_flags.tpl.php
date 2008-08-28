<input type="hidden" name="cmd" value="{$cmd}">
<input type="hidden" name="criteria" value="{$criteria}">
<!---<label><input name="crit_flag" type="radio" value="-1" checked>None</label><br>--->

<label><input type="radio" name="flag_mode" value="0" > <b>Not flagged</b></label><br>
<label><input type="radio" name="flag_mode" value="1" checked> <b>Selected agents:</b></label><br>
{foreach from=$agents item=agent key=agentId name=agents}
&nbsp; &nbsp;<label><input name="crit_flag[]" type="checkbox" value="{$agentId}">{$agent->getRealName()}</label><br>
{/foreach}
<div align="right"><input type="button" value="Add &gt;&gt;" onclick="doSearchCriteriaSet('{$label}');"></div>