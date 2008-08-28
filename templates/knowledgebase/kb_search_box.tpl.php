{assign var="col_span" value="2"}

<br>

<table>
	<tr>
		<td valign="top">
			<span id="kbsearch_searchCriteriaList"></span>
			<script type="text/javascript">
				{literal}
				YAHOO.util.Event.addListener(window,"load",function() {
					doSearchCriteriaList('kbsearch');
				});
				{/literal}
			</script>
		</td>
		<td valign="top">
			{include file="knowledgebase/search/search_builder.tpl.php" label="kbsearch"}
		</td>
	</tr>
</table>
