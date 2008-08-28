<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/xsp/xsp_master_gui.php");
require_once(FILESYSTEM_PATH . "cerberus-api/notification/CerNotification.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/parser/email_parser.php");
require_once(FILESYSTEM_PATH . "cerberus-api/parser/xml_structs.php");
require_once(FILESYSTEM_PATH . "cerberus-api/parser/xml_handlers.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/stats/cer_SystemStats.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/parser/CerRawEmail.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/sla/cer_SLA.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/audit_log.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/gui_parser_log.php");
require_once(FILESYSTEM_PATH . "cerberus-api/log/ticket_thread_errors.php");

class CerProcessEmail {
	
	var $cer_parser;
	var $cer_ticket;
	var $audit_log;
	var $cer_log;
	var $cer_search;
	var $cer_bayes;
	var $cfg;
	var $cer_ticket_errors;
	var $cer_stats;

	var $last_error_msg;

	function CerProcessEmail() {
		$this->cer_parser = new CER_PARSER();
		$this->cer_ticket = new CER_PARSER_TICKET();
		$this->audit_log = CER_AUDIT_LOG::getInstance();
		$this->cer_log = new CER_GUI_LOG();
		$this->cer_search = new cer_SearchIndexEmail();
		$this->cer_bayes = new cer_BayesianAntiSpam();
		$this->cfg = CerConfiguration::getInstance();
		$this->cer_ticket_errors = new CER_TICKET_THREAD_ERRORS();
		$this->cer_stats = new cer_SystemStats();
	}
	
	function process($o_raw_email,$options=array()) {
		// Cleanse the palette
		$this->cer_ticket = new CER_PARSER_TICKET();
		
		$bool = $this->_process($o_raw_email,$options);
		$o_raw_email->_cleanupResources();
		return $bool;
	}
	
