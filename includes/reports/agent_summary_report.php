<?php
define("REPORT_NAME","Agent Summary Report");
define("REPORT_SUMMARY","Date range breakdown for each agent showing average time logged in, average handle time and number of e-mails handled.");
define("REPORT_TAG","agent_summary_report");

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/sort/cer_PointerSort.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/reports/cer_ReportAverageHandleTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/reports/cer_ReportAverageTimeLoggedIn.class.php");

function init_report(&$cer_tpl)
{
	$report = new cer_AgentSummaryReport();
	$report->generate_report();
	return $report;
}

class cer_AgentSummaryReport extends cer_ReportModule
{
	
	function generate_report()
	{
		$this->report_name = REPORT_NAME;
		$this->report_summary = REPORT_SUMMARY;
		$this->report_tag = REPORT_TAG;
		
		$this->_init_calendar();
		
		$report_title = sprintf("%s for %s",
				REPORT_NAME,
				$this->report_dates->date_range_str			
			);

		$uids = array(); // All user IDs from the selected group (csv)

		$params = array(
				"from_date" => $this->report_dates->from_date,
				"to_date" => $this->report_dates->to_date,
				"queue_id" => $report_queue_id
				);
				
		$userAvgHandleTime = new cer_ReportAverageHandleTime();
		$user_times = $userAvgHandleTime->getUserAverageHandleTimes($uids,$params);
		
		// [JAS]: If we have no user times to display, don't bother 
		//	drawing the report.  It will show "No data for range." 
		//	by default
		if (empty($user_times))
			return;
			
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 5;			
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Report Name
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#FF6600";
		$new_row->cols[0] = new cer_ReportDataCol($report_title);
		$new_row->cols[0]->col_span = 5;
		array_push($this->report_data->rows,$new_row);

		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 5;			
		array_push($this->report_data->rows,$new_row);

		// [JAS]: Column Headings
		$new_row = new cer_ReportDataRow();
		$new_row->style = "cer_maintable_header";
		$new_row->bgcolor = "#AAAAAA";
		$new_row->cols[0] = new cer_ReportDataCol("Agent Name");
		$new_row->cols[1] = new cer_ReportDataCol("Agent Login");
		$new_row->cols[2] = new cer_ReportDataCol("Email Handled");
		$new_row->cols[3] = new cer_ReportDataCol("Avg. Response Time");
		$new_row->cols[4] = new cer_ReportDataCol("Avg. Time Logged In");
		$new_row->cols[2]->align = "center";
		$new_row->cols[3]->align = "center";
		$new_row->cols[4]->align = "center";
		array_push($this->report_data->rows,$new_row);
		
		// [JAS]: Black Spacer
		$new_row = new cer_ReportDataRow();
		$new_row->bgcolor = "#000000";
		$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
		$new_row->cols[0]->col_span = 5;			
		array_push($this->report_data->rows,$new_row);
		
		$total_times = 0;
		$total_num_times = 0;
		$total_samples = 0;
		$total_login_times = 0;
		
		$system_avg = new cer_WeightedAverage();
		$sorted_user_times = cer_PointerSort::pointerSortCollection($user_times,"user_name");
		
		$login_avg = new cer_WeightedAverage();
		
		$avg_login_time_handler = new cer_ReportAverageTimeLoggedIn();
		$avg_login_times = $avg_login_time_handler->getUserAverageTimeLoggedIn();
		unset($avg_login_time_handler);
		
		foreach($sorted_user_times as $idx => $user_time)
		{
			$user_avg_login = $avg_login_times[$user_time->user_id]->avg_login_time;
			$user_avg_samples = $avg_login_times[$user_time->user_id]->samples;
			
			// [JAS]: Data Rows
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#E5E5E5";
			$new_row->cols[0] = new cer_ReportDataCol("<b>".$user_time->user_name."</b>");
			$new_row->cols[1] = new cer_ReportDataCol($user_time->user_login);
			$new_row->cols[2] = new cer_ReportDataCol($user_time->r_samples);
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($user_time->average_response_time));
			$new_row->cols[4] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($user_avg_login));
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			$new_row->cols[4]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			$total_times += $user_time->average_response_time;
			$total_num_times++;
			$total_samples += $user_time->r_samples;
			$total_login_times += $user_avg_login;
			$system_avg->addSample($user_time->average_response_time,$user_time->r_samples);

			if((int)$user_avg_login)
				$login_avg->addSample($user_avg_login,$user_avg_samples);

			// [JAS]: White Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#FFFFFF";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 5;			
			array_push($this->report_data->rows,$new_row);
		}
		
		if($total_num_times)
		{
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 5;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Totals Heading
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#888888";
			$new_row->cols[0] = new cer_ReportDataCol("Agent Averages");
			$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
			$new_row->cols[2] = new cer_ReportDataCol($total_num_times . " agents");
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString(round($total_times/$total_num_times)));
			$new_row->cols[4] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString(round($total_login_times/$total_num_times)));
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			$new_row->cols[4]->align = "center";
			array_push($this->report_data->rows,$new_row);

			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 5;			
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Totals Heading
			$new_row = new cer_ReportDataRow();
			$new_row->style = "cer_maintable_header";
			$new_row->bgcolor = "#888888";
			$new_row->cols[0] = new cer_ReportDataCol("System Averages (weighted)");
			$new_row->cols[1] = new cer_ReportDataCol("&nbsp;");
			$new_row->cols[2] = new cer_ReportDataCol($total_samples . " samples");
			$new_row->cols[3] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($system_avg->getAverage()));
			$new_row->cols[4] = new cer_ReportDataCol(cer_DateTimeFormat::secsAsEnglishString($login_avg->getAverage()));
			$new_row->cols[2]->align = "center";
			$new_row->cols[3]->align = "center";
			$new_row->cols[4]->align = "center";
			array_push($this->report_data->rows,$new_row);
			
			// [JAS]: Black Spacer
			$new_row = new cer_ReportDataRow();
			$new_row->bgcolor = "#000000";
			$new_row->cols[0] = new cer_ReportDataCol(SPACER_1PX);
			$new_row->cols[0]->col_span = 5;			
			array_push($this->report_data->rows,$new_row);
		}
		
	}
};

?>