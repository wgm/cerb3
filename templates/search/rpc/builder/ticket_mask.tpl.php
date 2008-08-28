<input type="hidden" name="cmd" value="{$cmd}">
<input type="hidden" name="criteria" value="{$criteria}">
<input type="text" name="crit_mask" value="" size="20" onkeydown="doSearchEnterKiller(this.form);"><br>
<span class="cer_footer_text">(you can search by partial ticket masks or full ticket IDs)</span>
<div align="right"><input type="button" value="Add &gt;&gt;" onclick="doSearchCriteriaSet('{$label}');"></div>