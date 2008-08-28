<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| Developers involved with this file:
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");

class reports_custom_list extends reports_custom 
{

	function create_grouped_array() {
	}
	
	/*
	 * Override parent function because no special sorting is needed for this report type
	*/
	function presort_results(&$result_data) {
	}
	
	function create_report_xml_streamed() {
      if(!headers_sent()) {
	   	header("HTTP/1.0 200 OK");
	   	header("Status: 200");
         header("Content-Type: application/xml");
         //header("Content-Length: " . strlen(str_replace(chr(0)," ",$xml)));
      }	
      
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<cerberus_xml><status>success</status><version>0.1.5</version>';
		echo '<data>';
		echo '<report>';
		echo '<title>'.reports_custom::escape($this->model->title) .'</title>';
		echo '<display_columns>';

		if(is_array($this->model->display_column_tokens))
		foreach($this->model->display_column_tokens AS $key=>$val) {
			$this->col_info[$val] = custom_report_mappings::get_col_info($val);
			echo '<col name="'.reports_custom::escape($this->col_info[$val]['friendly_name']).'"/>';
		}
		echo '</display_columns>';
		
		if(is_array($this->model->filters)) {
			echo '<filters>';
			foreach($this->model->filters AS $key=>$val) {
				$friendly_operator = custom_report_mappings::get_operator_friendly_name($val['operator']);
				$col_inf = custom_report_mappings::get_col_info($val['column_token']);
				echo '<filter name="'.reports_custom::escape($col_inf['friendly_name']).'">';
				echo '<operator>'.reports_custom::escape($friendly_operator).'</operator>';
				echo '<value>' .reports_custom::escape($val['value_original']) . '</value>';
				echo '</filter>';
			}      				
			echo '</filters>';
		}		
		
		echo '<list>';
		echo '<data_rows>';		
		while(!$this->report_data->EOF) {
			
			echo '<row>';

			if(is_array($this->model->display_column_tokens))
			foreach($this->model->display_column_tokens AS $val) {
				$link = $this->get_link_attribute($this->report_data->fields, $val);
				echo '<col';
				if($link != "") {
					echo ' href="'.$link.'"';
				}
				
				$str = reports_custom::escape($this->report_data->fields[$val]);
				if($this->col_info[$val]['type'] == "unixtimestamp" 
						|| $this->col_info[$val]['type'] == "datetime"
						|| $this->col_info[$val]['type'] == "timestamp") {
					echo ' type="date"';
					//$str = gmdate("m/d/Y H:i",$this->report_data[$i][$val] + $this->model->UTC_time_offset);
					$str = reports_custom::escape(gmdate("m/d/Y",$this->report_data->fields[$val] + $this->model->UTC_time_offset));
				}
				//[mdf] display elapsed time for Ticket Time Worked
				if($val == "TIK_TIME_WORKED")
					$str = cer_DateTimeFormat::secsAsEnglishString($str);		
				
				echo '>';
				echo $str.'</col>';
				

			}
			
			echo '</row>';
			$this->report_data->MoveNext();
		}
		echo '</data_rows>';
		echo '</list>';
		echo '</report>';
		echo '</data>';
		echo '</cerberus_xml>';
		exit();
		
	}   
	
   
	function create_report_xml() {
		$xml =& xml_output::get_instance();
		$data =& $xml->get_child("data", 0);
		$report =& $data->add_child("report", xml_object::create("report"));

		$report->add_child("title", xml_object::create("title", $this->model->title));
		$display_columns =& $report->add_child("display_columns", xml_object::create("display_columns"));
		//print_r($this->model->display_column_tokens);
		if(is_array($this->model->display_column_tokens))
		foreach($this->model->display_column_tokens AS $key=>$val) {
			$this->col_info[$val] = custom_report_mappings::get_col_info($val);
			$column =& $display_columns->add_child("col", xml_object::create("col", NULL, array('name'=>$this->col_info[$val]['friendly_name'])));
		}      


		if(is_array($this->model->filters)) {
			$filters =& $report->add_child("filters", xml_object::create("filters", NULL, array()));
			foreach($this->model->filters AS $key=>$val) {
				$friendly_operator = custom_report_mappings::get_operator_friendly_name($val['operator']);
				$col_inf = custom_report_mappings::get_col_info($val['column_token']);
				$filter =& $filters->add_child("filter", xml_object::create("filter", NULL, array("name"=>$col_inf['friendly_name'])));
				$filter->add_child("operator", xml_object::create("operator", $friendly_operator, array()));
				$filter->add_child("value", xml_object::create("value", $val['value_original'], array()));
			}      	
		}

		$list =& $report->add_child("list", xml_object::create("list"));
		$data_rows =& $list->add_child("data_rows", xml_object::create("data_rows"));
		for($i=0; $i < sizeof($this->report_data); $i++) {
			$data_row =& $data_rows->add_child("row", xml_object::create("row"));

			if(is_array($this->model->display_column_tokens))
			foreach($this->model->display_column_tokens AS $val) {
				$col =& $data_row->add_child("col", xml_object::create("col", $this->report_data[$i][$val], array()));
				if($this->col_info[$val]['type'] == "unixtimestamp") {
					$col->add_attribute("type", "date");
					//$str = gmdate("m/d/Y H:i",$this->report_data[$i][$val] + $this->model->UTC_time_offset);
					$str = gmdate("m/d/Y",$this->report_data[$i][$val] + $this->model->UTC_time_offset);
					$col->set_data($str);						
				}

				$link = $this->get_link_attribute($this->report_data[$i], $val);
				if($link != "") {
					$col->add_attribute("href", $link);
				}
			}
		}
		
	}   

}

