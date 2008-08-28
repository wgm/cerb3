<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr> 
    <td colspan="2" valign="top">
    	<span class="text_title">Ticket #{$o_ticket->ticket_mask_id}: {$wsticket->subject|short_escape}</span>
    	<br>
    	<b>Status:</b> 
	    {if !$wsticket->is_closed && !$wsticket->is_deleted}open
	    {elseif $wsticket->is_closed && !$wsticket->is_deleted}closed
	    {elseif $wsticket->is_deleted}deleted{/if}
	    &nbsp; &nbsp;
	   <b>Priority:</b> 
    	{if empty($wsticket->priority)}<img src="includes/images/icone/16x16/star_alpha.gif" width="16" height="16" border="0" title="None" alt="No Priority"> None
    	{elseif $wsticket->priority > 0 && $wsticket->priority <= 25}<img src="includes/images/icone/16x16/star_grey.gif" width="16" height="16" border="0" title="Lowest" alt="Lowest Priority"> Lowest
    	{elseif $wsticket->priority > 25 && $wsticket->priority <= 50}<img src="includes/images/icone/16x16/star_blue.gif" width="16" height="16" border="0" title="Low" alt="Low Priority"> Low
    	{elseif $wsticket->priority > 50 && $wsticket->priority <= 75}<img src="includes/images/icone/16x16/star_green.gif" width="16" height="16" border="0" title="Moderate" alt="Moderate Priority"> Moderate
    	{elseif $wsticket->priority > 75 && $wsticket->priority <= 90}<img src="includes/images/icone/16x16/star_yellow.gif" width="16" height="16" border="0" title="High" alt="High Priority"> High
    	{elseif $wsticket->priority > 90 && $wsticket->priority <= 100}<img src="includes/images/icone/16x16/star_red.gif" width="16" height="16" border="0" title="Highest" alt="Highest Priority"> Highest
    	{/if}
    	&nbsp; &nbsp;
	   <b>Due:</b> 
    	{if $wsticket->date_due->mktime_datetime}{$wsticket->date_due->getUserDate()}{else}Not set{/if}
    	<br>
    	<a href="{$urls.tab_display}#latest" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_JUMP_TO_LATEST|lower}</a>
    	{*if $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)*}
   		  | <a href="my_cerberus.php?mode=layout#layout_display" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_CUSTOMIZE_LAYOUT|lower}</a>
   	{*/if*}
    </td>
  </tr>
  <tr> 
    <td valign="top">
    {include file="display/display_ticket_navbar.tpl.php"}
	</td>
  </tr>
</table>