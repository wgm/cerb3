{if $oThread->thread_id == $o_ticket->max_thread_id}<a name="latest"></a>{/if}

<div id="thread_{$oThread->thread_id}" {if $oThread->is_hidden}style="display:none;"{else}style="display:block;"{/if}>

<a name="thread_{$oThread->thread_id}"></a>
		<!---
		&nbsp;{$oThread->thread_display_date} 
		{$smarty.const.LANG_WORD_BY} 
		{$oThread->thread_display_author} ({$oThread->thread_type}) 
		{if $oThread->thread_author->address_banned} (BLOCKED) {/if}
		{if $oThread->thread_time_worked} ({$smarty.const.LANG_DISPLAY_TIME_WORKED}: {$oThread->thread_time_worked}){/if}
		--->
  
    {* Thread Content Block *}
    <table width="100%" border="0" cellspacing="0" cellpadding="0" bordercolor="#666666" class="table_comment">
      <tr>
        <td>
<img alt="An inbox" src="includes/images/crystal/16x16/{if $oThread->is_agent_message}outbox.gif{else}inbox.gif{/if}" width="16" height="16"> <b>{$smarty.const.LANG_WORD_FROM}: {$oThread->thread_display_author}</b><br>
{if empty($oThread->thread_subject)}
{$smarty.const.LANG_WORD_SUBJECT}: {$o_ticket->ticket_subject|short_escape|replace:"  ":"&nbsp;&nbsp;"|makehrefs:true:"cer_display_emailText"|regex_replace:"/(\n|^) /":"\n&nbsp;"|nl2br}<br>
{else}
{$smarty.const.LANG_WORD_SUBJECT}: {$oThread->thread_subject|short_escape|replace:"  ":"&nbsp;&nbsp;"|makehrefs:true:"cer_display_emailText"|regex_replace:"/(\n|^) /":"\n&nbsp;"|nl2br}<br>
{/if}

{if !$acl->has_restriction($smarty.const.REST_EMAIL_ADDY,$smarty.const.BITGROUP_2)}
  {if !empty($oThread->thread_to)}
    To: {$oThread->thread_to|short_escape}<br>
  {/if}
  {if !empty($oThread->thread_cc)}
    Cc: {$oThread->thread_cc|short_escape}<br>
  {/if}
  {if !empty($oThread->thread_bcc)}
    Bcc: {$oThread->thread_bcc|short_escape}<br>
  {/if}
  {if !empty($oThread->thread_replyto)}
    Reply-To: {$oThread->thread_replyto|short_escape}<br>
  {/if}
{/if}

Date: {$oThread->thread_date_rfc}<br>

