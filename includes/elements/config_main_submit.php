<?php
/*
 * Important Licensing Note from the Cerberus Helpdesk Team:
 * 
 * Yes, it would be really easy for you to to just cheat and edit this file to 
 * use the software without paying for it.  We're trusting the community to be
 * honest and understand that quality software backed by a dedicated team takes
 * money to develop.  We aren't volunteers over here, and we aren't working 
 * from our bedrooms -- we do this for a living.  This pays our rent, health
 * insurance, and keeps the lights on at the office.  If you're using the 
 * software in a commercial or government environment, please be honest and
 * buy a license.  We aren't asking for much. ;)
 * 
 * Encoding/obfuscating our source code simply to get paid is something we've
 * never believed in -- any copy protection mechanism will inevitably be worked
 * around.  Cerberus development thrives on community involvement, and the 
 * ability of users to adapt the software to their needs.
 * 
 * A legitimate license entitles you to support, access to the developer 
 * mailing list, the ability to participate in betas, the ability to
 * purchase add-on tools (e.g., Workstation, Standalone Parser) and the 
 * warm-fuzzy feeling of doing the right thing.
 *
 * Thanks!
 * -the Cerberus Helpdesk dev team (Jeff, Mike, Jerry, Darren, Brenan)
 * and Cerberus Core team (Luke, Alasdair, Philipp, Jeremy, Ben)
 *
 * http://www.cerberusweb.com/
 * support@cerberusweb.com
 */

require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");

