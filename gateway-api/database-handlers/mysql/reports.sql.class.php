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
|		Jeremy Johnstone    (jeremy@webgroupmedia.com)   [JSJ]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "gateway-api/database-handlers/mysql/reports_matrix_query_builder.class.php");

/**
 * Database abstraction layer for chat reports data
 *
 */
class reports_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
   var $db;

   /**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    * @return reports_sql
    */
   function reports_sql(&$db) {
      $this->db =& $db;
   }
	
function visitor_per_hour($params) {
      extract($params);
      $sql = "SELECT count(visitor_id) AS num_visits, date_format(FROM_UNIXTIME(visitor_time_start),'%%H') AS visit_hour FROM chat_visitors WHERE visitor_time_start > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '%d' DAY)) GROUP BY visit_hour";
		return $this->db->GetAll(sprintf($sql, $days));				
	}
	
   function visitor_per_hour_average($params) {
      extract($params);
      $sql = "SELECT count(visitor_id) AS num_visits, date_format(FROM_UNIXTIME(visitor_time_start),'%%a') AS visit_day, date_format(FROM_UNIXTIME(visitor_time_start),'%%H') AS visit_hour FROM chat_visitors WHERE visitor_time_start > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '%d' DAY)) GROUP BY visit_day, visit_hour ORDER BY visit_hour";
		return $this->db->GetAll(sprintf($sql, $days));				
	}
	
	function visitor_per_day($params) {
	   extract($params);
	   $sql = "SELECT count(visitor_id) AS num_visits, date_format(FROM_UNIXTIME(visitor_time_start),'%%a') AS visit_day, date_format(FROM_UNIXTIME(visitor_time_start),'%%w') AS day_num FROM chat_visitors WHERE visitor_time_start > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '%d' %s)) GROUP BY visit_day ORDER BY day_num";
	   return $this->db->GetAll(sprintf($sql, $range, $scope));
	}
	
	function visitor_per_day_average($params) {
	   extract($params);
	   $sql = "SELECT count(visitor_id) AS num_visits, date_format(FROM_UNIXTIME(visitor_time_start),'%%a') AS visit_day, date_format(FROM_UNIXTIME(visitor_time_start),'%%w') AS day_num, date_format(FROM_UNIXTIME(visitor_time_start),'%%U') AS week_num FROM chat_visitors WHERE visitor_time_start > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '%d' %s)) GROUP BY week_num, day_num ORDER BY week_num, day_num";
	   return $this->db->GetAll(sprintf($sql, $range, $scope));
	}
	
	function referrer_hosts($params) {
	   extract($params);
	   $sql = "SELECT count(cvp.page_referrer_host_id) as num_referrals, h.host FROM chat_visitor_pages cvp INNER JOIN stat_hosts h ON (cvp.page_referrer_host_id = h.host_id) WHERE cvp.page_timestamp > UNIX_TIMESTAMP(DATE_SUB(NOW(),INTERVAL '%d' DAY)) %s GROUP BY h.host HAVING (h.host <> '') ORDER BY num_referrals DESC LIMIT 0,%d";
	   if(isset($exclude_hosts) && is_array($exclude_hosts)) {
	      $exclude_string = sprintf(" AND h.host NOT IN ('%s') ", implode("','", $exclude_hosts));
	   }
	   else {
	      $exclude_string = '';
	   }
	   return $this->db->GetAll(sprintf($sql, $range, $exclude_string, $limit));
	}
	
	function referrer_urls($params) {
	   extract($params);
	   $sql = "SELECT count(cvp.page_referrer_url_id) as num_referrals, u.url, h.host FROM chat_visitor_pages cvp INNER JOIN stat_urls u ON (cvp.page_referrer_url_id=u.url_id) INNER JOIN stat_hosts h ON (cvp.page_referrer_host_id=h.host_id) WHERE cvp.page_timestamp > UNIX_TIMESTAMP(DATE_SUB(NOW(),INTERVAL '%d' DAY)) %s GROUP BY u.url_id HAVING (u.url <> '') ORDER BY num_referrals DESC LIMIT 0,%d";
	   if(isset($exclude_hosts) && is_array($exclude_hosts)) {
	      $exclude_string = sprintf(" AND h.host NOT IN ('%s') ", implode("','", $exclude_hosts));
	   }
	   else {
	      $exclude_string = '';
	   }
	   return $this->db->GetAll(sprintf($sql, $range, $exclude_string, $limit));
	}   
   
	function get_saved_reports($params) {
		extract($params);

		$sql = "SELECT report_id, report_name, report_data FROM saved_reports ";
		
		return $this->db->GetAll($sql);
	}
	
	function get_saved_report_by_id($params) {
		extract($params);
		//$id
		$sql = "SELECT report_id FROM saved_reports WHERE report_id = %d";
		return $this->db->GetAll(sprintf($sql, $id));
	}
   
	function delete_saved_report($params) {
		extract($params);

		$sql = "DELETE FROM saved_reports WHERE report_id = %d";
		return $this->db->Execute(sprintf($sql, $id));
	}
	
	function create_saved_report($params) {
		extract($params);
		//$name, $data, $id

		$sql = "INSERT INTO saved_reports () VALUES ()";
		
		$this->db->Execute($sql);
		
		return mysql_insert_id();
	}

	function update_saved_report($params) {
		extract($params);
		//$id, $name, $data

		$sql = "UPDATE saved_reports SET report_name = %s, report_data = %s WHERE report_id = '%d' ";
		$sql = sprintf($sql, $this->db->qstr($name), $this->db->qstr($data), $id);
		
		$this->db->Execute($sql);
	}	
	
	function get_report_data($params) {
		extract($params);
		//$dataset, $column_tokens, $groupings, $filters, $first_day_of_week, $ordering
		$sql_select = "SELECT ";
	    $prefix = "";
		
	    $disp_tables= array();
		//build most of the "SELECT" part of the query, including fields that will be needed only as hyperlink ids 
		if(is_array($column_tokens))
    	foreach($column_tokens as $token) {
			$token_arr = custom_report_mappings::get_col_info($token);

			$col_func_start = "";
			$col_func_end = "";
			if($token_arr['type'] == "timestamp" || $token_arr['type'] == "datetime") {
				$col_func_start = " UNIX_TIMESTAMP( ";
				$col_func_end = " ) ";
			}
	    	$sql_select .= $prefix . $col_func_start . $token_arr['table_name'] . "." . $token_arr['column_name'] . $col_func_end .  " " . $token;
			$prefix = ", ";
      
			$disp_tables[] = $token_arr['table_name'];
			
	  		//select extra field id for this token if a mapping is defined for it
			$link_token = custom_report_mappings::get_link_col_token($token);
			if($link_token != "") {
				$link_token_arr = custom_report_mappings::get_col_info($link_token);
				$sql_select .= $prefix . $link_token_arr['table_name'] . "." . $link_token_arr['column_name'] . " " . $link_token;
			}
      
		}
    	
		
		$grouping_tables = $this->get_tables_array($groupings);
		$filter_tables = $this->get_tables_array($filters);
		$ordering_tables = $this->get_tables_array($ordering);

		//merge together, remove duplicates, and reindex all of the possible tables from our arrays
		$table_list = array_merge($disp_tables, $grouping_tables, $filter_tables, $ordering_tables);
		$table_list = array_values(array_unique($table_list));		
		
		//create a dataset object which knows how all the tables in the dataset are joined
		$dataset_obj = custom_report_mappings::get_dataset($dataset);

		if(null == $dataset_obj)
			return FALSE;
		
		//builds the query 'FROM' clause 
		$sql_from = $dataset_obj->get_query($table_list);
		//$sql_from = custom_report_mappings::get_join_sql_from_dataset_token($dataset);
		//echo $sql_from; exit();
		
		//add a WHERE clause for each filter
		$sql_where = " WHERE 1=1 ";
		if(is_array($filters))
		foreach($filters as $filter) {
			$sql_where .= custom_report_mappings::get_condition_mapping($filter['column_token'], $filter['operator'], $filter['value']);
    	}

		if(sizeof($groupings) > 0) {
			//Build the "ORDER BY" clause by looping through any groupings
			$sql_order = " ORDER BY  " . 
			$prefix = "";
			for($i=0; $i < sizeof($groupings); $i++) {
				$token_arr = custom_report_mappings::get_col_info($groupings[$i]['column_token']);
				$sql_order .= $prefix . $token_arr['table_name'] . '.' . $token_arr['column_name'] . ' ' . $groupings[$i]['sort'] ;
				
				$prefix = ", ";
			}
		}
		else {
			//this is a list report, Build the "ORDER BY" clause by looping through any orderings 
			if(sizeof($ordering) > 0) {
				$sql_order = 'ORDER BY ';
				$prefix="";
				for($i=0; $i < sizeof($ordering); $i++) {
					$sql_order .= $prefix . ' ' . $ordering[$i]['column_token'] . ' ' . $ordering[$i]['sort'];
					$prefix=",";
				}
			}
		}
		//$sql_where .= " AND (opportunity.opportunity_id in ('5956','5959','5960','5961')) ";
		//$sql_where .= " AND opportunity.amount = 499.00 ";
		//print_r($column_tokens);
		$sql = $sql_select . $sql_from . $sql_where . $sql_order;
		//$sql .= " LIMIT 1000 "; 
		//echo "^^^". $sql . "^^^";
		
		//exit();

		if(sizeof($groupings) > 0) {
    		$results = $this->db->GetAll(sprintf($sql));
		}
		else {
	    	$results = $this->db->Execute(sprintf($sql));
		}
		return $results;
		
	}
	
	function get_tables_array($set) {
		$result = array();
		for($i=0; $i < sizeof($set); $i++) {
			$token = $set[$i]['column_token'];
			$col_info = custom_report_mappings::get_col_info($token);
			$result[] = $col_info['table_name'];
		}
		return $result;
	}	
	
	function get_matrix_report_data($params) {
		extract($params);
		//$dataset, $display_columns , $groupings, $global_filters, $first_day_of_week

		$results=array();
		
		for($i=0; $i < sizeof($display_columns); $i++) {
			$query_builder = new reports_matrix_query_builder($dataset, $display_columns[$i], $groupings, $global_filters, $first_day_of_week);
			$results[$i] =& $this->db->GetAll(sprintf($query_builder->get_query()));
			//echo "got results " . sizeof($results[$i]) ."\n";
		}
		return $results;

	}
	
	function get_tag_report_data($params) {
		extract($params);
		//date_start date_end
		if(!isset($date_start))
			$date_start = null;
		if(!isset($date_end))
			$date_end = null;
		$where = " WHERE 1=1 ";
		if($date_start !== null) {
			$where .= sprintf(" AND  ticket_date >= %s ", $this->db->DBDate($date_start));
		}
		if($date_end !== null) {
			$where .= sprintf(" AND  ticket_date <= %s ", $this->db->DBDate($date_end));
		}
		
		$sql = "SELECT wt.tag_id, wt.tag_name, count(*) ticket_count
		FROM workstation_tags wt
		INNER JOIN workstation_tags_to_tickets wtt ON wtt.tag_id = wt.tag_id 
		INNER JOIN ticket t on wtt.ticket_id = t.ticket_id ";
		
		$sql .= $where;
		
		$sql .=" GROUP BY wt.tag_id 
		ORDER BY ticket_count DESC
		";
		//echo $sql;exit();
		$results =& $this->db->GetAll($sql);
		return $results;
		
	}
	
	function get_performance_report_data($params) {

		extract($params); //grouping_type, first_day_of_week, date_start, date_end, $agent_ids
		$group_expression = "";
		switch($grouping_type) {
			case "DATE_WEEK":
				$select_format = "";
				if($first_day_of_week == 1) {
					$select_format = "Week #%V %X";
	    			$group_expression = " DATE_FORMAT(l.timestamp, '%X-%V') ";
				}
				else {
					$select_format = "Week #%v %x";
					$group_expression = " DATE_FORMAT(l.timestamp, '%x-%v') ";
				}				
			break;
			case "DATE_MONTH":
				$select_format = "%M %Y";
				$group_expression = " DATE_FORMAT(l.timestamp, '%Y%m') ";				
			break;
			case "DATE_YEAR":
				$select_format = "%Y";
				$group_expression = " YEAR(l.timestamp) ";
			break;
			default:
			case "DATE_DAY":
				$select_format = "%a %b %d %Y";
				$group_expression = " DATE_FORMAT(l.timestamp, '%Y-%m-%d') ";
			break;				
			
		}

		for($i=0; $i < sizeof($agent_ids); $i++) {
			$agent_ids[$i] = $this->db->qstr($agent_ids[$i]);
		}
		$agentIdList = implode(",", $agent_ids);
		
		$sql = "SELECT COUNT(*)  AS ticket_count, ".
				"u.user_name, ".
				"DATE_FORMAT( l.timestamp,  '".$select_format."'  )  AS ticket_date, ". //UNIX_TIMESTAMP(l.timestamp) timestamp, ".
				"l.user_id " .
				"FROM ticket_audit_log l ".
				"LEFT JOIN user u ON ( l.user_id = u.user_id ) ".
				"LEFT JOIN ticket t ON ( t.ticket_id = l.ticket_id ) ".
				"WHERE u.user_login !=  '' AND t.is_deleted != 1 ".
				"AND l.action = 4 ";
		$sql .= sprintf("AND u.user_id IN (%s) ", $agentIdList); 
		
		if($date_start != NULL) {
			$sql .=	sprintf("AND l.timestamp >= FROM_UNIXTIME(%d) ", $date_start);
		}
		if($date_end != NULL) {
			$sql .=	sprintf("AND l.timestamp <= FROM_UNIXTIME(%d) ", $date_end+86399);
		}
				
		$sql.=	sprintf("GROUP BY %s l.user_id ", $group_expression.",");
		$sql .=	"ORDER  BY l.timestamp ASC";
//				echo $sql;exit();
		$results =& $this->db->GetAll($sql);
		return $results;
	}

}