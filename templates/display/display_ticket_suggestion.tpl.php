{literal}
<script type="text/javascript">
	function toggleDisplaySuggestions() {
		if (document.getElementById) {
			if(document.getElementById("ticket_display_suggestions").style.display=="block") {
				document.getElementById("ticket_display_suggestions").style.display="none";
				document.getElementById("ticket_display_suggestions_icon").src=icon_expand.src;
				document.formSaveLayout.layout_display_show_suggestions.value = 0;
			}
			else {
				document.getElementById("ticket_display_suggestions").style.display="block";
				document.getElementById("ticket_display_suggestions_icon").src=icon_collapse.src;
				document.formSaveLayout.layout_display_show_suggestions.value = 1;
			}
		}
	}
</script>
{/literal}

<a href="#" onclick="javascript:toggleDisplaySuggestions();"><img alt="Toggle" id="ticket_display_suggestions_icon" src="includes/images/{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_suggestions}icon_collapse.gif{else}icon_expand.gif{/if}" width="16" height="16" border="0"></a>
<img alt="A document" src="includes/images/icone/16x16/document.gif" width="16" height="16">
<span class="link_ticket">{$smarty.const.LANG_FNR_TITLE}</span><br>
<table cellspacing="0" cellpadding="0" width="100%"><tr><td bgcolor="#DDDDDD"><img alt="" src="includes/images/spacer.gif" height="1" width="1"></td></tr></table>
<div id="ticket_display_suggestions" style="display:{if $session->vars.login_handler->user_prefs->page_layouts.layout_display_show_suggestions}block{else}none{/if};">
{if !empty($fnrArticles)}
	{include file="knowledgebase/kb_article_list2.tpl.php" articles=$fnrArticles}
{else}
	No matching articles.  Try adding some tags to this ticket.
{/if}
</div>
<br>
