<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: display.php
|
| Purpose: Display tickets, ticket properties and all ticket sub-tab
|		pages.  Handles the updates of any ticket properties.
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted	  (ben@webgroupmedia.com)   [BGH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/*
[JAS]: This whole section really needs ripped up and revamped using the API.  There's a bit
of redundant code here, and some things (aka the way the display ticket object works) that 
just don't make sense anymore.

Consider this area under construction.
*/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/functions/structs.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/general.php");
require_once(FILESYSTEM_PATH . "cerberus-api/i18n/languages.php");
require_once(FILESYSTEM_PATH . "cerberus-api/mail/cerbHtmlMimeMail.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xsp/xsp_master_gui.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/ticket/display_ticket.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/ticket_thread_errors.php");
require_once(FILESYSTEM_PATH . "cerberus-api/parser/email_parser.php");
require_once(FILESYSTEM_PATH . "cerberus-api/parser/xml_structs.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/ticket_thread_errors.php");
require_once(FILESYSTEM_PATH . "cerberus-api/bayesian/cer_BayesianAntiSpam.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/notification/CerNotification.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexEmail.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/custom_fields/cer_CustomField.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/views/cer_TicketView.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTicketTags.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/team/CerTeams.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/status/CerStatuses.class.php");

$acl = CerACL::getInstance();

$cer_tpl = new CER_TEMPLATE_HANDLER();
$cerberus_translate = new cer_translate;
$audit_log = CER_AUDIT_LOG::getInstance();
$cerberus_format = new cer_formatting_obj();
$als = new cer_admin_list_struct();
$time_entry_defaults = array();

// [JAS]: Set up the local variables from the scope objects
@$qid = $_REQUEST["qid"];
@$ticket = $_REQUEST["ticket"];
@$mode = $_REQUEST["mode"];
@$form_submit = $_REQUEST["form_submit"];
@$ticket_subject = $_REQUEST["ticket_subject"];
@$ticket_status = $_REQUEST["ticket_status"];
@$ticket_priority = round($_REQUEST["ticket_priority"]);
@$source = $_REQUEST["source"];
@$hp = $_REQUEST["hp"];
@$field_ids = $_REQUEST["field_ids"];
@$address_id = $_REQUEST["address_id"];
@$ticket_due = $_REQUEST["ticket_due"];

// [JAS]: Time Tracking Variables
$thread_time_id = ($_REQUEST["thread_time_id"]) ? $_REQUEST["thread_time_id"] : "";
$thread_time_date = ($_REQUEST["thread_time_date"]) ? $_REQUEST["thread_time_date"] : "";
$thread_time_working_agent_id = ($_REQUEST["thread_time_working_agent_id"]) ? $_REQUEST["thread_time_working_agent_id"] : 0;
$thread_time_hrs_spent = ($_REQUEST["thread_time_hrs_spent"]) ? $_REQUEST["thread_time_hrs_spent"] : 0.0;
$thread_time_hrs_chargeable = ($_REQUEST["thread_time_hrs_chargeable"]) ? $_REQUEST["thread_time_hrs_chargeable"] : "";
$thread_time_hrs_billable = ($_REQUEST["thread_time_hrs_billable"]) ? $_REQUEST["thread_time_hrs_billable"] : "";
$thread_time_hrs_payable = ($_REQUEST["thread_time_hrs_payable"]) ? $_REQUEST["thread_time_hrs_payable"] : "";
$thread_time_summary = ($_REQUEST["thread_time_summary"]) ? $_REQUEST["thread_time_summary"] : "";
$thread_time_date_billed = ($_REQUEST["thread_time_date_billed"]) ? $_REQUEST["thread_time_date_billed"] : 0;
$thread_time_delete = ($_REQUEST["thread_time_delete"]) ? $_REQUEST["thread_time_delete"] : "";
$thread_time_custom_gid = ($_REQUEST["thread_time_custom_gid"]) ? $_REQUEST["thread_time_custom_gid"] : 0;
$thread_time_custom_inst_id = ($_REQUEST["thread_time_custom_inst_id"]) ? $_REQUEST["thread_time_custom_inst_id"] : 0;
@$ticket_spam = $_REQUEST["ticket_spam"];

$cer_ticket = new CER_PARSER_TICKET();
if($ticket = $cer_ticket->find_ticket_id(trim($ticket))) {}

$origticket = CerWorkstationTickets::getTicketById($ticket);

// Access Check
if(!isset($acl->queues[$origticket->queue_id])) {
	header("Location: ".cer_href("index.php?errorcode=NOACCESS&errorvalue=" . urlencode($_REQUEST["ticket"])));
	exit;
}

// Customer history variables
@$c_history = $_REQUEST["c_history"];
if(!empty($c_history)) $session->vars["c_history"] = $c_history;
else if (!isset($session->vars["c_history"])) $session->vars["c_history"] = "customer";

// Thread variables
@$te_clear = $_REQUEST["te_clear"];
@$thread_action = $_REQUEST["thread_action"];
@$thread = $_REQUEST["thread"];
@$add_to_req = $_REQUEST["add_to_req"];
@$no_attachments = $_REQUEST["no_attachments"];
@$forward_to = $_REQUEST["forward_to"];

// Requester variables
@$req_ids = $_REQUEST["req_ids"];
@$req_address = $_REQUEST["req_address"];
@$req_suppress_ids = $_REQUEST["req_suppress_ids"];

// SLA variables

// CERBY KB Suggestion variables
@$kb_teaching = $_REQUEST["kb_teaching"];
@$kb_suggestion_id = $_REQUEST["kb_suggestion_id"];
@$kb_suggestion = $_REQUEST["kb_suggestion"];

// View variables
@$view_submit = $_REQUEST["view_submit"];

// Merge Variables
@$merge_to = $_REQUEST["merge_to"];
$merge_error = "";

$errorcode = isset($_REQUEST["errorcode"]) ? strip_tags($_REQUEST["errorcode"]) : "";

// [JAS]: Tickets handler object

if(isset($te_clear) && !empty($te_clear))
{
	$sql = sprintf("DELETE FROM thread_errors WHERE thread_id = %d",
		$te_clear
	);
	$cerberus_db->query($sql);
}


// ***************************************************************************************************************************
// [JAS]: Check if this ticket was merged somewhere else (and we're not submitting).  If so, redirect
$cer_parser = new CER_PARSER();

if(!isset($form_submit) && isset
($ticket))
{
	$forward_ticket = $cer_parser->check_if_merged($ticket);
	if($forward_ticket != $ticket) header("Location: " . cer_href("display.php?ticket=" . $forward_ticket));
}
// ***************************************************************************************************************************

if(isset($form_submit))
{
	switch($form_submit)
	{
		case "save_layout":
			$layout_display_show_suggestions = (isset($_REQUEST["layout_display_show_suggestions"])) ? $_REQUEST["layout_display_show_suggestions"] : 0;
			$layout_display_show_log = (isset($_REQUEST["layout_display_show_log"])) ? $_REQUEST["layout_display_show_log"] : 0;
			$layout_display_show_history = (isset($_REQUEST["layout_display_show_history"])) ? $_REQUEST["layout_display_show_history"] : 0;
			$layout_display_show_workflow = (isset($_REQUEST["layout_display_show_workflow"])) ? $_REQUEST["layout_display_show_workflow"] : 0;
			$layout_display_show_contact = (isset($_REQUEST["layout_display_show_contact"])) ? $_REQUEST["layout_display_show_contact"] : 0;
			$layout_display_show_fields = (isset($_REQUEST["layout_display_show_fields"])) ? $_REQUEST["layout_display_show_fields"] : 0;
			$layout_view_options_bv = (isset($_REQUEST["layout_view_options_bv"])) ? $_REQUEST["layout_view_options_bv"] : 0;

			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_suggestions"] = $layout_display_show_suggestions;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_log"] = $layout_display_show_log;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_history"] = $layout_display_show_history;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_workflow"] = $layout_display_show_workflow;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_contact"] = $layout_display_show_contact;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_display_show_fields"] = $layout_display_show_fields;			
			$session->vars["login_handler"]->user_prefs->page_layouts["layout_view_options_bv"] = $layout_view_options_bv;
			
			$sql = sprintf("UPDATE user_prefs SET page_layouts = %s WHERE user_id = %d",
					$cerberus_db->escape(serialize($session->vars["login_handler"]->user_prefs->page_layouts)),
					$session->vars["login_handler"]->user_id
				);
			$cerberus_db->query($sql);
			
			$errorcode = "Page layout saved!";
			
			break;
		
		case "take":
			$user_id = $session->vars["login_handler"]->user_id;
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
			CerWorkstationTickets::addFlagToTicket($user_id,$ticket);
			break;
			
		case "release":
			$user_id = $session->vars["login_handler"]->user_id;
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
			CerWorkstationTickets::removeFlagOnTicket($ticket,$user_id);
			break;
			
		case "reply":
			@$id = $_REQUEST['ticketId'];
			@$threadId = $_REQUEST['threadId'];
			@$reply_to = stripslashes($_REQUEST['reply_to']);
			@$reply = stripslashes($_REQUEST['reply']);
			@$comment = stripslashes($_REQUEST['comment']);
			
			@$reply_cc = stripslashes($_REQUEST['reply_cc']);
			@$reply_bcc = stripslashes($_REQUEST['reply_bcc']);
			
			@$reply_action_priority = $_REQUEST['reply_action_priority'];
			@$reply_action_status = $_REQUEST['reply_action_status'];
			@$reply_action_release = $_REQUEST['reply_action_release'];
			@$reply_action_waiting = $_REQUEST['reply_action_waiting'];
			@$reply_action_new_status = $_REQUEST['reply_action_new_status'];
			
			@$files = $_FILES['replyFile'];
			$from = $session->vars['login_handler']->user_email;
			$from_id = $session->vars['login_handler']->user_id;

			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
			$ws_ticket = CerWorkstationTickets::getTicketById($id);
			
			$attachments = array();
			if(is_array($files)) {
				for($x=0;$x<count($files[name]);$x++) {
				  $attachment = array();
			      $attachment['file_name'] = $files['name'][$x];
			      $attachment['file_type'] = $files['type'][$x];
			      $attachment['tmp_file'] = $files['tmp_name'][$x];
			      if($files['name'][$x]) { // strlen($attachment['data']) > 0
			         $attachments[] = $attachment;
			      }
			   }
			}
			
			if(!empty($comment)) {
				CerWorkstationTickets::comment($id,$comment,$from_id);
			} else {
				if(!empty($reply_to)) {
					CerWorkstationTickets::forward($id,$reply_to,$reply,$attachments);
				} else {
					$params = array('thread_id'=>$threadId);
					if(!empty($reply_cc)) $params['cc'] = $reply_cc;
					if(!empty($reply_bcc)) $params['bcc'] = $reply_bcc;
					CerWorkstationTickets::reply($id,$reply,$from,$attachments,$params);
				}
				
				// [JAS]: Ticket actions
				if($reply_action_priority != $ws_ticket->priority) { // change priority
					CerWorkstationTickets::setTicketPriority($id,$reply_action_priority);
				}
				if($reply_action_status != $ws_ticket->getStatus()) {
					CerWorkstationTickets::setTicketStatus($id, $reply_action_status);
					if($reply_action_status=="closed") {
						CerWorkstationTickets::sendCloseResponse($id);						
					}
				}
				// Custom Status
				if($ws_ticket->ticket_status_id != $reply_action_new_status) {
					CerWorkstationTickets::setTicketStatusId($id, $reply_action_new_status);
				}
				if(!empty($reply_action_release)) {
					CerWorkstationTickets::removeFlagOnTicket($id,$from_id);
				}
				if(!empty($reply_action_waiting)) {
					CerWorkstationTickets::setTicketWaitingOnCustomer($id,1);
				}
			}
			
			break;
			
		case "ticket_modify_workflow":
		{
			$db = cer_Database::getInstance();
			$user_id = $session->vars["login_handler"]->user_id;
			
//			@$workflowTags = $_REQUEST['workflowTags'];
//			@$workflowTeams = $_REQUEST['workflowTeams'];
//			@$workflowAgents = $_REQUEST['workflowAgents'];
			
			// [JAS]: Property changes
//			$origticket = CerWorkstationTickets::getTicketById($ticket);
//			print_r($origticket);
//			exit;
			
			@$ticket_status = $_REQUEST['ticket_status'];
			@$ticket_new_status = $_REQUEST['ticket_new_status'];
			@$ticket_priority = intval($_REQUEST['ticket_priority']);
			@$ticket_queue = $_REQUEST["ticket_queue"];
			@$ticket_spam = $_REQUEST['ticket_spam'];
			@$ticket_due = $_REQUEST['ticket_due'];
			@$ticket_delay = $_REQUEST['ticket_delay'];
			@$ticket_subject = $_REQUEST['ticket_subject'];
			@$ticket_waiting_on_customer = intval($_REQUEST['ticket_waiting_on_customer']);
			
			if($acl->has_priv(PRIV_TICKET_CHANGE)) { // $queue_access->has_write_access($qid)
				if($ticket_priority > 100) $ticket_priority = 100;  else if($ticket_priority < 0) $ticket_priority = 0;
	
				// [JAS]: [TODO] Move to the API
				// [JAS]: If we've changed the due date
				if(!empty($ticket_due)) {
					$due_date = new cer_DateTime($ticket_due);
					$due_date->changeGMTOffset($cfg->settings["server_gmt_offset_hrs"],$session->vars["login_handler"]->user_prefs->gmt_offset);
					
					if(isset($due_date)) {	
							$sql = sprintf("UPDATE ticket SET ticket_due = '%s' WHERE ticket_id = %d",
									$due_date->getDate("%Y-%m-%d %H:%M:%S"),
									$ticket
								);
						$db->query($sql);
					}
				} else { // clear
					$sql = sprintf("UPDATE ticket SET ticket_due = '%s' WHERE ticket_id = %d",
							date("Y-m-d H:i:s",0),
							$ticket
						);
					$db->query($sql);
				}
				
				if(!empty($ticket_delay)) {
					$delay_date = new cer_DateTime($ticket_delay);
					$delay_date->changeGMTOffset($cfg->settings["server_gmt_offset_hrs"],$session->vars["login_handler"]->user_prefs->gmt_offset);
					$secs = $delay_date->mktime_datetime - mktime();
					CerWorkstationTickets::addAgentDelayToTicket($user_id,$ticket,$secs);
				} else { // clear
					CerWorkstationTickets::removeAgentDelayFromTicket($user_id,$ticket);
				}

				// Queue
				if($origticket->queue_id != $ticket_queue) {
					CerWorkstationTickets::setTicketMailbox($ticket, $ticket_queue);
				}
				
				// Custom Status
				if($origticket->ticket_status_id != $ticket_new_status) {
					CerWorkstationTickets::setTicketStatusId($ticket, $ticket_new_status);
				}
				
				// [JAS]: Ticket Status
				if(0 == $origticket->is_closed && $ticket_status == "closed") {
					CerWorkstationTickets::setTicketStatus($ticket, "closed");
					CerWorkstationTickets::sendCloseResponse($ticket);
					
				} elseif (1 == $origticket->is_deleted && $ticket_status == "closed") {
					CerWorkstationTickets::setTicketStatus($ticket, "closed");
					
				} elseif (0 == $origticket->is_deleted && $ticket_status == "deleted") {
					CerWorkstationTickets::setTicketStatus($ticket, "deleted");
					
				} elseif ((0 != $origticket->is_deleted || 0 != $origticket->is_closed) && $ticket_status == "open") {
					if($acl->has_priv(PRIV_TICKET_DELETE))
						CerWorkstationTickets::setTicketStatus($ticket, "open");
				}
			
				// Priority
				if($origticket->priority != $ticket_priority) {
					CerWorkstationTickets::setTicketPriority($ticket, $ticket_priority);
				}
				
				// Waiting
				if($origticket->is_waiting_on_customer != $ticket_waiting_on_customer) {
					CerWorkstationTickets::setTicketWaitingOnCustomer($ticket,$ticket_waiting_on_customer);
				}
				
				// [JAS]: [TODO] This should be handled by the API
				if(isset($ticket_subject))  {
					$sql = sprintf("UPDATE ticket SET ticket_subject = %s WHERE ticket_id = %d",
						$db->escape($ticket_subject),
						$ticket
					);
					$db->query($sql);
					
					$cer_search = new cer_SearchIndexEmail();
					$cer_search->indexSingleTicketSubject($ticket);
				}
				
				// [JAS]: [TODO] This should be handled by the API
				if(!empty($ticket_spam))
				{
					switch($ticket_spam)
					{
						case "spam":
							CerWorkstationTickets::markSpam($ticket);
							$t = 2;
							break;
						case "notspam":
							CerWorkstationTickets::markHam($ticket);
							$t = 1;
							break;
					}
					
					// [JAS]: [TODO] This should be handled by the API.
					$sql = sprintf("UPDATE ticket SET ticket_spam_trained = %d WHERE ticket_id = %d",
						$t,
						$ticket
					);
					$db->query($sql);
				}
			}
			
			break;		
		}
			
		case "ticket_modify_tags":
		{
			@$ticket_tag_mode = $_REQUEST['ticket_tag_mode'];
			@$ticket_tag_ids = $_REQUEST['ticket_tag_ids'];
			
			if($ticket_tag_mode) {
				CerWorkstationTickets::addTagsToTicketId($ticket_tag_ids, $ticket);
			} else {
				CerWorkstationTickets::removeTagsFromTicketId($ticket_tag_ids, $ticket);
			}
			
			break;
		}
			
		case "merge":
		{
			$merge = new CER_TICKET_MERGE();
			if(!$merge->do_merge(array($merge_to,$ticket))) {
				$merge_error = $merge->merge_error;
			}
			break;
		}
		
		case "strip_html":
		{
			if(empty($thread)) break;
			
			$thread_handler = new cer_ThreadContentHandler();
			$thread_handler->loadThreadContent($thread);

			include_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_StripHTML.class.php");
			$strip = new cer_StripHTML();
			$html_part = $strip->strip_html($thread_handler->threads[$thread]->content);
			
			$thread_handler->writeThreadContent($thread,$html_part);

			break;
		}
		case "block_req":
		case "unblock_req":
		{
			$sql = sprintf("SELECT th.thread_address_id FROM thread th ".
				"WHERE th.thread_id = %d",
					$thread
			);
			$req_res = $cerberus_db->query($sql);
			
			if($req = $cerberus_db->grab_first_row($req_res))
			{
				$addy_id = $req["thread_address_id"];
				if($form_submit == "block_req") {
					$sql = sprintf("UPDATE address SET address_banned = 1 WHERE address_id = %d",$addy_id);
				} else {
					$sql = sprintf("UPDATE address SET address_banned = 0 WHERE address_id = %d",$addy_id);
				}
				$cerberus_db->query($sql);
			}
			break;
		}
		case "hide_thread":
		case "unhide_thread":
		{
			if($form_submit == "hide_thread") {
				$sql = sprintf("UPDATE thread SET is_hidden = 1 WHERE thread_id = %d",$thread);
			} else {
				$sql = sprintf("UPDATE thread SET is_hidden = 0 WHERE thread_id = %d",$thread);
			}
			$cerberus_db->query($sql);
			break;
		}
		case "add_req":
		{
			// [JAS]: [TODO] This could be done through AJAX now.
			if(empty($ticket) || empty($thread)) die("Cerberus [ERROR]: Ticket ID or Thread ID not passed for requester add.");
			
			$sql = sprintf("SELECT th.thread_address_id, a.address_address FROM thread th LEFT JOIN address a ON (th.thread_address_id = a.address_id) ".
				"WHERE th.thread_id = %d",
					$thread
			);
			$add_req = $cerberus_db->query($sql);
			
			if($req = $cerberus_db->grab_first_row($add_req))
			{
				$cer_ticket = new CER_PARSER_TICKET();
				if($cer_ticket->save_requester_link($ticket,$req["thread_address_id"])) {
					$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_ADD_REQUESTER,$req["address_address"]);
				}
			}
			
			break;
		}
		
 		case "bounce":    // [JXD]: 20030812
 		{
 			if(empty($ticket) || empty($thread) || empty($forward_to)) die("Cerberus [ERROR]: Ticket ID or Thread ID not passed for e-mail bounce.");
 			
 			$errors = false;
 			$error_log = array();
 			
 			$cer_parser = new CER_PARSER();
 			$cer_ticket = new CER_PARSER_TICKET();
 			$cer_email = new CerRawEmail();
 			
 			$cer_ticket->load_ticket_data($ticket);
 			
 			$sql = sprintf("SELECT th.thread_date, a.address_address ".
 				", th.thread_subject, th.thread_cc, th.thread_replyto " .
 				"FROM thread th ".
 				"LEFT JOIN address a ON (th.thread_address_id = a.address_id) ".
 				"WHERE th.thread_id = %d",
 					$thread
 			);
 			$th_res = $cerberus_db->query($sql);
 			
 			if(!$th_info = $cerberus_db->grab_first_row($th_res)) break;
 			
 			$from_address = $th_info["address_address"];
 			
			$thread_handler = new cer_ThreadContentHandler();
			$thread_handler->loadThreadContent($thread);
			$thread_content = &$thread_handler->threads[$thread]->content;
 			
			$date = new cer_DateTime($th_info["thread_date"]);
			
 			$cer_email->body = "[[ This message has been bounced by Cerberus Helpdesk ]]\r\n" . 
 				"Originally sent on " . $date->getUserDate() . " by " .
 				$from_address . ":\r\n\r\n" . $thread_content;
 
 			$cer_email->headers->from = $from_address;
 
 			$o_ticket = new CER_TICKET_DISPLAY();
 			$o_ticket->set_ticket_id($ticket);
 			$o_ticket->build_ticket();
 
 			if (count($o_ticket->threads))
 			{
				$tmp_dir_path = FILESYSTEM_PATH . "tempdir";
				
 				foreach ($o_ticket->threads as $one_thread)
 				{
 					if (($one_thread->type != "comment" && $one_thread->type != "email")
 						|| $one_thread->ptr->thread_id !== $thread)
 							continue;
 					
 					foreach ($one_thread->ptr->file_attachments as $attm)
 					{
 						$attmidx = count($cer_email->attachments);
 						$cer_email->add_attachment();
 						$cer_email->attachments[$attmidx]->filename = $attm->file_name;
 						
 						$sql = sprintf("SELECT part_content FROM thread_attachments_parts WHERE file_id = %d",
 							$attm->file_id
 						);
 						$file_parts_res = $cerberus_db->query($sql,false);
 						
 						while($file_part = $cerberus_db->fetch_row($file_parts_res))
 						{
							$tmp_name = tempnam(realpath(FILESYSTEM_PATH . "tempdir"),"cerb"); 
//							$tmp_name = tempnam($tmp_dir_path, "cerb");
							$tp = fopen($tmp_name,"wb");
							if($tp)
							{
								$file_content = $file_part[0];
								fwrite($tp,$file_content,strlen($file_content));
								fclose($tp);
								array_push($cer_email->attachments[$attmidx]->tmp_files,$tmp_name);
							}
 						}
 					}
 					
 				}
 			}
 									
 
 			$error_check = $cer_parser->send_email_to_address(
 				$forward_to,$cer_email,$cer_ticket,$th_info["thread_cc"],true,true);
 			if(is_array($error_check) && count($error_check)) {
 				$errors = true;
 				$error_msg = sprintf("Could not bounce e-mail to address (%s). (<b>%s</b>)",$forward_to,implode("; ",$error_check));
 				array_push($error_log,$error_msg);
 			}
 
 			// [JAS]: If we had errors sending e-mail above, log them.
 			if($errors && is_array($error_log) && count($error_log)) {
 				$ticket_errors = new CER_TICKET_THREAD_ERRORS();
 				$ticket_errors->log_thread_errors($thread,$cer_ticket->ticket_id,$error_log);
 			}
 			else {
 				if(!empty($add_to_req))	{
 					$requester_id = $cer_ticket->get_address_id($forward_to);
 					if($cer_ticket->save_requester_link($ticket,$requester_id)) {
 						$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_ADD_REQUESTER,$forward_to);
 					}
 				}
 				
 				$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_THREAD_BOUNCE,$forward_to);
 			}
 			
 			break;
 		}    // [JXD]: 20030812

		case "thread_split_to_new_ticket":
 		case "clone":
		{
			$sql = sprintf("SELECT t.ticket_id, t.ticket_subject, t.is_deleted, t.is_closed, t.is_waiting_on_customer, t.ticket_date, unix_timestamp(t.ticket_due) as mktime_due, t.ticket_queue_id, t.ticket_priority, th.thread_address_id, ad.address_address, q.queue_name, t.min_thread_id, t.max_thread_id, t.ticket_reopenings, t.ticket_time_worked, t.last_reply_by_agent, t.opened_by_address_id, t.last_wrote_address_id, t.ticket_last_date, t.ticket_status_id " .
				"FROM ticket t  " .
				"LEFT JOIN thread th USING (ticket_id) ".
				"LEFT JOIN address ad ON (th.thread_address_id=ad.address_id) ".
				"LEFT JOIN queue q ON (t.ticket_queue_id = q.queue_id) ".
				"WHERE t.ticket_id = %d GROUP BY th.thread_id LIMIT 0,1",
					$ticket
			);
			$result = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($result) == 0) { 	
				header("Location: ".cer_href("index.php?errorcode=NOACCESS&errorvalue=" . urlencode($_REQUEST["ticket"])));
				exit; 
 			}
			$ticket_data = $cerberus_db->fetch_row($result);
			
			$o_ticket = new CER_TICKET_DISPLAY();
			$o_ticket->set_ticket_id($ticket_data["ticket_id"]);
			$o_ticket->set_ticket_subject($ticket_data["ticket_subject"]);
			$o_ticket->set_ticket_date($ticket_data["ticket_date"]);
			$o_ticket->set_ticket_last_date($ticket_data["ticket_last_date"]);
			$o_ticket->set_ticket_due($ticket_data["mktime_due"]);
			$o_ticket->set_ticket_priority($ticket_data["ticket_priority"]);
			$o_ticket->set_ticket_status_id($ticket_data["ticket_status_id"]);
			$o_ticket->set_ticket_queue($ticket_data["ticket_queue_id"]);
			$o_ticket->set_ticket_queue_name($ticket_data["queue_name"]);
			$o_ticket->set_requestor_address($ticket_data["thread_address_id"],$ticket_data["address_address"]);
			$o_ticket->set_ticket_max_thread($ticket_data["max_thread_id"]);
			$o_ticket->set_ticket_min_thread($ticket_data["min_thread_id"]);
			$o_ticket->set_ticket_reopenings($ticket_data["ticket_reopenings"]);
			$o_ticket->set_ticket_time_worked($ticket_data["ticket_time_worked"]);
			
			// [JAS]: Generate Ticket Mask ID if enabled
			$clone_id_mask = "";
			if($cfg->settings["enable_id_masking"])
			{
				include_once(FILESYSTEM_PATH . "cerberus-api/parser/email_parser.php");
				$parser_ticket = new CER_PARSER_TICKET();
				$clone_id_mask = $parser_ticket->generate_unique_mask();
				unset($parser_ticket);
			}
			
			$sql = sprintf("INSERT INTO ticket(ticket_subject,ticket_date,ticket_priority,ticket_queue_id,ticket_status_id,ticket_reopenings,min_thread_id,max_thread_id,ticket_mask,ticket_time_worked,last_reply_by_agent,opened_by_address_id,last_wrote_address_id,ticket_last_date,ticket_due) ".
				"VALUES(%s,%s,%d,%d,%d,%d,%d,%d,%s,%d,%d,%d,%d,%s,from_unixtime(%d))",
					$cerberus_db->escape($o_ticket->ticket_subject),
					$cerberus_db->escape($o_ticket->ticket_date),
					$o_ticket->ticket_priority,
					$o_ticket->ticket_queue_id,
					$o_ticket->ticket_status_id,
					$o_ticket->ticket_reopenings,
					$o_ticket->min_thread_id,
					$o_ticket->max_thread_id,
					$cerberus_db->escape($clone_id_mask),
					$ticket_data["ticket_time_worked"],
					$ticket_data["last_reply_by_agent"],
					$ticket_data["opened_by_address_id"],
					$ticket_data["last_wrote_address_id"],
					$cerberus_db->escape($o_ticket->ticket_last_date),
					$ticket_data["mktime_due"]
			);
			$cerberus_db->query($sql);
			$clone_id = $cerberus_db->insert_id();
			
			// **** [ Clone REQUESTERS ]
			$o_ticket->requesters = new CER_TICKET_DISPLAY_REQUESTER($o_ticket);
			foreach($o_ticket->requesters->addresses as $req)
			{
				$sql = "INSERT INTO requestor (ticket_id,address_id,suppress) ".
					sprintf("VALUES(%d,%d,%d)",
						$clone_id,
						$req->address_id,
						$req->suppress
						);
				$cerberus_db->query($sql);
			}
			
			if($form_submit != "thread_split_to_new_ticket") { // no need to clone comments on a split
				// **** [ Clone COMMENTS ]
				$sql = sprintf("SELECT id,ticket_id,date_created,created_by_agent_id,note ".
					"FROM next_step ".
					"WHERE ticket_id = %d ".
					"ORDER BY id ASC",
					$o_ticket->ticket_id
				);
				$result = $cerberus_db->query($sql);
				
				if ($cerberus_db->num_rows($result) > 0) {
					while ($ar = $cerberus_db->fetch_row($result)) {
						$sql = sprintf("INSERT INTO next_step(ticket_id,date_created,created_by_agent_id,note) ".
							"VALUES(%d,%d,%d,%s)",
							$clone_id,
							$ar["date_created"],
							$ar["created_by_agent_id"],
							$cerberus_db->escape($ar["note"])
						);
						$cerberus_db->query($sql);
					}
				}
			}
			
			// **** [ Clone THREADS ]
			if($form_submit != "thread_split_to_new_ticket") { // only need one thread on a split
				$sql = sprintf("SELECT ticket_id,thread_id,thread_message_id,thread_address_id,thread_type,thread_date,thread_time_worked,is_agent_message ".
					"FROM thread ".
					"WHERE ticket_id = %d ".
					"ORDER BY thread_id ASC",
						$o_ticket->ticket_id
				);
			} else {// only need one thread on a split
				$sql = sprintf("SELECT ticket_id,thread_id,thread_message_id,thread_address_id,thread_type,thread_date,thread_time_worked,is_agent_message ".
					"FROM thread ".
					"WHERE thread_id = %d ",
						$thread
				);
			}
			$result = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($result) > 0)
			{
				$min_thread=0;
				$max_thread=0;
				
				while($ar = $cerberus_db->fetch_row($result))
				{
					// [DDH]: [TODO] Turn message id into an API call... the below was copied from CerWorkstationTickets.class.php, line 1500ish.
					// message_id MUST differ on a cloned ticket or replies will be attached to the original ticket.
					if($form_submit != "thread_split_to_new_ticket") { // only need new message-id on a clone.  For a split, we want to keep it.
						$message_id = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ?  $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
					} else {
						$message_id = $ar["thread_message_id"];
					}
					
					$sql = sprintf("INSERT INTO thread(ticket_id,thread_message_id,thread_address_id,thread_type,thread_date,thread_time_worked,is_agent_message,is_hidden) ".
						"VALUES(%d,%s,%d,%s,%s,%d,%d,0)",
						$clone_id,
						$cerberus_db->escape($message_id),
						$ar["thread_address_id"],
						$cerberus_db->escape($ar["thread_type"]),
						$cerberus_db->escape($ar["thread_date"]),
						$ar["thread_time_worked"],
						$ar["is_agent_message"]
					);
					$cerberus_db->query($sql);
					$th_id = $cerberus_db->insert_id();
					if($min_thread==0) $min_thread = $th_id;
					$max_thread = $th_id;
					
					$thread_handler = new cer_ThreadContentHandler();
					$thread_handler->loadThreadContent($ar["thread_id"]);
					$thread_content = &$thread_handler->threads[$ar["thread_id"]]->content;
					
					$thread_handler->writeThreadContent($th_id,$thread_content);
					
					// [JAS]: **** File attachment cloning
					$file_ids = array();
					
					$sql = sprintf("SELECT thread_id,file_id,file_name,file_size FROM thread_attachments WHERE thread_id = %d",
						$ar["thread_id"]
					);
					$res = $cerberus_db->query($sql);
					
					if($cerberus_db->num_rows($res))
					{
						while($tf = $cerberus_db->fetch_row($res))
						{
							$sql = "INSERT INTO thread_attachments(thread_id,file_name,file_size) ".
							sprintf("VALUES(%d,%s,%d)",
									$th_id,
									$cerberus_db->escape($tf["file_name"]),
									$tf["file_size"]
								);
							$cerberus_db->query($sql);
							
							$file_id_old = $tf["file_id"];
							$file_id_new = $cerberus_db->insert_id();
							
							$sql = sprintf("SELECT part_id,part_content FROM thread_attachments_parts WHERE file_id = %d",
									$file_id_old
								);
							$p_res = $cerberus_db->query($sql);
							
							if($cerberus_db->num_rows($p_res))
							{
								while($pf = $cerberus_db->fetch_row($p_res))
								{
									$sql = "INSERT INTO thread_attachments_parts (file_id,part_content) ".
									sprintf("VALUES(%d,%s)",
											$file_id_new,
											$cerberus_db->escape($pf["part_content"])
										);
									$cerberus_db->query($sql);
								}
							}
						} // end file loop
					}
					
					unset($tf);
					unset($file_ids);
					unset($res);
					
				} // [JAS]: End thread while loop
				
				// **** Clone CUSTOM FIELD INSTANCES (ticket bound)
				$instances = array();
				$new_instances = array();
				
				$sql = sprintf("SELECT efg.group_instance_id, efg.entity_code, efg.entity_index, efg.group_id ".
					"FROM entity_to_field_group efg ".
					"WHERE efg.entity_code = 'T' ".
					"AND efg.entity_index = %d",
						$o_ticket->ticket_id
				);
				$res = $cerberus_db->query($sql);
				
				if($cerberus_db->num_rows($res)) {
					while($row = $cerberus_db->fetch_row($res)) {
						$inst = $row["group_instance_id"];
						$instances[$inst] = $inst;
						
						$sql = sprintf("INSERT INTO entity_to_field_group (entity_code, entity_index, group_id) ".
							"VALUES (%s,%d,%d)",
								$cerberus_db->escape($row["entity_code"]),
								$clone_id,
								$row["group_id"]
						);
						$cerberus_db->query($sql);
						
						$new_instances[$inst] = $cerberus_db->insert_id();
					}
					
					if(!empty($instances)) {
						CerSecurityUtils::integerArray($instances);
						
						$sql = sprintf("SELECT fv.field_id, fv.field_value, fv.group_instance_id, fv.entity_code, ".
							"fv.entity_index, fv.field_group_id ".
							"FROM field_group_values fv ".
							"WHERE fv.entity_code = 'T' ".
							"AND fv.group_instance_id IN (%s)",
								implode(',',$instances)
						);
						$c_res = $cerberus_db->query($sql);
						
						if($cerberus_db->num_rows($c_res)) {
							while($c_row = $cerberus_db->fetch_row($c_res)) {
								$sql = sprintf("INSERT INTO field_group_values (field_id, field_value, group_instance_id, entity_code, entity_index, field_group_id) ".
									"VALUES (%d,%s,%d,'T',%d,%d)",
										$c_row["field_id"],
										$cerberus_db->escape($c_row["field_value"]),
										$new_instances[$c_row["group_instance_id"]],
										$clone_id,
										$c_row["field_group_id"]
								);
								$cerberus_db->query($sql);
							}							
						}
					}
				}
				
				// [JAS]: Reset Min/Max thread pointers after clone
				$sql = sprintf("UPDATE ticket ".
					"SET max_thread_id=%d,min_thread_id=%d WHERE ticket_id = %d",
						$max_thread,
						$min_thread,
						$clone_id
				); // $o_ticket->ticket_id;
				$result = $cerberus_db->query($sql);
			}

			if($form_submit == "thread_split_to_new_ticket") {
				// Delete original thread for a split
				$sql = sprintf("DELETE FROM thread WHERE thread_id = %d ",
						$thread
				);
				$result = $cerberus_db->query($sql);
				
				// reset original ticket's max_thread_id
				$sql = sprintf("UPDATE ticket SET max_thread_id = (SELECT MAX(thread_id) FROM thread WHERE ticket_id = %d) WHERE ticket_id = %d",
						$ticket,
						$ticket
				);
				$result = $cerberus_db->query($sql);
			}
			
			$sql = sprintf("SELECT al.audit_id,al.ticket_id,al.epoch,al.timestamp,al.user_id,al.action,al.action_value FROM ticket_audit_log al WHERE al.ticket_id = %d",
				$o_ticket->ticket_id
			);
			$result = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($result) > 0)
			{
				while($ar = $cerberus_db->fetch_row($result))
				{
					$sql = sprintf("INSERT INTO ticket_audit_log(ticket_id,epoch,timestamp,user_id,action,action_value) ".
						"VALUES(%d,%d,%s,%d,%d,%s)",
							$clone_id,
							$ar["epoch"],
							$cerberus_db->escape($ar["timestamp"]),
							$ar["user_id"],
							$ar["action"],
							$cerberus_db->escape($ar["action_value"])
					);
					$cerberus_db->query($sql);
				}
			}
			
			$sql = sprintf("SELECT word_id, in_subject from search_index where ticket_id = %d",
				$ticket
			);
			$result = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($result))
			{
				while($ar = $cerberus_db->fetch_row($result))
				{
					$sql = sprintf("INSERT INTO search_index(ticket_id,word_id,in_subject) ".
						"VALUES(%d,%d,%d)",
							$clone_id,
							$ar["word_id"],
							$ar["in_subject"]
					);
					$cerberus_db->query($sql);
				}
			}
			
			if($form_submit != "thread_split_to_new_ticket") {
				$audit_log->log_action($clone_id,$session->vars["login_handler"]->user_id,AUDIT_ACTION_TICKET_CLONED_FROM,$ticket); 
				$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_TICKET_CLONED_TO,$clone_id);
			} else {
				$audit_log->log_action($clone_id,$session->vars["login_handler"]->user_id,AUDIT_ACTION_TICKET_SPLIT_FROM,$ticket); 
				$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_TICKET_SPLIT_TO,$clone_id);
			}
			header("Location: ".cer_href("display.php?ticket=$clone_id"));
 
			exit;
			break;
		}
		
		case "edit_custom_fields":
		{
			$handler = new cer_CustomFieldGroupHandler();
			$handler->loadGroupTemplates();
			
			@$gi_id = $_REQUEST["instantiate_gid"];
			$for = explode("_",@$_REQUEST["instantiate_for"]);
			
			if($gi_id) {
				$handler->addGroupInstance($for[0],$for[1],$gi_id);
			}
			
			if(1) // $queue_access->has_write_access($qid)
			{
				if(@$_REQUEST["group_instances"])
				foreach($_REQUEST["group_instances"] as $gi) {
					
					$field_ids = $_REQUEST["g_{$gi}_field_ids"];
					foreach($field_ids as $id) {
						$value = $_REQUEST["g_{$gi}_field_{$id}"];
						
						// [JAS]: Timestamp?
						if($handler->field_to_template[$id]->fields[$id]->field_type=='E') {
							$date = new cer_DateTime($value);
							$value = $date->mktime_datetime;
							if(date("H:i:s",$value) == "00:00:00") {
								/*
								 * [JAS]: Add one second so we're BETWEEN 00:00:00 and whatever hour.  Otherwise a search 
								 * for the same date on TO/FROM won't return a match you'd logically expect.
								 *
								 */ 
								$value += 1; 
							}
						}
						
						$handler->setFieldInstanceValue($id,$gi,$value);
					}
				}
				
				@$instance_ids = $_REQUEST["instance_ids"];
				if(!empty($instance_ids)) {
					$handler->deleteGroupInstances($instance_ids);
				}
			}
			
			// [JAS]: \todo Need to handle these logging actions
//			$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CUSTOM_FIELDS_REQUESTOR,"");
//			$audit_log->log_action($ticket,$session->vars["login_handler"]->user_id,AUDIT_ACTION_CUSTOM_FIELDS_TICKET,"");
			
			break;
		}
					
		case "properties":
		{
			exit;
//			$acl = CerACL::getInstance();
			if($acl->has_priv(PRIV_TICKET_CHANGE)) { // $queue_access->has_write_access($qid)
				if(!isset($_REQUEST["ticket_priority"])) $ticket_priority=""; else $ticket_priority = $_REQUEST["ticket_priority"];
				$ticket_priority = intval($ticket_priority);
				if($ticket_priority > 100) $ticket_priority = 100;  else if($ticket_priority < 0) $ticket_priority = 0;

//				// [JAS]: Make the due date (entered in user time) a timestamp
				$date = new cer_DateTime($ticket_due);
				
				// [JAS]: Convert the user timestamp to server time
				$date->changeGMTOffset($cfg->settings["server_gmt_offset_hrs"],$session->vars["login_handler"]->user_prefs->gmt_offset);

				// [JAS]: Rebuild the due date string for the database
				$ticket_due_str = $date->getDate("%m/%d/%y %I:%M %p");
				
				// [JAS]: If we've changed the due date
				if(isset($initial_ticket_due) && $initial_ticket_due != $ticket_due_str) {	
					
						$ticket_due_date = new cer_DateTime($ticket_due_str);
						$sql = sprintf("UPDATE ticket SET ticket_due = '%s' WHERE ticket_id = %d",
								$ticket_due_date->getDate("%Y-%m-%d %H:%M:%S"),
								$ticket
							);
					$cerberus_db->query($sql);
				}
				
				// [JAS]: Ticket Status
				if(0 == $initial_status_closed && $ticket_status == "closed") {
					CerWorkstationTickets::setTicketStatus($ticket, "closed");
					CerWorkstationTickets::sendCloseResponse($ticket);
					
				} elseif (0 == $initial_status_deleted && $ticket_status == "deleted") {
					CerWorkstationTickets::setTicketStatus($ticket, "deleted");
					
				} elseif ((0 != $initial_status_deleted || 0 != $initial_status_closed) && $ticket_status == "open") {
					CerWorkstationTickets::setTicketStatus($ticket, "open");
				}
				
				if($initial_priority != $ticket_priority) {
					CerWorkstationTickets::setTicketPriority($ticket, $ticket_priority);
				}
				
				@$initial_waiting = intval($_REQUEST['initial_ticket_waiting_on_customer']);
				@$is_waiting = intval($_REQUEST['ticket_waiting_on_customer']);
				
				if($initial_waiting != $is_waiting) {
					CerWorkstationTickets::setTicketWaitingOnCustomer($ticket,$is_waiting);
				}
				
				if(isset($ticket_subject))  {
					$sql = sprintf("UPDATE ticket SET ticket_subject = %s WHERE ticket_id = %d",
						$cerberus_db->escape($ticket_subject),
						$ticket
					);
					$cerberus_db->query($sql);
				}
				
				// re-index the subject line
				$cer_search = new cer_SearchIndexEmail();
				$cer_search->indexSingleTicketSubject($ticket);
				
			}
			break;
		}
		case "thread_time_edit":
		{
//			$acl = CerACL::getInstance();
			
			if(empty($thread_time_id))
				break;
			
			// [JAS]: ACL check for edit
			if(!$acl->has_priv(PRIV_TICKET_CHANGE)	&& ($thread_ptr->ptr->working_agent_id == $session->vars["login_handler"]->user_id))
				break;
				
			// [JAS]: Are we deleting the time entry?
			if(!empty($thread_time_delete) && strtoupper($thread_time_delete) == "YES") {
				
				// [JAS]: ACL check for delete
				if($acl->is_superuser
						|| ($thread_ptr->ptr->working_agent_id == $session->vars["login_handler"]->user_id))
					{
						$time_handler = new cer_ThreadTimeTrackingHandler();
						$time_handler->deleteTimeEntry($thread_time_id);
						
						$custom_handler = new cer_CustomFieldGroupHandler();
						$custom_handler->load_entity_groups(ENTITY_TIME_ENTRY,$thread_time_id);
						
						// [JAS]: If we had any custom field groups assigned to this thread, time to delete them.
						if(!empty($custom_handler->group_instances))
						foreach($custom_handler->group_instances as $inst) {
							$custom_handler->deleteGroupInstances(array($inst->group_instance_id));	
						}

						break;
					}
			}
			
			// [JAS]: Perform time entry edit.
			$date = new cer_DateTime($thread_time_date);
			$date_billed = new cer_DateTime($thread_time_date_billed);
				
			$time_entry = new cer_ThreadTimeTracking();
				$time_entry->thread_time_id = $thread_time_id;
				$time_entry->ticket_id = $ticket;
				$time_entry->date = $date->getDate("%Y-%m-%d %H:%M:%S");
				$time_entry->working_agent_id = $thread_time_working_agent_id;
				$time_entry->hrs_spent = floatval($thread_time_hrs_spent);
				$time_entry->hrs_chargeable = floatval($thread_time_hrs_chargeable);
				$time_entry->hrs_billable = floatval($thread_time_hrs_billable);
				$time_entry->hrs_payable = floatval($thread_time_hrs_payable);
				$time_entry->summary = $thread_time_summary;
				$time_entry->date_billed = $date_billed->getDate("%Y-%m-%d %H:%M:%S");
			
			$time_handler = new cer_ThreadTimeTrackingHandler();
			$time_handler->updateTimeEntry($time_entry);
			
			// [JAS]: Do we have custom fields to update?
			// \todo This could probably also be moved into the bindings API for company/contact/time-entry edit
			if($thread_time_id && !empty($thread_time_custom_inst_id)) {
				$custom_handler = &$time_handler->custom_handler;
				$custom_handler->loadGroupTemplates();
				$custom_handler->loadSingleInstance($thread_time_custom_inst_id);
				$gid = $custom_handler->group_instances[$thread_time_custom_inst_id]->group_id;
				
				// [JAS]: Loop through each field for this group template and see if we were given input
				if(!empty($custom_handler->group_templates[$gid]->fields))
				foreach($custom_handler->group_templates[$gid]->fields as $id => $fld) {
					$fld_idx = "thread_time_custom_" . $fld->field_id;
					$val = (isset($_REQUEST[$fld_idx])) ? $_REQUEST[$fld_idx] : "" ;
					
					$custom_handler->setFieldInstanceValue($fld->field_id,$thread_time_custom_inst_id,$val);
				}
			}
			
			break;
		}
		
		case "thread_time_add":
		{
			// [JAS]: ACL check for create
			if(!$acl->has_priv(PRIV_TICKET_CHANGE))
				break;
			
			$date = new cer_DateTime($thread_time_date);
			$date_billed = new cer_DateTime($thread_time_date_billed);
			
			$time_entry = new cer_ThreadTimeTracking();
				$time_entry->ticket_id = $ticket;
				$time_entry->date = $date->getDate("%Y-%m-%d %H:%M:%S");
				$time_entry->working_agent_id = $thread_time_working_agent_id;
				$time_entry->hrs_spent = floatval($thread_time_hrs_spent);
				$time_entry->hrs_chargeable = floatval($thread_time_hrs_chargeable);
				$time_entry->hrs_billable = floatval($thread_time_hrs_billable);
				$time_entry->hrs_payable = floatval($thread_time_hrs_payable);
				$time_entry->summary = $thread_time_summary;
				$time_entry->date_billed = $date_billed->getDate("%Y-%m-%d %H:%M:%S");
			
			$time_handler = new cer_ThreadTimeTrackingHandler();
			$time_entry_id = $time_handler->createTimeEntry($time_entry);
			
			// [JAS]: Are we adding a group of custom fields to this entry?
			if($time_entry_id && !empty($thread_time_custom_gid)) {
				$custom_handler = &$time_handler->custom_handler;
				$custom_handler->loadGroupTemplates();
				$inst_id = $custom_handler->addGroupInstance(ENTITY_TIME_ENTRY,$time_entry_id,$thread_time_custom_gid);
				
				// [JAS]: Loop through each field for this group template and see if we were given input
				if(!empty($custom_handler->group_templates[$thread_time_custom_gid]->fields))
				foreach($custom_handler->group_templates[$thread_time_custom_gid]->fields as $id => $fld) {
					$fld_idx = "thread_time_custom_" . $fld->field_id;
					$val = (isset($_REQUEST[$fld_idx])) ? $_REQUEST[$fld_idx] : "" ;
					
					if(!empty($val)) {
						$custom_handler->setFieldInstanceValue($fld->field_id,$inst_id,$val);
					}
				}
			}
			
			break;
		}
		
		case "thread_create_time_entry":
		{
			$thread_handler = new cer_ThreadContentHandler();
			$thread_handler->loadThreadContent(array($thread));
			
			if(isset($thread_handler->threads[$thread])) {
				$time_entry_defaults["summary"] = $thread_handler->threads[$thread]->content;
			}
			
			break;
		}
	}
	
	// Send satellite status updates to the master GUI about the
	//	ticket's property changes
	if($cfg->settings["satellite_enabled"])
	{
		$xsp_upd = new xsp_login_manager();
		$xsp_upd->register_callback_acl($als,"is_admin");
		$xsp_upd->xsp_send_summary($ticket);
	}
}

