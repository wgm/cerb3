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

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/email/thread_content.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");

class email_views
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function email_views() {
      $this->db =& database_loader::get_instance();
   }

   function _ticket_order_sorter($a, $b) {
      $position_a = array_search($a["ticket_id"], $this->ticket_order_array);
      $position_b = array_search($b["ticket_id"], $this->ticket_order_array);
      if($position_a == $position_b) {
         return 0;
      }
      return ($position_a < $position_b) ? -1 : 1;
   }

   function get_headers($tickets_arr) {
      $this->ticket_order_array = $tickets_arr;
      $tickets = implode("','", $tickets_arr);
      $ticket_list = $this->db->Get("ticket", "get_headers", array("tickets"=>$tickets));
      if($ticket_list === FALSE || !is_array($ticket_list)) {
         return FALSE;
      }
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $headers =& $data->add_child("headers", xml_object::create("headers"));

      $ticket_hash = array();
      $req_res = $this->db->get("ticket", "requester_list_by_tickets", array("tickets"=>$tickets_arr));
      $watcher_res = $this->db->get("ticket", "watcher_list_by_tickets", array("tickets"=>$tickets_arr));
      $contact_res = $this->db->get("ticket", "get_primary_contact_by_tickets", array("tickets"=>$tickets_arr));
      $flag_res = $this->db->get("ticket", "flag_list_by_tickets", array("tickets"=>$tickets_arr));
//      $routing_res = $this->db->get("ticket", "routing_list_by_tickets", array("tickets"=>$tickets_arr));
      $tag_res = $this->db->get("ticket", "tag_list_by_tickets", array("tickets"=>$tickets_arr));
      $spotlight_res = $this->db->get("ticket", "spotlight_list_by_tickets", array("tickets"=>$tickets_arr));
      
      // [JAS]: Initialize
      if(is_array($tickets_arr)) {
      	foreach($tickets_arr as $tik) {
      		$tid = $tik['ticket_id'];
      		if(!isset($ticket_hash[$tid])) {
      			$ticket_hash[$tid]['reqs'] = array();
      			$ticket_hash[$tid]['watchers'] = array();
      			$ticket_hash[$tid]['contacts'] = array();
      			$ticket_hash[$tid]['flags'] = array();
      			$ticket_hash[$tid]['routing'] = array();
      			$ticket_hash[$tid]['tags'] = array();
      			$ticket_hash[$tid]['spotlights'] = array();
      		}
      	}
      }
      
      // [JAS]: Requesters
      if(is_array($req_res)) {
      	foreach($req_res as $res) {
      		$tid = $res['ticket_id'];
      		$ticket_hash[$tid]['reqs'][] = $res;
      	}
      }
      
      // [JAS]: Watchers
      if(is_array($watcher_res)) {
      	foreach($watcher_res as $res) {
      		$tid = $res['ticket_id'];
      		$ticket_hash[$tid]['watchers'][] = $res;
      	}
      }
      
      // [JAS]: Contacts
      if(is_array($contact_res)) {
      	foreach($contact_res as $res) {
      		$tid = $res['ticket_id'];
      		$ticket_hash[$tid]['contacts'][] = $res;
      	}
      }
      
      // [JAS]: Flags
      if(is_array($flag_res)) {
      	foreach($flag_res as $res) {
      		$tid = $res['ticket_id'];
      		$ticket_hash[$tid]['flags'][] = $res;
      	}
      }
      
      // [JAS]: Team Routing
      if(is_array($routing_res)) {
      	foreach($routing_res as $res) {
      		$tid = $res['ticket_id'];
      		$ticket_hash[$tid]['routing'][] = $res;
      	}
      }
      
      // [JAS]: Tags
      if(is_array($tag_res)) {
      	foreach($tag_res as $res) {
      		$tid = $res['ticket_id'];
      		$ticket_hash[$tid]['tags'][] = $res;
      	}
      }
      
      // [JAS]: Spotlights
      if(is_array($spotlight_res)) {
      	foreach($spotlight_res as $res) {
      		$tid = $res['ticket_id'];
      		$ticket_hash[$tid]['spotlights'][] = $res;
      	}
      }
      
      usort($ticket_list, array(&$this, "_ticket_order_sorter"));
      foreach($ticket_list as $ticket_item) {
      	$tid = $ticket_item['ticket_id'];
      	$ticket_item['requester_list'] = $ticket_hash[$tid]['reqs'];
         $ticket_item['watcher_list'] = $ticket_hash[$tid]['watchers'];
         $ticket_item['contact_list'] = $ticket_hash[$tid]['contacts'];
         $ticket_item['flag_list'] = $ticket_hash[$tid]['flags'];
         $ticket_item['route_list'] = $ticket_hash[$tid]['routing'];
         $ticket_item['tag_list'] = $ticket_hash[$tid]['tags'];
         $ticket_item['spotlight_list'] = $ticket_hash[$tid]['spotlights'];
         $ticket =& $this->gen_xml($headers, $ticket_item, TRUE, FALSE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE);
      }
      return TRUE;
   }

   function get_contents($tickets_arr) {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $contents =& $data->add_child("contents", xml_object::create("contents"));
      $this->thread_list = array();
      $thread_content_handler = new thread_content_handler();
      foreach($tickets_arr as $ticket_id=>$max_thread) {
         $db_max_thread_id = 0;
         $ticket_item['ticket_id'] = $ticket_id;
         $this->thread_list[$ticket_id] = $this->db->get("thread", "get_thread_data_max", array('ticket_id'=>$ticket_id, "max_thread_id"=>$max_thread));
         $thread_content_handler->load_ticket_content($ticket_id, $max_thread);
         $list = $this->thread_list[$ticket_id];
         foreach($list as $key=>$thread_item) {
            $this->thread_list[$ticket_id][$key]['content'] = $thread_content_handler->threads[$thread_item['thread_id']]->content;
            if($db_max_thread_id < $thread_item['thread_id']) $db_max_thread_id = $thread_item['thread_id'];
            $this->thread_list[$ticket_id][$key]['attachments'] = $this->db->Get("thread", "attachment_list", array("thread_id"=>$thread_item['thread_id']));
         }
         $ticket_item['max_thread_id'] = $db_max_thread_id;
         $ticket =& $this->gen_xml($contents, $ticket_item, FALSE, TRUE);
      }
      return TRUE;
   }

   function get_thread_headers($tickets_arr) {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $contents =& $data->add_child("contents", xml_object::create("contents"));
      $this->thread_list = array();
      $thread_content_handler = new thread_content_handler();
      foreach($tickets_arr as $ticket_id=>$max_thread) {
         $db_max_thread_id = 0;
         $ticket_item['ticket_id'] = $ticket_id;
         $this->thread_list[$ticket_id] = $this->db->get("thread", "get_thread_data_max", array('ticket_id'=>$ticket_id, "max_thread_id"=>$max_thread));
         $list = $this->thread_list[$ticket_id];
         foreach($list as $key=>$thread_item) {
            if($db_max_thread_id < $thread_item['thread_id']) $db_max_thread_id = $thread_item['thread_id'];
            $this->thread_list[$ticket_id][$key]['attachments'] = $this->db->Get("thread", "attachment_list", array("thread_id"=>$thread_item['thread_id']));
         }
         $ticket_item['max_thread_id'] = $db_max_thread_id;
         $ticket =& $this->gen_xml($contents, $ticket_item, FALSE, TRUE, FALSE, FALSE, FALSE);
      }
      return TRUE;
   }

   function load_view($view_type, $limit, $page, $order_by_field, $order_by_direction, $id) {
      $method = "load_" . $view_type . "_view";
      if(method_exists($this, $method)) {
         return $this->$method($limit, $page, $order_by_field, $order_by_direction, $id);
      }
      else {
         return FALSE;
      }
   }

   function load_basic_view($limit, $page, $order_by_field, $order_by_direction, $id = '') {
      $this->ticket_list = $this->db->Get("ticket", "basic_view", array("limit"=>$limit, "page"=>$page, "order_by_field"=>$order_by_field, "order_by_direction"=>$order_by_direction));
      if(!$this->ticket_list || !is_array($this->ticket_list)) {
         return FALSE;
      }
      else {
         return TRUE;
      }
   }

   function load_basic_threaddata_view($limit, $page, $order_by_field, $order_by_direction, $id = '') {
      $this->ticket_list = $this->db->Get("ticket", "basic_view", array("limit"=>$limit, "page"=>$page, "order_by_field"=>$order_by_field, "order_by_direction"=>$order_by_direction));
      $this->thread_list = array();
      $thread_content_handler = new cer_ThreadContentHandler();
      foreach($this->ticket_list as $ticket_item) {
         $ticket_id = $ticket_item['ticket_id'];
         $this->thread_list[$ticket_id] = $this->db->get("thread", "get_thread_data", array('ticket_id'=>$ticket_id));
         $thread_content_handler->loadTicketContentDB($ticket_id);
         $list = $this->thread_list[$ticket_id];
         foreach($list as $key=>$thread_item) {
            $this->thread_list[$ticket_id][$key]['content'] = $thread_content_handler->threads[$thread_item['thread_id']]->content;
         }
      }
      if(!$this->ticket_list || !$this->thread_list || !is_array($this->ticket_list) || !is_array($this->thread_list)) {
         return FALSE;
      }
      else {
         return TRUE;
      }
   }

   function build_view($view_type) {
      $method = "build_" . $view_type . "_view";
      if(method_exists($this, $method)) {
         return $this->$method();
      }
      else {
         return FALSE;
      }
   }

   function build_basic_view() {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $view =& $data->add_child("view", xml_object::create("view"));
      foreach($this->ticket_list as $ticket_item) {
         $ticket =& $this->gen_xml($view, $ticket_item);
      }
      return TRUE;
   }

   function build_basic_threaddata_view() {
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $view =& $data->add_child("view", xml_object::create("view"));
      foreach($this->ticket_list as $ticket_item) {
         $this->gen_xml($view, $ticket_item, TRUE, TRUE);
      }
      return TRUE;
   }

   function gen_xml(&$view, $ticket_item, $with_ticketdata = TRUE, $with_threaddata = FALSE, $with_watchers = FALSE, $with_requesters = FALSE, $with_threadcontent = TRUE, $with_contacts = FALSE, $with_flags = FALSE, $with_routing = FALSE, $with_tags = FALSE, $with_spotlights = FALSE) {
      global $priority_options;

      $ticket =& $view->add_child("ticket", xml_object::create("ticket", NULL, array("id"=>$ticket_item['ticket_id'])));
      if($with_ticketdata) {
         if(is_null($ticket_item['ticket_mask']) || strlen($ticket_item['ticket_mask']) < 1) {
            $ticket->add_child("mask", xml_object::create("mask", $ticket_item['ticket_id']));
         }
         else {
            $ticket->add_child("mask", xml_object::create("mask", $ticket_item['ticket_mask']));
         }

         $dueDateStr = "";
         $dueDateMktime = 0;
         if(!empty($ticket_item['due_date'])) {
         	$date = new cer_DateTime($ticket_item['due_date']);
         	$dueDateStr = $date->getDate(XML_DATE_FORMAT2);
         	$dueDateMktime = $date->mktime_datetime;
         }

         $slaExpireDate = "";
         $slaExpireMktime = 0;
         if(!empty($ticket_item['sla_expire_date'])  && $ticket_item['sla_id'] != 0) {
         	$date = new cer_DateTime($ticket_item['sla_expire_date']);
         	$slaExpireDate = $date->getDate(XML_DATE_FORMAT2);
         	$slaExpireMktime = $date->mktime_datetime;
         }

         $ticket->add_child("max_thread_type", xml_object::create("max_thread_type", substr($ticket_item['max_thread_type'], 0, 1)));
         $ticket->add_child("last_reply_by_agent", xml_object::create("last_reply_by_agent", $ticket_item['last_reply_by_agent']));
         $ticket->add_child("subject", xml_object::create("subject", stripslashes($ticket_item['ticket_subject'])));
         $queue =& $ticket->add_child("queue", xml_object::create("queue", NULL, array("id"=>$ticket_item['queue_id'])));
         $queue->add_child("name", xml_object::create("name", $ticket_item['queue_name']));
         $queue->add_child("address", xml_object::create("address", $ticket_item['queue_reply_to']));
         
         $ws_ticket = new CerWorkstationTicket();
         $ws_ticket->is_waiting_on_customer = $ticket_item['is_waiting_on_customer'];
         $ws_ticket->is_closed = $ticket_item['is_closed'];
         $ws_ticket->is_deleted = $ticket_item['is_deleted'];
         
         $ticket->add_child("status", xml_object::create("status", $ws_ticket->getStatus()));
         $ticket->add_child("is_closed", xml_object::create("is_closed", $ticket_item['is_closed']));
         $ticket->add_child("is_deleted", xml_object::create("is_deleted", $ticket_item['is_deleted']));
         $ticket->add_child("is_waiting_on_customer", xml_object::create("is_waiting_on_customer", $ticket_item['is_waiting_on_customer']));

         $ticket->add_child("last_wrote_date", xml_object::create("last_wrote_date", $ticket_item['last_wrote_date']));
         $date = new cer_DateTime($ticket_item['ticket_date']);
         $ticket->add_child("created", xml_object::create("created", $date->getDate(XML_DATE_FORMAT2), array("timestamp"=>$date->mktime_datetime)));
         $ticket->add_child("priority", xml_object::create("priority", NULL, array("id"=>$ticket_item['ticket_priority'])));
         $ticket->add_child("requester", xml_object::create("requester", $ticket_item['requester_address'], array("id"=>$ticket_item['requester_address_id'])));
         $ticket->add_child("last_wrote", xml_object::create("last_wrote", $ticket_item['address_address'], array("id"=>$ticket_item['thread_address_id'])));
         $ticket->add_child("company", xml_object::create("company", $ticket_item['company_name'], array("id"=>$ticket_item['company_id'])));
         $ticket->add_child("time_worked", xml_object::create("time_worked", $ticket_item['total_time_worked']));
         $ticket->add_child("min_thread_id", xml_object::create("min_thread_id", $ticket_item['min_thread_id']));
         $ticket->add_child("max_thread_id", xml_object::create("max_thread_id", $ticket_item['max_thread_id']));
         $due =& $ticket->add_child("due", xml_object::create("due"));
         $due->add_child("due_date", xml_object::create("due_date", $dueDateStr, array("timestamp"=>$dueDateMktime)));
         $due->add_child("due_override", xml_object::create("due_override", $ticket_item['due_override']));
         $spam =& $ticket->add_child("spam", xml_object::create("spam"));
         $spam->add_child("probability", xml_object::create("probability", $ticket_item['ticket_spam_probability']*1));
         $spam->add_child("trained", xml_object::create("trained", $ticket_item['ticket_spam_trained']));
         $sla =& $ticket->add_child("sla", xml_object::create("sla"), NULL, array("id"=>intval($ticket_item['sla_id'])));
         $sla->add_child("name", xml_object::create("name", $ticket_item['sla_name']));
         $sla->add_child("expire_date", xml_object::create("expire_date", $slaExpireDate, array("timestamp"=>$slaExpireMktime)));
//         $ticket->add_child("skill_count", xml_object::create("skill_count", $ticket_item['skill_count']));
      }
      if($with_requesters) {
         $requesters =& $ticket->add_child("requesters", xml_object::create("requesters"));
         if(is_array($ticket_item['requester_list'])) {
            foreach($ticket_item['requester_list'] as $requester_item) {
               $requesters->add_child("requester", xml_object::create("requester", $requester_item['address_address'], array("id"=>$requester_item['address_id'], "suppress"=>$requester_item['suppress'])));
            }
         }
      }
      if($with_contacts) {
         $contacts =& $ticket->add_child("contacts", xml_object::create("contacts"));
         if(is_array($ticket_item['contact_list'])) {
            foreach($ticket_item['contact_list'] as $contact_item) {
               $contact =& $contacts->add_child("contact", xml_object::create("contact", NULL, array("id"=>$contact_item['id'])));
               $contact->add_child("name", xml_object::create("name", $contact_item["name"]));
            }
         }
      }
      if($with_flags) {
         $flags =& $ticket->add_child("flags", xml_object::create("flags"));
         if(is_array($ticket_item['flag_list'])) {
            foreach($ticket_item['flag_list'] as $flag_item) {
               $flag =& $flags->add_child("flag", xml_object::create("flag", NULL, array("id"=>$flag_item['flag_id'])));
               $flag->add_child("agent", xml_object::create("agent", NULL, array("id"=>$flag_item["agent_id"])));
            }
         }
      }
      if($with_routing) {
      	/* @var $route xml_object */
         $routing =& $ticket->add_child("routing", xml_object::create("routing"));
         if(is_array($ticket_item['route_list'])) {
            foreach($ticket_item['route_list'] as $route_item) {
					$route =& $routing->add_child("route", xml_object::create("route", stripslashes($route_item['team_name']), array("team_id"=>$route_item['team_id'])));
            }
         }
      }
      if($with_tags) {
      	/* @var $tag xml_object */
         $tags =& $ticket->add_child("tags", xml_object::create("tags"));
         if(is_array($ticket_item['tag_list'])) {
            foreach($ticket_item['tag_list'] as $tag_item) {
					$tag =& $tags->add_child("tag", xml_object::create("tag", NULL, array("tag_id"=>$tag_item['tag_id'])));
					$tag->add_child("name", xml_object::create("name", $tag_item['tag_name']));
            }
         }
      }
      if($with_spotlights) {
      	/* @var $tag xml_object */
         $spotlights =& $ticket->add_child("spotlights", xml_object::create("spotlights"));
         if(is_array($ticket_item['spotlight_list'])) {
            foreach($ticket_item['spotlight_list'] as $spotlight_item) {
					$spotlight =& $spotlights->add_child("spotlight", xml_object::create("spotlight", stripslashes($spotlight_item["user_name"]), array("agent_id"=>$spotlight_item["agent_id"])));
            }
         }
      }
      if($with_watchers) {
         $watchers =& $ticket->add_child("watchers", xml_object::create("watchers"));
         if(is_array($ticket_item['watcher_list'])) {
            foreach($ticket_item['watcher_list'] as $watcher_item) {
               $watcher =& $watchers->add_child("watcher", xml_object::create("watcher", NULL, array("user_id"=>$watcher_item['user_id'])));
               $watcher->add_child("email", xml_object::create("email", $watcher_item['user_email']));
               $watcher->add_child("name", xml_object::create("name", $watcher_item['user_name']));
            }
         }
      }
      if($with_threaddata && !$with_ticketdata) {
         $ticket->add_child("max_thread_id", xml_object::create("max_thread_id", $ticket_item['max_thread_id']));
      }
      if($with_threaddata) {
         $threads =& $ticket->add_child("threads", xml_object::create("threads"));
         foreach($this->thread_list[$ticket_item['ticket_id']] as $thread_item) {
            $thread =& $threads->add_child("thread", xml_object::create("thread", NULL, array("id"=>$thread_item['thread_id'])));
            $thread->add_child("type", xml_object::create("type", $thread_item['thread_type']));
            $thread->add_child("address_banned", xml_object::create("address_banned", $thread_item['address_banned']));
            $thread->add_child("address", xml_object::create("address", $thread_item['address_address'], array('id'=>$thread_item['address_id'])));
           	$from =& $thread->add_child("from", xml_object::create("from"));
			$from->add_child("address", xml_object::create("address", $thread_item['address_address'], array('id'=>$thread_item['address_id'])));
            if($thread_item['public_user_id'] != "") {
            	$contact_name = $thread_item['name_first'].' '. $thread_item['name_last'];
            	$from->add_child("contact", xml_object::create("contact", $contact_name, array('id'=>$thread_item['public_user_id'])));
            }
            if($thread_item['company_id'] != "") {
            	$from->add_child("company", xml_object::create("company", $thread_item['company_name'], array('id'=>$thread_item['company_id'])));
            }
            $thread->add_child("subject", xml_object::create("subject", stripslashes($thread_item['thread_subject'])));
            $thread->add_child("to", xml_object::create("to", $thread_item['thread_to']));
            $thread->add_child("cc", xml_object::create("cc", $thread_item['thread_cc']));
            $thread->add_child("bcc", xml_object::create("bcc", $thread_item['thread_bcc']));
            $date = new cer_DateTime($thread_item['thread_date']);
            $thread->add_child("date", xml_object::create("date", $date->getDate(XML_DATE_FORMAT2), array("timestamp"=>$thread_item['thread_timestamp'])));
            $thread->add_child("replyto", xml_object::create("replyto", $thread_item['thread_replyto']));
            $thread->add_child("is_agent_message", xml_object::create("is_agent_message", $thread_item['is_agent_message']));
            if($with_threadcontent) {
				$ctrlCharFilteredContent = strtr ( stripslashes($thread_item['content']), '', ' ');
               $thread->add_child("content", xml_object::create("content", $ctrlCharFilteredContent));
            }
            $attachments =& $thread->add_child("attachments", xml_object::create("attachments"));
            if(is_array($thread_item['attachments']) && count($thread_item['attachments']) > 0) {
               $thread->add_child("has_attachments", xml_object::create("has_attachments", "TRUE"));
               foreach($thread_item['attachments'] as $attachment_item) {
                  $attachments->add_child("attachment", xml_object::create("attachment", $attachment_item['file_name'], array('size'=>$attachment_item['file_size'], 'id'=>$attachment_item['file_id'])));
               }
            }
            else {
               $thread->add_child("has_attachments", xml_object::create("has_attachments", "FALSE"));
            }
         }
      }
   }
}
