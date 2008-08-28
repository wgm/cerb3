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

require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");

/**
 * Database abstraction layer for search data
 *
 */
class search_sql
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
    * @return search_sql
    */
   function search_sql(&$db) {
      $this->db =& $db;
      $this->database_loader =& database_loader::get_instance();
   }

   /**
    * Search on keywords function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function keyword_search($params) {
      $keywords = $params['keywords'];
      $sql = "SELECT DISTINCT ticket_id FROM search_words LEFT JOIN search_index USING ( word_id ) WHERE word IN (%s)";
      return $this->parse_filters($this->db->GetAll(sprintf($sql, $keywords)), $params['filters']);
   }

   /**
    * Get full words matching partial function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_words_from_partial($params) {
      $word = $params['word'];
      $sql = "SELECT word FROM search_words WHERE word LIKE %s";
      return $this->db->GetAll(sprintf($sql, $this->db->qstr($word)));
   }

   /**
    * Search on requester function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function requester_search($params) {
      $requester = $params['requester'];
      $sql = "SELECT DISTINCT ticket_id FROM address LEFT JOIN requestor USING ( address_id ) WHERE address_address LIKE %s";
      return $this->parse_filters($this->db->GetAll(sprintf($sql, $this->db->qstr("%".$requester."%"))), $params['filters']);
   }

   /**
    * Search on part of a ticket id function
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function partial_ticket_id_search($params) {
      extract($params);
      $sql = "SELECT ticket_id FROM ticket WHERE ticket_id LIKE %s OR ticket_mask LIKE %s";
      return $this->parse_filters($this->db->GetAll(sprintf($sql, $this->db->qstr("%".$ticket_search."%"), $this->db->qstr("%".$ticket_search."%"))), $filters);
   }

   function parse_filters($ticket_array, $filters, $full_search = FALSE, $score_for_users = 0, $team_subset_str="") {
      if(is_array($ticket_array)) {
         $prefilter_count = count($ticket_array);
      }
      else {
         $prefilter_count = 0;
      }
      if(!is_array($filters) || count($filters) < 1) {
         return array("results"=>$ticket_array, "page"=>1, "total_pages"=>1, "prefilter_count"=>$prefilter_count, "postfilter_count"=>$prefilter_count);
      }

      if($full_search) {
         $sql = "SELECT %s FROM ticket t %s WHERE %s %s %s %s %s";
         $ticket_list = '1';
      }
      else {
         if(!is_array($ticket_array)) return FALSE;
         if($prefilter_count < 1) return array("results"=>$ticket_array, "page"=>0, "total_pages"=>0, "prefilter_count"=>$prefilter_count, "postfilter_count"=>$prefilter_count);
         $sql = "SELECT %s FROM ticket t %s WHERE t.ticket_id IN (%s) %s %s %s %s";
         $ticket_list = "''";
         foreach($ticket_array as $ticket_item) {
            $ticket_list .= ",'" . $ticket_item['ticket_id'] . "'";
         }
      }
      $where = '';
      
      $join_tables = array();
      $join_tables_actual = array();
      if($score_for_users!==0) {
      	
//      	if($score_for_users === "UNASSIGNED") {
//      		
//      		$join_tables[] = " LEFT JOIN workstation_routing_to_tickets wrt ON (t.ticket_id = wrt.ticket_id) ";
//      		$where .= ' AND wrt.team_id IS NULL ';
//      		
//      	}
//      	else {
	      	
	      	//[mdf] the following line makes it so that any user specific info we are using in this search is always about 
	      	//the server logged in user.  It overwrites the actual userid that was sent in the xml, which would 
	      	//only be useful if someday we want to let users search for other people's suggestions.
	      	//For now this is more secure in that users can only pull their own suggestions.
			$score_for_user = general_users::get_user_id();
//      	}						

      	$join_tables[] = sprintf(" LEFT JOIN dispatcher_delays dd ON (t.ticket_id = dd.ticket_id AND dd.agent_id = %d) ", $score_for_users );
      	$where .= " AND (dd.expire_timestamp IS NULL OR dd.expire_timestamp <= UNIX_TIMESTAMP(NOW()) ) ";

      	$projection = "t.ticket_id, t.ticket_priority as score";
      	
      	$groupby = " GROUP BY t.ticket_id ";


      }
      else {
      	$projection = 't.ticket_id';
      	$groupby = '';
      }

      //always add the queue permissions clause to all searches
      $permittedQueues = $this->getPermittedQueues();
      $where .= sprintf(" AND t.ticket_queue_id IN (%s)", 
      "'".implode("','",$permittedQueues)."'");
      
      $order_by = '';
      $limit = '';
      $has_limit = FALSE;
      $has_orderby = FALSE;
      $has_contacts = FALSE;
      $limit_page = 0;
//      $override_team_permissions = FALSE;
      $contacts_list = array();
      $status_search = "both";
      $tagged_ticket_ids = NULL;
      
      foreach($filters as $key=>$value) {
         switch($key) {
            case 'limit': {
               $has_limit = TRUE;
               if(!is_numeric($value) || $value < 1) {
                  $limit_count = 10;
               }
               else {
                  $limit_count = $value;
               }
               break;
            }
            case 'page': {
               if(!is_numeric($value) || $value < 0) {
                  $limit_page = 0;
               }
               else {
                  $limit_page = $value;
               }
               break;
            }
            case 'order_by': {
               $has_orderby = TRUE;
               $orderby_field = $value;
               break;
            }
            case 'order_by_dir': {
               $orderby_dir = $value;
               break;
            }
            case 'has_skills': {
               if($value) {
                  $where .= " AND t.skill_count > 0 ";
               }
               else {
                  $where .= " AND t.skill_count = 0 ";
               }
               break;
            }
            case 'queues': {
            	if(is_numeric($value)) {
            		$value = array($value);
            	}
               if(is_array($value)) {
                  $queue_list = "'" . implode("','", $value) . "'";
                  $where .= sprintf(" AND t.ticket_queue_id IN ( %s ) ", $queue_list);
               }
               break;
            }
            case 'teams': {
            	if(is_numeric($value)) {
            		$value = array($value);
            	}
               if(is_array($value)) {
                  $team_list = "'" . implode("','", $value) . "'";
                  $join_tables[] = " INNER JOIN queue ON t.ticket_queue_id = queue.queue_id ";
                  $join_tables[] = " INNER JOIN team_queues ON queue.queue_id = team_queues.queue_id ";
                  $where .= sprintf(" AND team_queues.team_id IN ( %s ) ", $team_list);
               }
               break;
            }
            
//            case 'override_team_permissions': {
//               if($value) {
//                  $override_team_permissions = TRUE;
//               }
//               break;
//            }
            case 'owners': {
            	/*
               if(is_array($value)) {
               		$join_tables[] = " INNER JOIN ticket_flags_to_agents tfa ON t.ticket_id = tfa.ticket_id ";
                  $owner_list = "'" . implode("','", $value) . "'";
                  $where .= sprintf(" AND tfa.user_id IN ( %s ) ", $owner_list);
                  
               	$owner_list = "'" . implode("','", $value) . "'";
                  if($value[0] != 0) {
                  	$owner_sql = sprintf("SELECT t.ticket_id from ticket_flags_to_agents tfa WHERE tfa.user_id IN (%s) ", $owner_list);
                  }
                  elseif($value[0] == 0) {
                  	$owner_sql_1 =  " select ticket_id FROM ticket_flags_to_agents ";
                  	$owner_result_1 = $this->db->GetAll(sprintf("SELECT public_user_id FROM public_gui_users WHERE company_id IN ( %s )", $companies_list));
                  	
                  	
                  	
                  	$owner_sql_ =  " SELECT t.ticket_id FROM ticket WHERE ticket_id not in (%s) ";
                  }
               }
               */
               break;
            }
            case 'statuses': {
				if(is_array($value)) {
					$tmpStatus = $value[0];
				}
				else {
					$tmpStatus = $value;
	           	}
           	
           		if($tmpStatus=="open") {
           			$status_search = "open";
           		}
           		elseif ($tmpStatus=="closed") {
           			$status_search = "closed";
           		}
           		else {
           			$status_search = "both";
           		}
               break;
            }
            case 'companies': {
               if(is_array($value)) {
                  $companies_list = "'" . implode("','", $value) . "'";
                  $contacts_db = $this->db->GetAll(sprintf("SELECT public_user_id FROM public_gui_users WHERE company_id IN ( %s )", $companies_list));
                  if(is_array($contacts_db)) {
                     foreach($contacts_db as $contacts_row) {
                        $contacts_list[$contacts_row['public_user_id']] = $contacts_row['public_user_id'];
                     }
                  }
                  $has_contacts = TRUE;
               }
               break;
            }
            case 'contacts': {
               if(is_array($value)) {
                  foreach($value as $contact_id) {
                     $contacts_list[$contact_id] = $contact_id;
                  }
                  $has_contacts = TRUE;
               }
               break;
            }
            case 'created_between': {
            	if(is_array($value)) {
            		$where .= sprintf(" AND t.ticket_date >= FROM_UNIXTIME(%d) AND t.ticket_date <= FROM_UNIXTIME(%d) ", $value["from"], $value["to"]);
            	}
            	break;	
            }
            case 'last_wrote_between': {
            	if(is_array($value)) {
            		$where .= sprintf(" AND th_max.thread_date >= FROM_UNIXTIME(%d) AND th_max.thread_date <= FROM_UNIXTIME(%d) ", $value["from"], $value["to"]);
            		$join_tables[] = " LEFT JOIN thread th_max ON (t.max_thread_id = th_max.thread_id) ";
            	}
            	break;	
            }
            case 'file_name': {
            	$where .= sprintf(" AND attach.file_name LIKE %s ", $this->db->qstr("%" . $value . "%"));
            	$join_tables[] = " INNER JOIN thread th_all ON (t.ticket_id = th_all.ticket_id) ";
            	$join_tables[] = " INNER JOIN thread_attachments attach ON (th_all.thread_id = attach.thread_id) ";
            	break;
            }
            case 'addresses': {
            	if(is_array($value)) {
            		$where .= sprintf(" AND req.address_id IN ( '%s' ) ", implode("','", $value));
            		$join_tables[] = " LEFT JOIN requestor req ON (req.ticket_id = t.ticket_id) ";
            	}
            	else {
            		$where .= sprintf(" AND req.address_id = %s ", $this->db->qstr($value));
            		$join_tables[] = " LEFT JOIN requestor req ON (req.ticket_id = t.ticket_id) ";
            	}
            	break;            		
            }
			case 'tags': {
				if($tag_search_type != "ANY"){
					if(is_array($value)) {
						$tag_id_list = "'" . implode("','", $value) . "'";
						if($tag_search_type == "ANY_SELECTED") {
							//[mdf] user chose to find tickets with any of the tags they checked
							$ticket_result = $this->db->GetAll(sprintf("SELECT distinct ticket_id FROM  `workstation_tags_to_tickets` WHERE tag_id IN ( %s )", $tag_id_list));
							$tagged_ticket_ids = array();
							foreach($ticket_result as $row) {
								$tagged_ticket_ids[] = $row['ticket_id'];
							}							
						}
						else if($tag_search_type =="ALL_SELECTED") {
							//[mdf] user chose to find tickets with all the tags they checked

							//[mdf] create an array of resultsets (of ticket ids), one for each tagid being searched for
							$tmp_result_array = array();
							foreach($value AS $tagid) {
								$ticket_result_array[] = $this->db->GetAll(sprintf("SELECT distinct ticket_id FROM  `workstation_tags_to_tickets` WHERE tag_id = %d", $tagid));				
							}
							
							//[mdf] put the values from the first result set into our final list of taged_ticket_ids (to use in next intersection step)
							$tagged_ticket_ids = array();
							foreach($ticket_result_array[0] as $row) {
								$tagged_ticket_ids[] = $row['ticket_id'];
							}
							
							//[mdf] calculate the intersection of each result set (after the first) obtained to simulate a UNION query
							for($i=1; $i < sizeof($ticket_result_array); $i++) {
								$tmp_ticket_ids = array();
								foreach($ticket_result_array[$i] as $row) {
									$tmp_ticket_ids[] = $row['ticket_id'];
								}											
								$tagged_ticket_ids = array_intersect($tagged_ticket_ids, $tmp_ticket_ids);
							}
						}
					}
					else {
						//[mdf] user searched for ANY_SELECTED or ALL_SELECTED but nothing was checked, so get ticket ids that have no tags
						$ticket_result = $this->db->GetAll("SELECT distinct ticket_id FROM  ticket t LEFT JOIN `workstation_tags_to_tickets` wtt WHERE wtt.tag_id IS NULL ");
						$tagged_ticket_ids = array();
						if(is_array($ticket_result)) {
							foreach($ticket_result as $row) {
								$tagged_ticket_ids[] = $row['ticket_id'];
							}								
						}			
					}
				}
				break;
			}
			case 'tag_search_type': {
				$tag_search_type = $value;
				break;
			}
			case 'spotlight_by_user': {
				$spotlight_user = $value;
            	$where .= sprintf(" AND tsa.agent_id = %d ", $spotlight_user);
            	
//       	       	[mdf] don't include flagged tickets in suggestions
//		       	$exclusionTickets = $this->getFlaggedTicketIds();
//		       	if(!empty($exclusionTickets)) {
//					$where .= ' AND t.ticket_id NOT IN ('.$exclusionTickets.')';
//		       	}
        	
            	$join_tables[] = " INNER JOIN ticket_spotlights_to_agents tsa ON (t.ticket_id = tsa.ticket_id) ";
				break;
			}
			case 'not_flagged_by_user': {
				//[mdf] changed this to work more like 'not_flagged' ie. ignores the user param passed
				$not_user = $value;
				
         		$join_tables[] = " LEFT JOIN ticket_flags_to_agents ntfa ON (t.ticket_id = ntfa.ticket_id) ";
				//$where .= sprintf(" AND (ntfa.agent_id != %d OR ntfa.agent_id IS NULL) ", $not_user);
				$where .= sprintf(" AND ntfa.agent_id IS NULL ", $not_user);
				
				break;
			}
			case 'not_flagged': {
				$not_user = $value;
				
         		$join_tables[] = " LEFT JOIN ticket_flags_to_agents ntfa ON (t.ticket_id = ntfa.ticket_id) ";
				$where .= sprintf(" AND ntfa.agent_id IS NULL ", $not_user);
				
				break;
				
			}
			case 'flagged_by_user': {
				$flag_user = $value;
            	$where .= sprintf(" AND tfa.agent_id = %d ", $flag_user);
            	$join_tables[] = " INNER JOIN ticket_flags_to_agents tfa ON (t.ticket_id = tfa.ticket_id) ";
            	break;
			}
         }
      }
      if($has_limit) {
         $limit = " LIMIT " . ($limit_page * $limit_count) . "," . $limit_count;
      }
      
      if($score_for_users) {
        $has_orderby = TRUE;
        $orderby_field = 'score';
        $orderby_dir = 'desc';
      }	
      
      if($has_orderby) {
         $order_by = " ORDER BY " . $orderby_field . " " . $orderby_dir;
      }
      if($has_contacts) {
         if(count($contacts_list) > 0) {
            $contact_list = "'" . implode("','", array_values($contacts_list)) . "'";
            $addresses_db = $this->db->GetAll(sprintf("SELECT address_id FROM address WHERE public_user_id IN ( %s )", $contact_list));
            if(is_array($addresses_db)) {
               $address_array = array();
               foreach($addresses_db as $address_row) {
                  $address_array[$address_row["address_id"]] = $address_row["address_id"];
               }
               $address_list = "'" . implode("','", array_values($address_array)) . "'";
            }
         }
         else {
            $address_list = "'-1'";
         }
         $join_tables[] = " LEFT JOIN requestor req ON (req.ticket_id = t.ticket_id) ";
         $where .= sprintf(" AND req.address_id IN ( %s ) ", $address_list);
      }

		//[mdf] ticket id list was created depending on if tags were specified with tag_search_type either ANY_SELECTED or ALL_SELECTED
	   if($tagged_ticket_ids !== NULL) {
      		if(sizeof($tagged_ticket_ids) > 0) {
			    $where .= sprintf(" AND t.ticket_id IN (%s) ", "'" . implode("','", $tagged_ticket_ids) . "'");
			}
			else {
				//[mdf] selected tags were specified, but there were no matching ticket ids, so make zero results appear
				$where .= " AND  1=2 ";
			}      	
      	
      }
     
	      if($score_for_users) {
	      	// Tickets Suggestions"
	      	$where  .= " AND t.is_waiting_on_customer  = 0 AND t.is_closed = 0 AND t.is_deleted = 0 ";
	      }
	      elseif(isset($flag_user)) {
	      	// "My Tickets"
	      	$where .= " AND t.is_closed = 0 AND t.is_deleted = 0 ";
	      }
	      else {
	      	//search
		      if($status_search =="open") {
		      	$where .= " AND t.is_closed = 0 AND t.is_deleted = 0";
		      }
		      elseif($status_search == "closed") {
	      		$where .= "AND (t.is_closed = 1 OR t.is_deleted = 1 ) ";
		      }
	      }