// [JAS]: Handle dynamic view filter options on form submit ]*****************************************************************
if(!empty($view_submit))
{
	@$filter_responded = $_REQUEST[$view_submit."_filter_responded"];
	if(empty($filter_responded)) $filter_responded = 0;
	$session->vars["login_handler"]->user_prefs->view_prefs->vars[$view_submit."_filter_responded"] = $filter_responded;

	@$filter_rows =  $_REQUEST[$view_submit."_filter_rows"];
	if(empty($filter_rows)) $filter_rows = 15;
	$session->vars["login_handler"]->user_prefs->view_prefs->vars[$view_submit."_filter_rows"] = $filter_rows;
}


// [JAS]: Load Ticket Display Object ]****************************************************************************************
log_user_who_action(WHO_DISPLAY_TICKET,$ticket);

// [TODO]: This needs to be cleaned up and moved into the API
$sql = sprintf("SELECT t.ticket_id, t.ticket_subject, t.is_closed, t.is_deleted, t.is_waiting_on_customer, t.ticket_date, unix_timestamp(t.ticket_due) as mktime_due, t.ticket_queue_id, ".
	"t.ticket_priority, th.thread_address_id, ad.address_address, ".
	"q.queue_name, t.min_thread_id, t.max_thread_id, t.ticket_reopenings, t.ticket_time_worked, ".
	"t.ticket_spam_trained, t.ticket_mask, ad.public_user_id " .
"FROM ticket t ".
"LEFT JOIN thread th USING (ticket_id) ".
"LEFT JOIN address ad ON (th.thread_address_id=ad.address_id) ".
"LEFT JOIN queue q ON (t.ticket_queue_id = q.queue_id) ".
//"LEFT JOIN company c ON (c.id = ad.company_id) " .
"WHERE 1 ".
"AND t.ticket_id = %d ".
"GROUP BY th.thread_id ".
"LIMIT 0,1",
$ticket
);