	function _process($o_raw_email,$options=array()) {
		global $verbose;
		
		$errors = false;
		$error_log = array();
		$cer_warcheck = false; // [JAS]: Reset the warcheck
		$suppress_watchers = false;
		
		// [JAS]: Are we adding CC'd addresses to the requester list automatically?
		$auto_cc_req = (($this->cfg->settings["auto_add_cc_reqs"]) ? true : false);
		
		if($verbose == 1) { echo "Entering _process()<br>"; flush(); }
		
		// [JAS]: Get/Create the Requester Address ID
		if(!empty($o_raw_email->headers->reply_to) && $o_raw_email->headers->reply_to != '@') { //[BGH] changed return_path to reply_to
			$this->cer_ticket->requester_id = $this->cer_ticket->get_address_id($o_raw_email->headers->reply_to); //[BGH] changed return_path to reply_to
			$this->cer_ticket->requester_address = $o_raw_email->headers->reply_to; //[BGH] changed return_path to reply_to
		}
		else {
			$this->cer_ticket->requester_id = $this->cer_ticket->get_address_id($o_raw_email->headers->from);
			$this->cer_ticket->requester_address = $o_raw_email->headers->from;
		}
		
		if($verbose == 1) { echo "Leaving requester parsing<br>"; flush(); }
		
		// [JAS]: Make sure our 'from' address validated
		if($this->cer_ticket->requester_address == '@' || empty($this->cer_ticket->requester_address))
		{
			$error_msg = sprintf("Incoming message didn't have a valid from address (%s).",$this->cer_ticket->requester_address);
			$this->cer_log->log($error_msg);
			$this->last_error_msg = $error_msg;
			return FALSE;
		}
		
		if($verbose == 1) { echo "Leaving requester validating<br>"; flush(); }
		
		// [JAS]: Check and see if the current sender is banned.
		if($this->cer_parser->is_banned_address($this->cer_ticket->requester_address))
		{
			$error_msg = sprintf("Incoming message is from banned sender (%s).",$this->cer_ticket->requester_address);
			$this->cer_log->log($error_msg);
			$this->last_error_msg = $error_msg;
			return FALSE;
		}
		
		if($verbose == 1) { echo "Leaving ban checking<br>"; flush(); }
		
		// [JAS]: Perform autoresponder war check / flood protection, returns pass/fail (true/false)
		if(!$this->cer_parser->perform_war_check($o_raw_email,$this->cer_ticket))
		{
			$error_msg = sprintf("Incoming message failed flood protection (%s).",$this->cer_ticket->requester_address);
			$this->cer_log->log($error_msg);
			$cer_warcheck = true;
		}
		
		if($verbose == 1) { echo "Leaving warcheck<br>"; flush(); }
		
		// [JAS]: Determine if this message is a comment or an e-mail thread.
		$thread_type = $this->cer_ticket->is_comment_or_email($o_raw_email->headers->subject);
		
		// [BGH]: Update the message_id on the ticket object
		if(!empty($o_raw_email->headers->message_id)) {
			$this->cer_ticket->last_message_id = $o_raw_email->headers->message_id;
		}
		
		if($verbose == 1) { echo "Leaving message id<br>"; flush(); }
		
		// [JAS]: Try to determine our destination queue in advance so it's available to pre-parse mail rules.
		$dest_queue = $this->cer_ticket->get_dest_queue_data($o_raw_email);
		
		// [JAS]: Determine if this is a new or reply ticket by checking for a Cerberus ticket ID in the subject
		if(($ticket_id = $this->cer_parser->find_ticketid_from_message_id($o_raw_email->headers->in_reply_to, $o_raw_email->headers->references)) 
			|| ($ticket_id = $this->cer_parser->find_ticketid_in_subject($o_raw_email->headers->subject)))
		{
			// [JAS]: Existing Ticket
			$ticket_id = $this->cer_parser->check_if_merged($ticket_id); // [JAS]: If merged, find the latest ticket ID
			$this->cer_ticket->is_new = false;
			$this->cer_ticket->load_ticket_data($ticket_id);
		} else {
			// [JAS]: New Ticket
			$ticket_id = false;
			$this->cer_ticket->is_new = true;
			$this->cer_ticket->is_reopened = false;
			
			//[mdf]: new tickets (not existing) must get the queue_id based on the addresses in the email header
			if($dest_queue) {
				$this->cer_ticket->ticket_queue_id = $dest_queue->queue_id;
			}
		}
		
		if($verbose == 1) { echo "Leaving dest queue<br>"; flush(); }
		
		/*
		 * [JAS]: Calculate the spam probability from the actual e-mail message and not the ticket 
		 * 	so we can use it in pre rules.
		 */
		$this->cer_ticket->ticket_spam_probability = $this->cer_bayes->calculate_spam_probability_from_plaintext($o_raw_email->headers->subject . " " . $o_raw_email->body);
		
		if($verbose == 1) { echo "Leaving spam prob<br>"; flush(); }
		
		// [JAS]: Process pre-parser mail rules for this e-mail
		$pre_rule_codes = $this->cer_parser->process_mail_rules(RULE_TYPE_PRE,$o_raw_email,$this->cer_ticket,$this->audit_log);
		
		if($verbose == 1) { echo "Leaving pre-rules<br>"; flush(); }
		
		// [JAS]: If we're being told by a mail rule to ignore this e-mail, then bail out of parsing.
		if(isset($pre_rule_codes["pre_ignore"])) {
			$error_msg = sprintf("Incoming message blocked by a mail rule (sender: <b>%s</b>; subject: <i>%s</i>).",
					$o_raw_email->headers->from,
					$o_raw_email->headers->subject
				);
			$this->cer_log->log($error_msg);
//			$this->last_error_msg = $error_msg;
//			return FALSE;
			return TRUE;
		}
		
		// [JAS]: EXISTING TICKET
		if($ticket_id)
		{
//			echo "EXISTING TICKET<br>"; flush();
			$thread_id = $this->cer_ticket->add_ticket_thread($o_raw_email,$thread_type,false,$auto_cc_req);
			
			if($this->cer_ticket->is_reopened) {
				$this->cer_ticket->reset_due_date(); // since it's reopened, set the SLA due date like it's new
				$this->audit_log->log_action($this->cer_ticket->ticket_id,0,AUDIT_ACTION_TICKET_REOPENED,"");
				$this->cer_parser->ticket_reopen($this->cer_ticket);
			}
			
			// [JAS]: Add an audit log entry if this is a reply from a customer & not staff
			//  And trigger notification
			if($thread_type == "email" && !$this->cer_ticket->is_admin_address($o_raw_email->headers->from))
			{
				$this->audit_log->log_action($this->cer_ticket->ticket_id,0,AUDIT_ACTION_REQUESTOR_RESPONSE,"");
				
				// [JAS]: Trigger the Client Reply Notification
				if(!isset($pre_rule_codes["pre_no_notification"])) {
					$notification = CerNotification::getInstance();
					$notification->triggerEvent(EVENT_CLIENT_REPLY,array('ticket_id'=>$this->cer_ticket->ticket_id));
				}
				
				$this->cer_parser->mark_ticket_customer_replied($this->cer_ticket);
			}
			elseif($thread_type == "email" && $uid = $this->cer_ticket->is_admin_address($o_raw_email->headers->from))
			{
				$error_check = $this->cer_parser->proxy_email_to_requesters($o_raw_email,$ticket_id,@$options['PROXY_CC'],@$options['PROXY_BCC'],true);
				
				if(is_array($error_check) && count($error_check)) {
					$errors = true;
					$error_msg = sprintf("Could not send e-mail to requester list. (<b>%s</b>)",implode("; ",$error_check));
					array_push($error_log,$error_msg);
				}
				
				$this->audit_log->log_action($ticket_id,$uid,AUDIT_ACTION_REPLIED,"");
			}
			
			$this->cer_search->indexSingleTicket($ticket_id);
		}
		
		elseif(stristr($o_raw_email->headers->from,"mailer-daemon@") !== false || stristr($o_raw_email->headers->from,"postmaster@") !== false)  // Ticket is from a mail server
		{    // [JSJ]: If the email is from a mail server itself then try to add to existing ticket
//			echo "BOUNCE TICKET<br>"; flush();
			$suppress_watchers = true;
			
			if($ticket_id = $this->cer_parser->find_ticketid_in_body($o_raw_email)) // [JSJ]: existing ticket
			{
				if($verbose == 1) { echo "BOUNCE post find_ticketid_in_body()<br>"; flush(); }
				$ticket_id = $this->cer_parser->check_if_merged($ticket_id); // [JAS]: If merged, find the latest ticket ID
				
				if($verbose == 1) { echo "BOUNCE post check_if_merged()<br>"; flush(); }
				
				$this->cer_ticket->load_ticket_data($ticket_id);
				if($verbose == 1) { echo "BOUNCE post load_ticket_data()<br>"; flush(); }
				
				$thread_id = $this->cer_ticket->add_ticket_thread($o_raw_email,$thread_type,false,$auto_cc_req);
				if($verbose == 1) { echo "BOUNCE post add_ticket_thread()<br>"; flush(); }
				if($this->cer_ticket->is_reopened) {
					$this->audit_log->log_action($this->cer_ticket->ticket_id,0,AUDIT_ACTION_TICKET_REOPENED,"");
					$this->cer_parser->ticket_reopen($this->cer_ticket);
					if($verbose == 1) { echo "BOUNCE post ticket_reopen()<br>"; flush(); }
				}
				
//				$this->cer_parser->mark_ticket_bounced($this->cer_ticket);
				$this->audit_log->log_action($this->cer_ticket->ticket_id,0,AUDIT_ACTION_DELIVERY_FAILURE,"");
			}
			else // [JSJ]: Could not match bounce to existing ticket so create new one
			{
				if($verbose == 1) { echo "BOUNCE new ticket<br>"; flush(); }
				// [JAS]: Merge the TO/CC/BCC addresses and attempt to find the destination queue
				if(!$dest_queue)
				{
					if($verbose == 1) { echo "BOUNCE post null dest_queue()<br>"; flush(); }
					$error_msg = sprintf("Incoming message didn't match a queue in TO/CC/BCC (<b>%s</b>).",
							implode(", ",@$o_raw_email->headers->to)
						);
					$this->cer_log->log($error_msg);
					$this->last_error_msg = $error_msg;
					return FALSE;
				}
				
				if($verbose == 1) { echo "BOUNCE pre create_new_ticket()<br>"; flush(); }
				$this->cer_ticket->create_new_ticket($o_raw_email,$dest_queue);
				if($verbose == 1) { echo "BOUNCE post create_new_ticket()<br>"; flush(); }
				$thread_id = $this->cer_ticket->add_ticket_thread($o_raw_email,$thread_type,true,$auto_cc_req);
				if($verbose == 1) { echo "BOUNCE post add_thread()<br>"; flush(); }
				$this->cer_ticket->save_requester_link($this->cer_ticket->ticket_id,$this->cer_ticket->requester_id);
				if($verbose == 1) { echo "BOUNCE post save_requester() - " . $this->cer_ticket->requester_id . "<br>"; flush(); }
				$this->audit_log->log_action($this->cer_ticket->ticket_id,0,AUDIT_ACTION_OPENED,$dest_queue->queue_id);
			}
		
		}
		
		else // New Ticket
		{
			if($verbose == 1) { echo "NEW TICKET<br>"; flush(); }
			
			// [JAS]: Merge the TO/CC/BCC addresses and attempt to find the destination queue
			if(!$dest_queue)
			{
				$error_msg = sprintf("Incoming message didn't match a queue in TO/CC/BCC (<b>%s</b>).",
				implode(", ",@$o_raw_email->headers->to));
				$this->cer_log->log($error_msg);
				$this->last_error_msg = $error_msg;
				return FALSE;
			}
			
			//================================================[ SLA Check ]============
			$cer_SLA = new cer_SLA();
			
			// [JAS]: Check to see if the destination queue is gated.
			if($cer_SLA->queueIsGated($dest_queue->queue_id)) {
				
				// [JAS]: It is, do we have a key?  If not:
				if(!$cer_SLA->requesterIdHasKeytoGatedQueue($this->cer_ticket->requester_id,$dest_queue->queue_id)) {
					$error_msg = sprintf("E-mail message from %s hit gated queue without an SLA. Sent gated autoresponse.",
							$this->cer_ticket->requester_address
					);
					if(!$cer_warcheck) { // if this MTA isn't at war with Cerberus
						$this->cer_log->log($error_msg);
						if(!isset($pre_rule_codes["pre_no_autoreply"])) {
							$this->cer_parser->send_gatedresponse($this->cer_ticket,$o_raw_email,$dest_queue);
						}
					}
//					$this->last_error_msg = $error_msg;
//					return FALSE;
					return TRUE;
				}
			}
			
			$due_date_mktime = $cer_SLA->getDueDateForRequesterOnQueue($this->cer_ticket->requester_id,$dest_queue->queue_id);
			if($verbose == 1) { echo "Ticket should be due on " . date("Y-m-d H:i:s",$due_date_mktime) . "<BR>"; flush(); }
			//=========================================================================
			
			$this->cer_ticket->create_new_ticket($o_raw_email,$dest_queue,$due_date_mktime);
			$thread_id = $this->cer_ticket->add_ticket_thread($o_raw_email,$thread_type,true,$auto_cc_req);
			$this->cer_ticket->save_requester_link($this->cer_ticket->ticket_id,$this->cer_ticket->requester_id);
			$this->audit_log->log_action($this->cer_ticket->ticket_id,0,AUDIT_ACTION_OPENED,$dest_queue->queue_id);
			
			// [JAS]: If autoresponses are enabled for this queue *AND* we haven't failed the warcheck 
			//  and a pre-rule hasn't surpressed autoreplies, send an autoresponse
			if($dest_queue->has_enabled_autoresponse() && $cer_warcheck === false && !isset($pre_rule_codes["pre_no_autoreply"]) && empty($options['NO_AUTOREPLY'])) {
				$error_check = $this->cer_parser->send_autoresponse($dest_queue,$this->cer_ticket);
				if(is_array($error_check) && count($error_check)) {
					$errors = true;
					$error_msg = sprintf("Could not send autoresponse e-mail. (%s)",implode("; ",$error_check));
					array_push($error_log,$error_msg);
				}
			}
			
			// [BGH]: Search index the email
			$this->cer_search->indexSingleTicketSubject($this->cer_ticket->ticket_id);
			$this->cer_search->indexSingleTicket($this->cer_ticket->ticket_id);
			
			// [BGH]: increment the daily ticket stats
			$this->cer_stats->incrementTicket($dest_queue->queue_id);
		}
		
		// [JAS]: Post rules use the ticket's first thread as a spam probability.
		$this->cer_ticket->ticket_spam_probability = $this->cer_ticket->_get_ticket_spam_probability();

		if($verbose == 1) { echo "Leaving get_ticket_spam_probability();<br>"; flush(); }
		
		// [JAS]: Workstation routing (if enabled)
		require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationRouting.class.php");
		$routing = new CerWorkstationRouting();
		$routing->routeTicket($this->cer_ticket->ticket_id, $this->cer_ticket->ticket_queue_id);
		
		if($verbose == 1) { echo "Leaving routeTicket();<br>"; flush(); }
		
		// [JAS]: Process parser mail rules for this e-mail
		$post_rule_codes = $this->cer_parser->process_mail_rules(RULE_TYPE_POST,$o_raw_email,$this->cer_ticket,$this->audit_log);
		
		if($verbose == 1) { echo "Leaving post mail rules<br>"; flush(); }
		
		// [JAS]: Workstation SLA
//		if(empty($cer_SLA)) $cer_SLA = new cer_SLA();
//		$sla_id = $cer_SLA->getSlaIdForRequesterId($this->cer_ticket->requester_id);
//		if(!empty($sla_id)) {
//			$routing->applySlaToTicket($sla_id,$this->cer_ticket->ticket_id);
//		}

		if($verbose == 1) { echo "Leaving SLA<br>"; flush(); }
		
		if($this->cer_ticket->is_new) {
			// [JAS]: Trigger the New Ticket Notification
			if(!isset($pre_rule_codes["pre_no_notification"]) && empty($options['NO_NOTIFICATIONS'])) {
				$notification = CerNotification::getInstance();
				$notification->triggerEvent(EVENT_NEW_TICKET,array('ticket_id'=>$this->cer_ticket->ticket_id));
			}
		}
		
		// [JSJ]: Send mail to all watchers for the ticket
		if(!$suppress_watchers && empty($options['NO_NOTIFICATIONS'])) {
			$error_check = $this->cer_parser->send_email_to_watchers($o_raw_email,$this->cer_ticket->ticket_id,"",$thread_type,true);
			if(is_array($error_check) && count($error_check)) {
				$errors = true;
				$error_msg = sprintf("Could not send e-mail to watchers. (<b>%s</b>)",implode("; ",$error_check));
				array_push($error_log,$error_msg);
			}
			if($verbose == 1) { echo "Leaving watchers<br>"; flush(); }
		}
		
		// [JAS]: If we had errors sending e-mail above, log them.
		if($errors && is_array($error_log) && count($error_log))
		{
			$this->cer_ticket_errors = new CER_TICKET_THREAD_ERRORS();
			$this->cer_ticket_errors->log_thread_errors($thread_id,$this->cer_ticket->ticket_id,$error_log);
		}
		
		if($verbose == 1) { echo "Leaving thread errors<br>"; flush(); }
		
		// Send satellite status updates to the master GUI about the
		//	ticket's property changes
		if($this->cfg->settings["satellite_enabled"])
		{
			$xsp_upd = new xsp_login_manager();
			$xsp_upd->register_callback_acl($this->cer_ticket,"is_admin_address");
			$xsp_upd->xsp_send_summary($this->cer_ticket->ticket_id);
		}
		
		if($verbose == 1) { echo "Leaving XSP<br>"; flush(); }
		
		return $this->cer_ticket->ticket_id;
	}
	
}