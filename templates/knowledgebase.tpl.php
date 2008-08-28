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
<link rel="stylesheet" href="skins/fresh/cerberus-theme.css?v={$smarty.const.GUI_BUILD}" type="text/css">
<link rel="stylesheet" href="includes/cerberus_2006.css?v={$smarty.const.GUI_BUILD}" type="text/css">
<link rel="stylesheet" href="includes/css/container.css?v={$smarty.const.GUI_BUILD}" type="text/css"> 
{include file="keyboard_shortcuts_jscript.tpl.php}

<script type="text/javascript" src="includes/scripts/yahoo/YAHOO.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/event.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/connection.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/dom.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/autocomplete.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/dragdrop.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/container.js?v={$smarty.const.GUI_BUILD}" ></script>

<script type="text/javascript" src="includes/scripts/cerb3/quickworkflow.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/knowledgebase.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/kbsearchbuilder.js?v={$smarty.const.GUI_BUILD}" ></script>

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

<br>

<!---
Tags:<br>
<span class="searchdiv">
<textarea name="tag_input" id="tag_input" class="search_input" style="width:300px;height:80px;"></textarea>
<div class="searchshadow"><div id="searchcontainer" class="searchcontainer"></div></div>
</span>
<br>
--->

<span class="cer_display_header">{$smarty.const.LANG_WORD_KNOWLEDGEBASE}</span><br>
<form style="margin:0px">
<input type="hidden" name="form_submit" value="kb_search">
<img src="includes/images/icone/16x16/view.gif"> <b>Search:</b> <input type="text" name="kb_keywords" value="{$kb_keyword_string|short_escape}" size="45"><input type="submit" value="Go!"> <!---(<a href="#">advanced search</a>)--->
{if $acl->has_priv($smarty.const.PRIV_KB_EDIT)}<input type="button" onclick="document.location='{"knowledgebase.php?mode=edit_entry&kbid=0"|cer_href}';" value="Add Article">{/if}
<br>
</form>

{* View Knowledgebase Categories *}
{if $mode == "browse"}
	
	{*
	{if $kb->show_kb_search !== false} 
		{include file="knowledgebase/kb_search_box.tpl.php" col_span=3}
	{/if}
	*}
	
	<br>
	{include file="knowledgebase/kb_category_table.tpl.php"}
	{include file="knowledgebase/kb_resource_list.tpl.php" resources=$kb_root->getResources(500)}
{/if}

{* Search Results *}
{if $mode == "keyword_results"}
	<br>
	{include file="knowledgebase/kb_article_results.tpl.php" articles=$articles}
{/if}

{* Create Knowledgebase Article *}
{if $mode == "create"}
	{include file="knowledgebase/kb_article_edit.tpl.php"}
{/if}
		

{* Edit Knowledgebase Article *}
{if $mode == "edit_entry"}
	{if $kb->show_article_edit !== false}
		{include file="knowledgebase/kb_article_edit.tpl.php"}
	{/if}
{/if}

{* View Knowledgebase Article *}
{if $mode == "view_entry"}
	{include file="knowledgebase/kb_article_view.tpl.php"}
{/if}

{include file="footer.tpl.php"}

<form style="margin:0px;" id="fnrResourceForm">
<span id="dynamicKbResource" style="visibility:hidden">
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