$wsticket = CerWorkstationTickets::getTicketById($ticket);
$cer_tpl->assign_by_ref("wsticket",$wsticket);

$result = $cerberus_db->query($sql);

if($cerberus_db->num_rows($result) == 0) {
	$sql = "SELECT to_ticket FROM merge_forward WHERE from_ticket = '%d'";
        $result = $cerberus_db->query(sprintf($sql, $ticket));
        if($cerberus_db->num_rows($result) == 0) {
		header("Location: ".cer_href("index.php?errorcode=NOACCESS&errorvalue=" . urlencode($_REQUEST["ticket"])));
		exit;
	}
	else {
		// Merged Ticket found. Goto Merged ticket (pkolmann)
		$ticket_data = $cerberus_db->fetch_row($result);
                header("Location: ".cer_href("display.php?ticket=" . urlencode($ticket_data["to_ticket"])));
                exit;
        } 
}

$ticket_data = $cerberus_db->fetch_row($result);

$o_ticket = new CER_TICKET_DISPLAY();
$o_ticket->set_ticket_id($ticket_data["ticket_id"]);
$o_ticket->set_ticket_mask($ticket_data["ticket_mask"]);
$o_ticket->set_ticket_subject($ticket_data["ticket_subject"]);
$o_ticket->set_ticket_date($ticket_data["ticket_date"]);
$o_ticket->set_ticket_due($ticket_data["mktime_due"]);
$o_ticket->set_ticket_time_worked($ticket_data["ticket_time_worked"]);
$o_ticket->set_ticket_priority($ticket_data["ticket_priority"]);
$o_ticket->set_ticket_queue($ticket_data["ticket_queue_id"]);
$o_ticket->set_ticket_queue_name($ticket_data["queue_name"]);
$o_ticket->set_requestor_address($ticket_data["thread_address_id"],$ticket_data["address_address"]);
$o_ticket->set_ticket_max_thread($ticket_data["max_thread_id"]);
$o_ticket->set_ticket_min_thread($ticket_data["min_thread_id"]);
$o_ticket->set_ticket_reopenings($ticket_data["ticket_reopenings"]);
$o_ticket->set_public_gui_user_id($ticket_data["public_user_id"]);
$o_ticket->set_spam_trained($ticket_data["ticket_spam_trained"]);
$o_ticket->build_ticket();

