<input type="hidden" name="cmd" value="{$cmd}">
<input type="hidden" name="criteria" value="{$criteria}">
<input type="text" name="crit_subject" value="" size="20" onkeydown="doSearchEnterKiller(this.form);"><input type="button" value="Add &gt;&gt;" onclick="doSearchCriteriaSet('{$label}');">
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
			<B>Must have:</B>
		</td>
		<td class="searchSyntaxText" nowrap>
			<i>+help +phone</i>
		</td>
	</tr>
	<tr>
		<td class="searchSyntaxText">
			<B>Can't have:</B>
		</td>
		<td class="searchSyntaxText" nowrap>
			<i>-spam</i>
		</td>
	</tr>
	<tr>
		<td class="searchSyntaxText">
			<B>Partial:</B>
		</td>
		<td class="searchSyntaxText" nowrap>
			<i>web*</i>
		</td>
	</tr>
	<tr>
		<td class="searchSyntaxText">
			<B>Any word:</B>
		</td>
		<td class="searchSyntaxText" nowrap>
			<i>help support assistance</i>
		</td>
	</tr>
	<tr>
		<td class="searchSyntaxText">
			<B>Mixed:</B>
		</td>
		<td class="searchSyntaxText" nowrap>
			<i>+help problem issue -phone</i>
		</td>
	</tr>
</table>
