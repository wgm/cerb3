<input type="hidden" name="cmd" value="{$cmd}">
<input type="hidden" name="criteria" value="{$criteria}">
<input type="text" name="keyword" value="" size="20" onkeydown="doSearchEnterKiller(this.form);"><input type="button" value="Add &gt;&gt;" onclick="doSearchCriteriaSet('{$label}');">
<br>
<br>
<table border="0" cellpadding="2" cellspacing="0">
	<tr>
		<td colspan="2" class="boxtitle_green_glass">
			Search Syntax Guide
		</td>
	</tr>
	<tr>
		<td class="searchSyntaxText">
			<B>Wildcards:</B>
		</td>
		<td class="searchSyntaxText" nowrap>
			<i>*web*</i>
		</td>
	</tr>
</table>