$bayes = new cer_BayesianAntiSpam();
$text = $o_ticket->ptr_first_thread->thread_subject . "\r\n" . $o_ticket->ptr_first_thread->thread_content;

// [JAS]: Are we doing Bayesian Training?
if(!empty($ticket_spam))
{
	switch($ticket_spam)
	{
		case "spam":
			$bayes->_mark_message_as_spam($o_ticket->ticket_id);
			if($cfg->settings["auto_delete_spam"]) $o_ticket->is_deleted = 1;
			$t = 2;
			break;
		case "notspam":
			$bayes->_mark_message_as_nonspam($o_ticket->ticket_id);
			$t = 1;
			break;
	}
	
	$sql = sprintf("UPDATE ticket SET ticket_spam_trained = %d WHERE ticket_id = %d",
		$t,
		$o_ticket->ticket_id
	);
	$cerberus_db->query($sql);
	
	$o_ticket->ticket_spam_trained = $t;
}

$o_ticket->ticket_spam_rating = 100 * $bayes->calculate_spam_probability($ticket_data["ticket_id"],0);

$cer_tpl->assign_by_ref("o_ticket",$o_ticket);
//print_r($o_ticket->threads);

$cer_tpl->assign('thread',$thread);
$cer_tpl->assign('thread_action',$thread_action);

