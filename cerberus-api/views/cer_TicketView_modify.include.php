<?php
	if(empty($audit_log)) {
		$audit_log = CER_AUDIT_LOG::getInstance();
	}
	
	$cfg = CerConfiguration::getInstance();
	$db = cer_Database::getInstance();

	switch($form_submit)
	{
		case "tickets_modify":
		{
			@$bids = $_REQUEST["bids"];
			@$mass_action_string = $_REQUEST['mass_commit_list'];

			if(empty($mass_action_string))
				return;
			
			$mass_actions = array();
			$tmp_actions = explode('||',$mass_action_string);
			if(!is_array($tmp_actions)) $tmp_actions = array($tmp_actions);

			foreach($tmp_actions as $action_item) {
				$action = explode('__',$action_item);
				if(!is_array($action)) continue;
				$mass_actions[] = array($action[0],$action[1]);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
			
			if (is_array($mass_actions) && !empty($bids)) {
				CerSecurityUtils::integerArray($bids);
				
				foreach($mass_actions as $iter => $action) {
					list($action_id,$action_value) = $action;
					
					switch($action_id)
					{
						case "priority":
							foreach($bids as $bid) {
								CerWorkstationTickets::setTicketPriority($bid,intval($action_value));
							}
							break;
						
						case "status":
							if($action_value != "open" && $action_value != "closed" && $action_value != "deleted") break;
							foreach($bids as $bid) {
								CerWorkstationTickets::setTicketStatus($bid,$action_value);
								// [JAS]: [TODO] Add close autoresponse (but we need to check previous status somehow to not dupe
							}
							break;
							
						case "queue":
							foreach($bids as $bid) {
								CerWorkstationTickets::setTicketMailbox($bid,intval($action_value));
							}
							break;
							
						case "due":
							if(!empty($action_value)) {
								$due_date = new cer_DateTime($action_value);
								$due_date->changeGMTOffset($cfg->settings["server_gmt_offset_hrs"],$session->vars["login_handler"]->user_prefs->gmt_offset);
							
								if(isset($due_date)) {	
										$sql = sprintf("UPDATE ticket SET ticket_due = '%s' WHERE ticket_id IN (%s)",
												$due_date->getDate("%Y-%m-%d %H:%M:%S"),
												implode(',', $bids)
											);
									$db->query($sql);
								}
							} else { // clear
								$sql = sprintf("UPDATE ticket SET ticket_due = '%s' WHERE ticket_id IN (%s)",
										date("Y-m-d H:i:s",0),
										implode(',', $bids)
									);
								$db->query($sql);
							}
							
							break;
							
						case "spam":
							if(intval($action_value)) {
								foreach($bids as $bid) {
									CerWorkstationTickets::markSpam($bid);
								}
							} else {
								foreach($bids as $bid) {
									CerWorkstationTickets::markHam($bid);
								}
							}
							break;
							
						case "waiting":
							if(intval($action_value)) {
								foreach($bids as $bid) {
									CerWorkstationTickets::setTicketWaitingOnCustomer($bid,1);
								}
							} else {
								foreach($bids as $bid) {
									CerWorkstationTickets::setTicketWaitingOnCustomer($bid,0);
								}
							}
							break;
							
						case "blocked":
							$tik_ids = implode(",",$bids);
							if(empty($tik_ids)) $tik_ids = "-1";
							
							$sql = "SELECT t.opened_by_address_id FROM ticket t WHERE t.ticket_id IN ($tik_ids)";
							$res = $cerberus_db->query($sql);
							$ban_ids = array();
							
							if($cerberus_db->num_rows($res))
							{
								while($row = $cerberus_db->fetch_row($res))
									$ban_ids[$row["opened_by_address_id"]] = 1;
		
								$ban_ary = array_keys($ban_ids);
								CerSecurityUtils::integerArray($ban_ary);
									
								$ban_value = ((intval($action_value)) ? 1 : 0);
								
								$sql = sprintf("UPDATE address SET address_banned = %d WHERE address_id IN (%s)",
									$ban_value,
									implode(',', $ban_ary)
								);
								$cerberus_db->query($sql);
							}
							
							break;
							
						case "merge":
							include_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");
							$merge = new CER_TICKET_MERGE();
							if(!empty($action_value)) {
								$merge->do_merge_into($bids,$action_value);
							}
							break;
							
						case "flag":
							if(intval($action_value)) { // take
								foreach($bids as $bid) {
									CerWorkstationTickets::addFlagToTicket($session->vars["login_handler"]->user_id,$bid);
								}
							} else { // release
								CerWorkstationTickets::removeFlagOnTickets($bids,$session->vars["login_handler"]->user_id);
							}
							break;
						
						case "tag":
							include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");

							$wstags = new CerWorkstationTags();
							$action_value = urldecode($action_value);
							
							if(is_array($bids))
							foreach($bids as $bid) {
								// [JAS]: [TODO] In the future this could be more efficient by remembering the lookup IDs
								// and reusing them for each subsequent ticket.
								$wstags->applyFnrTicketTags($action_value,$bid);
							}
							
							break;
							
						case "w+":
							$wtype = substr($action_value,0,2);
							$wid = substr($action_value,2);
							
							switch($wtype) {
//								case "g_": // team
//									CerWorkstationTickets::addTeamTickets(intval($wid),$bids);
//									break;
								case "t_": // tag
									CerWorkstationTickets::addTagTickets(intval($wid),$bids);
									break;
								case "a_": // agent
									CerWorkstationTickets::addAgentTickets(intval($wid),$bids);
									break;
								case "f_": // agent flag
									if(is_array($bids))
									foreach($bids as $bid) {
										CerWorkstationTickets::addFlagToTicket(intval($wid),$bid, true);
									}
									break;
							}
							break;
							
						case "w-":
							$wtype = substr($action_value,0,2);
							$wid = substr($action_value,2);
							
							switch($wtype) {
//								case "g_": // team
//									foreach($bids as $bid) {
//										CerWorkstationTickets::removeTeamsFromTicketId(array(intval($wid)),$bid);
//									}
//									break;
								case "t_": // tag
									foreach($bids as $bid) {
										CerWorkstationTickets::removeTagsFromTicketId(array(intval($wid)),$bid);
									}
									break;
								case "a_": // agent
									foreach($bids as $bid) {
										CerWorkstationTickets::removeAgentsFromTicketId(array(intval($wid)),$bid);
									}
									break;
								case "f_": // agent flag
									CerWorkstationTickets::removeFlagOnTickets($bids,intval($wid));
									break;
							}
							break;
					}
					
//					case "block_sender":
//					case "unblock_sender":
//						$tik_ids = implode(",",$bids);
//						if(empty($tik_ids)) $tik_ids = "-1";
//						
//						$tik_ids = CerSecurityUtils::integerArray($tik_ids);
//						
//						$sql = "SELECT t.opened_by_address_id FROM ticket t WHERE t.ticket_id IN ($tik_ids)";
//						$res = $cerberus_db->query($sql);
//						
//						$ban_ids = array();
//						
//						if($cerberus_db->num_rows($res))
//						{
//							while($row = $cerberus_db->fetch_row($res))
//								$ban_ids[$row["opened_by_address_id"]] = 1;
//	
//							$ban_ary = CerSecurityUtils::integerArray(array_keys($ban_ids));
//								
//							$ban_value = (($action_id == "block_sender") ? 1 : 0);
//							
//							$sql = sprintf("UPDATE address SET address_banned = %d WHERE address_id IN (%s)",
//								$ban_value,
//								implode(',', $ban_ary)
//							);
//							$cerberus_db->query($sql);
//						}
//						
//						break;
//						
//					case "merge":
//						$merge = new CER_TICKET_MERGE();
//						if(!$merge->do_merge($bids)) {
//							$merge_error = $merge->merge_error;
//						}
//						break;
				}
			}
			
			// Send satellite status updates to the master GUI about the
			//	ticket's property changes
			if($cfg->settings["satellite_enabled"])
			{
				if(count($bids))
				foreach($bids as $value) {
					$xsp_upd = new xsp_login_manager();
					$xsp_upd->xsp_send_summary($value);
				}
			}
			
			break;
		} // end case
	}