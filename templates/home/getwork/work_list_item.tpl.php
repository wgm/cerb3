<a name="ticket{$ticket->id}"></a>
<table width="100%" border="0" cellpadding="1" cellspacing="0">
   <tr>
     <td colspan="2"><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td bgcolor="#eeeeee"><img src="includes/images/spacer.gif" width="1" height="1" alt=""></td></tr></table></td>
   </tr>

	<tr>
     <td width="0%" nowrap valign="top">
		{if $ticket->priority <= 0}
			{assign var=priority_icon value="star_alpha.gif"}
		{elseif $ticket->priority <= 25}
			{assign var=priority_icon value="star_grey.gif"}
		{elseif $ticket->priority <= 50}
			{assign var=priority_icon value="star_blue.gif"}
		{elseif $ticket->priority <= 75}
			{assign var=priority_icon value="star_green.gif"}
		{elseif $ticket->priority <= 90}
			{assign var=priority_icon value="star_yellow.gif"}
		{else}
			{assign var=priority_icon value="star_red.gif"}
		{/if}
     
		<img alt="Priority" src="includes/images/icone/16x16/{$priority_icon}" width="16" height="16" border="0" align="middle" />
		{if count($ticket->flags) > 0 && $show_flags}<img alt="A flag" title="Flagged" src="includes/images/icone/16x16/flag_red.gif" width="16" height="16" border="0" align="middle"> {/if}
		{if $ticket->is_waiting_on_customer}<img alt="Waiting on Customer" title="Waiting on Customer" src="includes/images/icone/16x16/alarmclock_pause.gif" width="16" height="16" border="0" align="middle"> {/if}
     </td>
     <td width="100%" style="background-color:#fff">
     	<a href="{"display.php?ticket="|cat:$ticket->id|cer_href}" class="text_ticket_subject">{$ticket->subject|htmlentities}</a> <span class="box_text">#{$ticket->mask}</span>
     </td>
   </tr>
   
   <tr>
   	<td></td>
   	<td style="background-color:#fff" class="box_text">
			Updated {$ticket->date_latest_reply|date_format} by {if !$acl->has_restriction($smarty.const.REST_EMAIL_ADDY,$smarty.const.BITGROUP_2)}{$ticket->address_latest_reply|htmlentities}{else}email{/if} 
   	</td>
   </tr>
   
   <tr>
     <td></td>
     <td><table border="0" cellpadding="0" cellspacing="0" width="100%">
       <tr>
         <td style="background-color:#fff"><span id="getworkpre{$ticket->id}" style="display:none;padding:5px;"></span></td>
       </tr></table></td>
   </tr>

   <tr>
     <td></td>
     <td>
			<a href="javascript:;" onclick="getWorkShowPreview({$ticket->id});" class="link_navmenu"><img alt="Preview" src="includes/images/icone/16x16/window_view.gif" width="16" height="16" border="0" align="middle" style="padding-right:2px;" onmouseover="getWorkToolTip({$ticket->id},'Preview the latest message');" onmouseout="getWorkToolTip({$ticket->id},'');"></a>
     
   		{if $show_workflow && $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
	   	<a href="javascript:void(0);" onclick="getWorkWorkflow({$ticket->id});" class="link_navmenu"><img alt="Workflow" src="includes/images/icone/16x16/gear.gif" width="16" height="16" border="0" align="middle" style="padding-right:2px;" onmouseover="getWorkToolTip({$ticket->id},'Set teams, tags and agents workflow');" onmouseout="getWorkToolTip({$ticket->id},'');"></a>
	   	{/if}

	   	{if $show_take && $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
	   		{if !isset($ticket->flags.$user_id)}
	   			<a href="javascript:void(0);" onclick="preWorkTake({$ticket->id});" class="link_navmenu"><img alt="Flag" src="includes/images/icone/16x16/flag_green.gif" width="16" height="16" border="0" align="middle" style="padding-right:2px;" onmouseover="getWorkToolTip({$ticket->id},'Take (Flag) this ticket');" onmouseout="getWorkToolTip({$ticket->id},'');"></a>
	   		{else}
		   		{*<a href="javascript:void(0);" onclick="getWorkRelease({$ticket->id});" class="link_navmenu"><img alt="Release" src="includes/images/icone/16x16/document_down.gif" width="16" height="16" border="0" align="middle" style="padding-right:2px;" onmouseover="getWorkToolTip({$ticket->id},'Release my flag on ticket');" onmouseout="getWorkToolTip({$ticket->id},'');"></a>*}
					<a href="javascript:void(0);" onclick="toggleDiv('getworkrelease{$ticket->id}');" class="link_navmenu"><img alt="Release" src="includes/images/icone/16x16/document_down.gif" width="16" height="16" border="0" align="middle" style="padding-right:2px;" onmouseover="getWorkToolTip({$ticket->id},'Release my flag on ticket');" onmouseout="getWorkToolTip({$ticket->id},'');"></a>
	   		{/if}
	   	{/if}
	   	
	   	{if $show_close && $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
	   	<a href="javascript:;" onclick="getWorkShowClose({$ticket->id});" class="link_navmenu"><img alt="Close" src="includes/images/icone/16x16/document_error.gif" width="16" height="16" border="0" align="middle" style="padding-right:2px;" onmouseover="getWorkToolTip({$ticket->id},'Close this ticket (with reason)');" onmouseout="getWorkToolTip({$ticket->id},'');"></a>
	   	{/if}
	   	
	   	<span id="getworktip{$ticket->id}" class="box_text" style="border:1px solid #dddddd;padding:2px;background-color:#f5f5f5;visibility:hidden;"></span>
     </td>
   </tr>

   <tr>
     <td></td>
     <td><span id="getworkopts{$ticket->id}"></span></td>
   </tr>

   <tr>
   	<td></td>
   	<td><span id="getworkrelease{$ticket->id}" style="display:none;">
   		<form style="margin:0px;" action="javascript:;" onsubmit="return false;" id="frmrelease{$ticket->id}" name="frmrelease{$ticket->id}">
   		<input type="hidden" name="cmd" value="getwork_release_delay">
   		<input type="hidden" name="id" value="{$ticket->id}">
			<b>How soon should this ticket be suggested to you again?</b><br>
			<input type="text" name="release_delay" size="24" value=""><input type="button" value="Release Ticket" onclick="getWorkReleaseDelay('{$ticket->id}');"><br>
			<br>
			<b>Quick Set:</b> 
			<a href="javascript:;" onclick="frmrelease{$ticket->id}.release_delay.value='now';getWorkReleaseDelay('{$ticket->id}');">now</a> 
			| <a href="javascript:;" onclick="frmrelease{$ticket->id}.release_delay.value='+15 minutes';getWorkReleaseDelay('{$ticket->id}');">15 minutes</a> 
			| <a href="javascript:;" onclick="frmrelease{$ticket->id}.release_delay.value='+30 minutes';getWorkReleaseDelay('{$ticket->id}');">30 minutes</a> 
			| <a href="javascript:;" onclick="frmrelease{$ticket->id}.release_delay.value='+1 hour';getWorkReleaseDelay('{$ticket->id}');">1 hour</a> 
			| <a href="javascript:;" onclick="frmrelease{$ticket->id}.release_delay.value='+1 day';getWorkReleaseDelay('{$ticket->id}');">1 day</a> 
			<br>
			<br>
			<span class="cer_footer_text"><i>(dates can be entered relatively "+3 hours", "+15 minutes", "Monday", "Next Thursday" or absolutely "2006-12-31 08:00")</i></span>
			</form>
   	</span></td>
   </tr>
   
   <tr>
   	<td></td>
   	<td><span id="getworkclose{$ticket->id}" style="display:none;">
			<b>Reason for closing?</b>
			<a href="javascript:;" onclick="getWorkClose('{$ticket->id}');" class="link_navmenu"><img alt="Close Ticket" src="includes/images/icone/16x16/document_ok.gif" width="16" height="16" border="0" align="middle" onmouseover="getWorkToolTip('{$ticket->id}','Mark ticket as Resolved');" onmouseout="getWorkToolTip('{$ticket->id}','');" /></a> 
			<a href="javascript:;" onclick="getWorkSpam('{$ticket->id}');" class="link_navmenu"><img alt="Report Spam" src="includes/images/icone/16x16/spam.gif" width="16" height="16" border="0" align="middle" onmouseover="getWorkToolTip('{$ticket->id}','Report ticket as Spam');" onmouseout="getWorkToolTip('{$ticket->id}','');" /></a> 
			{if $acl->has_priv($smarty.const.PRIV_TICKET_DELETE)}
				<a href="javascript:;" onclick="getWorkTrash('{$ticket->id}');" class="link_navmenu"><img alt="Send to Trash" src="includes/images/icone/16x16/garbage_empty.gif" width="16" height="16" border="0" align="middle" onmouseover="getWorkToolTip('{$ticket->id}','Send ticket to Trash');" onmouseout="getWorkToolTip('{$ticket->id}','');" /></a>   	
			{/if}
   	</span></td>
   </tr>
   
   <tr>
     <td></td>
     <td><img src="includes/images/spacer.gif" width="1" height="5" alt=""></td>
   </tr>
   
</table>
