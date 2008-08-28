<table border="0" cellpadding="2" cellspacing="0" class="table_blue" bgcolor="#F0F0FF" width="100%">
      <tr>
        <td class="bg_blue"><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td><span class="text_title_white"><img src="includes/images/icone/16x16/businessman2.gif" alt="contact" width="16" height="16"/> Contact</span></td>
        </tr>
        </table></td>
      </tr>
      {* Need to not show names if we're not showing email addresses either. *}
      {if $acl->has_restriction($smarty.const.REST_EMAIL_ADDY,$smarty.const.BITGROUP_2)}
      <tr><td>Access denied.</td></tr>
      {else}
	      {if $o_ticket->sla->pub_user}
		      <tr>
		        <td valign="top" class="orange_heading"> Name:</td>
		      </tr>
		      <tr>
		        <td valign="top"><a href="{$o_ticket->sla->pub_user->url_view}" class="link_ticket_cmd">{$o_ticket->sla->pub_user->account_name_first|short_escape}{if $o_ticket->sla->pub_user->account_name_last} {$o_ticket->sla->pub_user->account_name_last|short_escape}{/if}</a></td>
		      </tr>
		      <tr>
		        <td valign="top" class="orange_heading">Company:</td>
		      </tr>
		      <tr>
		        <td valign="top"><a href="#" class="link_ticket_cmd">
					{if !empty($o_ticket->sla->pub_user->company_ptr) }
						<a href="{$o_ticket->sla->pub_user->company_ptr->url_view}" class="link_ticket_cmd">{$o_ticket->sla->pub_user->company_ptr->company_name}</a>
					{else}
						{$smarty.const.LANG_DISPLAY_NO_COMPANY}
					{/if}
		        </td>
		      </tr>
		      <tr>
		        <td valign="top" class="orange_heading">SLA:</td>
		      </tr>
		      <tr>
		        <td valign="top">
			      {if !empty($o_ticket->sla->sla_plan) }
			      	{$o_ticket->sla->sla_plan->sla_name|short_escape}
			      {else}
			      	<span>{$smarty.const.LANG_DISPLAY_NO_SLA_PLAN}</span>
			      {/if}
		       </td>
		      </tr>
		      
		   {else} {* No company *}
				<tr> 
			      <td>
			      	<b>{*$smarty.const.LANG_DISPLAY_ADDRESS_NOT_ASSIGNED*}This address isn't assigned<br>to a contact or company.</b><br>
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
			      	<a href="{$urls.contact_add}" target="_blank" title="{if !$acl->has_restriction($smarty.const.REST_EMAIL_ADDY,$smarty.const.BITGROUP_2)}{$o_ticket->requestor_address->address}{/if}">{$smarty.const.LANG_DISPLAY_ADDRESS_NOT_ASSIGNED_CREATE}<br>
			      	{$smarty.const.LANG_WORD_REQUESTER|lower}</a><br>
			      </td>
			    </tr>
			   {/if}
	      {/if}
	  {/if} {* end check for permission to display client email addresses *}
</table>
<br>