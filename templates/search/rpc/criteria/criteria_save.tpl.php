<input type="hidden" name="cmd" value="search_save">
{if count($saved_searches)}
	<label><input type="radio" name="save_mode" value="1"> 
	Save As: </label><select name="save_as">
	{foreach from=$saved_searches item=search name=searches key=searchId}
		<option value="{$search->id}">{$search->title}
	{/foreach}
	</select>
	<br>
{/if}

<label><input type="radio" name="save_mode" value="0" checked> New: </label><input type="text" name="save_new" value="" size="24"><br>
<input type="button" name="" value="Save" onclick="doSearchCriteriaSave(this.form.label.value);">
<input type="button" name="" value="Cancel" onclick="doSearchCriteriaClearIO(this.form.label.value);">