<br>
{if empty($cut_line)}
  {$oThread->thread_content|short_escape|replace:"  ":"&nbsp;&nbsp;"|makehrefs:true:"cer_display_emailText"|regex_replace:"/(\n|^) /":"\n&nbsp;"|nl2br}
{else}
  {$oThread->thread_content_new|short_escape|replace:"  ":"&nbsp;&nbsp;"|makehrefs:true:"cer_display_emailText"|regex_replace:"/(\n|^) /":"\n&nbsp;"|nl2br}
  {if !empty($oThread->thread_content_old)}
    <a href="javascript:toggleQuotedText({$oThread->thread_id});" class="cer_footer_text">{$smarty.const.LANG_DISPLAY_THREAD_TOGGLE_QUOTED}</a>
    <div id="thread_{$oThread->thread_id}_quoted" style="display: none;">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td>
            {$oThread->thread_content_old|short_escape|replace:"  ":"&nbsp;&nbsp;"|makehrefs:true:"cer_display_emailText"|regex_replace:"/(\n|^) /":"\n&nbsp;"|nl2br}
          </td>
        </tr>
      </table>
    </div>
  {/if}
{/if}
		</td>
      </tr>
		<tr>
			<td>
			  {if $suppress_links === false}
			  
			  <table width="100%" border="0" cellspacing="0" cellpadding="0">
			  
			  	{* Show Thread Action Links *}
				  <tr>
					<td align="left">
						{if  $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
								<!---&nbsp;[ <a href="{$oThread->url_reply}" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_REPLY}</a> ]--->
			
								[ <a href="javascript:;" onclick="displayReply({$oThread->thread_id});" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_REPLY}</a> ]
								
								{if $o_ticket->properties->show_forward_thread === true}
									[ <a href="javascript:;" onclick="displayForward({$oThread->thread_id});" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_FORWARD}</a> ]
								{/if}
								
							&nbsp;[ <a href="javascript:;" onclick="displayComment({$oThread->thread_id});" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_COMMENT}</a> ]
						{/if}
						
						[ <a href="javascript:toggleThreadOptions({$oThread->thread_id});" class="link_ticket_cmd">More Options...</a> ]
					</td>
			   	  </tr>
			    </table>
			   	    
				<div id="thread_{$oThread->thread_id}_options" style="display:none;">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
				  <tr>
					<td align="left">
						&nbsp;<span>options: </span>
						{if  $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE)}
						
								{if $o_ticket->properties->show_forward_thread === true}
									<!---[ <a href="{$oThread->url_forward}" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_FORWARD}</a> ]--->
									[ <a href="{$oThread->url_bounce}" class="link_ticket_cmd">Redirect</a> ]
								{/if}
							
							{if $oThread->is_hidden}
								[ <a href="{$oThread->url_unhide}" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_UNHIDE}</a> ]
							{else}
								[ <a href="{$oThread->url_hide}" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_HIDE}</a> ]
							{/if}
				
							{if $oThread->url_add_req}
								[ <a href="{$oThread->url_add_req}" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_ADD_TO_REQUESTERS}</a> ]
							{/if}
				
							{if $oThread->url_block_sender}
								[ <a href="{$oThread->url_block_sender}" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_BLOCK_SENDER}</a> ]
							{/if}
				
							{if $oThread->url_unblock_sender}
								[ <a href="{$oThread->url_unblock_sender}" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_UNBLOCK_SENDER}</a> ]
							{/if}
				
							[ <a href="{$oThread->url_strip_html}" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_STRIP_HTML}</a> ]
							
							[ <a href="{$oThread->url_track_time_entry}" class="link_ticket_cmd">Add Time Worked</a> ]
							
							{* No splitting the first thread off of a ticket.  That breaks stuff. *}
							{if $oThread->thread_id != $o_ticket->min_thread_id}
								[ <a href="{$oThread->url_split_to_new_ticket}" class="link_ticket_cmd">Split to New Ticket</a> ]
							{/if}
							
						{/if}
			            
						[ <a href="javascript: printTicket('{$oThread->print_thread}');" class="link_ticket_cmd">{$smarty.const.LANG_THREAD_PRINT}</a> ]
						
					</td>
			   	  </tr>
			    </table>
				</div>
				
			  {/if} {* end suppress links *}
			  
			  	{if !empty($thread_action) && isset($thread) && $oThread->thread_id == $thread}
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
			  		{if $thread_action == "forward"}
			  			{include file="display/actions/thread_action_forward.tpl.php"}
					{/if}
			   		{if $thread_action == "bounce"}  {* [jxdemel] Bounce Feature *}
			   			{include file="display/actions/thread_action_bounce.tpl.php"}
			 		{/if}
					{if $thread_action == "strip_html"}
						{include file="display/actions/thread_action_strip_html.tpl.php"}
					{/if}
			    </table>
			   	{/if}
			
			</td>
		</tr>

    {* Thread Errors *}
    {if count($oThread->thread_errors) }
    	<tr>
    		<td>
	        	&nbsp;Message Errors:&nbsp;
	        	{if $suppress_links === false}
	        		<a href="{$oThread->url_clear_errors}">Clear</a>
	        	{/if}
    		</td>
    	</tr>
      <tr>
    	<td>
		    {section name=error loop=$oThread->thread_errors->errors}
		      {$oThread->thread_errors->errors[error]}
		    {/section}
    	 	<br>
    	</td>
	  </tr>
    {/if}

    
    {* File Attachments *}
    {if count($oThread->file_attachments)}
    	<tr>
    		<td>
    		<span><b>{$smarty.const.LANG_DISPLAY_ATTACHMENTS}</b>: </span>
    		{section name=file loop=$oThread->file_attachments}
    		{* if(preg_match("/MSIE/", $_SERVER["HTTP_USER_AGENT"])) *}		
		    	<a href="{$oThread->file_attachments[file]->file_url}">
		    	{$oThread->file_attachments[file]->file_name} ({$oThread->file_attachments[file]->display_size})</a>&nbsp;&nbsp;
		    {/section}
    		</td>
    	</tr>
    {/if}
    
    </table>

   <form id="displayreply{$oThread->thread_id}" action="display.php" enctype="multipart/form-data" method="post">
		<input type="hidden" name="form_submit" value="reply">
		<input type="hidden" name="ticket" value="{$ticket}">
		<input type="hidden" name="ticketId" value="{$ticket}">
		<input type="hidden" name="threadId" value="{$oThread->thread_id}">
		<span id="reply_{$oThread->thread_id}"></span>
	</form>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr><td align="right"><a href="#top" class="link_ticket_cmd">{$smarty.const.LANG_DISPLAY_BACK_TO_TOP|lower}</a></td></tr>
	</table>

	<br>
	
	</div>
