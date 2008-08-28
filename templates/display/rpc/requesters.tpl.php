<input type="hidden" name="id" value="{$ticket->id}">
<table border="0" cellpadding="2" cellspacing="0" class="table_purple" width="100%">
      <tr>
        <td class="bg_purple"><table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td><span class="text_title_white"><img src="includes/images/icone/16x16/mail2.gif" alt="An envelope" width="16" height="16" /> Requesters
              </span></td>
              </tr>
        </table></td>
      </tr>
      <tr>
        <td bgcolor="#F6F3FF"><table width="100%" border="0" cellspacing="0" cellpadding="0">
        		{if $ticket}
				{foreach from=$ticket->getRequesters() item=req key=reqId}
              <tr>
                <td class="workflow_item">{$req}</td>
                <td><a href="javascript:;" onclick="requesterDel({$ticket->id},{$reqId});" class="text_ticket_links" title="Remove {$req}"><b>X</b></a></td>
              </tr>
            {/foreach}
            {/if}
            <tr>
            	<td colspan="2">
            		<input type="text" name="requester_add" size="15" value=""><input type="button" onclick="requesterAdd({$ticket->id});" value="+">
            	</td>
            </tr>
          </table></td>
      </tr>
</table>
<br>