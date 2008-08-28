<table border="0" cellpadding="2" cellspacing="0" class="table_orange" width="100%">
	<tr>
	  <td class="bg_orange"><table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
	      <td width="100%"><span class="text_title_white"> Search Builder</span></td>
	  </tr>
	  </table></td>
	</tr>
</table>

<script type="text/javascript">
	var kbSearchWorkflow = new CerQuickWorkflow('kbsearch','kbsearch_searchCriteriaForm');
	{literal}
	kbSearchWorkflow.postResultsAction = function() {
		doSearchCriteriaList('kbsearch');
	}
	{/literal}
</script>

<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr bgcolor="#F0F0FF">
		<td valign="top" width="0%" nowrap="nowrap">
			<form action="#" name="{$label}_searchBuilderForm" id="{$label}_searchBuilderForm" style="margin:0px;">
			<input type="hidden" name="label" value="{$label}">
			<input type="hidden" name="cmd" value="kbsearch_show_criteria">
			Criteria: <select name="criteria" onchange="doGetCriteria('{$label}');">
				<option value="">-- select a criteria --
				<option value="keyword">Text
				<option value="workflow">Workflow
			</select>
			<br>
			</form>
		</td>
		<td valign="top" width="0%" nowrap="nowrap">
			<form action="#" name="{$label}_searchCriteriaForm" id="{$label}_searchCriteriaForm" style="margin:0px;">
			<input type="hidden" name="label" value="{$label}">
			<span id="{$label}_searchCriteriaOptions"></span>
			</form>
		</td>
		<td width="100%"></td>
	</tr>
</table>