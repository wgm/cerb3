{* Get Work Template *}
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
{if $do_meta_refresh}
<META HTTP-EQUIV="Refresh" content="{$refresh_sec};URL={$refresh_url}">
{/if}

{include file="cerberus.css.tpl.php"}
<link rel="stylesheet" href="includes/cerberus_2006.css?v={$smarty.const.GUI_BUILD}" type="text/css">
<link rel="stylesheet" href="skins/fresh/cerberus-theme.css?v={$smarty.const.GUI_BUILD}" type="text/css">
<link rel="stylesheet" href="includes/css/calendar.css?v={$smarty.const.GUI_BUILD}" type="text/css">

{include file="keyboard_shortcuts_jscript.tpl.php}
<script type="text/javascript" src="includes/scripts/yahoo/YAHOO.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/event.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/connection.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/dom.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/animation.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/autocomplete.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/getwork.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/yahoo/calendar.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/quickworkflow.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/ticket.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/display.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/knowledgebase.js?v={$smarty.const.GUI_BUILD}" ></script>
</head>

<body bgcolor="#FFFFFF" {if $session->vars.login_handler->user_prefs->keyboard_shortcuts}onkeypress="doShortcutsIE(window,event);"{/if}>
{include file="header.tpl.php"}

<form name="spellform" method="POST" target="spellWindow" action="includes/elements/spellcheck/spellcheck.php" style="margin:0px;">
<input type="hidden" name="caller" value="">
<input type="hidden" name="spellstring" value="">
</form>

<br>
<form action="getwork.php" name="formSaveLayout" method="post">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="form_submit" value="save_layout">
</form>
<table width="100%" border="0" cellspacing="5" cellpadding="1">
  <tr> 
   <td valign="top" width="0%" nowrap> 
	</td>
    <td valign="top" width="100%">
    <form action="getwork.php" method="post">
		Work mode: 
		<select name="mode" class="box_text" onchange="this.form.submit();">
		  <option value="quickassign" {if $mode=="quickassign" || $mode==""}selected{/if}>Quick Assign</option>
		  <option value="monitor" {if $mode=="monitor"}selected{/if}>Monitor</option>
		</select>
		<input type="submit" value="Switch" class="cer_button_face" />
		
		<a href="javascript:;" onclick="createTicket();" class="box_text">create new ticket</a>
		<br />
		</form>

		<span id="divCreateTicket"></span>
		
		{if $mode == "monitor"}
			{include file="home/getwork/monitor.tpl.php"}
		{else}
			{include file="home/getwork/quick_assign.tpl.php"}
		{/if}
		
		<br>
		{include file="home/whos_online_box.tpl.php"}
		
    </td>
  </tr>
</table>
{include file="footer.tpl.php"}
{if $run_cron === true}
	<script type="text/javascript">
		YAHOO.util.Event.addListener(window,"load",runScheduledTasks);
	</script>
{/if}
</body>
</html>