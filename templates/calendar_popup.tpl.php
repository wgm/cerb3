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
<link rel="stylesheet" href="skins/fresh/cerberus-theme.css" type="text/css">

<script language="javascript" type="text/javascript">
{literal}
	function drawDate() {
		{/literal}
		window.opener.document.getElementById("{$label}").innerHTML = "{$chosenDisplay}";
		window.opener.document.getElementById("{$field}").value = "{$chosenTimestamp}";
		window.close();
		{literal}
	}
	
	function updateDate(day,month,year) {
		document.dateForm.date_dmy.value = month + "/" + day + "/" + year;
	}
{/literal}
</script>

</head>
<body bgcolor="#EEEEEE" {if $date_chosen}onload="javascript: drawDate();"{/if}>

<form action="calendar_popup.php" name="dateForm" style="margin:0px;">
<table cellpadding="0" cellspacing="0" border="0" align="center">
<tr>
	<td align="center" class="cer_maintable_heading">{$smarty.const.LANG_CHOOSEDATE_CHOOSEDATE}</td>
</tr>
<tr>
	<td align="center">
		{include file="my_cerberus/tabs/my_cerberus_dashboard_calendar.tpl.php" cal=$cal}
	</td>
</tr>
<tr>
	<td nowrap="nowrap">
		<input type="hidden" name="label" value="{$label}">
		<input type="hidden" name="field" value="{$field}">
		<input type="hidden" name="timestamp" value="{$timestamp}">
		<input type="hidden" name="show_time" value="{$show_time}">
		<input type="text" name="date_dmy" maxlength="10" size="10" value="{$mo_date}">
	
		{if $show_time}
		<select name="date_hr">
			{html_options options=$timestamp_select->hrs_opts selected=$mo_hr}
		</select>:<select name="date_min">
			{html_options options=$timestamp_select->mins_opts selected=$mo_min}
		</select><select name="date_ampm">
			{html_options options=$timestamp_select->ampm_opts selected=$mo_ampm}
		</select>
	</td>
	{/if}
</tr>
<tr>
	<td align="center">
		<input type="submit" value="Use Date" class="cer_button_face">
	</td>
</tr>

</table>
</form>

</body>
</html>