<input type="hidden" name="cmd" value="{$cmd}">
<input type="hidden" name="criteria" value="{$criteria}">
<label><input name="crit_has_teams" type="radio" value="1">Yes</label><br>
<label><input name="crit_has_teams" type="radio" value="0" checked>No</label><br>
<div align="right"><input type="button" value="Add &gt;&gt;" onclick="doSearchCriteriaSet('{$label}');"></div>