if((!DEMO_MODE && isset($form_submit)))
{
	switch($form_submit)
	{
		case "mail_settings":
			if(empty($cfg_sendmail)) $cfg_sendmail = 0;
			if(empty($cfg_parser_secure_enabled)) $cfg_parser_secure_enabled = 0;
			if(empty($cfg_auto_add_cc_reqs)) $cfg_auto_add_cc_reqs = 0;
			if(empty($cfg_bcc_watchers)) $cfg_bcc_watchers = 0;
			if(empty($cfg_watcher_no_system_attach)) $cfg_watcher_no_system_attach = 0;
			if(empty($cfg_watcher_assigned_tech)) $cfg_watcher_assigned_tech = 0;
			if(empty($cfg_not_to_self)) $cfg_not_to_self = 0;
			if(empty($cfg_watcher_from_user)) $cfg_watcher_from_user = 0;
 			if(empty($cfg_send_precedence_bulk)) $cfg_send_precedence_bulk = 0; // [jxdemel] Feature
			if(empty($cfg_save_message_xml)) $cfg_save_message_xml = 0;
			if(empty($cfg_subject_ids)) $cfg_subject_ids = 0;
			
			$sql = "SELECT count(*) as hits FROM configuration";
			$res = $cerberus_db->query($sql);
			if(($row = $cerberus_db->grab_first_row($res)) && empty($row['hits'])) {
				$sql = "INSERT INTO configuration(cfg_id) VALUES (1)";
				$cerberus_db->query($sql);
			}
			
			$sql = sprintf("UPDATE configuration SET ".
				"warcheck_secs=%d,auto_add_cc_reqs=%d,".
				"mail_delivery=%s,smtp_server=%s,smtp_server_user=%s,smtp_server_pass=%s,cut_line=%s,sendmail=%d,".
				"parser_secure_enabled=%d,parser_secure_user=%s,parser_secure_password=%s,".
				"watcher_assigned_tech=%d,watcher_from_user=%d,not_to_self=%d,watcher_no_system_attach=%d,send_precedence_bulk=%d,".
				"bcc_watchers=%d,save_message_xml=%d,subject_ids=%d"
				,
				$cfg_warcheck_secs,
				$cfg_auto_add_cc_reqs,
				$cerberus_db->escape($cfg_mail_delivery),
				$cerberus_db->escape($cfg_smtp_server),
				$cerberus_db->escape($cfg_smtp_server_user),
				$cerberus_db->escape($cfg_smtp_server_pass),
				$cerberus_db->escape($cfg_cut_line),
				$cfg_sendmail,
				$cfg_parser_secure_enabled,
				$cerberus_db->escape($cfg_parser_secure_user),
				$cerberus_db->escape($cfg_parser_secure_password),
				$cfg_watcher_assigned_tech,
				$cfg_watcher_from_user,
				$cfg_not_to_self,
				$cfg_watcher_no_system_attach,
				$cfg_send_precedence_bulk,
				$cfg_bcc_watchers,
				$cfg_save_message_xml,
				$cfg_subject_ids
			);
					
			$cerberus_db->query($sql);
			$cfg = CerConfiguration::getInstance();
			$cfg->reload();

			break;
		case "global_settings":
		{
			$sql = "SELECT count(*) as hits FROM configuration";
			$res = $cerberus_db->query($sql);
			if(($row = $cerberus_db->grab_first_row($res)) && empty($row['hits'])) {
				$sql = "INSERT INTO configuration(cfg_id) VALUES (1)";
				$cerberus_db->query($sql);
			}
			
			if(empty($cfg_enable_customer_history)) $cfg_enable_customer_history = 0;
			if(empty($cfg_enable_id_masking)) $cfg_enable_id_masking = 0;
			if(empty($cfg_enable_audit_log)) $cfg_enable_audit_log = 0;
			if(empty($cfg_track_sid_url)) $cfg_track_sid_url = 0;
			if(empty($cfg_satellite_enabled)) $cfg_satellite_enabled = 0;
			if(empty($cfg_search_index_numbers)) $cfg_search_index_numbers = 0;
			if(empty($cfg_show_kb_topic_totals)) $cfg_show_kb_topic_totals = 0;
			if(empty($cfg_kb_editors_enabled)) $cfg_kb_editors_enabled = 0;
 			if(empty($cfg_user_only_assign_own_queues)) $cfg_user_only_assign_own_queues = 0;
 			if(empty($cfg_auto_delete_spam)) $cfg_auto_delete_spam = 0;
 			$cfg_purge_wait_hrs = sprintf("%d",$cfg_purge_wait_hrs);
			
			$sql = sprintf("UPDATE configuration SET ".
				"who_max_idle_mins=%d,enable_customer_history=%d,enable_id_masking=%d,".
				"enable_audit_log=%d,track_sid_url=%d,satellite_enabled=%d,xsp_url=%s,xsp_login=%s,xsp_password=%s,".
				"overdue_hours=%d,customer_ticket_history_max=%d,time_adjust=%d,show_kb_topic_totals=%d,".
				"default_language=%s,session_lifespan=%d,kb_editors_enabled=%d,ob_callback=%s,".
 				"user_only_assign_own_queues=%d,auto_delete_spam=%d,purge_wait_hrs=%d,".
				"session_ip_security=%d,".
				"search_index_numbers=%d,parser_version=%s,server_gmt_offset_hrs=%s,helpdesk_title=%s".
				"",
					$cfg_who_max_idle_mins,
					$cfg_enable_customer_history,
					$cfg_enable_id_masking,
					$cfg_enable_audit_log,
					$cfg_track_sid_url,
					((!HIDE_XSP_SETTINGS) ? $cfg_satellite_enabled : $cfg->settings["satellite_enabled"]),
					((!HIDE_XSP_SETTINGS) ? $cerberus_db->escape($cfg_xsp_url) : $cfg->settings["xsp_url"]),
					((!HIDE_XSP_SETTINGS) ? $cerberus_db->escape($cfg_xsp_login) : $cfg->settings["xsp_login"]),
					((!HIDE_XSP_SETTINGS) ? $cerberus_db->escape($cfg_xsp_password) : $cfg->settings["xsp_password"]),
					$cfg_overdue_hours,
					$cfg_customer_ticket_history_max,
					$cfg_time_adjust,
					$cfg_show_kb_topic_totals,
					$cerberus_db->escape($cfg_default_language),
					$cfg_session_lifespan,
					$cfg_kb_editors_enabled,
					$cerberus_db->escape($cfg_ob_callback),
 					$cfg_user_only_assign_own_queues,
 					$cfg_auto_delete_spam,
 					$cfg_purge_wait_hrs,
					$cfg_session_ip_security,
					$cfg_search_index_numbers,
					$cerberus_db->escape($cfg_parser_version),
					$cerberus_db->escape($cfg_server_gmt_offset_hrs),
					$cerberus_db->escape($cfg_helpdesk_title)
			);
					
			$cerberus_db->query($sql);
			$cfg = CerConfiguration::getInstance();
			$cfg->reload();
			
			break;
		}
		
		/*
		 * [JAS]: Workstation
		 */
		case "ws_key":
		{
			if(!$acl->has_priv(PRIV_CFG_WORKSTATION,BITGROUP_3))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationLicense.class.php");
			$license = new CerWorkstationLicense();
			
			if(empty($ws_license_file))
				break;
			
			$filename = $ws_license_file["tmp_name"];
			
			if(!@file_exists($filename))
				break;
			
			$fp = @fopen($filename, "r");
			$license_xml = "";
			while(!feof($fp)) {
				$license_xml .= @fread($fp, 4096);
			}
			
			$license->saveLicense($license_xml);
			$cerlicense = new CerWorkstationLicense();
			
			break;
		}
		
		case "ws_reports_delete":
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationReports.class.php");
			@$rids=$_REQUEST["rids"];
			
			$reports = CerWorkstationReports::getInstance();
			
			if(is_array($rids))
			foreach($rids as $report_id) {
				// [TODO] [JAS]: Check permissions
				$reports->delete($report_id);
			}
			
			break;
		
		case "ws_reports_edit":
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationReports.class.php");
			
			@$report_title = $_REQUEST['report_title'];
			@$report_data = $_FILES['report_data'];
			@$report_scriptlet = $_FILES['report_scriptlet'];
			@$report_acl = (is_array($_REQUEST['report_acl'])) ? $_REQUEST['report_acl'] : array();
			$reports = CerWorkstationReports::getInstance();

			$data_file = "";
			$scriptlet_file = "";
			
			if($report_data['size']) {
				$data_file = $report_data['tmp_name'];
			}
			if($report_scriptlet['size']) {
				$scriptlet_file = $report_scriptlet['tmp_name'];
			}
			
			if(0 != $rid) { // saving
				$reports->save($rid,$report_title,$data_file,$scriptlet_file,$report_acl);
			} else { // inserting
				$rid = $reports->create($report_title,$data_file,$scriptlet_file,$report_acl);
			}
			
			break;
		
		case "ws_key_users":
		{
			if(!$acl->has_priv(PRIV_CFG_WORKSTATION,BITGROUP_3))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationLicense.class.php");
			$license = new CerWorkstationLicense();
			
			$license->saveValidUsers($ws_users);
			break;
		}
		
		case "ws_config":
		{
			if(!$acl->has_priv(PRIV_CFG_WORKSTATION,BITGROUP_3))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationSettings.class.php");
			$settings = new CerWorkstationSettings();
			
			if(empty($ws_ip_disable)) $ws_ip_disable = 0;
			
			$settings->setIpSecurityDisabled($ws_ip_disable);
			$settings->setValidIps($ws_valid_ips);
			$settings->saveSettings();
			
			break;
		}
		
		case "ws_teams_delete":
		{
			$tid = $_REQUEST["tid"];
			
			if(!$acl->has_priv(PRIV_CFG_TEAMS_DELETE,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
			$teams = new CerWorkstationTeams();
			
			if(!empty($tid)) {
				$teams->deleteTeam($tid);
			}
			
			unset($tid);
			@$_REQUEST["tid"] = 0;
			
			break;
		}
		
		case "ws_teams_edit":
		{
			$tid = $_REQUEST["tid"];
			
			if(!$acl->has_priv(PRIV_CFG_TEAMS_CHANGE,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
			$teams = new CerWorkstationTeams();
			
			@$ws_add_team_name = $_REQUEST["ws_add_team_name"];

			// [JAS]: Are we adding a team?
			if(!empty($ws_add_team_name)) {
				$tid = $teams->addTeam($ws_add_team_name);
				break;
			}
			
			// [JAS]: We were passed team information
			if(!empty($tid)) {
				@$ws_team_name = $_REQUEST["ws_team_name"];
				@$ws_team_members = $_REQUEST["ws_team_members"];
				@$ws_team_queues = $_REQUEST['ws_team_queues'];
				@$ws_team_quick_assigns = $_REQUEST['ws_team_quickassign'];
				@$ws_team_sets = $_REQUEST['ws_team_sets'];
				@$ws_team_acl1 = $_REQUEST["ws_team_acl1"];
				@$ws_team_acl2 = $_REQUEST["ws_team_acl2"];
				@$ws_team_acl3 = $_REQUEST["ws_team_acl3"];

				$acl1 = 0;
				$acl2 = 0;
				$acl3 = 0;
				
				if(empty($ws_team_queues) || !is_array($ws_team_queues))
					$ws_team_queues = array();
					
				if(empty($ws_team_sets) || !is_array($ws_team_sets))
					$ws_team_sets = array();
					
				if(empty($ws_team_members) || !is_array($ws_team_members))
					$ws_team_members = array();
					
				if(is_array($ws_team_acl1)) {
					foreach($ws_team_acl1 as $bit)
						$acl1 += $bit;
				}
				if(is_array($ws_team_acl2)) {
					foreach($ws_team_acl2 as $bit)
						$acl2 += $bit;
				}
				if(is_array($ws_team_acl3)) {
					foreach($ws_team_acl3 as $bit)
						$acl3 += $bit;
				}
				
				$teams->saveTeam($tid, $ws_team_name, $acl1, $acl2, $acl3, $ws_team_members, $ws_team_queues, $ws_team_sets, $ws_team_quick_assigns);
			}
			break;
		}
		
		case "ws_tags_delete":
		{
			$tid = $_REQUEST["tid"];
			
			if(!$acl->has_priv(PRIV_CFG_TAGS_DELETE,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
			$tags = new CerWorkstationTags();
			
			if(!empty($tid)) {
				$tags->deleteTag($tid);
			}
			
			unset($tid);
			@$_REQUEST["tid"] = 0;
			
			break;
		}
		
		case "ws_tags_edit":
		{
			$tid = $_REQUEST["tid"];

			if(!$acl->has_priv(PRIV_CFG_TAGS_CHANGE,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
			$tags = new CerWorkstationTags();
			
			// [JAS]: Are we adding a tag?
//			if(!empty($ws_add_tag_name)) {
//				$ws_add_tag_set_id = intval($_REQUEST["ws_add_tag_set_id"]);
//				$tid = $tags->addTag($ws_add_tag_name,$ws_add_tag_set_id);
//				break;
//			}
			
			// [JAS]: We were passed tag information
			$ws_tag_name = $_REQUEST["ws_tag_name"];
			$ws_tag_terms = $_REQUEST["ws_tag_terms"];

			if(!empty($ws_tag_terms)) {
				$ws_tag_terms = explode("\r\n", $ws_tag_terms);
			} else {
				$ws_tag_terms = array();
			}
			
			$tags->saveTag($tid, $ws_tag_name, $ws_tag_terms);
			
			break;
		}
		
//		case "ws_tag_sets_delete":
//			$tid = $_REQUEST["tid"];
//			
//			if(!$acl->has_priv(PRIV_CFG_TAGS_DELETE,BITGROUP_2))	{
//				die(LANG_CERB_ERROR_ACCESS);
//			}
//			
//			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
//			$tags = new CerWorkstationTags();
//			
//			if(!empty($tid)) {
//				if($tags->deleteTagSet($tid)) {
//					unset($sid);
//					@$_REQUEST["tid"] = 0;
//				}
//			}
//			
//			break;
//		
//		case "ws_tag_sets_edit":
//			$tsid = $_REQUEST["tsid"];
//			$ws_add_set_name = $_REQUEST["ws_add_set_name"];
//			
//			if(!$acl->has_priv(PRIV_CFG_TAGS_CHANGE,BITGROUP_2))	{
//				die(LANG_CERB_ERROR_ACCESS);
//			}
//			
//			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
//			$tags = new CerWorkstationTags();
//
//			// [JAS]: Are we adding a tag?
//			if(!empty($ws_add_set_name)) {
//				$tsid = $tags->addTagSet($ws_add_set_name);
//				break;
//			}
//			
//			// [JAS]: [TODO] Save Set Changes (Name, ACL, etc.)
//			@$ws_set_name = $_REQUEST['ws_set_name'];
//			@$ws_set_teams = $_REQUEST['ws_set_teams'];
//			
//			if(empty($ws_set_teams)) $ws_set_teams = array();
//			
//			$tags->saveSet($tsid,$ws_set_name,$ws_set_teams);
//			
//			break;
		
		case "ws_routing_import":
		{
			if(!$acl->has_priv(PRIV_CFG_WORKSTATION,BITGROUP_3))	{
				die(LANG_CERB_ERROR_ACCESS);
			}

			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationRouting.class.php");
			$routing = new CerWorkstationRouting();
			
			$routing->importRouting();
			
			break;
		}
		
		case "ws_sla_edit":
		{
			if(!$acl->has_priv(PRIV_CFG_SLA_CHANGE,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationSla.class.php");
			$ws_sla = new CerWorkstationSla();
			
			@$team_ids = $_REQUEST["team_ids"];
			if(empty($team_ids) || !is_array($team_ids))
				return;
			
			$sql = sprintf("DELETE FROM `sla_to_team` WHERE `sla_id` = %d",
				$pslid
			);
			$cerberus_db->query($sql);
				
			if(is_array($team_ids))
			foreach($team_ids as $team_id) {
				$team_schedule_id = @$_REQUEST["t" . $team_id . "_schedule"];
				$team_response_time = @$_REQUEST["t" . $team_id . "_response_time"];
				$ws_sla->saveSlaTeam($pslid,$team_id,$team_schedule_id,$team_response_time);
			}

			unset($ws_sla);
			
			break;
		}
		
		case "custom_status_edit":
		{
			$ticket_status_id = $_REQUEST["ticket_status_id"];
			
			if(!$acl->has_priv(PRIV_CFG_HD_SETTINGS,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/status/CerStatuses.class.php");
			$cerStatuses = CerStatuses::getInstance(); /* @var $cerStatuses CerStatuses */
			$statuses = $cerStatuses->getList();
						
			@$add_custom_status = $_REQUEST["add_custom_status_text"];

			// [DDH]: Are we adding a new custom status?
			if(!empty($add_custom_status)) {
				$new_status = new CerStatus();
				$new_status->setText($add_custom_status);
				$cerStatuses->save($new_status);
				break;
			}
			
			// [JAS]: We were passed team information
			if(!empty($ticket_status_id)) {
				@$ticket_status_text = $_REQUEST["ticket_status_text"];
				if(!empty($ticket_status_text)) {
					$edit_status = $cerStatuses->getById($ticket_status_id);
					$edit_status->setText($ticket_status_text);
					$cerStatuses->save($edit_status);
				}
			}
			break;
					}
		
		case "custom_status_delete":
		{
			$ticket_status_id = $_REQUEST["ticket_status_id"];
			
			if(!$acl->has_priv(PRIV_CFG_HD_SETTINGS,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/status/CerStatuses.class.php");
			$cerStatuses = CerStatuses::getInstance(); /* @var $cerStatuses CerStatuses */
			$statuses = $cerStatuses->getList();
						
			if(!empty($ticket_status_id)) {
				$cerStatuses->delete($ticket_status_id);
			}
			
			unset($ticket_status_id);
			@$_REQUEST["ticket_status_id"] = 0;
			
			break;
		}
		
		case "cron_config":
		{
			if(!$acl->has_priv(PRIV_CFG_SCHED_TASKS,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/cron/CerCron.class.php");
			
			@$ips = $_REQUEST["cron_valid_ips"];
			@$pollMode = $_REQUEST["poll_mode"];
			
			$cron = new CerCron();
			$cron->setPollMode($pollMode);
			$cron->setValidIps($ips);
			$cron->saveSettings();
			
			break;
		}
		
		case "cron_tasks_edit":
		{
			if(!$acl->has_priv(PRIV_CFG_SCHED_TASKS,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			@$task_enabled = $_REQUEST["task_enabled"];
			@$task_title = $_REQUEST["task_title"];
			@$task_script = $_REQUEST["task_script"];
			@$task_day = $_REQUEST["task_day"];
			@$task_hour = $_REQUEST["task_hour"];
			@$task_minute = $_REQUEST["task_minute"];
			
			include_once(FILESYSTEM_PATH . "cerberus-api/cron/CerCron.class.php");
			
			$cron = new CerCron();
			$task = $cron->getTaskById($ptid);
			
			if(empty($task)) {
				$task = new CerCronTask();
				$task->setId(0);
			}

			if(substr($task_day,0,1) == "w") { // week day
				$task_dow = substr($task_day,1);
				$task_dom = "*";
			} elseif(substr($task_day,0,1) == "d") { // cal day
				$task_dow = "*";
				$task_dom = substr($task_day,1);
			} else { // every day
				$task_dow = "*";
				$task_dom = "*";
			}
			
			$task->setEnabled($task_enabled);
			$task->setTitle($task_title);
			$task->setScript($task_script);
			$task->setDayOfMonth($task_dom);
			$task->setDayOfWeek($task_dow);
			$task->setHour($task_hour);
			$task->setMinute($task_minute);
			
			$cron->saveTask($task);
			
			$tid = $task->getId();
			break;	
		}
		
		case "cron_task_delete":
		{
			if(!$acl->has_priv(PRIV_CFG_SCHED_TASKS,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/cron/CerCron.class.php");

			@$delId = $_REQUEST["tid"];
			
			$cron = new CerCron();
			$cron->deleteTaskId($delId);
			
			break;
		}
		
		case "parser_manual":
		{
			if(!$acl->has_priv(PRIV_CFG_PARSER_IMPORT,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/parser/CerPop3RawEmail.class.php");
			include_once(FILESYSTEM_PATH . "cerberus-api/parser/CerProcessEmail.class.php");
			$process = new CerProcessEmail();
			
			$email = stripslashes($_REQUEST["raw_email"]);
			
			if(!empty($email)) {
				$pop3email = new CerPop3RawEmail($email);
				$result = $process->process($pop3email);
				if(!$result) { // re-fail...
					$refailed = $process->last_error_msg;
				} else {
					// success!
					$refailed = FALSE;
				}
			}
			
			break;
		}
		
		case "parser_fails":
		{
			if(!$acl->has_priv(PRIV_CFG_PARSER_FAILED,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			@$fail_ids = $_REQUEST["fail_ids"];
			$re_fails = array();
			
			if(!is_array($fail_ids))
				break;
			
			$hash_failfiles = array();
			$sql = sprintf("SELECT `id`,`message_source_filename` FROM `parser_fail_headers` ".
				"WHERE `id` IN (%s)",
					implode(',', $fail_ids)
			);
			$fail_res = $cerberus_db->query($sql);
			
			if(!$cerberus_db->num_rows($fail_res))
				break;
				
			while($row = $cerberus_db->fetch_row($fail_res)) {
				$hash_failfiles[$row["id"]] = stripslashes($row["message_source_filename"]);
			}

			include_once(FILESYSTEM_PATH . "cerberus-api/parser/CerPop3RawEmail.class.php");
			include_once(FILESYSTEM_PATH . "cerberus-api/parser/CerProcessEmail.class.php");
			$process = new CerProcessEmail();
			
			$nuke_ids = array();
			if(is_array($fail_ids))
			foreach($fail_ids as $fail_id) {
				$action = @$_REQUEST["action_" . $fail_id];
				if(empty($action))
					continue;
				
				if($action == 3) { // retry
					$failFile = @$hash_failfiles[$fail_id];
					if(!empty($failFile)) {
						$failPath = FILESYSTEM_PATH . "tempdir/" . $failFile;
						if(file_exists($failPath) && is_readable($failPath)) {
							$fp = fopen($failPath,"rb");
							$email = "";
							while(!feof($fp)) { $email .= fgets($fp,50000); }
							if(!empty($email)) {
								$pop3email = new CerPop3RawEmail($email);
								$result = $process->process($pop3email);
								if(!$result) { // re-fail...
									$re_fails[$fail_id] = $process->last_error_msg;
								} else { // success! clear our fail as if we had deleted
									$nuke_ids[] = $fail_id;
								}
							}
							@fclose($fp);
						}
					}
					
				}
					
				if($action == 2) { // delete
					$nuke_ids[] = $fail_id;
				}
					
			}

			// Delete disk files
			if(is_array($nuke_ids)) {
				foreach($nuke_ids as $nuke_id) {
					$failFile = @$hash_failfiles[$nuke_id];
					if(!empty($failFile)) {
						@unlink(FILESYSTEM_PATH . "tempdir/" . $failFile);
					}
				}
			}
			
			// Delete DB rows
			$sql = sprintf("DELETE FROM `parser_fail_headers` WHERE `id` IN (%s)",
					implode(',', $nuke_ids)
			);
			$cerberus_db->query($sql);
			
			break;
		}
		
		/*
		 * [JAS]: Handles cases of new or updated pop3 accounts.
		 */
		case "pop3_edit":
		{
			if(!$acl->has_priv(PRIV_CFG_POP3_CHANGE,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/pop3/CerPop3Accounts.class.php");
			if(empty($pop3_disabled)) $pop3_disabled = 0;
			if(empty($pop3_delete)) $pop3_delete = 0;
			
			$pop3accts = new CerPop3Accounts();
			$acct = new CerPop3Account();
				$acct->setId($pgid);
				$acct->setName($pop3_name);
				$acct->setHost($pop3_host);
				$acct->setPort($pop3_port);
				$acct->setLogin($pop3_login);
				$acct->setPass($pop3_pass);
				$acct->setDisabled($pop3_disabled);
				$acct->setDelete($pop3_delete);
				$acct->setMaxMessages($pop3_max_messages);
				$acct->setMaxSize($pop3_max_size);
			$id = $pop3accts->save($acct);	
		
			if(0 == $pgid) $pgid = $id;
			break;		
		}
		
		/*
		 * [JAS]: Handles deleting single or multiple accounts (pass int or array of ints)
		 */
		case "pop3_delete":
		{
			if(!$acl->has_priv(PRIV_CFG_POP3_DELETE,BITGROUP_2))	{
				die(LANG_CERB_ERROR_ACCESS);
			}
			
			include_once(FILESYSTEM_PATH . "cerberus-api/pop3/CerPop3Accounts.class.php");
			$pop3accts = new CerPop3Accounts();
			$pop3accts->delete($pgids);
			break;			
		}
		
		case "plugins_edit":
		{
			if(empty($plugin_enabled)) $plugin_enabled = 0;
			
			$login_mgr = new cer_LoginPluginHandler();
			$plugin_data = $login_mgr->getPluginById($pgid);
			$params = array();
			
			include_once(PATH_LOGIN_PLUGINS . $login_mgr->getPluginFile($pgid));
			$plugin = $login_mgr->instantiatePlugin($pgid,$params);
			
			if($plugin_enabled != $plugin_data->plugin_enabled) {
				$sql = sprintf("UPDATE `plugin` SET `plugin_enabled` = %d WHERE `plugin_id` = %d",
							$plugin_enabled,			
							$pgid
						);
				$cerberus_db->query($sql);
			}
			
			// [JAS]: Handle the inserts for each setting individually for simplicity
			// 	in the REPLACE statement (not UPDATE/INSERT)
			if(is_array($plugin->pluginConfigure()))
			$vars = $plugin->pluginConfigure();
			foreach($vars as $var => $setting) {
				$val = isset($_REQUEST["plugin_var_" . $var]) ? $_REQUEST["plugin_var_" . $var] : "";
				
				$sql = sprintf("REPLACE INTO `plugin_var` (plugin_id, var_name, var_value) ".
						"VALUES (%d, %s, %s)",
							$pgid,
							$cerberus_db->escape($var),
							$cerberus_db->escape($val)
					);
				$cerberus_db->query($sql);
			}
			
			unset($plugin_data);
			unset($plugin);
			unset($login_mgr);
			
			break;
		}
		
		case "sla_edit":
		{
			$sla_queues = array();
			
			// [JAS]: Hash it
			if(!empty($qids)) {
				if(is_array($qids))
				foreach($qids as $qid) {
					$sla_queues[$qid] = array($_REQUEST["q" . $qid . "_schedule"], $_REQUEST["q" . $qid . "_response_time"]);
				}
			}
			
			if(!empty($pslid)) { // clear old links
				
				$sql = sprintf("UPDATE `sla` SET `name` = %s WHERE id = %d",
						$cerberus_db->escape($sla_name),
						$pslid
					);
				$cerberus_db->query($sql);
			
				$sql = sprintf("DELETE FROM sla_to_queue WHERE sla_id = %d",
						$pslid
					);
				$cerberus_db->query($sql);
				
				$sla_id = $pslid;
			}
			else {
				$sql = sprintf("INSERT INTO `sla` (`name`) VALUES (%s)",
						$cerberus_db->escape($sla_name)
					);
				$cerberus_db->query($sql);
				
				$sla_id = $cerberus_db->insert_id();
			}
			
			$sla_vals = array();
			
			if(is_array($sla_queues))
			foreach($sla_queues as $q => $d) {
				$sla_vals[] = sprintf("(%d,%d,%d,%d)",
						$sla_id,
						$q,
						$d[0],
						$d[1]
					);
			}
			
			if(!empty($sla_vals)) {
				$sql = sprintf("INSERT INTO sla_to_queue (sla_id, queue_id, schedule_id, response_time) ".
						"VALUES %s ",
							implode(",",$sla_vals)
					);
				$cerberus_db->query($sql);
			}
			
			unset($pslid);
			unset($slid);
			
			break;
		}
		
		case "sla_delete":
		{
			if(!empty($sids)) {
				CerSecurityUtils::integerArray($sids);
				
				$sql = sprintf("DELETE FROM sla WHERE id IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
				
				$sql = sprintf("DELETE FROM sla_to_queue WHERE sla_id IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
				
				$sql = sprintf("UPDATE company SET sla_id = 0 WHERE sla_id IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "schedule_edit":
		{
			$days = array("sun","mon","tue","wed","thu","fri","sat");
			
			if(is_array($days))
			foreach($days as $day) {
				$v_hrs = $day . "_hrs";
				$v_open = $day . "_open";
				$v_close = $day . "_close";
				
				switch($$v_hrs) {
					case "24hrs":
							$$v_open = "00:00";
							$$v_close = "23:59";
						break;
					case "closed":
							$$v_open = "00:00";
							$$v_close = "00:00";
						break;
				}
			}
			
			if(!empty($pslid)) { // [JAS]: update
				$sql = sprintf("UPDATE schedule SET schedule_name = %s, sun_hrs=%s, sun_open=%s, sun_close=%s, mon_hrs=%s, mon_open=%s, mon_close=%s, ".
						"tue_hrs=%s, tue_open=%s, tue_close=%s, wed_hrs=%s, wed_open=%s, wed_close=%s, thu_hrs=%s, thu_open=%s, thu_close=%s, ".
						"fri_hrs = %s, fri_open=%s, fri_close=%s, sat_hrs=%s, sat_open=%s, sat_close=%s ".
						"WHERE schedule_id = %d",
							$cerberus_db->escape($schedule_name),
							$cerberus_db->escape($sun_hrs),
							$cerberus_db->escape($sun_open),
							$cerberus_db->escape($sun_close),
							$cerberus_db->escape($mon_hrs),
							$cerberus_db->escape($mon_open),
							$cerberus_db->escape($mon_close),
							$cerberus_db->escape($tue_hrs),
							$cerberus_db->escape($tue_open),
							$cerberus_db->escape($tue_close),
							$cerberus_db->escape($wed_hrs),
							$cerberus_db->escape($wed_open),
							$cerberus_db->escape($wed_close),
							$cerberus_db->escape($thu_hrs),
							$cerberus_db->escape($thu_open),
							$cerberus_db->escape($thu_close),
							$cerberus_db->escape($fri_hrs),
							$cerberus_db->escape($fri_open),
							$cerberus_db->escape($fri_close),
							$cerberus_db->escape($sat_hrs),
							$cerberus_db->escape($sat_open),
							$cerberus_db->escape($sat_close),
							$pslid
					);
				$cerberus_db->query($sql);
			}
			else { // insert
				$sql = sprintf("INSERT INTO schedule (schedule_name, sun_hrs, sun_open, sun_close, mon_hrs, mon_open, mon_close, ".
						"tue_hrs, tue_open, tue_close, wed_hrs, wed_open, wed_close, thu_hrs, thu_open, thu_close, fri_hrs, ".
						"fri_open, fri_close, sat_hrs, sat_open, sat_close) ".
						"VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s) ",
							$cerberus_db->escape($schedule_name),
							$cerberus_db->escape($sun_hrs),
							$cerberus_db->escape($sun_open),
							$cerberus_db->escape($sun_close),
							$cerberus_db->escape($mon_hrs),
							$cerberus_db->escape($mon_open),
							$cerberus_db->escape($mon_close),
							$cerberus_db->escape($tue_hrs),
							$cerberus_db->escape($tue_open),
							$cerberus_db->escape($tue_close),
							$cerberus_db->escape($wed_hrs),
							$cerberus_db->escape($wed_open),
							$cerberus_db->escape($wed_close),
							$cerberus_db->escape($thu_hrs),
							$cerberus_db->escape($thu_open),
							$cerberus_db->escape($thu_close),
							$cerberus_db->escape($fri_hrs),
							$cerberus_db->escape($fri_open),
							$cerberus_db->escape($fri_close),
							$cerberus_db->escape($sat_hrs),
							$cerberus_db->escape($sat_open),
							$cerberus_db->escape($sat_close)
					);
				$cerberus_db->query($sql);
			}
	
			unset($pslid); // go back to schedule screen, not edit.
			break;
		}
		
		case "schedule_delete":
		{
			if(!empty($sids)) {
				CerSecurityUtils::integerArray($sids);
				
				$sql = sprintf("DELETE FROM schedule WHERE schedule_id IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
				
				$sql = sprintf("UPDATE sla_to_queue SET schedule_id = 0 WHERE schedule_id IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
				
				$sql = sprintf("UPDATE queue SET queue_default_schedule = 0 WHERE queue_default_schedule IN (%s)",
						implode(",",$sids)
					);
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "public_gui_profiles":
		{
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
//			$teams = new CerWorkstationTeams();
//			$teamList = $teams->getTeams();

//			@$pub_mod_kb_tag_sets = $_REQUEST["pub_mod_kb_tag_sets"];
			
			if(empty($login_plugin_id)) $login_plugin_id = 0;
			if(empty($pub_mod_registration)) $pub_mod_registration = 0;
			if(empty($pub_mod_kb)) $pub_mod_kb = 0;
			if(empty($pub_mod_kb_root)) $pub_mod_kb_root = 0;
//			if(empty($pub_mod_kb_tag_sets)) $pub_mod_kb_tag_sets = array();
			if(empty($pub_mod_my_account)) $pub_mod_my_account = 0;
			if(empty($pub_mod_open_ticket)) $pub_mod_open_ticket = 0;
			if(empty($pub_mod_open_ticket_locked)) $pub_mod_open_ticket_locked = 0;
			if(empty($pub_mod_track_tickets)) $pub_mod_track_tickets = 0;
			if(empty($pub_mod_announcements)) $pub_mod_announcements = 0;
			if(empty($pub_mod_welcome)) $pub_mod_welcome = 0;
			if(empty($pub_mod_contact)) $pub_mod_contact = 0;
			
//			$ts = array();
			
//			@$pub_ts = $_REQUEST['pub_ts'];
//			@$pub_t = $_REQUEST['pub_t'];
//			@$pub_tmask = $_REQUEST['pub_tmask'];
//			@$pub_tmbox = $_REQUEST['pub_tmbox'];
//			@$pub_t_field_group = $_REQUEST['pub_t_field_group'];
			
//			if(count($pub_t))
//			{
//				if(is_array($pub_t))
//				foreach($pub_t as $t)
//				{
//					$ts[$t] = new cer_PublicGUITeam();
//					$ts[$t]->team_id = $t;
//				}
//				
//				if(is_array($pub_ts))
//				foreach($pub_ts as $idx => $tm)
//				{
//					if(isset($ts[$tm]))
//					{
//						if(!empty($pub_tmask[$idx])) {
//							$ts[$tm]->team_mask = stripslashes($pub_tmask[$idx]);
//						} else {
//							$ts[$tm]->team_mask = $teamList[$tm]->name;
//						}
//						$ts[$tm]->team_mailbox = $pub_tmbox[$idx];
//						$ts[$tm]->team_field_group = $pub_t_field_group[$idx];
//					}
//				}
//			}
			
//			$pub_teams = serialize($ts);
			
			if(0 == $pfid) // INSERT
			{
				$sql = "INSERT INTO public_gui_profiles (profile_name,pub_url,pub_company_name,pub_company_email,".
					"pub_confirmation_subject,pub_confirmation_body,pub_mod_registration,".
					"pub_mod_registration_mode,pub_mod_kb,pub_mod_kb_root,pub_mod_my_account,pub_mod_open_ticket,".
					"pub_mod_open_ticket_locked,pub_mod_track_tickets,pub_mod_announcements,".
					"pub_mod_welcome,pub_mod_welcome_title,pub_mod_welcome_text, ".
					"pub_mod_contact, pub_mod_contact_text, login_plugin_id".
					") ".
					sprintf("VALUES (%s,%s,%s,%s,%s,%s,%d,%s,%d,%d,%d,%d,%d,%d,%d,%d,%s,%s,%d,%s,%d)",
							$cerberus_db->escape($profile_name),
							$cerberus_db->escape($pub_url),
							$cerberus_db->escape($pub_company_name),
							$cerberus_db->escape($pub_company_email),
							$cerberus_db->escape($pub_confirmation_subject),
							$cerberus_db->escape($pub_confirmation_body),
							$pub_mod_registration,
							$cerberus_db->escape($pub_mod_registration_mode),
							$pub_mod_kb,
							$pub_mod_kb_root,
							$pub_mod_my_account,
							$pub_mod_open_ticket,
							$pub_mod_open_ticket_locked,
							$pub_mod_track_tickets,
							$pub_mod_announcements,
							$pub_mod_welcome,
							$cerberus_db->escape($pub_mod_welcome_title),
							$cerberus_db->escape($pub_mod_welcome_text),
							$pub_mod_contact,
							$cerberus_db->escape($pub_mod_contact_text),
							$login_plugin_id
						);
				$cerberus_db->query($sql);
				$pfid = $cerberus_db->insert_id();
			}
			else // UPDATE
			{
				$sql = sprintf("UPDATE public_gui_profiles ".
					"SET profile_name=%s,pub_url=%s,pub_company_name=%s,".
					"pub_company_email=%s,pub_confirmation_subject=%s,pub_confirmation_body=%s,".
					"pub_mod_registration=%d,pub_mod_registration_mode=%s,pub_mod_kb=%d,pub_mod_kb_root=%d,pub_mod_my_account=%d, ".
					"pub_mod_open_ticket=%d,pub_mod_open_ticket_locked=%d,pub_mod_track_tickets=%d, ".
					"pub_mod_announcements=%d,pub_mod_welcome=%d,pub_mod_welcome_title=%s,pub_mod_welcome_text=%s, ".
					"pub_mod_contact=%d, pub_mod_contact_text = %s, login_plugin_id = %d ".
					"WHERE profile_id=%d",
							$cerberus_db->escape($profile_name),
							$cerberus_db->escape($pub_url),
							$cerberus_db->escape($pub_company_name),
							$cerberus_db->escape($pub_company_email),
							$cerberus_db->escape($pub_confirmation_subject),
							$cerberus_db->escape($pub_confirmation_body),
							$pub_mod_registration,
							$cerberus_db->escape($pub_mod_registration_mode),
							$pub_mod_kb,
							$pub_mod_kb_root,
							$pub_mod_my_account,
							$pub_mod_open_ticket,
							$pub_mod_open_ticket_locked,
							$pub_mod_track_tickets,
							$pub_mod_announcements,
							$pub_mod_welcome,
							$cerberus_db->escape($pub_mod_welcome_title),
							$cerberus_db->escape($pub_mod_welcome_text),
							$pub_mod_contact,
							$cerberus_db->escape($pub_mod_contact_text),
							$login_plugin_id,
							$pfid
						);
				$cerberus_db->query($sql);
			}
			
			$pubgui = new cer_PublicGUISettings($pfid);
			unset($pubgui); // [JAS]: eh?
			
			include_once(FILESYSTEM_PATH . 'cerberus-api/queue/cer_Queue.class.php');
			$qh = cer_QueueHandler::getInstance();
			$qaddys = $qh->getQueueAddresses();
			
			@$pub_mbox = $_REQUEST['pub_mbox'];
			@$pub_mbox_id = $_REQUEST['pub_mbox_id'];
			@$pub_mbox_mask = $_REQUEST['pub_mbox_mask'];
			@$pub_mbox_field_group = $_REQUEST['pub_mbox_field_group'];
			@$pub_mbox_del = $_REQUEST['pub_mbox_del'];
			
			// Updating existing mailboxes
			if(is_array($pub_mbox) && !empty($pub_mbox)) {
				foreach($pub_mbox as $idx => $mboxid) {
					$sql = sprintf("UPDATE `mailbox_to_support_center` SET ".
						"`mailbox_id` = %d,".
						"`mailbox_address_id` = %d,".
						"`mailbox_alias` = %s,".
						"`profile_id` = %d,".
						"`field_group` = %d ".
						"WHERE `id` = %d",
							$qaddys[$pub_mbox_id[$idx]]->queue_id,
							$qaddys[$pub_mbox_id[$idx]]->address_id,
							$cerberus_db->escape($pub_mbox_mask[$idx]),
							$pfid,
							$pub_mbox_field_group[$idx],
							$mboxid
					);
					$cerberus_db->query($sql);
				}
			}
			
			// Deleting existing mailboxes			
			if(is_array($pub_mbox_del) || !empty($pub_mbox_del)) {
				$sql = sprintf("DELETE FROM `mailbox_to_support_center` WHERE `id` IN (%s)",
					implode(',', $pub_mbox_del)
				);
				$cerberus_db->query($sql);
			}
			
			@$add_mask = $_REQUEST['add_mask'];
			@$add_mbox = $_REQUEST['add_mbox'];
			@$add_field_group = $_REQUEST['add_field_group'];
			
			// We're adding one or more mailboxes
			if(is_array($add_mbox)) {
				foreach($add_mbox as $idx => $mbox) {
					if(empty($mbox) || !isset($qaddys[$mbox])) continue;
					
					$sql = sprintf("INSERT IGNORE INTO `mailbox_to_support_center` (mailbox_id,mailbox_address_id,mailbox_alias,profile_id,field_group) ".
						"VALUES (%d,%d,%s,%d,%d)",
							$qaddys[$mbox]->queue_id,
							$qaddys[$mbox]->address_id,
							$cerberus_db->escape($add_mask[$idx]),
							$pfid,
							$add_field_group[$idx]
					);
					$cerberus_db->query($sql);
				}
			}
			
			break;
		}
		
		case "public_gui_profiles_delete":
		{
			if(is_array($fids))
			foreach($fids as $key => $value) {
				$sql = sprintf("DELETE FROM public_gui_profiles WHERE profile_id = %d",
					$value
				);
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "public_gui_fields_edit":
		{
			if(!isset($field_handler)) {
				$field_handler = new cer_CustomFieldGroupHandler();
				$field_handler->loadGroupTemplates();
			}
			
			$pg_group = new cer_PublicGUIGroup();
			$pg_group->group_name = $group_name;
			
			if(!empty($fld_ids))
			foreach($fld_ids as $f) {
				$fld = new cer_PublicGUIGroupField();
				$fld->field_id = $f;
				$fld->field_name = $name_{$f};
				$fld->field_option = $option_{$f};
				$fld->field_type = $field_handler->field_to_template[$f]->fields[$f]->field_type;
				array_push($pg_group->fields,$fld);
			}
			
			if(0 != $fid) { // [JAS]: update
				$sql = sprintf("UPDATE public_gui_fields SET `group_name`=%s, group_fields=%s WHERE `group_id`=%d",
					$cerberus_db->escape($pg_group->group_name),
					$cerberus_db->escape(serialize($pg_group->fields)),
					$fid
				);
				$cerberus_db->query($sql);
			}
			else { // [JAS]: insert
				$sql = sprintf("INSERT INTO public_gui_fields (`group_name`,`group_fields`) VALUES(%s,%s)",
					$cerberus_db->escape($pg_group->group_name),
					$cerberus_db->escape(serialize($pg_group->fields))
				);
				$cerberus_db->query($sql);
				$fid=$cerberus_db->insert_id();
			}
			break;
		}
		
		case "public_gui_fields_delete":
		{
			if(is_array($fids))
			foreach($fids as $key => $value) {
				$sql = sprintf("DELETE FROM public_gui_fields WHERE group_id = %d",
					$value
				);
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "catchall_edit":
		{
			if($acl->has_priv(PRIV_CFG_QUEUES_CATCHALL,BITGROUP_2)) {

				$nuke_ids = array();
				
				if(!empty($catchall_ids))
				foreach($catchall_ids as $idx => $cid) {
					if(isset($catchall_delete_ids[$cid])) {
						$nuke_ids[] = $cid;
					}
					else {
						$sql = sprintf("UPDATE queue_catchall SET catchall_order = %d WHERE catchall_id = %d",
								$catchall_order[$idx],
								$cid
							);
						$cerberus_db->query($sql);
					}
				}
				
				CerSecurityUtils::integerArray($nuke_ids);
				
				$sql = sprintf("DELETE FROM queue_catchall WHERE catchall_id IN (%s)",
						implode(",",$nuke_ids)
					);
				$cerberus_db->query($sql);
				
			}
			break;
		}
		
		case "catchall_add":
		{
			$max_order = 0;
			
			$sql = "SELECT max(catchall_order) As max_order FROM queue_catchall";
			$res = $cerberus_db->query($sql);
			$highest = $cerberus_db->grab_first_row($res);
			
			if(isset($highest))
				$max_order = $highest["max_order"];
			
			$sql = "INSERT INTO queue_catchall (catchall_name, catchall_pattern, catchall_to_qid, catchall_order) ".
				sprintf("VALUES (%s,%s,%d,%d)",
						$cerberus_db->escape($catchall_name),
						$cerberus_db->escape($catchall_pattern),
						$catchall_to_qid,
						++$max_order
					);
				$cerberus_db->query($sql);
			break;
		}
		
		case "log":
		{
			if(isset($action) && $action=="delete") {
				$sql = "DELETE FROM log;";
				$cerberus_db->query($sql);
			}
			break;
		}
		case "addresses":
		{
			if($acl->has_priv(PRIV_BLOCK_SENDER,BITGROUP_1)) {
				if(isset($all_emails) && !empty($all_emails)) { 
					CerSecurityUtils::integerArray($all_emails);
					$sql = "UPDATE `address` SET `address_banned`=0 WHERE address_id IN (" . $all_emails . ")";
					$address_result = $cerberus_db->query($sql);
				}
	
				if(isset($ban_emails)) {
				  $cerberus_db = cer_Database::getInstance();
				
				  if(is_array($ban_emails))
					foreach($ban_emails as $key => $value) {
				    	$sql = sprintf("UPDATE `address` SET `address_banned`=1  WHERE `address_id`=%d",
				    		$value
				    	);
				    	$address_result = $cerberus_db->query($sql);
					}
				}
			}
			break;
		}
		case "maintenance":
		case "maintenance_optimize":
		case "maintenance_repair":
		case "maintenance_attachments_purge":
		{
			$sql = "SHOW TABLE STATUS";
			$res = $cerberus_db->query($sql);
			$db_tables = array();
			if($cerberus_db->num_rows($res)) {
				while($row = $cerberus_db->fetch_row($res)) {
					$db_tables[] = $row["Name"];
				}
			}
			$db_table_list = implode(",", $db_tables);
			
			if(isset($action) && $action == "optimize") {
				if(!$acl->has_priv(PRIV_CFG_MAINT_REPAIR,BITGROUP_2))
					break;
					
				$optimize_output = "";
				$sql = "OPTIMIZE TABLE `%s`";
				
				if(is_array($db_tables))
				foreach($db_tables as $db_table) {
					$opt_result = $cerberus_db->query(sprintf($sql,$db_table));
					
					if($cerberus_db->num_rows($opt_result))
					while($opt_row = $cerberus_db->fetch_row($opt_result)) {
						$optimize_output .= "<span class='cer_maintable_heading'>". $opt_row["Table"] . ":</span> <span class='cer_footer_text'>". $opt_row["Msg_text"] . "</span><br>";
					}
				}
			}
			else if(isset($action) && $action == "repair") {
				if(!$acl->has_priv(PRIV_CFG_MAINT_REPAIR,BITGROUP_2))
					break;
				$sql = "REPAIR TABLE `%s`";
				if(is_array($db_tables))
				foreach($db_tables as $db_table) {
					$rep_result = $cerberus_db->query(sprintf($sql,$db_table));
				}
			}
			else if(isset($action) && $action == "attachments_purge") {
				if(empty($attachment_purge_ids) || !is_array($attachment_purge_ids))
					break;
					
				if(!$acl->has_priv(PRIV_CFG_MAINT_ATTACH,BITGROUP_2))
					break;
				
				CerSecurityUtils::integerArray($attachment_purge_ids);
					
				// [JAS]: This should probably be in the attachment API
				$sql = sprintf("DELETE FROM thread_attachments_parts WHERE file_id IN (%s)",
						implode(",", $attachment_purge_ids)
					);
				$cerberus_db->query($sql);
				
				$sql = sprintf("DELETE FROM thread_attachments WHERE file_id IN (%s)",
						implode(",", $attachment_purge_ids)
					);
				$cerberus_db->query($sql);
			}
			else if(isset($action) && $action == "purge") {
				if(!$acl->has_priv(PRIV_CFG_MAINT_PURGE,BITGROUP_2))
					break;
					
				$sql = sprintf("SELECT t.ticket_id FROM ticket t WHERE t.is_deleted=1 AND t.ticket_date < DATE_SUB(NOW(),INTERVAL \"%d\" HOUR)",$cfg->settings["purge_wait_hrs"]);
				$purge_data = $cerberus_db->query($sql,false);
				if($purge_data && $cerberus_db->num_rows($purge_data) > 0) {
				   $num_purged_tickets = $cerberus_db->num_rows($purge_data);
				   $ticket_ids_arr = array();
				   $ticket_ids = -1;
					while($row = $cerberus_db->fetch_row($purge_data))
					   $ticket_ids_arr[$row[0]]= $row[0];
					if(count($ticket_ids_arr))
					   CerSecurityUtils::integerArray($ticket_ids);
					   $ticket_ids = implode(",",$ticket_ids_arr);
					$sql = "SELECT th.thread_id FROM thread th WHERE th.ticket_id IN (" . $ticket_ids . ")";
					$purge_thread_data = $cerberus_db->query($sql,false);
					if($purge_thread_data && $cerberus_db->num_rows($purge_thread_data) > 0) {
						$purge_thread_arr = array(); 
						$purge_thread_ids = "-1";
						while($row = $cerberus_db->fetch_row($purge_thread_data))
							$purge_thread_arr[$row[0]] = $row[0];
						if(count($purge_thread_arr))
							CerSecurityUtils::integerArray($purge_thread_ids);
							$purge_thread_ids = implode(",",$purge_thread_arr);
							
						$sql = "DELETE FROM thread_content WHERE thread_id IN (" . $purge_thread_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM thread_content_part WHERE thread_id IN (" . $purge_thread_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM thread_errors WHERE thread_id IN (" . $purge_thread_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM thread WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM merge_ticket WHERE to_ticket IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM ticket WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM requestor WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM thread_time_tracking WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM ticket_audit_log WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM search_index WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM `entity_to_field_group` WHERE entity_code = 'T' AND entity_index IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM `field_group_values` WHERE entity_code = 'T' AND entity_index IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM `next_step` WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						$sql = "DELETE FROM `ticket_flags_to_agents` WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
//						$sql = "DELETE FROM `workstation_routing_to_tickets` WHERE ticket_id IN (" . $ticket_ids . ")";
//						$cerberus_db->query($sql);
						$sql = "DELETE FROM `workstation_tags_to_tickets` WHERE ticket_id IN (" . $ticket_ids . ")";
						$cerberus_db->query($sql);
						
						$sql = "SELECT f.file_id FROM thread_attachments f WHERE f.thread_id IN (" . $purge_thread_ids . ")";
						$file_res = $cerberus_db->query($sql,false);
						if($file_res && $cerberus_db->num_rows($file_res))
						{
							$sql = "DELETE FROM thread_attachments WHERE thread_id IN (" . $purge_thread_ids . ")";
							$cerberus_db->query($sql);

							$purge_file_arr = array(); 
							$purge_file_ids = "-1";
							while($row = $cerberus_db->fetch_row($file_res))
								$purge_file_arr[$row[0]] = $row[0];
							if(count($purge_file_arr))
								CerSecurityUtils::integerArray($purge_file_ids);
								$purge_file_ids = implode(",",$purge_file_arr);
							
							$sql = "DELETE FROM thread_attachments_parts WHERE file_id IN (" . $purge_file_ids . ")";
							$cerberus_db->query($sql);
						}
					}
				}
			}
			break;
		}
		
		case "maintenance_tempdir":
		{
			include_once(FILESYSTEM_PATH . "cerberus-api/utility/tempdir/cer_Tempdir.class.php");
			$cer_tempdir = new cer_Tempdir();
			$cer_tempdir->purge_db_tempdirs();
			$purged_files = $cer_tempdir->purge_tempdir();
			break;
		}
		
		case "rules_order":
		{
			$pre_ordered = explode(',',$_REQUEST["pre_rules_ordered"]);
			$post_ordered = explode(',',$_REQUEST["post_rules_ordered"]);
			
			if(!is_array($pre_ordered)) $pre_ordered = array($pre_ordered);
			if(!is_array($post_ordered)) $post_ordered = array($post_ordered);
			
			$order = 0;
			
			if(is_array($pre_ordered))
			foreach($pre_ordered as $o) {
				$sql = sprintf("UPDATE rule_entry SET `rule_order` = %d WHERE `rule_id` = %d",
						$order++,
						$o
					);
				$cerberus_db->query($sql);
			}
			
			$order = 0;
			
			if(!empty($post_ordered))
			foreach($post_ordered as $o) {
				$sql = sprintf("UPDATE rule_entry SET `rule_order` = %d WHERE `rule_id` = %d",
						$order++,
						$o
					);
				$cerberus_db->query($sql);
			}
			
			break;
		}
		
		case "rules_edit":
		{
			if(empty($rule_name))
				$rule_name = "No Name";
			
			if(0!=$rid) { // [JAS]: update
				$sql = sprintf("UPDATE rule_entry SET `rule_name`=%s, rule_pre_parse=%d WHERE `rule_id`=%d",
					$cerberus_db->escape($rule_name),
					$rule_pre_parse,
					$rid
				);
				$cerberus_db->query($sql);
			}
			else { // [JAS]: insert
				$sql = sprintf("INSERT INTO rule_entry(`rule_name`,`rule_pre_parse`) VALUES(%s,%d)",
					$cerberus_db->escape($rule_name),
					$rule_pre_parse
				);
				$cerberus_db->query($sql);
				$rid=$cerberus_db->insert_id();
			}
			
			// [JAS]: Clear out existing criteria + actions for this rule.
			$sql = sprintf("DELETE FROM rule_fov WHERE rule_id = %d",
				$rid
			);
			$cerberus_db->query($sql);
			
			$sql = sprintf("DELETE FROM rule_action WHERE rule_id = %d",
				$rid
			);
			$cerberus_db->query($sql);
			
			// [JAS]: Loop through form input for rule criteria and add to database
			$rule_crits = array("rule_crit_sender","rule_crit_subject","rule_crit_body","rule_crit_queue","rule_crit_sla","rule_crit_new","rule_crit_reopened","rule_crit_attachment_name","rule_crit_spam_probability");
			if(is_array($rule_crits))
			foreach($rule_crits as $rc)
			{
				if($$rc) {
					$var = $rc . "_oper";
					$rc_oper = $$var;
					$var = $rc . "_value";
					$rc_value = $$var;
					
					$sql = sprintf("INSERT INTO rule_fov(rule_id,fov_field,fov_oper,fov_value) ".
						"VALUES(%d,%d,%d,%s)",
							$rid,
							$$rc,
							$rc_oper,
							$cerberus_db->escape(addslashes($rc_value))
					);
					$cerberus_db->query($sql);
				}
			}
			
			// [JAS]: Loop through form input for rule actions and add to database
 			$rule_actions = array("rule_act_chowner","rule_act_chqueue","rule_act_chstatus","rule_act_custwait","rule_act_chpriority","rule_act_break", "rule_act_pre_redirect", "rule_act_pre_bounce","rule_act_pre_ignore","rule_act_pre_no_autoreply","rule_act_pre_no_notification","rule_act_clearworkflow","rule_act_tags","rule_act_flags","rule_act_agents");
 			if(is_array($rule_actions))
			foreach($rule_actions as $ra)
			{
				if($$ra) {
					$var = $ra . "_value";
					$ra_value = $$var;
					
					if(is_array($ra_value)) {
						$ra_value = implode(',', $ra_value);
					}
					
					$sql = sprintf("INSERT INTO rule_action(rule_id,action_type,action_value) ".
						"VALUES(%d,%d,%s)",
							$rid,
							$$ra,
							$cerberus_db->escape($ra_value)
					);
					$cerberus_db->query($sql);
				}
			}
			
			break;
		}
		
		case "rule_delete":
		{
			if(0==$rid)
				break;
			
			$sql = sprintf("DELETE FROM rule_entry WHERE rule_id = %d",
				$rid
			);
			$cerberus_db->query($sql);
			$sql = sprintf("DELETE FROM rule_fov WHERE rule_id = %d",
				$rid
			);
			$cerberus_db->query($sql);
			$sql = sprintf("DELETE FROM rule_action WHERE rule_id = %d",
				$rid
			);
			$cerberus_db->query($sql);
			
			$prid = 0;
			$rid = 0;
			
			break;
		}
		
		case "queues_edit":
		{
			$open=0;
			$closed=0;
			$remote_access=0;
			
			if(empty($queue_mode)) $queue_mode = 0;
			if(empty($queue_default_schedule)) $queue_default_schedule = 0;
			if(empty($queue_default_response_time)) $queue_default_response_time = 0;
			
			if(isset($queue_send_open) && $queue_send_open) {
				$open=1;
			}
			if(isset($queue_send_closed) && $queue_send_closed) {
				$closed=1;
			}
			if(isset($queue_core_update) && $queue_core_update) {
				$remote_access=1;
			}
			
			if($qid!=0) { // update
				$sql = sprintf("UPDATE queue SET queue_name = %s, queue_email_display_name = %s, queue_reply_to = %s, queue_prefix = %s,".
				" queue_response_open = %s, queue_response_close = %s, queue_response_gated = %s, " .
				" queue_send_open=%d, queue_send_closed=%d, queue_core_update=%d, queue_mode=%d, ".
				" queue_default_response_time = %d, queue_default_schedule = %d ".
				"WHERE queue_id = %d",
					$cerberus_db->escape($queue_name),
					$cerberus_db->escape($queue_email_display_name),
					$cerberus_db->escape($queue_reply_to),
					$cerberus_db->escape($queue_prefix),
					$cerberus_db->escape($queue_response_open),
					$cerberus_db->escape($queue_response_close),
					$cerberus_db->escape($queue_response_gated),
					$open,
					$closed,
					$remote_access,
					$queue_mode,
					$queue_default_response_time,
					$queue_default_schedule,
					$qid
				);
				$cerberus_db->query($sql);
			}
			else { // insert
				$sql = sprintf("INSERT INTO `queue` (`queue_name`, `queue_email_display_name`, `queue_reply_to`, `queue_prefix`, `queue_response_open`, ". 
				"`queue_response_close`,`queue_response_gated`,`queue_send_open`, `queue_send_closed`, `queue_core_update`, `queue_mode`, `queue_default_response_time`, `queue_default_schedule`) ".
				"VALUES (%s, %s, %s, %s, %s, %s, %s, %d, %d, %d, %d, %d, %d)",
						$cerberus_db->escape($queue_name),
						$cerberus_db->escape($queue_email_display_name),
						$cerberus_db->escape($queue_reply_to),
						$cerberus_db->escape($queue_prefix),
						$cerberus_db->escape($queue_response_open),
						$cerberus_db->escape($queue_response_close),
						$cerberus_db->escape($queue_response_gated),
						$open,
						$closed,
						$remote_access,
						$queue_mode,
						$queue_default_response_time,
						$queue_default_schedule
					);
				$cerberus_db->query($sql);
				$qid=$cerberus_db->insert_id();
			}
			
			// [JAS]: If we're adding a queue address from the create/edit form
			if(!empty($queue_address) && !empty($queue_domain)) {
				$sql = sprintf("INSERT IGNORE INTO `queue_addresses` (`queue_id`,`queue_address`,`queue_domain`) ".
				"VALUES (%d,%s,%s);",
				$qid,
				$cerberus_db->escape(strtolower($queue_address)),$cerberus_db->escape(strtolower($queue_domain)));
				$cerberus_db->query($sql);
			}
			
			// [JAS]: Assign this queue to teams
			@$queue_to_teams = $_REQUEST['queue_to_teams'];
			@$quick_assign_teams = is_array($_REQUEST['quick_assign_teams']) ? $_REQUEST['quick_assign_teams'] : array();
			
			$sql = sprintf("DELETE FROM `team_queues` WHERE `queue_id` = %d",
				$qid
			);
			$cerberus_db->query($sql);
			
			if(!empty($queue_to_teams) && is_array($queue_to_teams)) {
				foreach($queue_to_teams as $team_id) {
					$pos = array_search($team_id,$quick_assign_teams);
					$quick_assign = (!is_null($pos) && $pos !== false) ? 1 : 0;
					
					$sql = sprintf("REPLACE INTO `team_queues` (`team_id`,`queue_id`,`quick_assign`) ".
						"VALUES (%d,%d,%d)",
						$team_id,
						$qid,
						$quick_assign
					);
					$cerberus_db->query($sql);
				}
			}
			
			// [JAS]: We need to do a check here for queue access and queue edit privs.
			if(isset($queue_addresses) && !empty($queue_addresses)) {
				CerSecurityUtils::integerArray($queue_addresses);
				$qa_ids = implode(",",$queue_addresses);
				$sql = "DELETE FROM `queue_addresses` WHERE `queue_addresses_id` IN ($qa_ids);";
				$cerberus_db->query($sql);
			}

			// [JAS]: Routing
			include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationRouting.class.php");
			$routing = new CerWorkstationRouting();

			@$queue_tags = $_REQUEST['queue_tags'];
			@$queue_flagged_agents = $_REQUEST['queue_flagged_agents'];
			@$queue_suggested_agents = $_REQUEST['queue_suggested_agents'];
			
			$routing->saveQueueTags($qid, $queue_tags);
			$routing->saveQueueAgents($qid, $queue_suggested_agents, 0);
			$routing->saveQueueAgents($qid, $queue_flagged_agents, 1);
			
			break;
		}
		case "queues_delete":
		{
			// [BGH] Move tickets to new queue and delete the queue
			if(is_array($qids))
			foreach($qids as $key => $value) {
				// [JAS]: get ticket ID for deleting stuff
				$sql = sprintf("UPDATE `ticket` SET `ticket_queue_id`=%d WHERE `ticket_queue_id` = %d",
					$destination_queue,
					$value
				);
				$ticket_result = $cerberus_db->query($sql);
				
				$sql = sprintf("DELETE FROM `queue` where `queue_id`=%d",$value);
				$cerberus_db->query($sql);
				$sql = sprintf("DELETE FROM `queue_addresses` where `queue_id`=%d",$value);
				$cerberus_db->query($sql);
//				$sql = sprintf("DELETE FROM `workstation_routing` where `queue_id`=%d",$value);
//				$cerberus_db->query($sql);
				$sql = sprintf("DELETE FROM `workstation_routing_tags` where `queue_id`=%d",$value);
				$cerberus_db->query($sql);
				$sql = sprintf("DELETE FROM `workstation_routing_agents` where `queue_id`=%d",$value);
				$cerberus_db->query($sql);
				$sql = sprintf("DELETE FROM `team_queues` where `queue_id`=%d",$value);
				$cerberus_db->query($sql);
				$sql = sprintf("DELETE FROM `mailbox_to_support_center` where `mailbox_id`=%d",$value);
				$cerberus_db->query($sql);
			}
			break;
		}
		
		case "custom_fields_edit":
		{
			if(empty($field_not_searchable)) $field_not_searchable = 0;

			$handler = new cer_CustomFieldGroupHandler();
			
			// [JAS]: New group we're adding
			if(empty($gid)) {
				$gid = $handler->addGroup($group_name);
				$pgid = $gid; // [JAS]: Set persistent
			}
			// [JAS]: Editing a group
			else {
				$handler->updateGroupName($gid,$group_name);
			}
			
			// [JAS]: Add a new field			
			if(!empty($field_name) && !empty($field_type)) {
				$handler->addGroupField($field_name,$field_type,$gid,0);
			}
			
			// [JAS]: Delete any fields that were checked
			if(!empty($field_ids)) {
				$handler->deleteGroupFields($gid,$field_ids);
			}
			
			if(!empty($dropdown_ids)) {
				if(is_array($dropdown_ids))
				foreach($dropdown_ids as $drop_id) {
					
					$initial = explode(',',$_REQUEST["field_" . $drop_id . "_initial"]);
					$ordered = explode(',',$_REQUEST["field_" . $drop_id . "_ordered"]);
					$deleted = array_diff($initial,$ordered);
					
					// [JAS]: Delete any field options that were checked
					if(!empty($deleted)) {
						$handler->deleteFieldOptions($deleted);
					}
					
					if(!empty($ordered)) {
						$handler->updateFieldOptionOrdering($ordered);
					}
			
					// [JAS]: Check for new dropdown options
					$new_option = $_REQUEST["option_name_" . $drop_id];
					
					// [JAS]: If this dropdown had a new option to add
					if(!empty($new_option)) {
						$order = count($initial);
						$handler->addFieldOption($drop_id,$new_option,$order);
					}
				}
			}
			
			break;	
		}
		
		case "custom_field_bindings":
		{
			$handler = new cer_CustomFieldGroupHandler();
			
			if(!empty($custom_binding))
			foreach($custom_binding as $idx => $binding) {
				$val = (isset($custom_binding_val[$idx])) ? $custom_binding_val[$idx] : 0;
				
				$sql = sprintf("REPLACE INTO `field_group_bindings` (entity_code, group_template_id) ".
						"VALUES (%s,%d)",
							$cerberus_db->escape($binding),
							$val
					);				
				$cerberus_db->query($sql);	
				
				// [JAS]: Remove old group instances if we're changing binding groups
				$sql = sprintf("SELECT efg.group_instance_id ".
					"FROM `entity_to_field_group` efg ".
					"WHERE efg.entity_code = %s ".
					"AND efg.group_id != %d",
						$cerberus_db->escape($binding),
						$val
					);
				$res = $cerberus_db->query($sql);
				
				$inst_ids = array();
				if($cerberus_db->num_rows($res)) {
					while($row = $cerberus_db->fetch_row($res)) {
						$inst_ids[] = $row["group_instance_id"];
					}
				}
				
				if(!empty($inst_ids)) {
					$handler->deleteGroupInstances($inst_ids);
				}
				
			}
			
			break;
		}
		
		case "custom_fields_delete":
		{
			$handler = new cer_CustomFieldGroupHandler();
			
			if(!empty($group_ids)) {
				foreach($group_ids as $drop_id) {
					$initial = explode(',',$_REQUEST["group_" . $drop_id . "_initial"]);
					$ordered = explode(',',$_REQUEST["group_" . $drop_id . "_ordered"]);
					$deleted = array_diff($initial,$ordered);
					
					// [JAS]: Delete any fields that were checked
//					if(!empty($deleted)) {
//						$handler->deleteGroupFields($drop_id,$deleted);
//					}
					
					if(!empty($ordered)) {
						$handler->updateFieldOrdering($ordered);
					}
				}
			}
				
			if(!empty($gids)) {
				$handler = new cer_CustomFieldGroupHandler();
				$handler->deleteGroups($gids);
			}	
		}
		
		case "users_edit":
		{
			$pass = md5($user_password_1);
			$supa = 0;
			
			// [JAS]: Boolean checkboxes to bits
			if(isset($user_superuser) && $user_superuser == 1)
				$supa=1;
					
			if($supa == 1 && $session->vars["login_handler"]->user_superuser == 0)
			{ echo "Cerberus [ERROR]: You are not permitted to make changes to superusers.";exit; }
			
			if(0!=$uid) {
				
				if($user_password_1!="") {
					$sql = sprintf("UPDATE `user` ".
					"SET `user_name` = %s, `user_display_name` = %s, `user_email` = %s, ".
					"`user_login` = %s, `user_password` = %s, ".
					"`user_superuser` = %d  ".
					"WHERE `user_id`=%d",
						$cerberus_db->escape($user_name),
						$cerberus_db->escape($user_display_name),
						$cerberus_db->escape($user_email),
						$cerberus_db->escape($user_login),
						$cerberus_db->escape($pass),
						$supa,
						$uid
					);
				}
				else {
					$sql = sprintf("UPDATE `user` ".
					"SET `user_name` = %s, `user_display_name` = %s, `user_email` = %s, ".
					"`user_login` = %s, ".
					"`user_superuser` = %d ".
					"WHERE `user_id`=%d",
						$cerberus_db->escape($user_name),
						$cerberus_db->escape($user_display_name),
						$cerberus_db->escape($user_email),
						$cerberus_db->escape($user_login),
						$supa,
						$uid
					);
				}
				$cerberus_db->query($sql);
				
			}
			else {
				
				//[TAR]: Check to see if user login already exists in database.
				$sql = sprintf("SELECT count(*) as user_exists FROM user WHERE user_login=%s",
					$cerberus_db->escape($user_login)
				);
				$usr_result = $cerberus_db->query($sql);
				$usr_count = $cerberus_db->fetch_row($usr_result);
								
				if($usr_count["user_exists"] == 1){ 
					$user_error_msg = "Cerberus [ERROR]: User login '" . $user_login ."' already exists."; 
					break;
				}

				$sql = sprintf("INSERT INTO `user` (`user_name`, `user_display_name`, `user_email`, `user_login`, `user_password`, `user_last_login`, `user_superuser`) ".
				"VALUES (%s, %s, %s, %s, %s, NOW(NULL), %d)",
					$cerberus_db->escape($user_name),
					$cerberus_db->escape($user_display_name),
					$cerberus_db->escape($user_email),
					$cerberus_db->escape($user_login),
					$cerberus_db->escape($pass),
					$supa
				);
				$cerberus_db->query($sql);
				$uid=$cerberus_db->insert_id();
			}
			break;
		}
		case "users_delete":
		{
			if($acl->has_priv(PRIV_CFG_AGENTS_DELETE,BITGROUP_2)) {
				if(is_array($uids))
				foreach($uids as $key => $value) {
					$sql = sprintf("DELETE FROM `user` where `user_id`=%d",$value);
					$cerberus_db->query($sql);
					$sql = sprintf("DELETE FROM `user_sig` where `user_id`=%d",$value);
					$cerberus_db->query($sql);
					$sql = sprintf("DELETE FROM `whos_online` where `user_id`=%d",$value);
					$cerberus_db->query($sql);
					$sql = sprintf("DELETE FROM `user_notification` where `user_id`=%d",$value);
					$cerberus_db->query($sql);
				}
			}
			break;
		}
		case "kbase_comments":
		{
			CerSecurityUtils::integerArray($comment_ids);
			if(count($comment_ids))	$coms = implode(",",$comment_ids); else $coms = "0";
			if($comment_action == "approve") // approve all checked comments
			{
				$sql = "UPDATE knowledgebase_comments SET kb_comment_approved = 1 WHERE kb_comment_id IN ($coms)";
				$cerberus_db->query($sql);
			}
			else if($comment_action=="reject") //reject and delete all checked comments
			{
				$sql = "DELETE FROM knowledgebase_comments WHERE kb_comment_id IN ($coms)";
				$cerberus_db->query($sql);
			}
			break;
		}
		case "branding":
		{
			if(($logo_img["tmp_name"] != "none" && $logo_img["tmp_name"] != "") && !isset($reset_default))
			{
				if (!copy($logo_img["tmp_name"], "logo.gif")) {
					echo "failed to install logo image... check permissions on logo.gif<br>\n";
					exit();
				}
			}
			else if (isset($reset_default))
			{
				if (!copy("cer_inctr_logo.gif","logo.gif")) {
					echo "failed to restore default logo... check permissions on logo.gif<br>\n";
					exit();
				}
			}
			break;
		}
		case "key_update":
		{
			$sql = "SELECT `key_file` FROM `product_key`";
			$key_results = $cerberus_db->query($sql);
			if($cerberus_db->num_rows($key_results) > 0) {
				$sql = sprintf("UPDATE `product_key` SET `product_key`.`key_file` = %s, `key_date`=NOW();",
					$cerberus_db->escape($product_key)
				);
				$cerberus_db->query($sql);
			}
			else {
				$sql = sprintf("INSERT `product_key`(`key_file`,`key_date`) VALUES(%s,NOW());",
					$cerberus_db->escape($product_key)
				);
				$cerberus_db->query($sql);
			}
			break;
		}
		case "feedback_send":
		{
			$mail_to = "feedback@cerberusweb.com";
			$message = "Reporter: $feedback_sender\r\nFeedback: $feedback_content\r\n";
			
			$mail = new cerbHtmlMimeMail();
			$mail->setText(stripcslashes($message));
			$mail->setFrom($feedback_sender_email);
			$mail->setSubject(stripcslashes($feedback_subject));
			$mail->setReturnPath($feedback_sender_email);
		    $mail->setHeader("X-Mailer", "Cerberus Helpdesk v. " . GUI_VERSION . " (http://www.cerberusweb.com)");	// [BGH] added mailer info
			$result = $mail->send(array($mail_to),$cfg->settings["mail_delivery"]);
			
			break;
		}
	}
}

?>
