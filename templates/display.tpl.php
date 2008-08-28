<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>[{if !empty($o_ticket->ticket_mask)}{$o_ticket->ticket_mask}{else}{$o_ticket->ticket_id}{/if}]: {$o_ticket->ticket_subject} - {$smarty.const.LANG_HTML_TITLE}</title>
<META HTTP-EQUIV="content-type" CONTENT="{$smarty.const.LANG_CHARSET}">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META HTTP-EQUIV="Pragma-directive" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Directive" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="0">

{include file="cerberus.css.tpl.php"}
<link rel="stylesheet" href="skins/fresh/cerberus-theme.css?v={$smarty.const.GUI_BUILD}" type="text/css">
<link rel="stylesheet" href="includes/cerberus_2006.css?v={$smarty.const.GUI_BUILD}" type="text/css">
<link rel="stylesheet" href="includes/css/calendar.css?v={$smarty.const.GUI_BUILD}" type="text/css">
<link rel="stylesheet" href="includes/css/container.css?v={$smarty.const.GUI_BUILD}" type="text/css"> 

{include file="keyboard_shortcuts_jscript.tpl.php}
<script type="text/javascript" src="includes/scripts/yahoo/YAHOO.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/dom.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/event.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/connection.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/autocomplete.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/dragdrop.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/container.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/display.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/knowledgebase.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/calendar.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/quickworkflow.js?v={$smarty.const.GUI_BUILD}" ></script>

<script language="javascript" type="text/javascript" src="includes/scripts/tiny_mce/tiny_mce.js?v={$smarty.const.GUI_BUILD}"></script>

{literal}
<script language="javascript" type="text/javascript">
	tinyMCE.init({
		theme : "advanced",
		editor_selector : "mceEditor"
	});
</script>
{/literal}

</head>

<body bgcolor="#FFFFFF" {if $session->vars.login_handler->user_prefs->keyboard_shortcuts}onkeypress="doShortcutsIE(window,event);"{/if}>
{include file="header.tpl.php"}

<script type="text/javascript">
{literal}
	function savePageLayout() {
		// [JAS]: Force submit the form
		document.formSaveLayout.submit();
	}
{/literal}
	
var ticketWorkflow = new CerQuickWorkflow('{$o_ticket->ticket_id}','frmQuickWorkflowSearch_{$o_ticket->ticket_id}');
{literal}
ticketWorkflow.postResultsAction = function() {
	this.refresh();
}
ticketWorkflow.postAddTagAction = function() {
	getFnrSuggestions('{/literal}{$o_ticket->ticket_id}{literal}');
}

// Overload!
doPostRemoveTagAction = function(id) {
	if(null == getFnrSuggestions) return;
	getFnrSuggestions(id);
}
{/literal}
</script>

<form name="spellform" method="POST" target="spellWindow" action="includes/elements/spellcheck/spellcheck.php" style="margin:0px;">
<input type="hidden" name="caller" value="">
<input type="hidden" name="spellstring" value="">
</form>

<br>

<a name="top"></a>

<form action="display.php" name="formSaveLayout" method="post">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="form_submit" value="save_layout">
	<input type="hidden" name="ticket" value="{$o_ticket->ticket_id}">
	<input type="hidden" name="mode" value="{$mode}">
	<input type="hidden" name="layout_display_show_log" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_log}">
	<input type="hidden" name="layout_display_show_suggestions" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_suggestions}">
	<input type="hidden" name="layout_display_show_history" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_history}">
	<input type="hidden" name="layout_display_show_workflow" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_workflow}">
	<input type="hidden" name="layout_display_show_contact" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_contact}">
	<input type="hidden" name="layout_display_show_fields" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_display_show_fields}">
  	<input type="hidden" name="layout_view_options_bv" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_view_options_bv}">
</form>

{if $mode == "tkt_fields" || $mode == "properties"}
	{include file="display/display_ticket_heading.tpl.php"}

	{if $acl->has_priv($smarty.const.PRIV_TICKET_CHANGE) }
		{include file="display/tabs/display_ticket_merge.tpl.php"}
	{/if}

{elseif $mode == "anti_spam"}
	{include file="display/display_ticket_heading.tpl.php"}
	{include file="display/tabs/display_ticket_antispam.tpl.php"}

{elseif $mode == "batch"}
	{include file="display/display_ticket_heading.tpl.php"}
	{include file="display/tabs/display_ticket_batch.tpl.php"}

{elseif $mode == "log"}
	{include file="display/display_ticket_heading.tpl.php"}
	{include file="display/tabs/display_ticket_log.tpl.php"}

{else}
	{include file="display/display_ticket.tpl.php"}

{/if}

{include file="footer.tpl.php"}

<form style="margin:0px;" id="fnrResourceForm">
<span id="dynamicKbResource" style="visibility:visible">
		<div class="hd"></div>
		<div class="bd"></div>
		<div class="ft"></div>
</span>
</form>

<span id="dynamicKbCategories" style="visibility:hidden">
		<div class="hd"></div>
		<div class="bd"></div>
		<div class="ft"></div>
</span>

</body>
</html>
