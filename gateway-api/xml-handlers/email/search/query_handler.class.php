<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/search.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles getting view data
 *
 */
class query_handler extends xml_parser
{
	/**
    * XML data packet from client GUI
    *
    * @var object
    */
	var $xml;

	/**
    * Class constructor
    *
    * @param object $xml
    * @return query_handler
    */
	function query_handler(&$xml) {
		$this->xml =& $xml;
	}

	/**
    * main() function for this class. 
    *
    */
	function process() {
		$users_obj =& new general_users();
		if($users_obj->check_login() === FALSE) {
			xml_output::error(0, 'Not logged in. Please login before proceeding!');
		}

		$search_obj =& new email_search();

		$search_type = $this->xml->get_child_data("search_type", 0);
		if(empty($search_type)) {
			$search_type = 'keyword';
		}

		$score_for_users = $this->xml->get_child_data("score_for_users", 0);
		if(empty($score_for_users)) {
			$score_for_users = 0;
		}
		
		$team_subset_str="";
		$teams_elm =& $this->xml->get_child("teams", 0);
		if(is_object($teams_elm)) {
			$teams_elm_children =& $teams_elm->get_children("team");
			$firstTime=true;
			if(is_array($teams_elm_children))
			foreach($teams_elm_children AS $team_elm) {
					if(!$firstTime) {
						$team_subset_str.=",";
					}
					else {$firstTime=false;}
					
					$team_subset_str.=$team_elm->get_attribute('id',FALSE);
			}
		}

		$fields_xml =& $this->xml->get_child("fields", 0);
		if(is_object($fields_xml)) {
			$fields_xml_children =& $fields_xml->get_children();
		}
		$fields = array();

		if(is_array($fields_xml_children)) {
			foreach($fields_xml_children as $field_xml_instance) {
				foreach($field_xml_instance as $field_xml) {
					$fields[$field_xml->get_token()] = $field_xml->get_data_trim();
				}
			}
		}

		$filters_xml =& $this->xml->get_child("filters", 0);
		if(is_object($filters_xml)) {
			$filters_xml_children =& $filters_xml->get_children();
		}
		$filters = array();

		if(is_array($filters_xml_children)) {
			foreach($filters_xml_children as $filters_xml_instance) {
				foreach($filters_xml_instance as $filter_xml) {
					$token = strtolower($filter_xml->get_token());
					if(!$filter_xml->has_children()) {
						$filters[$token] = $filter_xml->get_data_trim();
					}
					else {
						$filters[$filter_xml->get_token()] = array();
						
//						if($token == "next_step") {
//							$filters[$token] =& $this->parse_next_step_filters($filter_xml);
//						}
//						else {
						$filter_xml_children = $filter_xml->get_children();
						if(is_array($filter_xml_children)) {
							foreach($filter_xml_children as $child_xml_instances) {
								foreach($child_xml_instances as $child_xml) {
									$child_token = $child_xml->get_token();
									if("date_range" == $child_token) {
										$from = $child_xml->get_attribute("from", FALSE);
										$to = $child_xml->get_attribute("to", FALSE);
										if(!is_numeric($from) && !is_numeric($to)) {
											xml_output::error(0, "Invalid date_range specified for " . $token);
										}
										$filters[$token]["from"] = $from;
										$filters[$token]["to"] = $to;
									}
									else {
										$child_id = $child_xml->get_attribute("id", FALSE);
										if(is_numeric($child_id)) {
											$filters[$token][] = $child_id;
										}
										else {
											$filters[$token][] = $child_xml->get_data_trim();
										}
									}
								}
							}
						}
//						}
					}
				}
			}
		}
		//print_r($filters);

		if($search_obj->do_search($search_type, $fields, $filters, $score_for_users, $team_subset_str) === FALSE) {
			xml_output::error(0, 'Search query failed');
		}
		else {
			xml_output::success();
		}
	}
	
//	function parse_next_step_filters(&$filter_xml) {
///* example:
//	<next_step>
//	<assigned_to>
//		<agent id="1"/>
//		<agent id="2"/>
//		<team id="1"/>
//	</assigned_to>
//	<priority>85</priority>
//	<completed>0</completed>
//	<delay_customer>1</delay_customer>
//	<delay_date_past>1</delay_date_past>
//</next_step>*/		
//		$next_step_filter = array();
//		$filter_xml_children = $filter_xml->get_children();
//		if(is_array($filter_xml_children)) {
//			foreach($filter_xml_children as $child_xml_instances) {
//				foreach($child_xml_instances as $child_xml) {
//					$child_token = $child_xml->get_token();
//					
//					switch($child_token) {
//						case 'assigned_to':
//							$assign_children = $child_xml->get_children();
//							if(is_array($assign_children)) {
//								foreach($assign_children as $assign_child_instances) {
//									foreach($assign_child_instances as $assign_xml) {
//										$assign_token = $assign_xml->get_token();
//										$assign_id = $assign_xml->get_attribute("id", FALSE);
//										if(is_numeric($assign_id)) {
//											$next_step_filter['assignment'][$assign_token][]= $assign_id;
//										}										
//									}
//								}
//							}
//						break;
//						case 'priority':
//						case 'completed':
//						case 'delay_customer':
//						case 'delay_date_past':
//							$next_step_filter[$child_token] =  $child_xml->get_data_trim();
//						break;
//					}
//				}
//			}
//		}
//		//print_r($next_step_filter);exit();
//		return $next_step_filter;
//		
//	}
	
}
