<?php
define("REPORT_NAME","Team Summary Report");
define("REPORT_SUMMARY","Date range breakdown by team for each agent showing average handle time and number of e-mails handled.");
define("REPORT_TAG","group_summary_report");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/reports/cer_ReportAverageHandleTime.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_GroupSummaryReport();
	$report->generate_report();
	return $report;
}

class cer_GroupSummaryReport extends cer_ReportModule
{
	function generate_report()
	{
		$this->report_name = REPORT_NAME;
		$this->report_summary = REPORT_SUMMARY;
		$this->report_tag = REPORT_TAG;
		
		$this->_init_team_list();
		$this->_init_calendar();

		$report_team_id = $this->report_data->team_data->report_team_id;
		
		// [JAS]: If the 'all' option is set, clear the filter.
		if($report_team_id == -1) $report_team_id = 0;
		
		$report_title = sprintf("%s for %s",
				REPORT_NAME,
				$this->report_dates->date_range_str			
			);

		$uids = array(); // All user IDs from the selected group (csv)

		// [JAS]: If we're filtering by group, make a list of user IDs from a group
		if (!empty($report_team_id)) {
			$sql = sprintf("SELECT u.agent_id ".
						   "FROM team_members u ".
						   "WHERE u.team_id = %d",
						$report_team_id
					);
			$g_res = $this->db->query($sql);
			
			if ($this->db->num_rows($g_res)) {
				while($g_row = $this->db->fetch_row($g_res)) {
					$uids[$g_row["agent_id"]] = 1;
				}
			}
		}
		
		$params = array(
				"from_date" => $this->report_dates->from_date,
				"to_date" => $this->report_dates->to_date,
				"queue_id" => $report_queue_id
				);
				
		$AvgHandleTime = new cer_ReportAverageHandleTime();
		$group_times = $AvgHandleTime->getGroupUserAverageHandleTimes($uids,$params);
		
		// [JAS]: If we have no user times to display, don't bother 
		//	drawing the report.  It will show "No data for range." 
		//	by default
		if (empty($group_times))
			return;
			
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 4;			
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Report Name
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#FF6600";
		$new_row->cols[0] = new cer_ReportDataCol($report_title);
		$new_row->cols[0]->col_span = 4;
		array_push($this->report_data->rows,$new_row);

		$total_times = 0;
		$total_num_times = 0;
		$total_samples = 0;
		
		$total_group_times = 0;
		$total_groups = 0;
		
		$system_avg = new cer_WeightedAverage();
		$sorted_group_times = cer_PointerSort::pointerSortCollection($group_times,"group_name");
		
		foreach($sorted_group_times as $group_time)
		{
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 4;			
			array_push($this->report_data->rows,$new_row);
		
			// [JAS]: Group Name
//			$new_row = new cer_ReportDataRow();
//			$new_row->style = "cer_maintable_header";
//			$new_row->bgcolor = "#AAAAAA";
//			$new_row->cols[0] = new cer_ReportDataCol("Agent: " . $group_time->group_name);
//			$new_row->cols[0]->col_span = 4;
//			array_push($this->report_data->rows,$new_row);

			// [JAS]: Black Spacer
//			$new_row = new cer_ReportDataRow();
//			$new_row->bgcolor = "#000000";
//			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
//			$new_row->cols[0]->col_span = 4;			
//			array_push($this->report_data->rows,$new_row);
				
			// [JAS]: Column Headings
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_headingSM";
			$new_row->bgcolor = "#CCCCCC";
			$new_row->cols[0] = new cer_ReportDataCol("Agent Name");
			$new_row->cols[1] = new cer_ReportDataCol("Agent Login");
			$new_row->cols[2] = new cer_ReportDataCol("Email Handled");
			$new_row->cols[3] = new cer_ReportDataCol("Avg. Response Time");
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 4;			
			array_push($this->report_data->rows,$new_row);

			$sorted_users = cer_PointerSort::pointerSortCollection($group_time->user_times,"user_name");
			
			foreach($sorted_users as $user_time) {
				// [JAS]: Data Rows
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#E5E5E5";
				$new_row->cols[0] = new cer_ReportDataCol("<b>".$user_time->user_name."</b>");
				$new_row->cols[1] = new cer_ReportDataCol($user_time->user_login);
				$new_row->cols[2] = new cer_ReportDataCol($user_time->r_samples);
				$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($user_time->average_response_time));
				$new_row->cols[2]->align = "center";
				$new_row->cols[3]->align = "center";
				array_push($this->report_data->rows,$new_row);
				
				$total_times += $user_time->average_response_time;
				$total_num_times++;
				$total_samples += $user_time->r_samples;
				$system_avg->addSample($user_time->average_response_time,$user_time->r_samples);
	
				// [JAS]: White Spacer
				$new_row = new cer_ReportDataRow();
				$new_row->bgcolor = "#FFFFFF";
				$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
				$new_row->cols[0]->col_span = 4;			
				array_push($this->report_data->rows,$new_row);
			}
			
			// [JAS]: Subtotal Heading
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_headingSM";
			$new_row->bgcolor = "#D0D0D0";
			$new_row->cols[0] = new cer_ReportDataCol();
			$new_row->cols[1] = new cer_ReportDataCol();
			$new_row->cols[2] = new cer_ReportDataCol($group_time->r_samples);
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($group_time->average_response_time));
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			$total_group_times += $group_time->average_response_time;
			$total_groups++;
		}
		
		if($total_num_times)
		{
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 4;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Totals Heading
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#888888";
			$new_row->cols[0] = new cer_ReportDataCol("Group Averages");
			$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
			$new_row->cols[2] = new cer_ReportDataCol($total_groups . " groups");
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString(round($total_group_times/$total_groups)));
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			array_push($this->report_data->rows,$new_row);

			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 4;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Totals Heading
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#888888";
			$new_row->cols[0] = new cer_ReportDataCol("System Averages (weighted)");
			$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
			$new_row->cols[2] = new cer_ReportDataCol($total_samples . " samples");
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($system_avg->getAverage()));
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 4;			
			array_push($this->report_data->rows,$new_row);
		}
		
	}
};

?>