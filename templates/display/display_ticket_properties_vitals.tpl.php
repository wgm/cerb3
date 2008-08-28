{literal}
<script type="text/javascript">
	function toggleDisplayGlance() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_glance").style.display=="block") {
				document.getElementById("ticket_display_glance").style.display="none";
				document.getElementById("ticket_display_glance_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_glance.value = 0;
			}
			else {
				document.getElementById("ticket_display_glance").style.display="block";
				document.getElementById("ticket_display_glance_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_glance.value = 1;
			}
		}
	}
</script>
{/literal}

	{* Ticket At a Glance Box *}
	{* include file="display/display_ticket_glance.tpl.php" col_span=6 *}
	
<div id="ticket_display_glance" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_glance}block{else}none{/if};">
</div>