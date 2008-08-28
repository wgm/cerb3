<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
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
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/audit_log.php");

/*
 * ATTENTION: For Safari, every packet must return SOME sort of textual response,
 * that's why you see all the echo functions sending a space back if there's no
 * HTML template to send.  Be sure you do something similar to maintain Safari 
 * compatibility.
 */

header('Content-type: text/html; charset=iso-8859-1');

$cer_tpl = new CER_TEMPLATE_HANDLER();
$log = CER_AUDIT_LOG::getInstance();
$acl = CerACL::getInstance();
$cer_tpl->assign("acl",$acl);
$cfg = CerConfiguration::getInstance();

@$cmd = $_REQUEST['cmd'];
@$email = $_REQUEST['email'];
$user_id = $session->vars['login_handler']->user_id;
$cer_tpl->assign("cfg",$cfg);
$cer_tpl->assign("user_id",$user_id);

switch($cmd) {
	// GET WORK ***************
	
	case "getwork_teams":
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		
		@$getwork_teams = $_REQUEST['getwork_teams'];
		@$getwork_limit = $_REQUEST['getwork_limit'];

//			if(!$acl->has_priv(PRIV_VIEW_UNASSIGNED)) exit;
//			$cerTickets = CerWorkstationTickets::getUnassignedTickets($getwork_order,$getwork_limit,$tag);

		if(CerWorkstationTickets::quickAssignToAgent($getwork_teams,$user_id,$getwork_limit)) {
			$cerMyTickets = CerWorkstationTickets::getMyTickets($user_id);
			
			if(!empty($cerMyTickets)) {
				$cer_tpl->assign("show_take",true);
				$cer_tpl->assign("show_close",true);
				$cer_tpl->assign("show_workflow",true);
				$cer_tpl->assign("show_flags",true);
				$cer_tpl->assign_by_ref("tickets",$cerMyTickets);
				$cer_tpl->display("home/getwork/work_list.tpl.php");
			} else {
				echo "You have no flagged tickets.";
			}
		} else { // error
			echo "Unable to quick assign tickets.  Make sure you selected teams to assign from.";
		}
		
		break;
	
	case "getwork_team_loads":
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");

		@$getwork_limit = intval($_REQUEST['getwork_limit']);

		$cerTeams = CerWorkstationTeams::getInstance(); /* @var $cerTeams CerWorkstationTeams */
		$qids = array_keys($acl->queues);
		$teams = $cerTeams->getTeamsWithQuickAssignLoads($user_id,$qids);
		
		$cerTickets = new CerWorkstationTickets();

//		if($acl->has_priv(PRIV_VIEW_UNASSIGNED)) {
//			$numUnassigned = $cerTickets->getUnassignedTicketsCount();
//			$tagsUnassigned = CerWorkstationTickets::getUnassignedTags();
//			$cer_tpl->assign_by_ref("numUnassigned",$numUnassigned);
//			$cer_tpl->assign_by_ref("tagsUnassigned",$tagsUnassigned);
//		}
		
		$cer_tpl->assign_by_ref("teams",$teams);
		$cer_tpl->assign("limit",$getwork_limit);
		$cer_tpl->display("home/getwork/team_list.tpl.php");
		
		break;
		
	// [TODO] Needs ACL
	case "getwork_item":
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$cerTicket = CerWorkstationTickets::getTicketById($id);
		
		if(!empty($cerTicket) && !$cerTicket->is_closed && !$cerTicket->is_waiting_on_customer && !$cerTicket->is_deleted
		 && (empty($cerTicket->date_delay) || $cerTicket->date_delay->mktime_datetime < mktime())) { // hide delayed
			$cer_tpl->assign("show_take",true);
			$cer_tpl->assign("show_close",true);
			$cer_tpl->assign("show_flags",true);
			$cer_tpl->assign("show_workflow",true);
			$cer_tpl->assign_by_ref("ticket",$cerTicket);
			$cer_tpl->display("home/getwork/work_list_item.tpl.php");
		}
		
		echo " ";
		break;
	
	case "getwork_workflow":
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTicketTags.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
//		include_once(FILESYSTEM_PATH . "cerberus-api/team/CerTeams.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");

		$cerTicket = CerWorkstationTickets::getTicketById($id);
		$tags = new CerWorkstationTicketTags();
		
		$cerAgents = CerAgents::getInstance(); /* @var $cerAgents CerAgents */
		$agents = $cerAgents->getList("RealName");
		
//		$cerTeams = CerTeams::getInstance(); /* @var $cerTeams CerAgents */
//		$teams = $cerTeams->getList("Name");
		
		$queueHandler = cer_QueueHandler::getInstance();
		$queues = $queueHandler->getQueues();
		
		if(!empty($cerTicket)) {
			$cer_tpl->assign_by_ref("ticket",$cerTicket);
			$cer_tpl->assign_by_ref("tags",$tags);
//			$cer_tpl->assign_by_ref("teams",$teams);
			$cer_tpl->assign_by_ref("agents",$agents);
			$cer_tpl->assign_by_ref('queues',$queues);
			
			$cer_tpl->display("home/getwork/work_list_item_workflow.tpl.php");
		}
		
		echo " ";
		break;
	
	case "getwork_checkflag":
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		
		$ticket = CerWorkstationTickets::getTicketById($id); /* @var $ticket CerWorkstationTicket */
		
		if(!empty($ticket) && !empty($ticket->flags) && !isset($ticket->flags[$user_id])) {
			echo sprintf("Ticket is flagged by: %s",
				implode(',', $ticket->flags)
			);
		} else {
			echo " ";
		}
		
		break;
		
	case "save_workflow":
		$db = cer_Database::getInstance();
		
		@$id = $_REQUEST['id'];
		@$tags = $_REQUEST['tags'];
		@$agents = $_REQUEST['agents'];
		@$teams = $_REQUEST['teams'];
		
		@$ticket_status = $_REQUEST['ticket_status'];
		@$ticket_priority = intval($_REQUEST['ticket_priority']);
		@$ticket_spam = $_REQUEST['ticket_spam'];
		@$ticket_queue = $_REQUEST['ticket_queue'];
		@$ticket_due = $_REQUEST['ticket_due'];
		@$ticket_delay = $_REQUEST['ticket_delay'];
		@$ticket_subject = utf8_decode($_REQUEST['ticket_subject']);
		@$ticket_waiting_on_customer = intval($_REQUEST['ticket_waiting_on_customer']);
		
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;
		
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$tickets = new CerWorkstationTickets();
		
//		$tickets->changeTicketTags($id,$tags);
//		$tickets->changeTicketTeams($id,$teams);
//		$tickets->changeTicketAgents($id,$agents);

		// [JAS]: Property changes
		$origticket = CerWorkstationTickets::getTicketById($id);
		
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
								$id
							);
					$db->query($sql);
				}
			} else { // clear
				$sql = sprintf("UPDATE ticket SET ticket_due = '%s' WHERE ticket_id = %d",
						date("Y-m-d H:i:s",0),
						$id
					);
				$db->query($sql);
			}
			
			if(!empty($ticket_delay)) {
				$delay_date = new cer_DateTime($ticket_delay);
				$delay_date->changeGMTOffset($cfg->settings["server_gmt_offset_hrs"],$session->vars["login_handler"]->user_prefs->gmt_offset);
				$secs = $delay_date->mktime_datetime - mktime();
				CerWorkstationTickets::addAgentDelayToTicket($user_id,$id,$secs);
			} else { // clear
				CerWorkstationTickets::removeAgentDelayFromTicket($user_id,$id);
			}
			
			// [JAS]: Ticket Status
			if(0 == $origticket->is_closed && $ticket_status == "closed") {
				$tickets->setTicketStatus($id, "closed");
				$tickets->sendCloseResponse($id);
				
			} elseif (0 == $origticket->is_deleted && $ticket_status == "deleted") {
				$tickets->setTicketStatus($id, "deleted");
				
			} elseif ((0 != $origticket->is_deleted || 0 != $origticket->is_closed) && $ticket_status == "open") {
				if($acl->has_priv(PRIV_TICKET_DELETE))
					$tickets->setTicketStatus($id, "open");
			}
			
			if($origticket->queue_id != $ticket_queue) {
				$tickets->setTicketMailbox($id, $ticket_queue);
			}
			
			if($origticket->priority != $ticket_priority) {
				$tickets->setTicketPriority($id, $ticket_priority);
			}
			
			if($origticket->is_waiting_on_customer != $ticket_waiting_on_customer) {
				$tickets->setTicketWaitingOnCustomer($id,$ticket_waiting_on_customer);
			}
			
			// [JAS]: [TODO] This should be handled by the API
			if(isset($ticket_subject))  {
				$sql = sprintf("UPDATE ticket SET ticket_subject = %s WHERE ticket_id = %d",
					$db->escape($ticket_subject),
					$id
				);
				$db->query($sql);
				
				$cer_search = new cer_SearchIndexEmail();
				$cer_search->indexSingleTicketSubject($id);
			}
			
			// [JAS]: [TODO] This should be handled by the API
			if(!empty($ticket_spam))
			{
				switch($ticket_spam)
				{
					case "spam":
						$tickets->markSpam($id);
						$t = 2;
						break;
					case "notspam":
						$tickets->markHam($id);
						$t = 1;
						break;
				}
				
				// [JAS]: [TODO] This should be handled by the API.
				$sql = sprintf("UPDATE ticket SET ticket_spam_trained = %d WHERE ticket_id = %d",
					$t,
					$id
				);
				$db->query($sql);
			}
			
		}
		
		echo " ";
		break;
		
	case "getwork_my":
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$cerMyTickets = CerWorkstationTickets::getMyTickets($user_id);
		
		if(!empty($cerMyTickets)) {
			$cer_tpl->assign("show_take",true);
			$cer_tpl->assign("show_close",true);
			$cer_tpl->assign("show_workflow",true);
			$cer_tpl->assign("show_flags",true);
			$cer_tpl->assign_by_ref("tickets",$cerMyTickets);
			$cer_tpl->display("home/getwork/work_list.tpl.php");
		} else {
			echo "You have no flagged tickets.";
		}
		echo " ";
		break;
		
	case "getwork_suggested":
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$cerSugTickets = CerWorkstationTickets::getSuggestedTickets($user_id);
		
		if(!empty($cerSugTickets)) {
			foreach($cerSugTickets as $ticketIdx => $ticket) {
				if(isset($ticket->flags[$user_id])) {
					unset($cerSugTickets[$ticketIdx]);
				}
			}
			
			$cer_tpl->assign("show_take",true);
			$cer_tpl->assign("show_close",true);
			$cer_tpl->assign("show_workflow",true);
			$cer_tpl->assign("show_flags",true);
			$cer_tpl->assign_by_ref("tickets",$cerSugTickets);
			$cer_tpl->display("home/getwork/work_list.tpl.php");
		} else {
			echo "You have no suggested tickets.";
		}
		echo " ";
		break;
		
	case "getwork_monitor":
		if(empty($session->vars['monitor_epoch'])) {
			$session->vars['monitor_epoch'] = mktime() - (60*15); // 15 min ago
		}
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$cerMonitorTickets = CerWorkstationTickets::getMonitorEvents($user_id,$session->vars['monitor_epoch']);
		if(!empty($cerMonitorTickets)) $cer_tpl->assign_by_ref("tickets",$cerMonitorTickets);
		$cer_tpl->display("home/getwork/monitor_list.tpl.php");
		
		$session->vars['monitor_epoch'] = mktime();
		$session->save_session();
		echo " ";
		break;
		
	case "getwork_preview":
		@$id = $_REQUEST['id'];
		@$thid = $_REQUEST['thid'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$pobj = CerWorkstationTickets::getTicketPreviewObject($id,$thid); 
		$pre = $pobj['preview'];
		$threads = $pobj['threads'];
		$thread_id = $pobj['thread_id'];
		$cer_tpl->assign("id", $id);
		$cer_tpl->assign("sender", $pre->sender);
		$cer_tpl->assign("date", $pre->date);
		$cer_tpl->assign("text", $pre->text);
		$cer_tpl->assign("thread_id", $thread_id);
		$cer_tpl->assign("threads", $threads);
		$cer_tpl->assign("current", $threads[$thread_id]);
		$cer_tpl->assign("num_threads", count($threads));
		$cer_tpl->display("home/getwork/quick_preview.tpl.php");
		echo " ";
		break;
		
	case "getwork_reply":
		@$id = $_REQUEST['id'];
		@$thid = $_REQUEST['thid'];
		include_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
		$agents = CerAgents::getInstance(); /* @var $agents CerAgents */
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$pobj = CerWorkstationTickets::getTicketPreviewObject($id,$thid);
		$pre = $pobj['preview'];
		
		// [JAS]: Flag the ticket during a reply
		CerWorkstationTickets::addFlagToTicket($user_id,$id);
		$wsticket = CerWorkstationTickets::getTicketById($id);
		
		include_once(FILESYSTEM_PATH . "cerberus-api/status/CerStatuses.class.php");
		$cerStatuses = CerStatuses::getInstance(); /* @var $cerStatuses CerStatuses */
		$statuses = $cerStatuses->getList();
		
		$cer_tpl->assign("id", $id);
		$cer_tpl->assign("wsticket", $wsticket);
		$cer_tpl->assign("statuses", $statuses);
		$cer_tpl->assign("sender", $pre->sender);
		$cer_tpl->assign("date", $pre->date);
		$cer_tpl->assign("text", $pre->text);
		$cer_tpl->assign("sig", $agents->getSignature($user_id));
		$cer_tpl->display("home/getwork/quick_reply.tpl.php");
		echo " ";
		break;
		
	case "getwork_reply_save":
		@$id = $_REQUEST['id'];
		@$threadId = $_REQUEST['threadId'];
		$from_id = $session->vars['login_handler']->user_id;
		$from = $session->vars['login_handler']->user_email;
		@$reply_cc = $_REQUEST['reply_cc'];
		@$reply_bcc = $_REQUEST['reply_bcc'];
		
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;
		
		$replyEmail = utf8_decode(stripslashes($_REQUEST['reply']));

		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$empty = array();
		$params = array('thread_id'=>$threadId);
		if(!empty($reply_cc)) $params['cc'] = $reply_cc;
		if(!empty($reply_bcc)) $params['bcc'] = $reply_bcc;
		CerWorkstationTickets::reply($id,$replyEmail,$from,$empty,$params);
		
		$wsticket = CerWorkstationTickets::getTicketById($id);
		
		@$reply_action_priority = $_REQUEST['reply_action_priority'];
		@$reply_action_status = $_REQUEST['reply_action_status'];
		@$reply_action_release = $_REQUEST['reply_action_release'];
		@$reply_action_waiting = $_REQUEST['reply_action_waiting'];
		@$reply_action_new_status = $_REQUEST['reply_action_new_status'];
		
		// [JAS]: Ticket actions
		if($reply_action_priority != $wsticket->priority) { // change priority
			CerWorkstationTickets::setTicketPriority($id,$reply_action_priority);
		}
		if($reply_action_status != $wsticket->getStatus()) {
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
		
		echo " ";
		break;
		
	case "getwork_comment":
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$cer_tpl->assign("id", $id);
		$cer_tpl->display("home/getwork/quick_comment.tpl.php");
		echo " ";
		break;
		
	case "getwork_comment_save":
		@$id = $_REQUEST['id'];
		
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;
		
		$comment = utf8_decode(stripslashes($_REQUEST['comment']));
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		CerWorkstationTickets::comment($id,$comment,$user_id);
		echo " ";
		break;
		
	case "getwork_take":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;
		
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		CerWorkstationTickets::addFlagToTicket($user_id,$id);
		echo " ";
		break;
		
	case "getwork_release":
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		CerWorkstationTickets::removeFlagOnTicket($id,$user_id);
		echo " ";
		break;
		
	case "getwork_release_delay":
		@$id = $_REQUEST['id'];
		@$release_delay = $_REQUEST['release_delay'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		CerWorkstationTickets::removeFlagOnTicket($id,$user_id);
		
		if(!empty($release_delay)) {
			$release_delay = new cer_DateTime($release_delay);
			$release_delay->changeGMTOffset($cfg->settings["server_gmt_offset_hrs"],$session->vars["login_handler"]->user_prefs->gmt_offset);
			$secs = $release_delay->mktime_datetime - mktime();
			CerWorkstationTickets::addAgentDelayToTicket($user_id,$id,$secs);
		}
		
		echo " ";
		break;
		
	case "getwork_spam":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;
		
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		CerWorkstationTickets::setTicketStatus($id, "deleted");
		CerWorkstationTickets::markSpam($id);
		echo " ";
		break;
		
//	case "getwork_delay":
//		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
//			return;
//		
//		@$id = $_REQUEST['id'];
//		@$secs = $_REQUEST['secs'];
//		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
//		
//		if($secs == -1) { // waiting on customer reply
//			CerWorkstationTickets::setTicketWaitingOnCustomer($id, 1);
//		} else { // delay
//			CerWorkstationTickets::addAgentDelayToTicket($user_id,$id,$secs);
//		}
//		
//		echo " ";
//		break;
//		
	case "getwork_set_closed":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;
		
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		CerWorkstationTickets::setTicketStatus($id, "closed");
		echo " ";
		break;
		
	case "getwork_trash":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE) && !$acl->has_priv(PRIV_TICKET_DELETE))
			return;
		
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		CerWorkstationTickets::setTicketStatus($id, "deleted");
		echo " ";
		break;
		
	case "getwork_set_priority":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;
		
		@$id = $_REQUEST['id'];
		@$priority = $_REQUEST['priority'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		CerWorkstationTickets::setTicketPriority($id, $priority);
		echo " ";
		break;
	
	case "create_ticket":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;

		$cer_tpl->assign('threadId',0);
		if($email)
			$cer_tpl->assign('email_address',$email);
		$cer_tpl->display("home/getwork/create_ticket.tpl.php");
		echo " ";
		break;

	// DISPLAY ******************
	case "display_reply":
	case "display_comment":
	case "display_forward":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;
		
		@$threadId = $_REQUEST['threadId'];
		include_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
		$agents = CerAgents::getInstance(); /* @var $agents CerAgents */
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		$pre = CerWorkstationTickets::getThreadText($threadId);
		
		$wsticket = CerWorkstationTickets::getTicketById($pre->ticket_id);
		
		$sig_autoinsert = @$session->vars['login_handler']->user_prefs->user_signature_autoinsert;
		$sig_pos = @$session->vars['login_handler']->user_prefs->user_signature_pos;
		$quote_previous = @$session->vars['login_handler']->user_prefs->user_quote_previous;
		
		include_once(FILESYSTEM_PATH . "cerberus-api/status/CerStatuses.class.php");
		$cerStatuses = CerStatuses::getInstance(); /* @var $cerStatuses CerStatuses */
		$statuses = $cerStatuses->getList();

		// [JAS]: Flag the ticket during a reply
		if($cmd == "display_reply") {
			CerWorkstationTickets::addFlagToTicket($user_id,$pre->ticket_id);
		}
		
		$cer_tpl->assign("ticketId", $pre->ticket_id);
		$cer_tpl->assign("wsticket", $wsticket);
		$cer_tpl->assign("threadId", $threadId);
		$cer_tpl->assign("sender", $pre->sender);
		$cer_tpl->assign("date", $pre->date);
		$cer_tpl->assign("text", $pre->text);
		$cer_tpl->assign("sig", $agents->getSignature($user_id));
		$cer_tpl->assign("sig_auto", $sig_autoinsert);
		$cer_tpl->assign("sig_pos", $sig_pos);
		$cer_tpl->assign("quote_previous", $quote_previous);
		$cer_tpl->assign("statuses",$statuses);
		
		if($cmd=="display_reply") {
			$cer_tpl->display("display/rpc/reply.tpl.php");
		} elseif($cmd=="display_comment") {
			$cer_tpl->display("display/rpc/comment.tpl.php");
		} else {
			$cer_tpl->display("display/rpc/forward.tpl.php");
		}
		echo " ";
		break;
		
	case "get_signature":
		include_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
		$agents = CerAgents::getInstance(); /* @var $agents CerAgents */
		echo "\r\n" . $agents->getSignature($user_id);
		echo " ";
		break;
	
	case "display_get_requesters":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE)
		||	$acl->has_restriction(REST_EMAIL_ADDY,BITGROUP_2))
			return;
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		@$id = $_REQUEST['id'];
		$ticket = CerWorkstationTickets::getTicketById($id);
		$cer_tpl->assign("ticket", $ticket);
		$cer_tpl->display("display/rpc/requesters.tpl.php");
		echo " ";
		break;
	
	case "display_requesters_add":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		@$id = intval($_REQUEST['id']);
		@$addy = $_REQUEST['requester_add'];
		CerWorkstationTickets::addRequesterToTicket($addy,$id);
		echo " ";
		break;
		
	case "display_requesters_del":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE))
			return;
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		@$id = intval($_REQUEST['id']);
		@$req = intval($_REQUEST['req']);
		@$user = $session->vars["login_handler"]->user_id;
		CerWorkstationTickets::removeRequesterIdFromTicket($req,$id,$user);
		echo " ";
		break;
	
	case "workflow_snapshot":
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		// [JAS]: [TODO] Needs permission check on ticket ---vvvv
		$wsticket = CerWorkstationTickets::getTicketById($id);
		$cer_tpl->assign_by_ref('wsticket',$wsticket);
		$cer_tpl->display('display/boxes/box_ticket_tags.tpl.php');
		echo " ";
		break;
		
	case "workflow_search":
		@$id = $_REQUEST['id'];
		@$keyword = stripslashes($_REQUEST['q']); // [JAS]: [TODO] Change to Jeremy's variable function
		@$category = stripslashes($_REQUEST['c']);
