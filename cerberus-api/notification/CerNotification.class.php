<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2006, WebGroup Media LLC
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
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");

require_once(FILESYSTEM_PATH . "cerberus-api/parser/email_parser.php");
require_once(FILESYSTEM_PATH . "cerberus-api/email_templates/cer_email_templates.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");

define("EVENT_NEW_TICKET",1);
define("EVENT_ASSIGNMENT",2);
define("EVENT_CLIENT_REPLY",3);

class CerNotification {
	var $users = array();
	
	function CerNotification() {
		$db = cer_Database::getInstance();
		
		$sql = "SELECT n.user_id, n.notify_options FROM user_notification n ";
		$res = $db->query($sql);
		
		if($db->num_rows($res))
		{
			while($row = $db->fetch_row($res))
			{
				$u_id = intval($row["user_id"]);
				$this->users[$u_id] = unserialize(stripslashes($row["notify_options"]));
			}
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @return CerNotification
	 */
	function getInstance() {
		static $instance = null;
		
		if(null == $instance) {
			$instance = new CerNotification();
		}
		
		return $instance;
	}
	
	function triggerEvent($event_id,$params) {
		$email_templates = new CER_EMAIL_TEMPLATES();
		$cfg = CerConfiguration::getInstance();
		
		switch($event_id)
		{
			case EVENT_NEW_TICKET:
			{
				$ticket_id = @$params['ticket_id'];
				$ticket = CerWorkstationTickets::getTicketById($ticket_id);				

				include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
				$wsteams = CerWorkstationTeams::getInstance();
				
				if(empty($ticket_id) || empty($ticket))
					return;
					
				$mask_string = sprintf("[%s #%s]: ",
					$ticket->queue_prefix,
					$ticket->mask
				);

				$subject = sprintf("NEW TICKET: %s%s",
					(!empty($cfg->settings['subject_ids']) ? $mask_string : ""),
					$ticket->subject
				);
				
				// [JAS]: Keep a list of the e-mail addresses we've notified, and don't notify people multiple times.
				$notified = array();
				
				if(is_array($this->users))
				foreach($this->users as $user) { /* @var $user CER_NOTIFICATION_USER */
					if($user->n_new_ticket && @is_array($user->n_new_ticket->teams_send_to)) {
						
						foreach($user->n_new_ticket->teams_send_to as $teamId => $send_to) {
							$pos = @array_search($teamId,$wsteams->queues[$ticket->queue_id]);
							
							if(null == $pos || false === $pos)
								continue;
							
							$emails = explode(',', str_replace(', ',',', $send_to));
							
							if(count($emails)) {
								if(empty($ticket->queue_display_name)) {
									$from = $ticket->queue_reply_to;
								} else {
									$from = "\"" . $ticket->queue_display_name . "\" <" . $ticket->queue_reply_to . ">";
								}								
								$text = $user->n_new_ticket->template;
								$body = $email_templates->parse_template_text($text,$ticket_id);
								
								foreach($emails as $to) {
									if(!isset($notified[$to])) {
										CerWorkstationTickets::send_outgoing_mail($to,$subject,$body,$from);
										$notified[$to] = true;
									}
								}
							}
						}
					}
				}
				
				break;
			}
				
			case EVENT_ASSIGNMENT:
			{
				$ticket_id = @$params['ticket_id'];
				$agent_id = @$params['agent_id'];
				
				$emails = array();
				$ticket = CerWorkstationTickets::getTicketById($ticket_id);				
				
				if(empty($agent_id) || empty($ticket_id) || empty($ticket))
					return;
				
				@$user =& $this->users[$agent_id]; /* @var $user CER_NOTIFICATION_USER */

				if($user && $user->n_assignment && $user->n_assignment->enabled && $user->n_assignment->send_to) {
					$emails = explode(',', str_replace(', ',',', $user->n_assignment->send_to));
					if(count($emails)) {
						$mask_string = sprintf("[%s #%s]: ",
							$ticket->queue_prefix,
							$ticket->mask
						);

						$subject = sprintf("ASSIGNMENT: %s%s",
							(!empty($cfg->settings['subject_ids']) ? $mask_string : ""),
							$ticket->subject
						);
						if(empty($ticket->queue_display_name)) {
							$from = $ticket->queue_reply_to;
						} else {
							$from = "\"" . $ticket->queue_display_name . "\" <" . $ticket->queue_reply_to . ">";
						}						$text = $user->n_assignment->template;
						$body = $email_templates->parse_template_text($text,$ticket_id);
						
						foreach($emails as $to) {
							CerWorkstationTickets::send_outgoing_mail($to,$subject,$body,$from);
						}
					}
				}
				
				break;
			}
				
			case EVENT_CLIENT_REPLY:
			{
				$ticket_id = @$params['ticket_id'];
				
				$emails = array();
				$ticket = CerWorkstationTickets::getTicketById($ticket_id);				
				
				if(empty($ticket_id) || empty($ticket))
					return;

				if(!is_array($ticket->flags))
					return;
					
				$flag_ids = array_keys($ticket->flags);
				
				foreach($flag_ids as $agent_id) {
					@$user =& $this->users[$agent_id]; /* @var $user CER_NOTIFICATION_USER */
	
					if($user && $user->n_client_reply && $user->n_client_reply->enabled && $user->n_client_reply->send_to) {
						$emails = explode(',', str_replace(', ',',', $user->n_client_reply->send_to));
						
						if(count($emails)) {
							$mask_string = sprintf("[%s #%s]: ",
								$ticket->queue_prefix,
								$ticket->mask
							);

							$subject = sprintf("REPLY: %s%s",
								(!empty($cfg->settings['subject_ids']) ? $mask_string : ""),
								$ticket->subject
							);
							if(empty($ticket->queue_display_name)) {
								$from = $ticket->queue_reply_to;
							} else {
								$from = "\"" . $ticket->queue_display_name . "\" <" . $ticket->queue_reply_to . ">";
							}							$text = $user->n_client_reply->template;
							$body = $email_templates->parse_template_text($text,$ticket_id);
							
							foreach($emails as $to) {
								CerWorkstationTickets::send_outgoing_mail($to,$subject,$body,$from);
							}
						}
					}
				}
				
				
				// [JAS]: If we have notification options for the given user and they have
				//	an event set for client replies.
//				if(isset($this->active_user) 
//					&& $this->active_user->n_client_reply->enabled)
//					{
//						$emails = @$this->active_user->n_client_reply->send_to;
//						if(!empty($emails))
//						{
//							$text = $this->active_user->n_client_reply->template;
//							$this->_send_notification($parser,$cer_email,$cer_ticket,$emails,$text);
//						}
//					}
				break;
			}
		}
	}
	
	function _send_notification(&$parser,&$cer_email,&$cer_ticket,$send_to,$text)
	{
		$email_templates = new CER_EMAIL_TEMPLATES();
		$send_to = explode(",",$send_to);
		
		// [JAS]: Remove any spaces before or after commas from the email list
		foreach($send_to as $idx => $email)
			$send_to[$idx] = trim($email);
			
		$cer_email->body = $email_templates->parse_template_text($text,$cer_ticket->ticket_id);
		
		foreach($send_to as $addy)
			$parser->send_email_to_address($addy,$cer_email,$cer_ticket);
	}
	
	
};

class CER_NOTIFICATION_USER
{
	var $user_id = null;
	var $n_new_ticket = null;
	var $n_assignment = null;
	var $n_client_reply = null;
	
	function CER_NOTIFICATION_USER($uid)
	{
		$this->user_id = $uid;
		$this->n_new_ticket = new CER_NOTIFICATION_NEW_TICKET();
		$this->n_assignment = new CER_NOTIFICATION_ASSIGNMENT();
		$this->n_client_reply = new CER_NOTIFICATION_CLIENT_REPLY();
	}
}

class CER_NOTIFICATION_NEW_TICKET
{
	var $teams_send_to = array();
	var $template = "============================\r\nNEW TICKET NOTIFICATION\r\n============================\r\nTicket ID: ##ticket_id##\r\nTicket Subject: ##ticket_subject##\r\n";
};

class CER_NOTIFICATION_ASSIGNMENT
{
	var $enabled = 0;
	var $send_to = null;
	var $template = "============================\r\nNEW ASSIGNMENT NOTIFICATION\r\n============================\r\nTicket ID: ##ticket_id##\r\nTicket Subject: ##ticket_subject##\r\n";
};

class CER_NOTIFICATION_CLIENT_REPLY
{
	var $enabled = 0;
	var $send_to = null;
	var $template = "============================\r\nNEW CLIENT REPLY\r\n============================\r\nTicket ID: ##ticket_id##\r\nTicket Subject: ##ticket_subject##\r\n";
};
