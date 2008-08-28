{if count($tickets)}
{foreach from=$tickets item=ticket name=tickets}
<div id="getwork{$ticket->id}">
<table width="100%" border="0" cellpadding="1" cellspacing="0">
   <tr>
     <td width="0%" nowrap rowspan="2" valign="top">
	     {if $ticket->priority <= 0}
		     	<img src="includes/images/spacer.gif" alt="*" width="16" height="16" align="middle" title="None" alt="No Priority" />
	     {elseif $ticket->priority <= 25}
		     	<img src="includes/images/icone/16x16/star_grey.gif" alt="*" width="16" height="16" align="middle"  title="Lowest" alt="Lowest Priority" />
	     {elseif $ticket->priority <= 50}
		     	<img src="includes/images/icone/16x16/star_blue.gif" alt="*" width="16" height="16" align="middle" title="Low" alt="Low Priority" />
	     {elseif $ticket->priority <= 75}
		     	<img src="includes/images/icone/16x16/star_green.gif" alt="*" width="16" height="16" align="middle" title="Moderate" alt="Moderate Priority" />
	     {elseif $ticket->priority <= 90}
		     	<img src="includes/images/icone/16x16/star_yellow.gif" alt="*" width="16" height="16" align="middle" title="High" alt="High Priority" />
	     {else}
		     	<img src="includes/images/icone/16x16/star_red.gif" alt="*" width="16" height="16" align="middle" title="Highest" alt="Highest Priority" />
	     {/if}
     </td>
     <td width="100%" style="background-color:#fff">
     	<a href="{"display.php?ticket="|cat:$ticket->id|cer_href}" class="text_ticket_subject">{$ticket->subject|htmlentities}</a> <span class="box_text">#{$ticket->mask}</span>
     </td>
   </tr>
   <tr>
     <td style="background-color:#fff">
     <span class="box_text">{$ticket->action_timestamp|date_format:"%d-%b-%y %I:%M%p"}:</span> {$ticket->action}
     </td>
   </tr>
   <tr>
     <td colspan="2"><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td bgcolor="#eeeeee"><img src="includes/images/spacer.gif" width="1" height="1" alt=""></td></tr></table></td>
   </tr>
</table>
</div>
{/foreach}
{/if}