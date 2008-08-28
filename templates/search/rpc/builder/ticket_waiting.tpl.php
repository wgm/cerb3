<input type="hidden" name="cmd" value="{$cmd}">
<input type="hidden" name="criteria" value="{$criteria}">
<label><input name="crit_waiting" type="radio" value="1" checked>Yes</label><br>
<label><input name="crit_waiting" type="radio" value="0">No</label><br>
<div align="right"><input type="button" value="Add &gt;&gt;" onclick="doSearchCriteriaSet('{$label}');"></div>