//	    if($score_for_users !== "UNASSIGNED") {
		    //all searches need to enforce team permissions for the user searching
//			$join_tables[] = $this->getTeamsJoinSQL(); //joins workstation_routing_to_tickets to tickets
//			$where .= $this->getTeamsCondition(general_users::get_user_id(), $team_subset_str);	      
//	    }
	      
//		if(!$override_team_permissions) {
//			if($score_for_users == 0) {
//				$queue_array = $this->database_loader->Get("teams", "get_queues_from_user_id_read_writable", array("user_id"=>general_users::get_user_id(), "team_subset_str"=>$team_subset_str));
//			}
//			else {
//				$queue_array = $this->database_loader->Get("teams", "get_queues_from_user_id_workable", array("user_id"=>$score_for_users,"team_subset_str"=>$team_subset_str));			
//			}
//			$queues_list = "'";
//			if(is_array($queue_array)) {
//				foreach($queue_array as $queue_row) {
//					$queues_list .= "','" . $queue_row["queue_id"];
//				}
//			}
//			$queues_list .= "'";
//			$where .= sprintf(" AND t.ticket_queue_id IN ( %s ) ", $queues_list);      		
//		}
      
		//$count_join_tables_sql = implode(' ', array_unique($join_tables_without_nextsteps));
		$join_tables = array_unique($join_tables);

		$join_tables_actual = array_merge(array_unique($join_tables_actual), $join_tables);

		$count_join_tables_sql = implode(' ', $join_tables);
		$join_tables_actual_sql = implode(' ', $join_tables_actual);

		//echo sprintf($sql, 'COUNT(t.ticket_id)', $count_join_tables_sql, $ticket_list, $where, '', '', '');exit();
		$postfilter_count = $this->db->GetOne(sprintf($sql, 'COUNT(DISTINCT t.ticket_id)', $count_join_tables_sql, $ticket_list, $where, '', '', ''));

		if($has_limit) {
			$total_pages = ceil($postfilter_count / $limit_count);
		}
		else {
			$total_pages = 1;
		}
      
		//echo sprintf($sql, $projection, $join_tables_actual_sql, $ticket_list, $where, $groupby, $order_by, $limit);exit();
		$results = $this->db->GetAll(sprintf($sql, $projection, $join_tables_actual_sql, $ticket_list, $where, $groupby, $order_by, $limit));
		//$postfilter_count = sizeof($results);
		//echo $where;exit();
		return array("page"=>$limit_page,
		"total_pages"=>$total_pages,
		"prefilter_count"=>$prefilter_count,
		"postfilter_count"=>$postfilter_count,
		"results"=>$results);
	}

