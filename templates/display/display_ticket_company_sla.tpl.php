{literal}
<script type="text/javascript">
	function toggleDisplayContact() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_contact").style.display=="block") {
				document.getElementById("ticket_display_contact").style.display="none";
				document.getElementById("ticket_display_contact_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_contact.value = 0;
			}
			else {
				document.getElementById("ticket_display_contact").style.display="block";
				document.getElementById("ticket_display_contact_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_contact.value = 1;
			}
		}
	}
</script>
{/literal}

<img alt="Contact" id="ticket_display_contact_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_contact}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" onclick="javascript:toggleDisplayContact();" onmouseover="javascript:this.style.cursor='hand';">
<span class="link_ticket">{$smarty.const.LANG_DISPLAY_COMPANYCONTACT}</span><br>
<table cellspacing="0" cellpadding="0" width="100%"><tr><td bgcolor="#DDDDDD"><img src="includes/images/spacer.gif" height="1" width="1" alt=""></td></tr></table>
<div id="ticket_display_contact" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_contact}block{else}none{/if};">
<table border="0" cellpadding="1" cellspacing="1" class="ticket_display_table" width="100%">

{if $o_ticket->sla->pub_user}
	<tr> 
      <td nowrap>
      	<b>{$smarty.const.LANG_WORD_COMPANY}:</b>
      </td>
	      <td>
	      {if !empty($o_ticket->sla->pub_user->company_ptr) }
	      	{$o_ticket->sla->pub_user->company_ptr->company_name}
	      	<span>{$smarty.const.LANG_DISPLAY_SHOW_COMPANY_DETAILS}</a>)</span>
	      {else}
	      	<span>{$smarty.const.LANG_DISPLAY_NO_COMPANY}</span>
	      {/if}
	      </td>
    </tr>
 	
    <tr>
      <td nowrap>
      	<b>{$smarty.const.LANG_DISPLAY_SLA_COVERAGE}</b>
      </td>
      <td width="95%" valign="middle">
      {if !empty($o_ticket->sla->sla_plan) }
      	<span>{$o_ticket->sla->sla_plan->sla_name|short_escape}</span>
      {else}
      	<span>{$smarty.const.LANG_DISPLAY_NO_SLA_PLAN}</span>
      {/if}
      </td>
    </tr>
    
    {if !empty($o_ticket->sla->sla_plan) }
    <tr>
      <td nowrap>
      	<b>{$smarty.const.LANG_WORD_EXPIRES}</b>
      </td>
      <td width="95%" valign="middle">
      	
      </td>
    </tr>
    {/if}
    
 	<tr> 
      <td nowrap>
      	<b>{$smarty.const.LANG_WORD_CONTACT}:</b>
      </td>
      <td width="95%" valign="middle">
      	<span>{$o_ticket->sla->pub_user->account_name_first|short_escape} {$o_ticket->sla->pub_user->account_name_last|short_escape}</cer_maintable_text>
      	<span>{$smarty.const.LANG_DISPLAY_SHOW_CONTACT_DETAILS}</a>)</span>
      </td>
    </tr>

    {else}

		<tr> 
	      <td>
	      	<b>{$smarty.const.LANG_DISPLAY_ADDRESS_NOT_ASSIGNED}</b><br>
	      </td>
	    </tr>
		<tr> 
	      <td valign="middle">
	      	<a href="{$urls.clients}" target="_blank">{$smarty.const.LANG_DISPLAY_ADDRESS_NOT_ASSIGNED_SEARCH}</a><br>
	      </td>
	    </tr>
	   
	   {if $acl->has_priv($smarty.const.PRIV_CONTACT_CHANGE)}
	     
		<tr> 
	      <td valign="middle">
	      	<a href="{$urls.contact_add}" target="_blank">{$smarty.const.LANG_DISPLAY_ADDRESS_NOT_ASSIGNED_CREATE} {if $acl->has_restriction($smarty.const.REST_EMAIL_ADDY,$smarty.const.BITGROUP_2)}{$smarty.const.LANG_WORD_REQUESTER}{else}{$o_ticket->requestor_address->address}{/if}</a><br>
	      </td>
	    </tr>
	    
	   {/if}
    
	{/if}
 	
</table>

{if $o_ticket->sla->pub_user && !empty($o_ticket->sla->sla_plan) && !empty($o_ticket->sla->sla_queue_ptr) }
	
	<br>
	
	<table cellpadding="1" cellspacing="1" border="0" width='100%'>
		<tr>
			<td>
		      	<span>
					Queue SLA Coverage: {$o_ticket->sla->sla_plan->sla_name|short_escape}
		      	</span>
			</td>
		</tr>
	</table>
	
	{if !empty($o_ticket->sla->sla_plan) && !empty($o_ticket->sla->sla_queue_ptr)}
	
	<table border="0" cellpadding="1" cellspacing="1">
	
		<tr>
			<td>{$smarty.const.LANG_DISPLAY_SLABOX_TABLE_QUEUE}</td>
			<td>{$smarty.const.LANG_DISPLAY_SLABOX_TABLE_QUEUEMODE}</td>
			<td>{$smarty.const.LANG_DISPLAY_SLABOX_TABLE_SLASCHEDULE}</td>
			<td>{$smarty.const.LANG_DISPLAY_SLABOX_TABLE_TARGETRESPONSETIME}</td>
		</tr>
	
		{if !empty($o_ticket->sla->sla_queue_ptr) }
			<tr>
				<td><B>{$o_ticket->sla->sla_queue_ptr->queue_name|short_escape}</B></td>
				<td>{$o_ticket->sla->sla_queue_ptr->queue_mode}</td>
				<td>{$o_ticket->sla->sla_queue_ptr->queue_schedule_name}</a></td>
				<td>{$o_ticket->sla->sla_queue_ptr->queue_response_time}{$smarty.const.LANG_DATE_SHORT_HOURS_ABBR}</td>
			</tr>
		{/if}
		
	</table>		
	
	{/if}

{/if}

</div>

<br>

