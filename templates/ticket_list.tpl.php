<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>{$smarty.const.LANG_HTML_TITLE}</title>
<META HTTP-EQUIV="content-type" CONTENT="{$smarty.const.LANG_CHARSET}">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<META HTTP-EQUIV="Pragma-directive" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Directive" CONTENT="no-cache">
<META HTTP-EQUIV="Expires" CONTENT="0">

{include file="cerberus.css.tpl.php"}
<link rel="stylesheet" href="includes/cerberus_2006.css" type="text/css">
<link rel="stylesheet" href="skins/fresh/cerberus-theme.css" type="text/css">
<link rel="stylesheet" href="includes/css/container.css?v={$smarty.const.GUI_BUILD}" type="text/css"> 

{include file="keyboard_shortcuts_jscript.tpl.php}

<script type="text/javascript" src="includes/scripts/yahoo/YAHOO.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/event.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/connection.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/dom.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/animation.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/autocomplete.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/dragdrop.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/container.js?v={$smarty.const.GUI_BUILD}" ></script>

<script type="text/javascript" src="includes/scripts/cerb3/dashboard.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/quickworkflow.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/searchbuilder.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/knowledgebase.js?v={$smarty.const.GUI_BUILD}" ></script>

{literal}
<script type="text/javascript">
	search_on = new Image;
	search_on.src = "includes/images/tab_search_on.gif";
	search_off = new Image;
	search_off.src = "includes/images/tab_search_off.gif";

	function toggleSearchBox() {
		if (document.getElementById) {
			if(document.getElementById("search").style.display=="block") {
				document.getElementById("search").style.display="none";
				document.getElementById("search_tab").src=search_off.src;
				document.formSaveLayout.layout_home_show_search.value = 0;
			}
			else {
				document.getElementById("search").style.display="block";
				document.getElementById("search_tab").src=search_on.src;
				document.formSaveLayout.layout_home_show_search.value = 1;
			}
		}
	}

	function init_home(e, obj) {
		load_init();
	}
	
	function savePageLayout() {
		// [JAS]: Force submit the form
		document.formSaveLayout.submit();
	}

	YAHOO.util.Event.addListener(window,"load",init_home);
</script>
{/literal}

</head>

<body bgcolor="#FFFFFF" {if $session->vars.login_handler->user_prefs->keyboard_shortcuts}onkeypress="doShortcutsIE(window,event);"{/if}>
{include file="header.tpl.php"}

<table width="100%" cellspacing="1" cellpadding="3" border="0">
<form action="ticket_list.php" name="formSaveLayout" method="post" style="margin:0px;">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="form_submit" value="save_layout">
	
	<input type="hidden" name="layout_view_options_sv" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_view_options_sv}">
	<input type="hidden" name="layout_home_show_queues" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_home_show_queues}">
	<input type="hidden" name="layout_home_show_search" value="{$session->vars.login_handler->user_prefs->page_layouts.layout_home_show_search}">
</form>
  <tr> 
    <td valign="top" width="0%" nowrap> 
		{*include file="home/system_status.tpl.php" col_span=3*}
		<span id="search_searchCriteriaList"></span>
		<script type="text/javascript">
			{literal}
			YAHOO.util.Event.addListener(window,"load",function() {
				doSearchCriteriaList('search');
			});
			{/literal}
		</script>
		
	</td>
    <td valign="top" width="100%">
    	{include file="search/search_builder.tpl.php" label="search"}
    	<br>
    	
    	{*include file="search/quick_search.tpl.php"*}
    	
		<a name="results"></a>
		<span class="cer_display_header">{$smarty.const.LANG_WORD_SEARCH_RESULTS}</span>&nbsp;&nbsp;
		<span class="cer_maintable_text">Matched {$s_view->show_of} Tickets</span>&nbsp;&nbsp;
		<a href="#mass_actions" class="cer_maintable_text">Jump to Mass Actions</a>
		
		{include file="views/ticket_view.tpl.php" view=$s_view col_span=$s_view->view_colspan}
		<a name="mass_actions"></a>
    </td>
   </tr>
</table>

<br>
{include file="footer.tpl.php"}
</body>
</html>