//	/**
//	 * gets the list of tickets flagged by $user_id so we can avoid those when pulling suggested tickets
//	 *
//	 * @param int user id to get flagged tickets for
//	 * @return comma separated string of ticket ids
//	 */
//	function getFlaggedTicketIds() {
//		//[mdf] get the list of my flagged tickets so we can avoid those when pulling suggestions
//       	$myFlaggedResult = $this->db->GetAll(sprintf("SELECT ticket_id FROM ticket_flags_to_agents "));
//       	$myFlaggedTicketIds = array();
//		if(is_array($myFlaggedResult)) {
//			foreach($myFlaggedResult as $flag_row) {
//				$myFlaggedTicketIds[] = $flag_row['ticket_id'];
//			}
//		}       	
//       	$myFlaggedTicketIdList = implode(",", $myFlaggedTicketIds);	
//       	return $myFlaggedTicketIdList;
//	}

//	function getTeamsJoinSQL() {
//		return " INNER JOIN workstation_routing_to_tickets wrt ON (t.ticket_id = wrt.ticket_id) ";
//	}

	function getPermittedQueues() {
   		require_once(FILESYSTEM_PATH . "/cerberus-api/acl/CerACL.class.php");
		$cerAcl =& CerACL::getInstance();
		$queues = $cerAcl->queues;
		return $queues;
	}
	
	function filter_search($params) {
		return $this->parse_filters(array(), $params['filters'], TRUE, $params['score_for_users'], $params['team_subset_str']);
	}

}