//		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTicketTags.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/team/CerTeams.class.php");

		$tags = array();
		$teams = array();
		$agents = array();
		
//		$cerTicket = CerWorkstationTickets::getTicketById($id);

		// [JAS]: [TODO] Also need to handle favorites/tagsets.
		$cerAgents = CerAgents::getInstance(); /* @var $cerAgents CerAgents */
		
		if("agent" == $category) { // "any" == $category || 
			$agents = $cerAgents->getListByKeyword($keyword,"RealName","RealName");
		}

		if("flag" == $category) { // [JAS]: [TODO] Needs ACL check
			$flagAgents = $cerAgents->getListByKeyword($keyword,"RealName","RealName");
		}
		
		
//		if("any" == $category || "team" == $category) {
//			$cerTeams = CerTeams::getInstance(); /* @var $cerTeams CerAgents */
//			$teams = $cerTeams->getListByKeyword($keyword,"Name","Name");
//			
//			if(is_array($teams))
//			foreach($teams as $ti => $t) {
//				if(!isset($acl->teams[$ti])) {
//					unset($teams[$ti]);
//				}
//			}
//		}
		
		if(!empty($id))
			$cer_tpl->assign('id',$id);
		
		$cer_tpl->assign_by_ref("tags",$tags);
//		$cer_tpl->assign_by_ref("teams",$teams);
		$cer_tpl->assign_by_ref("agents",$agents);
		$cer_tpl->assign_by_ref("flagAgents",$flagAgents);
		
		$cer_tpl->display("display/rpc/quickworkflow_results.tpl.php");
		
		echo " ";
		break;
		
	case "workflow_set":
		@$id = $_REQUEST['id'];
		@$quickworkflow_string = $_REQUEST['quickworkflow_string'];

		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		
		if(empty($quickworkflow_string)) break;
		
		$workflow_items = explode('||', $quickworkflow_string);

		if(is_array($workflow_items))
		foreach($workflow_items as $item) {
			$val = substr($item,2);
			switch(substr($item,0,2)) {
//				case "g_": // team
//					CerWorkstationTickets::addTeamTickets($val,array($id));
//					break;
				case "a_": // agent
					CerWorkstationTickets::addAgentTickets($val,array($id));
					break;
				case "t_": // tag
					CerWorkstationTickets::addTagTickets($val,array($id));
					break;
				case "f_": // flag
					CerWorkstationTickets::addFlagToTicket($val,$id,true);
					break;
			}
		}
		
		echo "Workflow set!";
		break;
		
	case "workflow_unset":
		// [JAS]: [TODO] Needs permission check on ticket ---vvvv
		@$id = $_REQUEST['id'];
		@$type = $_REQUEST['type'];
		@$itemId = $_REQUEST['itemId'];
	
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
		
		switch($type) {
//			case "g": // team
//				CerWorkstationTickets::removeTeamsFromTicketId(array($itemId),$id);
//				break;
			case "t": // tag
				CerWorkstationTickets::removeTagsFromTicketId(array($itemId),$id);
				break;
			case "a": // agent
				CerWorkstationTickets::removeAgentsFromTicketId(array($itemId),$id);
				break;
			case "f": // flag
				if($acl->has_priv(PRIV_REMOVE_ANY_FLAGS,BITGROUP_2) || $itemId==$user_id)
					CerWorkstationTickets::removeFlagOnTicket($id,$itemId);
				break;
		}
		
		echo " ";
		break;
	
	case "dashboard_loads":
		include_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");

		@$dash_id = $_REQUEST['dash_id'];
		if(!empty($dash_id)) {
			include_once(FILESYSTEM_PATH . "cerberus-api/dashboard/CerDashboards.class.php");
			$cerDashboards = new CerDashboards();
			$dashboards = $cerDashboards->getList($user_id);
			$dashboard =& $dashboards[$dash_id];
			$cer_tpl->assign('dashboard',$dashboard);
		}

		$qh = cer_QueueHandler::getInstance();
		$queues = $qh->getQueues();
		
		// ACL
		if(is_array($queues))
		foreach($queues as $idx => $q) {
			if(!isset($acl->queues[$idx]) || isset($dashboard->hide_queues[$idx])) {
				unset($queues[$idx]);
			}
		}

		$cerTeams = CerWorkstationTeams::getInstance();
		$qids = array_keys($acl->queues);
		$teams = $cerTeams->getTeamsWithRelativeLoads($user_id,$qids);

		// ACL
		if(is_array($teams))
		foreach($teams as $idx => $t) {
			if(!isset($acl->teams[$idx]) || isset($dashboard->hide_teams[$idx])) {
				unset($teams[$idx]);
			}
		}
		
		$cer_tpl->assign('total_team_hits',$cerTeams->total_hits);
		$cer_tpl->assign_by_ref("teams",$teams);

		$cer_tpl->assign("total_queue_hits",$qh->total_active_tickets);
		$cer_tpl->assign_by_ref("queues",$queues);
		
		$cer_tpl->display("home/dashboard/rpc/dashboard_loads.tpl.php");
		break;
		
	case "search_show_criteria":
		@$criteria = $_REQUEST['criteria'];
		@$label = $_REQUEST['label'];
		$cer_tpl->assign('criteria', $criteria);
		$cer_tpl->assign('label', $label);
		$cer_tpl->assign('cmd','search_set_criteria');
		
		switch($criteria) {
			case "mask":
				$cer_tpl->display('search/rpc/builder/ticket_mask.tpl.php');
				break;
			case "status":
				$cer_tpl->display('search/rpc/builder/ticket_status.tpl.php');
				break;
			case "ticket_status":
				include_once(FILESYSTEM_PATH . "cerberus-api/status/CerStatuses.class.php");
				$cerStatuses = CerStatuses::getInstance(); /* @var $cerStatuses CerStatuses */
				$statuses = $cerStatuses->getList();
				$cer_tpl->assign('statuses',$statuses);
				$cer_tpl->display('search/rpc/builder/ticket_new_status.tpl.php');
				break;
			case "requester":
				$cer_tpl->display('search/rpc/builder/ticket_requester.tpl.php');
				break;
			case "tags":
				$cer_tpl->assign('no_flags',true);
				$cer_tpl->assign('no_suggestions',true);
				$cer_tpl->display('search/rpc/builder/ticket_workflow.tpl.php');
				break;
			case "workflow":
				$cer_tpl->assign('no_flags',true);
				$cer_tpl->assign('no_tags',true);
				$cer_tpl->display('search/rpc/builder/ticket_workflow.tpl.php');
				break;
			case "subject":
				$cer_tpl->display('search/rpc/builder/ticket_subject.tpl.php');
				break;
			case "content":
				$cer_tpl->display('search/rpc/builder/ticket_content.tpl.php');
				break;
			case "company":
				$cer_tpl->display('search/rpc/builder/ticket_company.tpl.php');
				break;
			case "queue":
				include_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");
				$qh = new cer_QueueHandler();
				$queues = $qh->getQueues();
				$cer_tpl->assign('queues',$queues);
				$cer_tpl->display('search/rpc/builder/ticket_queue.tpl.php');
				break;
			case "priority":
				global $priority_options;
				$cer_tpl->assign('priorities',$priority_options);
				$cer_tpl->display('search/rpc/builder/ticket_priority.tpl.php');
				break;
			case "flag":
				include_once(FILESYSTEM_PATH . 'cerberus-api/agent/CerAgents.class.php');
				$wsagents = CerAgents::getInstance();
				$agents = $wsagents->getList("RealName");
				$cer_tpl->assign('agents',$agents);
				$cer_tpl->display('search/rpc/builder/ticket_flags.tpl.php');
				break;
			case "waiting":
				$cer_tpl->display('search/rpc/builder/ticket_waiting.tpl.php');
				break;
//			case "has_teams":
//				$cer_tpl->display('search/rpc/builder/ticket_has_teams.tpl.php');
//				break;
			case "created":
				$cer_tpl->display('search/rpc/builder/ticket_created.tpl.php');
				break;
			case "last_updated":
				$cer_tpl->display('search/rpc/builder/ticket_last_updated.tpl.php');
				break;
			case "due":
				$cer_tpl->display('search/rpc/builder/ticket_due.tpl.php');
				break;
			default:
				// Custom Fields
				if(substr($criteria,0,12) == "custom_field") {
					$field_id = substr($criteria,12);
					$field_handler = new cer_CustomFieldGroupHandler();
					$field_handler->loadGroupTemplates();
					$type = @$field_handler->field_to_template[$field_id]->fields[$field_id]->field_type;
					
					if($type=="D") { // dropdown
						$options = @$field_handler->field_to_template[$field_id]->fields[$field_id]->field_options;
						$cer_tpl->assign('options',$options);
					}
					
					$cer_tpl->assign('field_id',$field_id);
					$cer_tpl->assign('type',$type);
					$cer_tpl->display('search/rpc/builder/custom_field.tpl.php');	
				}
				break;
		}
		
		echo " ";
		break;
		
	case "search_set_criteria":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/agent/CerAgents.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/status/CerStatuses.class.php");
		
		@$criteria = $_REQUEST['criteria'];
		@$label = $_REQUEST['label'];
		$params = array();
		
		// [JAS]: [TODO] This pointer causing a problem?
		$searchBuilder =& $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		
		$wstags = new CerWorkstationTags();
		$wsteams = CerWorkstationTeams::getInstance();
		$wsagents = CerAgents::getInstance();
		$wsqueues = cer_QueueHandler::getInstance();
		$cerStatuses = CerStatuses::getInstance(); /* @var $cerStatuses CerStatuses */
		
		$tags = $wstags->getTags();
		$teams = $wsteams->getTeams();
		$agents = $wsagents->getList("RealName");
		$queues = $wsqueues->getQueues();
		$statuses = $cerStatuses->getList();
		
		switch($criteria) {
			case "mask":
				@$crit_mask = $_REQUEST['crit_mask'];
				$params['mask'] = $crit_mask;
				break;
			case "status":
				@$crit_status = $_REQUEST['crit_status'];
				$params['status'] = $crit_status;
				break;
			case "requester":
				@$crit_requester = $_REQUEST['crit_requester'];
				$params['requester'] = $crit_requester;
				break;
			case "tags":
				@$crit_tags = $_REQUEST['tags'];
				$setTags = $wstags->_getFnrTags($crit_tags);
				
				$wtags = array();
				
//				print_r($setTags);
				
				foreach($setTags as $tn=>$tid) {
					$wtags[$tid] = $tn; // @$tags[$tid]->name
				}
				
				$params['tags'] = $wtags;
				break;
			case "workflow":
				@$workflow = $_REQUEST['workflow'];
				
//				$wteams = array();
				$wagents = array();

				if(is_array($workflow))
				foreach($workflow as $w) {
					$wid = substr($w,2);
					switch(substr($w,0,2)) {
						case "a_":
							$wagents[$wid] = @$agents[$wid]->getRealName();
							break;
//						case "g_":
//							$wteams[$wid] = @$teams[$wid]->name;
//							break;
					}
				}
				
//				$params['teams'] = $wteams;
				$params['agents'] = $wagents;
				
				break;
//			case "has_teams":
//				@$crit_has_teams = $_REQUEST['crit_has_teams'];
//				$params['show'] = $crit_has_teams;
//				break;
			case "subject":
				@$crit_subject = $_REQUEST['crit_subject'];
				@$contains = $_REQUEST['contains'];
				$params['subject'] = $crit_subject;
				break;
			case "content":
				@$crit_content = $_REQUEST['crit_content'];
				@$contains = $_REQUEST['contains'];
				$params['content'] = $crit_content;
				break;
			case "company":
				@$crit_company = $_REQUEST['crit_company'];
				$params['company'] = $crit_company;
				break;
			case "queue":
				@$wqueues = $_REQUEST['queues'];
				
				$qs = array();
				if(is_array($wqueues))
					foreach($wqueues as $q)
						$qs[$q] = @$queues[$q]->queue_name;
				
				$params['queues'] = $qs;
				break;
			case "ticket_status":
				@$wstatuses = $_REQUEST['statuses'];
				
				$nss = array();
				if(is_array($wstatuses))
					foreach($wstatuses as $ns)
						$nss[$ns] = @$statuses[$ns]->getText();
				
				$params['statuses'] = $nss;
				break;
			case "priority":
				global $priority_options;
				
				$priorities = array();
				$pri = @$_REQUEST['priorities'];
				
				if(is_array($pri))
				foreach($pri as $p) {
					$priorities[$p] = $priority_options[$p];
				}
				$params['priorities'] = $priorities;
				break;
			case "flag":
				include_once(FILESYSTEM_PATH . 'cerberus-api/agent/CerAgents.class.php');
				$wsagents = CerAgents::getInstance();
				$agents = $wsagents->getList("RealName");
				
				@$crit_flag = $_REQUEST['crit_flag'];
				@$flag_mode = $_REQUEST['flag_mode'];

				$params['flag_mode'] = intval($flag_mode);
				
				if($flag_mode) { // flagged
					$flags = array();
					if(is_array($crit_flag))
					foreach($crit_flag as $flag)
						$flags[$flag] = @$agents[$flag]->getRealName();
					
					$params['flags'] = $flags;
				} else { // not flagged
					$params['flags'] = array();
				}
				break;
			case "waiting":
				@$crit_waiting = $_REQUEST['crit_waiting'];
				$params['waiting'] = $crit_waiting;
				break;
			case "created":
			case "last_updated":
			case "due":
				@$from = $_REQUEST['from'];
				@$to = $_REQUEST['to'];
				$params['to'] = $to;
				$params['from'] = $from;
				break;
			default:
				// Custom Fields
				if(substr($criteria,0,12) == "custom_field") {
					$field_id = substr($criteria,12);
					$field_handler = new cer_CustomFieldGroupHandler();
					$field_handler->loadGroupTemplates();
					
					$type = @$_REQUEST['type'];
					$params['type'] = $type;
					$value = "";

					$group_name = @$field_handler->field_to_template[$field_id]->group_name;
					$field_name = @$field_handler->field_to_template[$field_id]->fields[$field_id]->field_name;
					
					$params['name'] = $group_name . "-&gt;" . $field_name;

					switch($type) {				
						case "S":
						case "T":
							$value = @$_REQUEST['crit_custom_field'];
							$params['value'] = $value;
							break;
						case "D":
							$field_options = @$field_handler->field_to_template[$field_id]->fields[$field_id]->field_options;
							$o = @$_REQUEST['crit_custom_field_opts'];
							$opts = array();
							
							if(!is_array($field_options))
								break;
								
							if(is_array($o))
							foreach($o as $opt) {
								$opts[$opt] = @$field_options[$opt];
							}
							
							$params['options'] = $opts;
							break;
						case "E":
							$params['from'] = @$_REQUEST['from'];
							$params['to'] = @$_REQUEST['to'];
							break;
					}
					
				}
				
				break;
		}
		
		$searchBuilder->add($criteria,$params);
		$session->save_session();
		echo " ";
		break;
		
	case "search_list_criteria":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		@$label = $_REQUEST['label'];
		@$searchBuilder = $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		$cer_tpl->assign('label', $label);
		$cer_tpl->assign('search_builder',$searchBuilder);
		$cer_tpl->assign('filter_rows',$session->vars["login_handler"]->user_prefs->view_prefs->vars['sv_filter_rows']);
		$cer_tpl->display('search/rpc/builder/criteria_list.tpl.php');
		echo " ";
		break;
	
	case "search_toggle_criteria":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		@$criteria = $_REQUEST['criteria'];
		@$param = $_REQUEST['param'];
		@$label = $_REQUEST['label'];
		// [JAS]: [TODO] Pointer
		@$searchBuilder =& $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		$searchBuilder->toggle($criteria,$param);
		$session->save_session();
		echo " ";
		break;
		
	case "search_remove_criteria":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		@$label = $_REQUEST['label'];
		@$criteria = $_REQUEST['criteria'];
		@$param = $_REQUEST['param'];
		@$arg = $_REQUEST['arg'];
		// [JAS]: [TODO] Pointer
		@$searchBuilder =& $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		$searchBuilder->remove($criteria,$param,$arg);
		$session->save_session();
		echo " ";
		break;
		
	case "search_clear_criteria":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		@$label = $_REQUEST['label'];
		// [JAS]: [TODO] Pointer
		@$searchBuilder =& $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		$searchBuilder->reset();
		$session->save_session();
		echo " ";
		break;
		
	case "search_save_get":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearch.class.php");
		@$label = $_REQUEST['label'];
		@$searchBuilder = $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		$savedSearches = CerSearch::getList($user_id);
		$cer_tpl->assign('search_builder',$searchBuilder);
		$cer_tpl->assign('saved_searches',$savedSearches);
		$cer_tpl->display('search/rpc/criteria/criteria_save.tpl.php');
		echo " ";
		break;
		
	case "search_load_get":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearch.class.php");
		@$label = $_REQUEST['label'];
		@$searchBuilder = $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		$savedSearches = CerSearch::getList($user_id);
		$cer_tpl->assign('search_builder',$searchBuilder);
		$cer_tpl->assign('saved_searches',$savedSearches);
		$cer_tpl->display('search/rpc/criteria/criteria_load.tpl.php');
		echo " ";
		break;
	
	case "search_delete":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearch.class.php");
		
		@$label = $_REQUEST['label'];
		@$load_id = $_REQUEST['load_id'];
		
		CerSearch::deleteSearch($load_id);
		echo " ";
		break;
		
	case "search_save":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearch.class.php");
		
		@$label = $_REQUEST['label'];
		@$searchBuilder = $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		@$save_mode = $_REQUEST['save_mode'];
		@$save_as = $_REQUEST['save_as'];
		@$save_new = $_REQUEST['save_new'];
		
		$params = $searchBuilder->criteria;
		
		if(empty($save_mode)) { // save new
			CerSearch::createSearch($params,$save_new,$user_id);
			echo "Created!";
		} else { // save as
			CerSearch::saveSearch($params,$save_as);
			echo "Saved!";
		}
		
		break;
		
	case "search_load":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearch.class.php");
		
		@$label = $_REQUEST['label'];
		@$searchBuilder &= $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		@$load_id = $_REQUEST['load_id'];
		
		if($load_id) { // load
			$search = CerSearch::loadSearch($load_id);
			$session->vars[$label . '_builder']->criteria = $search->params;
		}
		$session->save_session();
		echo " ";
		break;
		
	case "kb_article_workflow":
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationKb.class.php");
		@$id = $_REQUEST['id'];
		$wskb = new CerWorkstationKb();
		$article = $wskb->getArticleById($id);
		$cer_tpl->assign_by_ref('article',$article);
		$cer_tpl->display("knowledgebase/rpc/kb_article_workflow.tpl.php");
		echo " ";
		break;
		
	case "kb_article_add_tags":
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationKb.class.php");
		@$workflow = $_REQUEST['workflow'];
		$wskb = new CerWorkstationKb();
		
		$tags = array();
		if(is_array($workflow))
		foreach($workflow as $w) {
			$key = substr($w,0,2);
			$val = substr($w,2);
			if($key=="t_") $tags[] = $val;
		}
		$wskb->addTagsToArticleId($tags,$id);

		echo " ";
		break;
		
	case "kb_article_remove_tag":
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationKb.class.php");
		@$id = $_REQUEST['id'];
		@$tag = $_REQUEST['tag'];
		$wskb = new CerWorkstationKb();
		$wskb->removeTagsFromArticleId(array($tag),$id);
		
		echo " ";
		break;
		
	case "kbsearch_show_criteria":
		@$criteria = $_REQUEST['criteria'];
		@$label = $_REQUEST['label'];
		$cer_tpl->assign('criteria', $criteria);
		$cer_tpl->assign('label', $label);
		$cer_tpl->assign('cmd','kbsearch_set_criteria');
		
		switch($criteria) {
			case "workflow":
				$cer_tpl->display('knowledgebase/search/rpc/builder/kb_workflow.tpl.php');
				break;
			case "keyword":
				$cer_tpl->display('knowledgebase/search/rpc/builder/kb_keyword.tpl.php');
				break;
		}
		
		echo " ";
		break;
		
	case "kbsearch_set_criteria":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
		
		@$criteria = $_REQUEST['criteria'];
		@$label = $_REQUEST['label'];
		$params = array();
		$searchBuilder =& $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		
		$wstags = new CerWorkstationTags();
		$tags = $wstags->getTags();
		
		switch($criteria) {
			case "workflow":
				@$workflow = $_REQUEST['workflow'];
				
				$wtags = array();

				if(is_array($workflow))
				foreach($workflow as $w) {
					$wid = substr($w,2);
					switch(substr($w,0,2)) {
						case "t_":
							$wtags[$wid] = @$tags[$wid]->name;
							break;
					}
				}
				
				$params['tags'] = $wtags;
				
				break;
			case "keyword":
				@$keyword = $_REQUEST['keyword'];
				$params['keyword'] = $keyword;
				break;
		}
		
		$searchBuilder->add($criteria,$params);
		$session->save_session();
		echo " ";
		break;
		
	case "kbsearch_list_criteria":
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		@$label = $_REQUEST['label'];
		@$searchBuilder = $session->vars[$label . '_builder']; /* @var $searchBuilder CerSearchBuilder */
		$cer_tpl->assign('label', $label);
		$cer_tpl->assign('search_builder',$searchBuilder);
		$cer_tpl->display('knowledgebase/search/rpc/builder/criteria_list.tpl.php');
		echo " ";
		break;
		
	case "auto_queue_addresses":
		@$query = $_REQUEST['query'];
		$len = strlen($query);
		
		include_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");
		$queues = cer_QueueHandler::getInstance();
		$addys = $queues->getQueueAddresses();
		
		foreach($addys as $idx => $qa) {
			if(strtolower(substr($qa->address,0,$len)) == strtolower($query) 
				&& isset($acl->queues[$qa->queue_id]))
				echo $qa->address . "\t" . $idx . "\n";
		}
