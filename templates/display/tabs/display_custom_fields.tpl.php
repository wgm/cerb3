
	{foreach from=$field_handler->group_instances item=group name=group}
        
	  	<tr>
          <td colspan="2">
          	<table cellspacing="0" cellpadding="0" border="0" width="100%">
          		<tr >
          			<td align="left"><b>{$group->group_name} (bound to {$group->entity_name})</b></td>
          			<td align="right">
          				Delete:
          				<input type="checkbox" name="instance_ids[]" value="{$group->group_instance_id}">
          			</td>
          		</tr>
          	</table>
          </td>
        </tr>
        
        {if count($group->fields) }
          
            {* Custom Fields Loop *}
            {foreach from=$group->fields item=field name=field}
                <input type="hidden" name="g_{$group->group_instance_id}_field_ids[]" value="{$field->field_id}">
                <tr> 
                  <td width="10%">{$field->field_name|short_escape}:</td>
                  
                  <td width="80%">
                  {if $o_ticket->writeable !== false}
                  
                  	{if $field->field_type == "S"}
                    	<input type="text" name="g_{$group->group_instance_id}_field_{$field->field_id}" size="65" value="{$field->field_value|short_escape}" >
                    {/if}

                  	{if $field->field_type == "E"}
						<input type="text" name="g_{$group->group_instance_id}_field_{$field->field_id}" size="20" value="{$field->field_value|cer_dateformat}">
			          	<span class="cer_footer_text" >
							Dates can be entered relatively (e.g. "-1 day", "+1 week", "now")	or absolutely (e.g. "12/31/06 08:00:00")
			          	</span>
                    {/if}

                  	{if $field->field_type == "T"}
                    	<textarea cols="65" rows="3" name="g_{$group->group_instance_id}_field_{$field->field_id}" wrap="virtual" >{$field->field_value|short_escape}</textarea><br>
                    	<span >(maximum 255 characters)</span>
                    {/if}
                    
                  	{if $field->field_type == "D"}
                    	<select name="g_{$group->group_instance_id}_field_{$field->field_id}" >
	                      <option value="">
	                      {html_options options=$field->field_options selected=$field->field_value}
                        </select>
                    {/if}
                    
                  {/if}
                  </td>
                </tr>
            {/foreach}
            
            <input type="hidden" name="group_instances[]" value="{$group->group_instance_id}">
            <input type="hidden" name="entity_codes[]" value="{$group->entity_code}">
            <input type="hidden" name="entity_indexes[]" value="{$group->entity_index}">
        {/if}
          
	  <tr> 
	  	<td colspan="2"><img src="images/spacer.gif" width="1" height="4" alt=""></td>
	  </tr>
          
	  <tr> 
	  	<td colspan="2"><img src="images/spacer.gif" width="1" height="2" alt=""></td>
	  </tr>
          
  {/foreach}

