<span class="cer_display_header">Reports: {$report_list->report->report_name}</span><br>
<span class="cer_maintable_text">{$report_list->report->report_summary}</span><br>
<span class="cer_maintable_text">(<a href="{$urls.reports}" class="cer_maintable_heading">back to reports list</a>)</span><br>
<br>
	<form action="" method="post">
		<input type="hidden" name="form_submit" value="x">
		<input type="hidden" name="report" value="{$report}">
		<input type="hidden" name="sid" value="{$session_id}">
	
		{include file="reports/shared_calender.tpl.php"}
		
		<table border="0" cellpadding="5" cellspacing="0">
			<tr>
				<td>{include file="reports/shared_group_dropdown.tpl.php" report=$report_list->report}</td>
				<td valign="bottom"><input type="submit" class="cer_button_face" value="Run Report!"></td>
			</tr>
		</table>
		
	</form>

<br>

{include file="reports/report_data.tpl.php" report=$report_list->report}