//		echo " ";
		break;
		
	case "auto_tag":
		@$query = $_REQUEST['query'];
		$len = strlen($query);
		
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
		$th = new CerWorkstationTags();
		$tags = $th->getTags();
		
		if(is_array($tags))
		foreach($tags as $tag) {
			if(!empty($tag->name) && !empty($query)) {
				if(false !== stristr($tag->name,$query)) {
					echo $tag->name . "\n";
				} elseif(is_array($tag->terms)) {
					foreach($tag->terms as $term) {
						if(false !== stristr($term,$query)) {
							echo $tag->name . "\n";
							break;
						}
					}
				}
			}
		}
		
		break;
	
	case "fnr_resource_apply_tags":
		if(!$acl->has_priv(PRIV_KB_EDIT,BITGROUP_1)) break;
		
		@$id = $_REQUEST['id'];
		@$tags = stripslashes($_REQUEST['tag_input']);
		
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
		$th = new CerWorkstationTags();
		$th->applyFnrResourceTags($tags,$id);
		
		echo "";
		break;
		
		
	case "fnr_resource_remove_tags":
		if(!$acl->has_priv(PRIV_KB_EDIT,BITGROUP_1)) break;
//		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		@$id = $_REQUEST['resource_id'];
		@$unset_tags = $_POST['unset_tags'];
		$db = cer_Database::getInstance();
		
		// [JAS]: [TODO] Move to API
		if(is_array($unset_tags))	{
			$sql = sprintf("DELETE FROM `workstation_tags_to_kb` WHERE kb_id = %d AND tag_id IN (%s)",
				$id,
				implode(',', $unset_tags)
			);
			$db->query($sql);
		}
		
		echo " ";
		break;
	
	case "fnr_get_ticket_suggestions":
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTicketTags.class.php");
		@$id = $_REQUEST['id'];
		$tags = new CerWorkstationTicketTags();
		
		$fnr_hits = $tags->getRelatedArticlesByTicket($id,10);
		if(!empty($fnr_hits)) {
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationKb.class.php");
			$wskb = new CerWorkstationKb();
			$articles = $wskb->getArticlesByIds(array_keys($fnr_hits),false,true);
//			$cer_tpl->assign_by_ref('fnrArticles',$articles);
		}
		
		$cer_tpl->assign('articles',$articles);
		$cer_tpl->display("knowledgebase/kb_article_list2.tpl.php");
		break;

		
	case "fnr_ticket_tag":
		if(!$acl->has_priv(PRIV_TICKET_CHANGE,BITGROUP_1)) break;
		@$id = $_REQUEST['id'];
		@$tags = urldecode(stripslashes($_REQUEST['tags']));
		
		include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
		$th = new CerWorkstationTags();
		$th->applyFnrTicketTags($tags,$id);
		break;
		
	case "auto_contact_addresses":
		@$query = $_REQUEST['query'];
		$len = strlen($query);
		