$session->vars["login_handler"]->ticket_id = $o_ticket->ticket_id;
$session->vars["login_handler"]->ticket_mask = $o_ticket->ticket_mask;
$session->vars["login_handler"]->ticket_subject = $o_ticket->ticket_subject;
$session->vars["login_handler"]->ticket_url = cer_href("display.php?ticket=" . $o_ticket->ticket_id);
// ***************************************************************************************************************************


$qid = ((isset($qid))?$qid:$o_ticket->ticket_queue_id);

// [JAS]: Default Template Vars ]*********************************************************************************************

$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('form_submit',$form_submit);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);

$cer_tpl->assign_by_ref('acl',$acl);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);
$cer_tpl->assign_by_ref('cerberus_disp',$cerberus_disp);

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
$counts = CerWorkstationTickets::getAgentCounts($session->vars['login_handler']->user_id);
$cer_tpl->assign("header_flagged",$counts['flagged']);
$cer_tpl->assign("header_suggested",$counts['suggested']);
	
$urls = array('preferences' => cer_href("my_cerberus.php"),
			  'logout' => cer_href("logout.php"),
			  'home' => cer_href("index.php"),
			  'search_results' => cer_href("ticket_list.php"),
			  'knowledgebase' => cer_href("knowledgebase.php"),
			  'configuration' => cer_href("configuration.php"),
			  'clients' => cer_href("clients.php"),
			  'reports' => cer_href("reports.php"),
			  'take_ticket' => cer_href("display.php?ticket=$ticket&qid=$qid&form_submit=take"),
			  'tab_display' => cer_href("display.php?ticket=".$o_ticket->ticket_id),
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib"),
			  'save_layout' => "javascript:savePageLayout();",
 			  'print_ticket' => cer_href("printdisplay.php?level=ticket&ticket=".$o_ticket->ticket_id),
			  'contact_add' => cer_href("clients.php?mode=u_add" . (($o_ticket->requestor_address->address) ? "&add_email=" . $o_ticket->requestor_address->address : "") )
			  );
			  
