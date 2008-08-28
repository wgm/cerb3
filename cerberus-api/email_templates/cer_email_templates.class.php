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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/ticket/cer_ThreadContent.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/public-gui/cer_PublicUser.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTimeFormat.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/sla/cer_SLA.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/schedule/cer_Schedule.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");

class CER_EMAIL_TEMPLATES
{
	var $db = null;
	var $tokens = array();
	var $tokens_values = array();
	
	var $email_templates = array();
	
	function CER_EMAIL_TEMPLATES()
	{
		$this->db = cer_Database::getInstance();
	}
	
	function _load_tokens($ticket_id=0)
	{
		global $session;
		$user_sig = "";
		
		// [JAS]: [TODO] Use WSTICKET API
		
		// [JAS]: Load up the ticket tokens if a ticket ID was given
		if(!empty($ticket_id))
		{
			$ticket = CerWorkstationTickets::getTicketById($ticket_id);
			$thread_handler = new cer_ThreadContentHandler();
			
			if($ticket)
			{
				$cer_due = $ticket->date_due;
				$tmp = $this->_generateQueueServiceDataForRequesterId($ticket->queue_id,$ticket->opened_by_address_id);
				
				if($tmp) {
					$queue_hours = $tmp["queue_hours"];
					$queue_response_time = $tmp["queue_response_time"];
				} else {
					$queue_hours = "";
					$queue_response_time = "";
				}

				@$thread_handler->loadThreadContent(array($ticket->min_thread_id,$ticket->max_thread_id));
				
				$this->tokens[] = "##ticketid##"; // for compatibility with old autoreplies
				$this->tokens[] = "##ticket_id##";
				$this->tokens[] = "##ticket_subject##";
				$this->tokens[] = "##ticket_status##";
				$this->tokens[] = "##ticket_due##";
				$this->tokens[] = "##ticket_email##";
				$this->tokens[] = "##first_email##";
				$this->tokens[] = "##latest_email##";
//				$this->tokens[] = "##ticket_time_worked##";
				$this->tokens[] = "##queue_id##";
				$this->tokens[] = "##queue_name##";
				$this->tokens[] = "##queue_hours##";
				$this->tokens[] = "##queue_response_time##";
				$this->tokens[] = "##requester_address_id##";
				$this->tokens[] = "##requester_address##";
				$this->tokens[] = "##requestor_address_id##";
				$this->tokens[] = "##requestor_address##";
				
				$this->tokens_values[] = $ticket->mask;
				$this->tokens_values[] = $ticket->mask;
				$this->tokens_values[] = $ticket->subject;
				$this->tokens_values[] = $ticket->getStatus();
				$this->tokens_values[] = $cer_due->getDate();
				$this->tokens_values[] = @$thread_handler->threads[$ticket->min_thread_id]->content;
				$this->tokens_values[] = @$thread_handler->threads[$ticket->min_thread_id]->content;
				$this->tokens_values[] = @$thread_handler->threads[$ticket->max_thread_id]->content;
//				$this->tokens_values[] = cer_DateTimeFormat::secsAsEnglishString($ticket_data["ticket_time_worked"]*60,true,4);
				$this->tokens_values[] = $ticket->queue_id;
				$this->tokens_values[] = $ticket->queue_name;
				$this->tokens_values[] = $queue_hours;
				$this->tokens_values[] = $queue_response_time;
				$this->tokens_values[] = $ticket->opened_by_address_id;
				$this->tokens_values[] = $ticket->opened_by_address;
				$this->tokens_values[] = $ticket->opened_by_address_id;
				$this->tokens_values[] = $ticket->opened_by_address;

				if($ticket->public_user_id) {
					$this->_setPublicUserTokens($ticket->public_user_id);
				}
				else {
					$this->tokens[] = "##contact_name##";
					$this->tokens[] = "##company_name##";
					$this->tokens[] = "##company_acct_num##";
					$this->tokens[] = "##sla_name##";
					
					$this->tokens_values[] = "";
					$this->tokens_values[] = "";
					$this->tokens_values[] = "";
					$this->tokens_values[] = "none";
				}
				
			}
			
		}
		
		// [JAS]: Current User Tokens
		if(isset($session) && !empty($session))
		{
			// [JAS]: User Signature Token
			$sql = sprintf("SELECT s.sig_content FROM user_sig s WHERE user_id = %d",
				$session->vars["login_handler"]->user_id
			);
			$sig_data = $this->db->query($sql);
			if($sig_row = $this->db->grab_first_row($sig_data))
				$user_sig = $sig_row["sig_content"];
				
			$this->tokens[] = "##user_id##";
			$this->tokens[] = "##user_name##";
			$this->tokens[] = "##user_login##";
			$this->tokens[] = "##user_email##";
			$this->tokens[] = "##user_address##";
			$this->tokens[] = "##user_signature##";
			
			$this->tokens_values[] = $session->vars["login_handler"]->user_id;
			$this->tokens_values[] = $session->vars["login_handler"]->user_name;
			$this->tokens_values[] = $session->vars["login_handler"]->user_login;
			$this->tokens_values[] = $session->vars["login_handler"]->user_email;
			$this->tokens_values[] = $session->vars["login_handler"]->user_email;
			$this->tokens_values[] = $user_sig;
		}
	}
	
