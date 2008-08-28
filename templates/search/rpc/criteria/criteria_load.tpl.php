<input type="hidden" name="cmd" value="search_load">
{if count($saved_searches)}
	Load: <select name="load_id">
	{foreach from=$saved_searches item=search name=searches key=searchId}
		<option value="{$search->id}">{$search->title}
	{/foreach}
	</select>
	
	<br>
	<input type="button" name="" value="Load" onclick="this.form.cmd.value='search_load';doSearchCriteriaLoad(this.form.label.value);">
	<input type="button" name="" value="Delete" onclick="this.form.cmd.value='search_delete';doSearchCriteriaDelete(this.form.label.value);">
	<input type="button" name="" value="Cancel" onclick="doSearchCriteriaClearIO(this.form.label.value);">
{else}
	You have no saved searches!
{/if}
