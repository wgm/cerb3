<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td valign="top">
			<span class="cer_maintable_heading">Filter by Team:</span><br>
			<select name="report_team_id" class="cer_footer_text">
			<option value='-1'>- any team -
				{html_options options=$report->report_data->team_data->team_list selected=$report->report_data->team_data->report_team_id}
			</select>
			</td>
	</tr>
</table>