//		include_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");
//		$queues = cer_QueueHandler::getInstance();
//		$addys = $queues->getQueueAddresses();
		
		foreach($addys as $idx => $qa) {
			if(strtolower(substr($qa->address,0,$len)) == strtolower($query) 
				&& isset($acl->queues[$qa->queue_id]))
				echo $qa->address . "\t" . $idx . "\n";
		}
//		echo " ";
		break;

	case "fnr_get_resource":
		if(!$acl->has_priv(PRIV_KB,BITGROUP_1)) break;
		@$id = $_REQUEST['id'];
		$db = cer_Database::getInstance();

		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		// [JAS]: [TODO] Move this into the API
		$sql = sprintf("SELECT k.id, k.title, k.public, kc.content " .
			" FROM `kb` k ".
			" LEFT JOIN `kb_content` kc ON (kc.`kb_id`=k.`id`) ".
			" WHERE k.id = %d",
				$id
		);
		$res = $db->query($sql);

		$article = new CerKnowledgebaseArticle($id);
		$article->loadCategories();
		$tags = $article->getTags();
		$permalinks = $article->getPermalinks();
		
		// [JAS]: [TODO] Move to template
		if($db->num_rows($res)) {
			$article = $db->fetch_row($res);
			$cer_tpl->assign('id',intval($article['id']));
			$cer_tpl->assign('title',stripslashes($article['title']));
			$cer_tpl->assign('public',intval($article['public']));
			$cer_tpl->assign('tags',$tags);
			$cer_tpl->assign('permalinks',$permalinks);
			$cer_tpl->assign_by_ref('content',stripslashes($article['content']));
			$cer_tpl->display("knowledgebase/rpc/fnr_resource_window.tpl.php");
		} else {
			echo sprintf("Invalid knowledge resource (id: %d)",
				$id
			);
		}
		
		break;
		
	case "fnr_save_resource":
		if(!$acl->has_priv(PRIV_KB_EDIT,BITGROUP_1)) break;
		
		@$id = intval($_REQUEST['id']);
		@$title = stripslashes($_REQUEST['title']);
		@$private = intval($_REQUEST['private']);
		@$content = stripslashes($_REQUEST['content']);
		
		$db = cer_Database::getInstance();
		
		$sql = sprintf("UPDATE kb SET title = %s, public = %d WHERE id = %d",
			$db->escape($title),
			(($private) ? 0 : 1 ),
			$id
		);
		$db->query($sql);
		
		$sql = sprintf("UPDATE kb_content SET content = %s WHERE kb_id = %d",
			$db->escape($content),
			$id
		);
		$db->query($sql);
		
		echo " ";
		break;
		
	case "fnr_manage_categories":
		if(!$acl->has_priv(PRIV_KB_EDIT,BITGROUP_1)) break;
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		$kb = new CerKnowledgebase();
		$root = $kb->getRoot();
		$cer_tpl->assign("kb",$kb);
		$cer_tpl->assign("root",$root);
		$cer_tpl->display("knowledgebase/rpc/fnr_manage_categories.tpl.php");
		break;
		
	case "fnr_get_edit_category":
		if(!$acl->has_priv(PRIV_KB_EDIT,BITGROUP_1)) break;
		@$id = $_REQUEST['id'];
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		$kb = new CerKnowledgebase();
		$root = $kb->getRoot();
		$category = $kb->flat_categories[$id]; /* @var $category CerKnowledgebaseCategory */
		
		// [JAS]: [TODO] Need to block the ability to set a category parent to itself
		$ancestors = $category->getDescendents();
		if(is_array($ancestors))
		foreach($ancestors as $a) {
			$kb->flat_categories[$a] = 1;
		}
		
		$cer_tpl->assign("kb",$kb);
		$cer_tpl->assign("root",$root);
		$cer_tpl->assign("category",$category);
		$cer_tpl->display("knowledgebase/rpc/fnr_edit_category.tpl.php");
		break;

	case "fnr_get_permalinks":
		if(!$acl->has_priv(PRIV_KB,BITGROUP_1)) break;
		@$id = $_REQUEST['id'];
		$db = cer_Database::getInstance();

		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		$article = new CerKnowledgebaseArticle($id);
		$article->loadCategories();