$page = "display.php";
$cer_tpl->assign("page",$page);

$cer_tpl->assign_by_ref('errorcode',$errorcode);

// ***************************************************************************************************************************


// [JAS]: Determine what tabs we're allowing the user to see ]****************************************************************
if($acl->has_priv(PRIV_TICKET_CHANGE)) // $o_ticket->writeable
{
 	$urls['tab_props'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=properties");
 	$urls['tab_merge'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=properties","merge");
}
 	
// [JAS]: This is manage requesters now
if($acl->has_priv(PRIV_TICKET_CHANGE)) //$o_ticket->writeable
	$urls['tab_edit'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=properties");
	
if($acl->has_priv(PRIV_TICKET_CHANGE)) // $o_ticket->writeable
	$urls['tab_antispam'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=anti_spam");
	
if($acl->has_priv(PRIV_TICKET_CHANGE) && $cfg->settings["enable_audit_log"])
	$urls['tab_log'] = cer_href("display.php?ticket=".$o_ticket->ticket_id."&mode=log");
// ***************************************************************************************************************************


// [JAS]: [DISPLAY] Ticket at a Glance Functionality ]************************************************************************

// [JSJ]: Added code to generate the priority dropdown list.
if($o_ticket->writeable && $acl->has_priv(PRIV_TICKET_CHANGE)) { 
      $ticket_glance_priority_options = array();
      $priorities = $cer_hash->get_priority_hash();             
      $cer_tpl->assign_by_ref('ticket_glance_priority_options', $priorities);
}
else {
	$o_ticket->ticket_priority_string = "Unassigned";
	$prihash = $cer_hash->get_priority_hash();
	foreach($prihash as $idx => $pri)
		if($idx == $o_ticket->ticket_priority)
			$o_ticket->ticket_priority_string = $pri;
}

// ***************************************************************************************************************************

$tags = new CerWorkstationTicketTags();

$cerAgents = CerAgents::getInstance(); /* @var $cerAgents CerAgents */
$agents = $cerAgents->getList("RealName");

$cerTeams = CerTeams::getInstance(); /* @var $cerTeams CerTeams */
$teams = $cerTeams->getList("Name");

$queues = cer_QueueHandler::getInstance();
$queueList = $queues->getQueues();

$cerStatuses = CerStatuses::getInstance(); /* @var $cerStatuses CerStatuses */
$statuses = $cerStatuses->getList();

$cut_line = $cfg->settings['cut_line'];
if(empty($cut_line)) $cut_line = null;

$cer_tpl->assign_by_ref("agents",$agents);
$cer_tpl->assign_by_ref("teams",$teams);
$cer_tpl->assign_by_ref("queues",$queueList);
$cer_tpl->assign_by_ref("statuses",$statuses);
$cer_tpl->assign_by_ref("cut_line",$cut_line);

// Post Ticket Actions ]******************************************************************************************************
switch($mode)
{
	default: // display
		$fnr_hits = $tags->getRelatedArticlesByTicket($ticket,10);
		if(!empty($fnr_hits)) {
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationKb.class.php");
			$wskb = new CerWorkstationKb();
			$articles = $wskb->getArticlesByIds(array_keys($fnr_hits),false,true);
			$cer_tpl->assign_by_ref('fnrArticles',$articles);
		}
		break;
		
	case "anti_spam":
		break;
}
// ***************************************************************************************************************************

$cer_tpl->assign('mode',$mode);
$cer_tpl->assign('hp',$hp);

$cer_tpl->assign_by_ref('tags',$tags);

$tabs = new CER_TICKET_DISPLAY_TABS($mode);
$cer_tpl->assign_by_ref('tabs',$tabs);

$time_entry_defaults["mdy"] = strftime("%m/%d/%y");
$time_entry_defaults["h"] = strftime("%I");
$time_entry_defaults["m"] = 5*(round(strftime("%M")/5));
$time_entry_defaults["ampm"] = strtolower(strftime("%p"));

	// [JAS]: See if we need to attach a set of custom fields to this entity
	$field_binding = new cer_CustomFieldBindingHandler();
	$custom_handler = new cer_CustomFieldGroupHandler();
	$bind_gid = $field_binding->getEntityBinding(ENTITY_TIME_ENTRY);
	
	// [JAS]: If we do have custom fields, store the custom field group template + ID
	if(!empty($bind_gid)) {
		$custom_handler->loadGroupTemplates();
		$time_entry_defaults["custom_gid"] = $bind_gid;
		$time_entry_defaults["custom_fields"] = $custom_handler->group_templates[$bind_gid];
	}

if(!empty($time_entry_defaults)) $cer_tpl->assign_by_ref('time_entry_defaults',$time_entry_defaults);

if(!empty($merge_error)) $cer_tpl->assign('merge_error',$merge_error);

$user_layout = &$session->vars["login_handler"]->user_prefs->layout_prefs;
$cer_tpl->assign_by_ref('user_layout',$user_layout);

$cer_tpl->assign('ticket',$ticket);
$cer_tpl->assign_by_ref('urls',$urls);
$cer_tpl->display("display.tpl.php");

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
?>
