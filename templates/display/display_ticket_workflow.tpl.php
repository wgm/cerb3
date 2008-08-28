{literal}
<script type="text/javascript">
	function toggleDisplayWorkflow() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_workflow").style.display=="block") {
				document.getElementById("ticket_display_workflow").style.display="none";
				document.getElementById("ticket_display_workflow_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_workflow.value = 0;
			}
			else {
				document.getElementById("ticket_display_workflow").style.display="block";
				document.getElementById("ticket_display_workflow_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_workflow.value = 1;
			}
		}
	}
</script>
{/literal}

<a href="#" onclick="javascript:toggleDisplayWorkflow();"><img alt="Toggle" id="ticket_display_workflow_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_workflow}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" border="0"></a>
<img alt="A tag" src="includes/images/icone/16x16/bookmark.gif" width="16" height="16">
<span class="link_ticket">Ticket Workflow</span><br>
<table cellspacing="0" cellpadding="0" width="100%"><tr><td bgcolor="#DDDDDD"><img src="includes/images/spacer.gif" height="1" width="1" alt=""></td></tr></table>
<div id="ticket_display_workflow" style="display:{if !empty($hp) || $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_workflow}block{else}none{/if};">
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="ticket_display_table">
	<tr>
		<td>
			<table border="0" cellspacing="2" cellpadding="0">
	        <tr>
	        	 <td valign="top">
					<span id="workflowSnapshot_{$ticket}">Initializing...<br></span>
					<script type="text/javascript">YAHOO.util.Event.addListener(document.body,"load",ticketWorkflow.refresh());</script>
	        	 </td>
	          <td valign="top">
	          	{include file="display/boxes/box_quick_workflow.tpl.php ticketId=$ticket}
				<script>
					YAHOO.util.Event.addListener(document.body, "load", autoTags('tag_input_{$ticket}','searchcontainer_{$ticket}'));
					ticketWorkflow.selectFirst();
				</script>
	          </td>
	        </tr>
	      </table>
		</td>
	</tr>
</table>
</div>
  
<br>