	// [JAS]: Move this to the SLA or Scheduler object?  (We need it in the public tool too)
	function _generateQueueServiceDataForRequesterId($qid,$req_id) {
		$sla = new cer_SLA();
		$sched_handler = new cer_ScheduleHandler();
		$response_time = null;
		$schedule_id = null;
		
		if($tmp = $sla->getQueueDefaultDue($qid)) {
			$response_time = $tmp["queue_default_response_time"];
			$schedule_id = $tmp["queue_default_schedule"];
		}
		
		if($sid = $sla->getSlaIdForRequesterId($req_id)) {
			$sla_resp = @$sla->plans[$sid]->queues[$qid]->queue_response_time;
			if(!empty($sla_resp)) $response_time = $sla_resp;
			
			$sla_sched = @$sla->plans[$sid]->queues[$qid]->queue_schedule_id;
			if(!empty($sla_sched)) $schedule_id = $sla_sched;
		}
		
		if(empty($response_time)) {
			$cfg = CerConfiguration::getInstance();
			$response_time = $cfg->settings["overdue_hours"];
		}
		
		if(!empty($schedule_id)) {
			$sched_ptr = &$sched_handler->schedules[$schedule_id];
			$queue_hours = null;				
			
			if(!empty($sched_ptr)) {
				
				$dno = 0;
				foreach($sched_handler->days as $day => $abbrev) {
					$stamp = strtotime("next $day");
					
					switch($sched_ptr->weekday_hours[$dno]->hrs) {
						case "custom":
							$hours = $sched_handler->times_opt[$sched_ptr->weekday_hours[$dno]->open] . ' - ' . $sched_handler->times_opt[$sched_ptr->weekday_hours[$dno]->close] . " " . date("T");
							break;
						case "closed":
							$hours = "Closed";
							break;
						default:
						case "24hrs":
							$hours = "24 hours";
							break;
					}
					
					$queue_hours .= sprintf("%s: %s\r\n",
							//date("D",$stamp), // for localized strings
							strftime("%a",$stamp),
							$hours							
						);
				
					$dno++;
				}
			}
			else {
				foreach($sched_handler->days as $day => $abbrev) {
					$stamp = strtotime("next $day");
					$queue_hours .= sprintf("%s: %s\r\n",
							date("D",$stamp),
							"24 hours"
						);
				}
			}
					
		}
		
		return array(
				"queue_hours" => @$queue_hours, 
				"queue_response_time" => @$response_time,
			);
	}
	
	function _setPublicUserTokens($id) {
		$pubuser = null;
		
		$pubuser_handler = new cer_PublicUserHandler();
		$pubuser_handler->loadUsersByIds(array($id));
		$pubuser = &$pubuser_handler->users[$id];

		if($pubuser) {
			$sla_id = $pubuser->company_ptr->sla_id;
			
			$this->tokens[] = "##contact_name##";
			$this->tokens[] = "##company_name##";
			$this->tokens[] = "##company_acct_num##";
			$this->tokens[] = "##sla_name##";
			
			$this->tokens_values[] = $pubuser->account_name_first . " " . $pubuser->account_name_last;
			$this->tokens_values[] = $pubuser->company_ptr->company_name;
			$this->tokens_values[] = $pubuser->company_ptr->company_account_number;
			$this->tokens_values[] = (($pubuser->company_ptr->sla_ptr->sla_name) ? $pubuser->company_ptr->sla_ptr->sla_name : "none");
		}
	}
	
	function parse_canned_template($template_id,$ticket_id=0)
	{
		$parsed = "";
		
		$this->_load_tokens($ticket_id);
		
		$sql = sprintf("SELECT t.template_text FROM email_templates t WHERE t.template_id = %d",
			$template_id
		);
		$t_res = $this->db->query($sql,false);
		
		if($this->db->num_rows($t_res))
		{
			$t_row = $this->db->fetch_row($t_res);
			$email = stripslashes($t_row[0]);

			$parsed = str_replace($this->tokens,$this->tokens_values,$email);
		}
		
		return $parsed;
	}
	
	function parse_template_text($text,$ticket_id=0)
	{
		$this->_load_tokens($ticket_id);
		$parsed = str_replace($this->tokens,$this->tokens_values,$text);
		return $parsed;
	}
	
	function getTemplates() {
		$sql = "SELECT t.template_id, t.template_name, t.template_description, template_text from email_templates t ORDER BY t.template_name;";
		
//print_r($this);exit();
		$t_res  = $this->db->query($sql);
		
		if($this->db->num_rows($t_res)) {
			while($row = $this->db->fetch_row($t_res)) {
				$this->email_templates[$row["template_id"]]->id = $row["template_id"];
				$this->email_templates[$row["template_id"]]->name = $row["template_name"];
				$this->email_templates[$row["template_id"]]->description = $row["template_description"];
				$this->email_templates[$row["template_id"]]->body = $row["template_text"];
			}
		}
		//print_r($t_res->fields);exit();
		return $this->email_templates;
	}
	
	function saveTemplate($id, $name, $description, $body) {
		settype($id, 'integer');
		
		$sql = sprintf("UPDATE email_templates SET template_name = %s, template_description=%s, template_text=%s WHERE template_id = %d",
			$this->db->escape($name),
			$this->db->escape($description),
			$this->db->escape($body),
			$id
		);
		
		return $this->db->query($sql);
	}
	
	function addTemplate($name, $description, $body) {
		 $sql = sprintf("INSERT INTO email_templates(template_name,template_description,template_text) " .
      	"VALUES	(%s,%s,%s)",
      		$this->db->escape($name),
      		$this->db->escape($description),
      		$this->db->escape($body)
      );
      //echo $sql;exit();
		return $this->db->query($sql);
	}
	
	function deleteTemplate($id) {
		$sql = sprintf("DELETE FROM email_templates WHERE template_id = %d",
			$id
		);
		//echo $sql;exit();
		//echo $this->db->query($sql); exit();
		return $this->db->query($sql);    
	}
	

};

?>