//		$tags = $article->getTags();
		$permalinks = $article->getPermalinks();
		
		$cer_tpl->assign("id",$id);
		$cer_tpl->assign("resource",$article);
		$cer_tpl->assign("permalinks",$permalinks);
		$cer_tpl->display("knowledgebase/rpc/fnr_get_permalinks.tpl.php");
		break;
		
	case "fnr_do_delete_category":
		if(!$acl->has_priv(PRIV_KB_DELETE,BITGROUP_1)) break;
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");

		@$category_id = intval($_REQUEST['id']);

		$kb = new CerKnowledgebase();
		$kb->deleteCategories($category_id);
		
		break;
		
	case "fnr_do_edit_category":
		if(!$acl->has_priv(PRIV_KB_EDIT,BITGROUP_1)) break;
		$db = cer_Database::getInstance();
		@$category_id = intval($_REQUEST['category_id']);
		@$category_name = stripslashes($_REQUEST['category_name']);
		@$parent_id = intval($_REQUEST['category_parent']);
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		$kb = new CerKnowledgebase();
		
		// [JAS]: [TODO] Move to API
		$sql = sprintf("UPDATE kb_category SET name=%s, parent_id=%d WHERE id=%d",
			$db->escape($category_name),
			$parent_id,
			$category_id
		);
		$db->query($sql);
		
		echo "Saved!";
		break;
		
	case "fnr_get_new_category":
		if(!$acl->has_priv(PRIV_KB_EDIT,BITGROUP_1)) break;
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		$kb = new CerKnowledgebase();
		$root = $kb->getRoot();
		
		$cer_tpl->assign("kb",$kb);
		$cer_tpl->assign("root",$root);
		$cer_tpl->display("knowledgebase/rpc/fnr_new_category.tpl.php");
		break;
		
	case "fnr_do_new_category":
		if(!$acl->has_priv(PRIV_KB_EDIT,BITGROUP_1)) break;
		$db = cer_Database::getInstance();
		@$category_name = stripslashes($_REQUEST['category_name']);
		@$parent_id = intval($_REQUEST['category_parent']);
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		$kb = new CerKnowledgebase();
		
		// [JAS]: [TODO] Move to API
		$sql = sprintf("INSERT INTO kb_category (name,parent_id) VALUES (%s,%d)",
			$db->escape($category_name),
			$parent_id
		);
		$db->query($sql);
		
		echo "Saved!";
		break;
		
	case "fnr_get_resource_category_manager":
		if(!$acl->has_priv(PRIV_KB,BITGROUP_1)) break;
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		@$id = $_REQUEST['id'];
		@$div = stripslashes($_REQUEST['div']);
		$db = cer_Database::getInstance();
		$kb = new CerKnowledgebase();
		$kb_root = $kb->getRoot();
		$resource = new CerKnowledgebaseArticle($id);
		$resource->loadCategories();
		
		$cer_tpl->assign('kb',$kb);
		$cer_tpl->assign('div',$div);
		$cer_tpl->assign('resource',$resource);
		$cer_tpl->assign('kb_root',$kb_root);
		$cer_tpl->display("knowledgebase/rpc/fnr_resource_category_manager.tpl.php");
		break;
		
	case "fnr_get_resource_tag_manager":
		if(!$acl->has_priv(PRIV_KB,BITGROUP_1)) break;
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		@$id = $_REQUEST['id'];
		@$div = stripslashes($_REQUEST['div']);
		$db = cer_Database::getInstance();
		
		// [JAS]: [TODO] Move to API
		$resource = new CerKnowledgebaseArticle($id);
		$tags = $resource->getTags();
		
		$cer_tpl->assign('div',$div);
		$cer_tpl->assign('tags',$tags);
		$cer_tpl->assign('resource',$resource);
		$cer_tpl->display("knowledgebase/rpc/fnr_resource_tag_manager.tpl.php");
		break;
		
	case "fnr_set_resource_categories":
		if(!$acl->has_priv(PRIV_KB_EDIT,BITGROUP_1)) break;
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		@$id = $_REQUEST['resource_id'];
		@$set_categories = $_POST['set_categories'];
		$db = cer_Database::getInstance();
		
		// [JAS]: [TODO] Move to API
		if(is_array($set_categories))
		foreach($set_categories as $cat) {
			$sql = sprintf("INSERT IGNORE INTO `kb_to_category` (kb_id,kb_category_id) VALUES (%d,%d)",
				$id,
				$cat
			);
			$db->query($sql);
		}
		
//		print_r($set_categories);
		echo " ";
		break;
		
	case "fnr_unset_resource_categories":
		if(!$acl->has_priv(PRIV_KB_EDIT,BITGROUP_1)) break;
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		
		@$id = $_REQUEST['resource_id'];
		@$unset_categories = $_POST['unset_categories'];
		$db = cer_Database::getInstance();
		
		// [JAS]: [TODO] Move to API
		// [JAS]: [TODO] Move to API
		if(is_array($unset_categories))	{
			$sql = sprintf("DELETE FROM `kb_to_category` WHERE kb_id = %d AND kb_category_id IN (%s)",
				$id,
				implode(',', $unset_categories)
			);
			$db->query($sql);
		}
		
//		print_r($unset_categories);
		echo " ";
		break;
}