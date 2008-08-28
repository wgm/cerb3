{* Index Template *}
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
<link rel="stylesheet" href="includes/cerberus_2006.css?v={$smarty.const.GUI_BUILD}" type="text/css">
<link rel="stylesheet" href="skins/fresh/cerberus-theme.css?v={$smarty.const.GUI_BUILD}" type="text/css">
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
<script type="text/javascript" src="includes/scripts/cerb3/ticket.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/display.js?v={$smarty.const.GUI_BUILD}" ></script>
<script type="text/javascript" src="includes/scripts/cerb3/knowledgebase.js?v={$smarty.const.GUI_BUILD}" ></script>
</head>

{literal}
<script type="text/javascript">
	function savePageLayout() {
		// [JAS]: Force submit the form
		document.formSaveLayout.submit();
	}
	var refreshActive = true;
	var refreshMsec = {/literal}{if $selDashboard->reload_mins}{$selDashboard->reload_mins}{else}0{/if}{literal} * 60 * 1000;
	var oTimeout = null;
	
	function doRefresh() {
		{/literal}
		{if isset($selDashboard) && !empty($selDashboard->reload_mins)}
		oTimeout = setTimeout('autoRefresh()', refreshMsec);
		{/if}
		{literal}
	}
	function autoRefresh() {
		if(refreshActive) {
			document.location = 'index.php';
		}
	}
	function toggleRefresh(state) {
		var l = document.getElementById('pauseLink');
		if(null == l || null == refreshActive) return;

		var toState = (null==state) ? !refreshActive : state;

		if(!toState) { // turn off
			refreshActive = false;
			l.innerHTML = 'start ' + (refreshMsec/60/1000) + 'm refresh';
			clearTimeout(oTimeout);
		} else { // turn on
			refreshActive = true;
			l.innerHTML = 'stop ' + (refreshMsec/60/1000) + 'm refresh';
			oTimeout = setTimeout('autoRefresh()', refreshMsec);
		}
	}
	YAHOO.util.Event.addListener(window,"load",doRefresh);
</script>
{/literal}

<body bgcolor="#FFFFFF" {if $session->vars.login_handler->user_prefs->keyboard_shortcuts}onkeypress="doShortcutsIE(window,event);"{/if}>
{include file="header.tpl.php"}

<br>
<form action="index.php" name="formSaveLayout" method="post" style="margin:0px;">
	<input type="hidden" name="sid" value="{$session_id}">
	<input type="hidden" name="form_submit" value="save_layout">
	<input type="hidden" name="default_dashboard_id" value="{$selDashboard->id}">
</form>

<form action="index.php" method="post" style="margin:0px;">
<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td width="100%">
			{if $selDashboard}
			<span class="text_title">{$selDashboard->title|escape:"htmlall"}</span>
			<a href="{"index.php?form_submit=add_view&dashid="|cat:$selDashboard->id|cer_href}" class="box_text" title="Add a new Ticket View">add view</a>
			| <a href="javascript:;" onclick="toggleDiv('divEditDashboard');" class="box_text" title="Customize Dashboard">customize</a>
			| <a href="{"index.php?form_submit=delete_dashboard&dashid="|cat:$selDashboard->id|cer_href}" class="box_text" title="Remove Dashboard" onclick="return confirm('Are you sure you want to delete this dashboard?');">remove</a>
			{else}
			<span class="text_title">No Active Dashboard</span>
			<a href="javascript:;" onclick="toggleDiv('divCreateDashboard');" class="box_text" title="Create a new dashboard">add a dashboard</a>
			{/if}
			| <a href="javascript:;" onclick="toggleRefresh(false);createTicket();" class="box_text">create new ticket</a>
			{if $selDashboard && $selDashboard->reload_mins}
				| <a href="javascript:;" onclick="toggleRefresh();" class="box_text" id='pauseLink'>stop {$selDashboard->reload_mins}m refresh</a>
			{/if}
		</td>
		<td align="right" width="0%" nowrap="nowrap">
			Dashboard: 
			<select name="dashid" class="box_text" onchange="this.form.submit();">
				<option value="">- choose a dashboard -
				{foreach from=$dashboards item=dash}
					{if $dash->id}
					<option value="{$dash->id}" {if $dash->id==$selDashboard->id}selected{/if}>{$dash->title}</option>
					{/if}
				{/foreach}
			</select>
			<input type="submit" value="Switch" class="cer_button_face" />
			<a href="javascript:;" onclick="toggleDiv('divCreateDashboard');" class="box_text" title="Create a new dashboard">add dashboard</a>
		</td>
	</tr>
</table>
</form>

<div id="divCreateTicket"></div>

<div id="divCreateDashboard" style="display:none;">
	<form action="index.php" style="margin:0px;">
	<input type="hidden" name="form_submit" value="create_dashboard">
	<table class="table_purple" bgcolor="#F0F0FF">
		<tr>
			<td>
				<b>Dashboard Name:</b> <input type="text" name="newDashboardName" value="" size="50">
				<input type="submit" name="" value="Create"><input type="button" value="Cancel" onclick="toggleDiv('divCreateDashboard',0);">
			</td>
		</tr>
	</table>
	</form>
	<br>
</div>

<div id="divEditDashboard" style="display:none;">
	<form action="index.php" style="margin:0px;">
	<table class="table_purple" bgcolor="#F0F0FF">
		<tr>
			<td colspan="2">
				<input type="hidden" name="dashid" value="{$selDashboard->id}">
				<input type="hidden" name="form_submit" value="edit_dashboard">
				<b>Dashboard Name:</b> <input type="text" name="dashboard_name" value="{$selDashboard->title|htmlentities}" size="50"><br>
			</td>
		</tr>
		<tr>
			<td colspan='2'><i>On this dashboard...</i></td>
		</tr>
		<tr>
			<td valign="top">
				<b>Monitor these Teams:</b><br>
				{if empty($acl->teams)}
					You are not a member of any teams.
				{/if}
				{foreach from=$teams item=dash_team key=dash_teamId}
				{if $acl->teams.$dash_teamId}
					<input type="hidden" name="dashboard_hide_teams[]" value="{$dash_teamId}">
					<label><input type="checkbox" name="dashboard_teams[]" value="{$dash_teamId}" {if $selDashboard->hide_teams.$dash_teamId}{else}checked{/if}>{$dash_team->name}</label><br>
				{/if}
				{/foreach}
			</td>
			<td valign="top">
				<b>Monitor these Mailboxes:</b><br>
				{if empty($acl->queues)}
					Your teams have no mailboxes configured.
				{/if}
				{foreach from=$queues item=dash_queue key=dash_queueId}
				{if $acl->queues.$dash_queueId}
					<input type="hidden" name="dashboard_hide_queues[]" value="{$dash_queueId}">
					<label><input type="checkbox" name="dashboard_queues[]" value="{$dash_queueId}" {if $selDashboard->hide_queues.$dash_queueId}{else}checked{/if}>{$dash_queue->queue_name}</label><br>
				{/if}
				{/foreach}
			</td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr>
			<td colspan="2">
				<b>Auto refresh dashboard</b> every <input name="dashboard_reload" type="text" value="{$selDashboard->reload_mins}" size="2" maxlength="3"> minute(s).
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Save Dashboard"><input type="button" value="Cancel" onclick="toggleDiv('divEditDashboard',0);"></td>
		</tr>
	</table>
	</form>
	<br>
</div>

<table width="100%" border="0" cellspacing="5" cellpadding="1">
  <tr> 
   <td valign="top" width="0%" nowrap> 
   	{include file="home/dashboard/rpc/dashboard_loads.tpl.php"}
	</td>
    <td valign="top" width="100%">
		{include file="home/dashboard/dashboard.tpl.php"}
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