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
@set_time_limit(3600); // 1hr

require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 2.7.0 to 3.0.130 RC1 Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20060407");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","fbdb155c25f4ba500442f8cfaf6bc9bc"); // 2.7.0 Clean
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	init_table_agent_sender_profile();
	init_table_cron_settings();
	init_table_cron_tasks();
	init_table_cron_valid_ips();
	init_table_dashboard();
	init_table_jasper_reports();
	init_table_jasper_reports_acl();
	init_table_kb();
	init_table_kb_content();
	init_table_migration_events();
	init_table_next_step();
	init_table_parser_fail_headers();
	init_table_pop3_accounts();
	init_table_saved_search();
	init_table_sla_to_team();
	init_table_ticket_flags_to_agents();
	init_table_ticket_due_dates();
	init_table_ticket_spotlights_to_agents();
	init_table_workstation();
	init_table_workstation_routing();
	init_table_workstation_routing_agents();
	init_table_workstation_routing_tags();
	init_table_workstation_routing_to_tickets();
	init_table_workstation_settings();
	init_table_workstation_tags();
	init_table_workstation_tags_to_kb();
	init_table_workstation_tags_to_terms();
	init_table_workstation_tags_to_tickets();
	init_table_workstation_valid_ips();
	
	update_table_next_step();
	update_table_parser_fail_headers();
	update_table_public_gui_profiles();
	update_table_pop3_accounts();
	update_table_queue();
	update_table_saved_reports();
	update_table_team();
	update_table_team_members();
	update_table_ticket();
	update_table_ticket_flags_to_agents();
	update_table_ticket_spotlights_to_agents();
	update_table_ticket_views();
	update_table_user_prefs();
	update_table_workstation();
	update_table_workstation_routing();
	update_table_workstation_routing_to_tickets();
	update_table_workstation_tags();

	migrate_knowledgebase(); // [JAS]: This needs to stay above archives
	migrate_comments();
	migrate_ticket_owners();
	migrate_ticket_statuses();
	migrate_groups();
	
	archive_table_knowledgebase();
	archive_table_knowledgebase_categories(); // [JAS]: This needs to stay after archive_table_knowledgebase()
	archive_table_knowledgebase_problem();
	archive_table_knowledgebase_solution();
	archive_fields_ticket();
	
	rename_table_knowledgebase_comments();
	rename_table_knowledgebase_ratings();
	
	update_table_kb_ratings(); // [JAS]: This needs to stay after archive + rename calls
	update_table_user();
	
	drop_table_department();
	drop_table_department_teams();
	drop_table_dispatcher_suggestions();
	drop_table_license();
	drop_table_next_step_worker();
	drop_table_parser_fail_rawemail();
	drop_table_queue_access();
	drop_table_queue_group_access();
	drop_table_team_queues();
	drop_table_ticket_categories();
	drop_table_trigram();
	drop_table_trigram_to_kb();
	drop_table_trigram_to_ticket();
	drop_table_trigram_stats();
	drop_table_trigram_training();
	drop_table_user_access_levels();
	drop_table_user_extended_info();
	drop_table_workstation_bins();
	drop_table_workstation_bin_teams();
	drop_table_workstation_tag_teams();

	add_primary_requesters(); // to ticket table
	fix_queue_addresses();
	fix_ticket_max_threads();
	set_precursor_hashes();
	add_default_scheduled_tasks();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

function add_primary_requesters() {
	global $cerberus_db;
	
	$sql = "SELECT t.ticket_id, a.address_id ".
		"FROM `ticket` t ".
		"INNER JOIN `thread` th ON (t.min_thread_id=th.thread_id) ".
		"INNER JOIN `address` a ON (th.thread_address_id=a.address_id) ".
		"WHERE t.opened_by_address_id = 0";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		echo "Importing ticket primary requesters<br>";
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE `ticket` SET `opened_by_address_id` = %d WHERE `ticket_id` = %d",
				$row['address_id'],
				$row['ticket_id']
			);
			$cerberus_db->query($sql);
		}
	}
}

function fix_queue_addresses() {
	global $cerberus_db;
	$queuesHandler = cer_QueueHandler::getInstance();
	$queues = $queuesHandler->getQueues();
	
	$sql = "SELECT t.ticket_id,t.ticket_queue_id,qa.queue_address FROM ticket t LEFT JOIN queue_addresses qa ON (t.queue_addresses_id=qa.queue_addresses_id) WHERE qa.queue_addresses_id IS NULL";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		echo "Fixing tickets with no queue address<br>";
		while($row = $cerberus_db->fetch_row($res)) {
			if(!is_array($queues[$row['ticket_queue_id']]->queue_addresses))
				continue;
			$addys = array_keys($queues[$row['ticket_queue_id']]->queue_addresses);
			
			if(!empty($addys)) {
				$sql = sprintf("UPDATE ticket SET queue_addresses_id = %d WHERE ticket_id = %d",
					$addys[0],
					$row['ticket_id']
				);
				$cerberus_db->query($sql);
			}
		}
	}
	
}

function fix_ticket_max_threads() {
	global $cerberus_db;
	
	$sql = sprintf("select t.ticket_id,t.max_thread_id,max(th.thread_id) as actual_max from ticket t LEFT JOIN thread th ON (t.ticket_id=th.ticket_id) GROUP BY th.ticket_id HAVING actual_max != t.max_thread_id");
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		echo "Fixing ticket-&gt;thread relationships<br>";
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE ticket SET max_thread_id = %d WHERE ticket_id = %d",
				$row['actual_max'],
				$row['ticket_id']
			);
			$cerberus_db->query($sql);
		}
	}
}

function init_table_agent_sender_profile() {
	global $cerberus_db;
	
   $TABLE_DEF = new CER_DB_TABLE("agent_sender_profile",false);
   
	$TABLE_DEF->create_sql = "CREATE TABLE `agent_sender_profile` ( ".
		"`id` INT UNSIGNED NOT NULL AUTO_INCREMENT , ".
		"`agent_id` BIGINT UNSIGNED default '0' NOT NULL , ".
		"`nickname` VARCHAR( 32 ) NOT NULL , ".
		"`reply_to` VARCHAR( 64 ) NOT NULL , ".
		"`signature` TEXT NOT NULL , ".
		"`is_default` TINYINT(3) NOT NULL , ".
		"PRIMARY KEY ( `id` ) , ".
		"INDEX ( `agent_id` ), ".
		"INDEX ( `is_default` ) ".
		") TYPE = MYISAM";
	
   $TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","int(11) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["nickname"] = new CER_DB_FIELD("nickname","varchar(32)","","","","");
   $TABLE_DEF->fields["reply_to"] = new CER_DB_FIELD("reply_to","varchar(64)","","","","");
   $TABLE_DEF->fields["signature"] = new CER_DB_FIELD("signature","text","","","","");
   $TABLE_DEF->fields["is_default"] = new CER_DB_FIELD("is_default","tinyint(3)","","MUL","","");
	
   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
   $TABLE_DEF->indexes["agent_id"] = new CER_DB_INDEX("agent_id","1","agent_id");
   $TABLE_DEF->indexes["is_default"] = new CER_DB_INDEX("is_default","1","is_default");

   table($TABLE_DEF);
}

function init_table_cron_settings() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("cron_settings", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE cron_settings ( ".
		"cron_poll_mode tinyint(3) unsigned NOT NULL default '0', ".
		"lock_time bigint(20) unsigned NOT NULL default '0' ".
		") TYPE=MyISAM;";

   $TABLE_DEF->fields["cron_poll_mode"] = new CER_DB_FIELD("cron_poll_mode","tinyint(3) unsigned","","","0","");
   $TABLE_DEF->fields["lock_time"] = new CER_DB_FIELD("lock_time","bigint(20) unsigned","","","0","");

	table($TABLE_DEF);
}

function init_table_cron_tasks() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("cron_tasks", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `cron_tasks` ( ".
		"`id` int(10) unsigned NOT NULL auto_increment, ".
		"`enabled` tinyint(3) unsigned NOT NULL default '0', ".
		"`minute` char(12) NOT NULL default '*', ".
		"`hour` char(3) NOT NULL default '*', ".
		"`day_of_month` char(3) NOT NULL default '*', ".
		"`day_of_week` char(3) NOT NULL default '*', ".
		"`title` char(64) NOT NULL default '', ".
		"`script` char(32) NOT NULL default '', ".
		"`next_runtime` bigint(20) unsigned NOT NULL default '0', ".
		"`last_runtime` bigint(20) unsigned NOT NULL default '0', ".
		"PRIMARY KEY  (`id`), ".
		"KEY `enabled` (`enabled`), ".
		"KEY `next_runtime` (`next_runtime`) ".
		") TYPE=MyISAM;";

   $TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["enabled"] = new CER_DB_FIELD("enabled","tinyint(3) unsigned","","MUL","0","");
   $TABLE_DEF->fields["minute"] = new CER_DB_FIELD("minute","char(12)","","","","");
   $TABLE_DEF->fields["hour"] = new CER_DB_FIELD("hour","char(3)","","","*","");
   $TABLE_DEF->fields["day_of_month"] = new CER_DB_FIELD("day_of_month","char(3)","","","*","");
   $TABLE_DEF->fields["day_of_week"] = new CER_DB_FIELD("day_of_week","char(3)","","","*","");
   $TABLE_DEF->fields["title"] = new CER_DB_FIELD("title","char(64)","","","","");
   $TABLE_DEF->fields["script"] = new CER_DB_FIELD("script","char(32)","","","","");
   $TABLE_DEF->fields["next_runtime"] = new CER_DB_FIELD("next_runtime","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["last_runtime"] = new CER_DB_FIELD("last_runtime","bigint(20) unsigned","","","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
   $TABLE_DEF->indexes["enabled"] = new CER_DB_INDEX("enabled","1","enabled");
   $TABLE_DEF->indexes["next_runtime"] = new CER_DB_INDEX("next_runtime","1","next_runtime");

	table($TABLE_DEF);
}

function init_table_cron_valid_ips() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("cron_valid_ips", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE cron_valid_ips ( ".
		"ip_mask char(15) NOT NULL default '', ".
		"PRIMARY KEY  (ip_mask) ".
		") TYPE=MyISAM;";

   $TABLE_DEF->fields["ip_mask"] = new CER_DB_FIELD("ip_mask","char(15)","","PRI","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","ip_mask");

	table($TABLE_DEF);
}

function init_table_dashboard() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("dashboard", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `dashboard` ( ".
		"`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT , ".
		"`title` CHAR(64) NOT NULL , ".
		"`agent_id` BIGINT UNSIGNED DEFAULT '0' NOT NULL , ".
		"PRIMARY KEY (`id`) , ".
		"INDEX (`agent_id`) ".
		") TYPE=MYISAM ;";

   $TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["title"] = new CER_DB_FIELD("title","char(64)","","","","");
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","bigint(20) unsigned","","MUL","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
   $TABLE_DEF->indexes["agent_id"] = new CER_DB_INDEX("agent_id","1","agent_id");

	table($TABLE_DEF);
}

function init_table_jasper_reports() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("jasper_reports",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `jasper_reports` (
		`jasper_report_id` bigint(20) unsigned NOT NULL auto_increment,
		`report_name` varchar(255) NOT NULL default '',
		`report_obj` mediumblob NOT NULL,
		`scriptlet` mediumblob,
		PRIMARY KEY  (`jasper_report_id`)
		) TYPE=MyISAM ";
	
	$TABLE_DEF->fields["jasper_report_id"] = new CER_DB_FIELD("jasper_report_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["report_name"] = new CER_DB_FIELD("report_name","varchar(255)","","","","");
	$TABLE_DEF->fields["report_obj"] = new CER_DB_FIELD("report_obj","mediumblob","","","","");
	$TABLE_DEF->fields["scriptlet"] = new CER_DB_FIELD("scriptlet","mediumblob","YES","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","jasper_report_id");
	
	table($TABLE_DEF);
}

function init_table_jasper_reports_acl() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("jasper_reports_acl",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `jasper_reports_acl` ( ".
		"`report_id` BIGINT(20) UNSIGNED DEFAULT '0' NOT NULL , ".
		"`team_id` BIGINT(20) UNSIGNED DEFAULT '0' NOT NULL , ".
		"PRIMARY KEY ( `report_id` , `team_id` ) ".
		") TYPE = MYISAM;";
	
	$TABLE_DEF->fields["report_id"] = new CER_DB_FIELD("report_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","bigint(20) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("report_id","team_id"));
	
	table($TABLE_DEF);
}

function init_table_kb() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("kb", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `kb` ( ".
		"`id` bigint(20) unsigned NOT NULL auto_increment PRIMARY KEY , ".
		"`title` char(128) NOT NULL, ".
		"`public` tinyint(3) unsigned default '0' NOT NULL, ".
		"`rating` float default '0' NOT NULL, ".
		"`votes` int(11) unsigned default '0' NOT NULL, ".
		"`views` int(11) unsigned default '0' NOT NULL, ".
		"KEY `public` (`public`) ".
		") TYPE = MYISAM ;";

   $TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["title"] = new CER_DB_FIELD("title","char(128)","","","","");
   $TABLE_DEF->fields["public"] = new CER_DB_FIELD("public","tinyint(3) unsigned","","MUL","0","");
   $TABLE_DEF->fields["rating"] = new CER_DB_FIELD("rating","float","","","0","");
   $TABLE_DEF->fields["votes"] = new CER_DB_FIELD("votes","int(11) unsigned","","","0","");
   $TABLE_DEF->fields["views"] = new CER_DB_FIELD("views","int(11) unsigned","","","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
   $TABLE_DEF->indexes["public"] = new CER_DB_INDEX("public","1","public");

	table($TABLE_DEF);
}

function init_table_kb_content() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("kb_content", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE kb_content ( ".
		"kb_id bigint(20) unsigned NOT NULL, ".
		"content text NOT NULL, ".
		"PRIMARY KEY  (kb_id) ".
		") TYPE=MyISAM;";

   $TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","bigint(20) unsigned","","PRI","","");
   $TABLE_DEF->fields["content"] = new CER_DB_FIELD("content","text","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","kb_id");

	table($TABLE_DEF);
}

function migrate_groups() {
	global $cerberus_db; /* @var $cerberus_db cer_Database */

	$sql = "SELECT g.group_id, g.group_name FROM `user_access_levels` g";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("INSERT INTO `team` (team_name) VALUES (%s)",
				$cerberus_db->escape(stripslashes($row['group_name']))
			);
			$cerberus_db->query($sql);
			$team_id = $cerberus_db->insert_id();
			
			$sql = sprintf("SELECT u.user_id FROM `user` u WHERE `user_group_id` = %d",
				$row['group_id']
			);
			$res2 = $cerberus_db->query($sql);
			if($cerberus_db->num_rows($res2)) {
				while($row2 = $cerberus_db->fetch_row($res2)) {
					$sql = sprintf("INSERT INTO `team_members` (team_id,agent_id) VALUES (%d,%d)",
						$team_id,
						$row2['user_id']
					);
					$cerberus_db->query($sql);
				}
			}
		}
	}
}

function migrate_knowledgebase() {
	global $cerberus_db; /* @var $cerberus_db cer_Database */

	// count rows to see if empty (or get from max ID in old table, and > max in new)
	$sql = "SELECT max(`id`) as num FROM `kb`";
	$res = $cerberus_db->query($sql);
	
	$row = $cerberus_db->grab_first_row($res);
	$num = intval($row['num']);
	
	$sql = sprintf("INSERT IGNORE INTO `kb` (`id`,`title`,`public`,`rating`,`votes`,`views`) ".
		"SELECT k.`kb_id`,kp.`kb_problem_summary`,k.`kb_public`,k.`kb_avg_rating`,k.`kb_rating_votes`,k.`kb_public_views` ".
		"FROM `knowledgebase` k ".
		"INNER JOIN `knowledgebase_problem` kp ".
		"USING (`kb_id`) ".
		"WHERE k.`kb_id` > %d",
		$num
	);
	$cerberus_db->query($sql);
	
	// query content from both old tables
	$sql = sprintf("SELECT k.`kb_id`,kp.`kb_problem_text`, kp.`kb_problem_text_is_html`, ks.`kb_solution_text`, ks.`kb_solution_text_is_html` ".
		"FROM `knowledgebase` k ".
		"INNER JOIN `knowledgebase_problem` kp USING (`kb_id`) ".
		"INNER JOIN `knowledgebase_solution` ks USING (`kb_id`) ".
		"WHERE k.`kb_id` > %d",
			$num
	);
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$problem_text = stripslashes($row['kb_problem_text']);
			$solution_text = stripslashes($row['kb_solution_text']);
			
			$kb_id = intval($row['kb_id']);
			
			// make all plaintext parts into html nl2br
			if(!$row['kb_problem_text_is_html'])
				$problem_text = nl2br($problem_text);
			if(!$row['kb_solution_text_is_html'])
				$solution_text = nl2br($solution_text);
			
			// insert into new table
			$sql = sprintf("INSERT INTO `kb_content` (`kb_id`,`content`) ".
				"VALUES (%d,%s)",
					$kb_id,
					$cerberus_db->escape("<h3>Problem: </h3>\r\n" . $problem_text . "\r\n<br>\r\n<br>\r\n<hr>\r\n<br>\r\n<h3>Solution: </h3>\r\n" . $solution_text)
			);
			$cerberus_db->query($sql);
		}
	}
	
}

function migrate_comments() {
	global $cerberus_db;
	
	// select all comments from the thread table (sender, content)
	$sql = sprintf("SELECT th.thread_id, th.ticket_id, unix_timestamp(th.thread_date) as mktime_created, th.thread_address_id, u.user_id ".
		"FROM thread th ".
		"INNER JOIN address a ON (th.thread_address_id=a.address_id) ".
		"LEFT JOIN `user` u ON (a.address_address=u.user_email) ".
		"WHERE th.thread_type = 'comment' "
	);
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$thread_id = intval($row['thread_id']);
			$ticket_id = intval($row['ticket_id']);
			$created = intval($row['mktime_created']);
			$user_id = intval($row['user_id']);
			
			// insert comments into next steps
			$sql = sprintf("INSERT INTO `next_step` (ticket_id,date_created,created_by_agent_id) ".
				"VALUES (%d,%d,%d)",
					$ticket_id,
					$created,
					$user_id
			);
			$cerberus_db->query($sql);
			
			$comment_id = $cerberus_db->insert_id();
			
			if(empty($comment_id))
				continue;
			
			$sql = sprintf("SELECT thread_content_part as txt FROM thread_content_part WHERE thread_id = %d ORDER BY content_id",
				$thread_id
			);
			$text_res = $cerberus_db->query($sql);
			
			if($cerberus_db->num_rows($text_res)) {
				$text = "";
				while($text_row = $cerberus_db->fetch_row($text_res)) {
					$text .= stripslashes($text_row['txt']);
				}
				$sql = sprintf("UPDATE `next_step` SET `note` = %s WHERE `id` = %d",
					$cerberus_db->escape($text),
					$comment_id
				);
				$cerberus_db->query($sql);
				
				$sql = sprintf("DELETE FROM `thread` WHERE `thread_id` = %d", $thread_id);
				$cerberus_db->query($sql);
				$sql = sprintf("DELETE FROM `thread_content_part` WHERE `thread_id` = %d", $thread_id);
				$cerberus_db->query($sql);
				$sql = sprintf("DELETE FROM `thread_attachments` WHERE `thread_id` = %d", $thread_id);
				$cerberus_db->query($sql);
				$sql = sprintf("DELETE FROM `thread_errors` WHERE `thread_id` = %d", $thread_id);
				$cerberus_db->query($sql);
			}
		}
		
		// clear attachment parts with blank parents
		$sql = sprintf("SELECT fp.part_id ".
			"FROM `thread_attachments_parts` fp ".
			"LEFT JOIN `thread_attachments` f ON (fp.file_id=f.file_id) ".
			"WHERE f.file_id IS NULL"
		);
		$res = $cerberus_db->query($sql);
		
		if($cerberus_db->num_rows($res)) {
			while($row = $cerberus_db->fetch_row($res)) {
				$sql = sprintf("DELETE FROM `thread_attachments_parts` WHERE `part_id` = %d",
					intval($row['part_id'])
				);
				$cerberus_db->query($sql);
			}
		}
	}
	
	// remove comment type from thread schema
	$sql = "ALTER TABLE `thread` CHANGE `thread_type` `thread_type` ENUM( 'email', 'forward' ) NOT NULL DEFAULT 'email'";
	$cerberus_db->query($sql);
}

function migrate_ticket_owners() {
   global $cerberus_db;

   // [JAS]: This should move into some kind of migration event API.
   $sql = sprintf("SELECT me.event, me.outcome FROM `migration_events` me WHERE me.version = %s AND me.event = %s",
   	$cerberus_db->escape("3.0.0"),
   	$cerberus_db->escape("owner_to_flag")
   );
   $res = $cerberus_db->query($sql);
   if(0 == $cerberus_db->num_rows($res)) {
   	$sql = sprintf("INSERT INTO `ticket_flags_to_agents` (`ticket_id`,`agent_id`) ".
   		"select t.`ticket_id`,u.`user_id` ".
			"from `ticket` t ".
			"inner join `user` u ON (t.`ticket_assigned_to_id`=u.`user_id`)"
   	);
   	$owner_res = $cerberus_db->query($sql);
   	
		// success
		$sql = sprintf("INSERT INTO `migration_events` (version,event,outcome) ".
			"VALUES ('%s','%s','%s')",
				"3.0.0",
				"owner_to_flag",
				"done"
		);
		$cerberus_db->query($sql);
   }
}

function migrate_ticket_statuses() {
   global $cerberus_db;

   // [JAS]: This should move into some kind of migration event API.
   $sql = sprintf("SELECT me.event, me.outcome FROM `migration_events` me WHERE me.version = %s AND me.event = %s",
   	$cerberus_db->escape("3.0.0"),
   	$cerberus_db->escape("status_to_tag")
   );
   $res = $cerberus_db->query($sql);
   if(0 == $cerberus_db->num_rows($res)) {
   	$statuses = new cer_TicketStatusesDeprecated();
   	$status_list = $statuses->getTicketStatuses();
   	$custom_list = $status_list;
   	
   	unset($custom_list['new']);
   	unset($custom_list['awaiting-reply']);
   	unset($custom_list['customer-reply']);
   	unset($custom_list['bounced']);
   	unset($custom_list['resolved']);
   	unset($custom_list['dead']);

   	include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");
   	include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationRouting.class.php");
   	$tags = new CerWorkstationTags();
   	$routing = new CerWorkstationRouting();
   	$status_tag_id = $tags->addTag("Status", 0);
   	
   	// [JAS]: Tag old tickets
   	if(is_array($custom_list))
   	foreach($custom_list as $status) {
   		$status_id = $tags->addTag($status,$status_tag_id);
   		$sql = sprintf("INSERT INTO `workstation_tags_to_tickets` (tag_id, ticket_id) " .
   			"SELECT '%d', t.ticket_id FROM `ticket` t WHERE t.`ticket_status` = %s",
   				$status_id,
   				$cerberus_db->escape($status)
   		);
   		$cerberus_db->query($sql);
   	}
   	
   	// [JAS]: Convert to base statuses
   	$sql = sprintf("UPDATE `ticket` SET `is_closed` = 1 WHERE `ticket_status` IN ('resolved','dead')");
   	$cerberus_db->query($sql);
   	
   	// [JAS]: Convert to base statuses
   	$sql = sprintf("UPDATE `ticket` SET `is_deleted` = 1 WHERE `ticket_status` IN ('dead')");
   	$cerberus_db->query($sql);
   	
   	// [JAS]: Convert to base statuses
   	$sql = sprintf("UPDATE `ticket` SET `is_waiting_on_customer` = 1 WHERE `ticket_status` = 'awaiting-reply'");
   	$cerberus_db->query($sql);
   	
   	// success
   	$sql = sprintf("INSERT INTO `migration_events` (version,event,outcome) ".
   		"VALUES ('%s','%s','%s')",
   			"3.0.0",
   			"status_to_tag",
   			"done"
   	);
   	$cerberus_db->query($sql);
   }
}

function init_table_ticket_spotlights_to_agents() {
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("ticket_spotlights_to_agents",false);
   
	$TABLE_DEF->create_sql = "CREATE TABLE `ticket_spotlights_to_agents` ( ".
		"`ticket_id` bigint(20) unsigned NOT NULL default '0', ".
		"`agent_id` bigint(20) unsigned NOT NULL default '0', ".
		"KEY `ticket_id` (`ticket_id`), ".
		"KEY `agent_id` (`agent_id`) ".
		") TYPE=MyISAM";
	
   $TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","bigint(20) unsigned","","MUL","0","");
	
   $TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1","ticket_id");
   $TABLE_DEF->indexes["agent_id"] = new CER_DB_INDEX("agent_id","1","agent_id");

   table($TABLE_DEF);
}

function init_table_parser_fail_headers() {
	global $cerberus_db;
	
   $TABLE_DEF = new CER_DB_TABLE("parser_fail_headers",false);
   
	$TABLE_DEF->create_sql = "CREATE TABLE `parser_fail_headers` ( ".
	  "`id` bigint(20) unsigned NOT NULL auto_increment, ".
	  "`header_to` char(64) NOT NULL default '', ".
	  "`header_from` char(64) NOT NULL default '', ".
	  "`header_subject` char(64) NOT NULL default '', ".
	  "`date_created` bigint(20) unsigned NOT NULL default '0', ".
	  "`error_msg` char(128) NOT NULL default '', ".
	  "PRIMARY KEY  (`id`) ".
	") TYPE=MyISAM";
	
   $TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["header_to"] = new CER_DB_FIELD("header_to","char(64)","","","","");
   $TABLE_DEF->fields["header_from"] = new CER_DB_FIELD("header_from","char(64)","","","","");
   $TABLE_DEF->fields["header_to"] = new CER_DB_FIELD("header_to","char(64)","","","","");
   $TABLE_DEF->fields["header_subject"] = new CER_DB_FIELD("header_subject","char(64)","","","","");
   $TABLE_DEF->fields["date_created"] = new CER_DB_FIELD("date_created","bigint(20) unsigned","","","0","");
   $TABLE_DEF->fields["error_msg"] = new CER_DB_FIELD("error_msg","char(128)","","","","");
	
   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");

   table($TABLE_DEF);
}

function init_table_pop3_accounts() {
   global $cerberus_db;

   $TABLE_DEF = new CER_DB_TABLE("pop3_accounts",false);

   $TABLE_DEF->create_sql = "CREATE TABLE `pop3_accounts` ( ".
		"`id` INT UNSIGNED NOT NULL AUTO_INCREMENT , ".
		"`name` VARCHAR( 64 ) DEFAULT '' NOT NULL , ".
		"`host` VARCHAR( 64 ) DEFAULT '' NOT NULL , ".
		"`login` VARCHAR( 32 ) DEFAULT '' NOT NULL , ".
		"`pass` VARCHAR( 32 )  DEFAULT '' NOT NULL , ".
		"`last_polled` bigint(20) DEFAULT '0',".
		"`disabled` TINYINT DEFAULT '0' NOT NULL , ".
		"PRIMARY KEY ( `id` ) , ".
		"INDEX ( `disabled` ) ".
		") TYPE=MyISAM";

   $TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","int(11) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["name"] = new CER_DB_FIELD("name","varchar(64)","","","","");
   $TABLE_DEF->fields["host"] = new CER_DB_FIELD("host","varchar(64)","","","","");
   $TABLE_DEF->fields["login"] = new CER_DB_FIELD("login","varchar(32)","","","","");
   $TABLE_DEF->fields["pass"] = new CER_DB_FIELD("pass","varchar(32)","","","","");
   $TABLE_DEF->fields["last_polled"] = new CER_DB_FIELD("last_polled","bigint(20)","","","0","");
   $TABLE_DEF->fields["disabled"] = new CER_DB_FIELD("disabled","tinyint(4)","","MUL","0","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
   $TABLE_DEF->indexes["disabled"] = new CER_DB_INDEX("disabled","1","disabled");

   table($TABLE_DEF);
}

function init_table_next_step() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("next_step",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `next_step` ( ".
		"  `id` bigint(20) unsigned NOT NULL auto_increment, ".
		"  `ticket_id` bigint(20) unsigned NOT NULL default '0', ".
		"  `date_created` bigint(20) unsigned NOT NULL default '0', ".
		"  `created_by_agent_id` bigint(20) unsigned NOT NULL default '0', ".
		"  `note` char(255) NOT NULL default '', ".
		"  PRIMARY KEY  (`id`), ".
		"  KEY `ticket_id` (`ticket_id`) ".
		") TYPE=MyISAM";
	
   $TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","MUL","0","");
   $TABLE_DEF->fields["date_created"] = new CER_DB_FIELD("date_created","bigint(20) unsigned","","","0","");
   $TABLE_DEF->fields["created_by_agent_id"] = new CER_DB_FIELD("created_by_agent_id","bigint(20) unsigned","","","0","");
   $TABLE_DEF->fields["note"] = new CER_DB_FIELD("note","char(255)","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
   $TABLE_DEF->indexes["ticket_id"] = new CER_DB_INDEX("ticket_id","1","ticket_id");

   table($TABLE_DEF);
}

function init_table_migration_events() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("migration_events",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `migration_events` ( ".
		"`version` CHAR( 16 ) NOT NULL , ".
		"`event` CHAR( 32 ) NOT NULL , ".
		"`outcome` CHAR( 32 ) NOT NULL , ".
		"PRIMARY KEY ( `version` , `event` ) ".
		") TYPE = MYISAM;";
	
   $TABLE_DEF->fields["version"] = new CER_DB_FIELD("version","char(16)","","PRI","","");
   $TABLE_DEF->fields["event"] = new CER_DB_FIELD("event","char(32)","","PRI","","");
   $TABLE_DEF->fields["outcome"] = new CER_DB_FIELD("outcome","char(32)","","","","");

   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("version","event"));

   table($TABLE_DEF);
}


function init_table_ticket_flags_to_agents() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("ticket_flags_to_agents", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `ticket_flags_to_agents` ( ".
		"`ticket_id` bigint(20) unsigned NOT NULL default '0', ".
		"`agent_id` bigint(20) unsigned NOT NULL default '0', ".
		"PRIMARY KEY  (`ticket_id`,`agent_id`) ".
		") TYPE=MyISAM ";
		
   $TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","bigint(20) unsigned","","PRI","0","");
	
   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("ticket_id","agent_id"));
		
	table($TABLE_DEF);
}

function init_table_ticket_due_dates() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("ticket_due_dates", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `ticket_due_dates` ( ".
		"`ticket_id` bigint(20) unsigned NOT NULL default '0', ".
		"`due_date` bigint(20) unsigned NOT NULL default '0', ".
		"`override` tinyint(3) unsigned NOT NULL default '0', ".
		"PRIMARY KEY (`ticket_id`) ".
		") TYPE=MyISAM ";
		
   $TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["due_date"] = new CER_DB_FIELD("due_date","bigint(20) unsigned","","","0","");
   $TABLE_DEF->fields["override"] = new CER_DB_FIELD("override","tinyint(3) unsigned","","","0","");
	
   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","ticket_id");
		
	table($TABLE_DEF);
}

function init_table_saved_search() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("saved_search", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `saved_search` ( ".
		"`search_id` bigint(20) unsigned NOT NULL auto_increment, ".
		"`title` varchar(128) NOT NULL default '', ".
		"`created_by_uid` bigint(20) unsigned NOT NULL default '0', ".
		"`params` TEXT NOT NULL default '', ".
		"PRIMARY KEY (`search_id`) ".
		") TYPE=MyISAM;";
		
	$TABLE_DEF->fields["search_id"] = new CER_DB_FIELD("search_id","bigint(20) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["title"] = new CER_DB_FIELD("title","varchar(128)","","","","");
	$TABLE_DEF->fields["created_by_uid"] = new CER_DB_FIELD("created_by_uid","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["params"] = new CER_DB_FIELD("params","text","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","search_id");
		
	table($TABLE_DEF);
}

function init_table_sla_to_team() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("sla_to_team", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `sla_to_team` ( ".
		"`sla_id` bigint(20) unsigned NOT NULL default '0', ".
		"`team_id` bigint(20) unsigned NOT NULL default '0', ".
		"`schedule_id` bigint(20) unsigned NOT NULL default '0', ".
		"`response_time` int(11) NOT NULL default '0', ".
		"UNIQUE KEY `sla_id` (`sla_id`,`team_id`) ".
		") TYPE=MyISAM;";
		
	$TABLE_DEF->fields["sla_id"] = new CER_DB_FIELD("sla_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","bigint(20) unsigned","","PRI","0","");
	$TABLE_DEF->fields["schedule_id"] = new CER_DB_FIELD("schedule_id","bigint(20) unsigned","","","0","");
	$TABLE_DEF->fields["response_time"] = new CER_DB_FIELD("response_time","int(11)","","","0","");
	
	$TABLE_DEF->indexes["sla_id"] = new CER_DB_INDEX("sla_id","0",array("sla_id","team_id"));
		
	table($TABLE_DEF);
}

function init_table_workstation() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation` (".
		"`key_xml` BLOB NOT NULL ,".
		"`licensed_to` CHAR( 128 ) NOT NULL ,".
		"`max_users` INT UNSIGNED default '0' NOT NULL ,".
		"`expires` DATE DEFAULT '0000-00-00' NOT NULL ".
		") TYPE = MYISAM ;";
		
   $TABLE_DEF->fields["key_xml"] = new CER_DB_FIELD("key_xml","blob","","","","");
   $TABLE_DEF->fields["licensed_to"] = new CER_DB_FIELD("licensed_to","char(128)","","","","");
   $TABLE_DEF->fields["max_users"] = new CER_DB_FIELD("max_users","int(11) unsigned","","","0","");
   $TABLE_DEF->fields["expires"] = new CER_DB_FIELD("expires","date","","","0000-00-00","");
	
	table($TABLE_DEF);
}

function init_table_workstation_tags() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_tags", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_tags` ( ".
		"`tag_id` bigint(20) unsigned NOT NULL auto_increment, ".
		"`tag_name` char(32) NOT NULL default '', ".
		"PRIMARY KEY  (`tag_id`) ".
		") TYPE=MyISAM;";
		
   $TABLE_DEF->fields["tag_id"] = new CER_DB_FIELD("tag_id","bigint(20) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["tag_name"] = new CER_DB_FIELD("tag_name","char(32)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","tag_id");
   
	table($TABLE_DEF);
}

function init_table_workstation_tag_teams() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_tag_teams", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_tag_teams` ( ".
		"`tag_id` bigint(20) unsigned NOT NULL default '0', ".
		"`team_id` bigint(20) unsigned NOT NULL default '0', ".
		"PRIMARY KEY  (`tag_id`, `team_id`) ".
		") TYPE=MyISAM;";
		
   $TABLE_DEF->fields["tag_id"] = new CER_DB_FIELD("tag_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","bigint(20) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("tag_id","team_id"));
   
	table($TABLE_DEF);
}

function init_table_workstation_tags_to_kb() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_tags_to_kb", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_tags_to_kb` ( ".
		"`tag_id` bigint(20) unsigned NOT NULL default '0', ".
		"`kb_id` bigint(20) unsigned NOT NULL default '0', ".
		"PRIMARY KEY ( `tag_id` , `kb_id` ) ".
		") TYPE = MYISAM ;";
		
   $TABLE_DEF->fields["tag_id"] = new CER_DB_FIELD("tag_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","bigint(20) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("tag_id","kb_id"));
   
	table($TABLE_DEF);
}

function init_table_workstation_tags_to_tickets() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_tags_to_tickets", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_tags_to_tickets` ( ".
		"`tag_id` bigint(20) unsigned NOT NULL default '0', ".
		"`ticket_id` bigint(20) unsigned NOT NULL default '0', ".
		"PRIMARY KEY  (`tag_id`, `ticket_id`) ".
		") TYPE=MyISAM;";
		
   $TABLE_DEF->fields["tag_id"] = new CER_DB_FIELD("tag_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("tag_id","ticket_id"));
   
	table($TABLE_DEF);
}

function init_table_workstation_tags_to_terms() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_tags_to_terms", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_tags_to_terms` (".
		"`tag_id` bigint(20) unsigned DEFAULT '0' NOT NULL, ".
		"`term` char(32) NOT NULL, ".
		"PRIMARY KEY (`tag_id`,`term`) ".
		") TYPE=MYISAM;";
		
   $TABLE_DEF->fields["tag_id"] = new CER_DB_FIELD("tag_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["term"] = new CER_DB_FIELD("term","char(32)","","PRI","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("tag_id","term"));
   
	table($TABLE_DEF);
}

function init_table_workstation_settings() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_settings", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_settings` ( ".
		"`ip_security_disabled` TINYINT UNSIGNED DEFAULT '0' NOT NULL ".
		") TYPE = MYISAM";
		
   $TABLE_DEF->fields["ip_security_disabled"] = new CER_DB_FIELD("ip_security_disabled","tinyint(4) unsigned","","","0","");
	
	table($TABLE_DEF);
}

function init_table_workstation_valid_ips() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_valid_ips", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_valid_ips` ( ".
		"`ip_mask` CHAR( 15 ) NOT NULL , ".
		"PRIMARY KEY ( `ip_mask` ) ".
		") TYPE = MYISAM ;";
		
   $TABLE_DEF->fields["ip_mask"] = new CER_DB_FIELD("ip_mask","char(15)","","PRI","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","ip_mask");

	table($TABLE_DEF);
}

function init_table_workstation_routing() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_routing", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_routing` ( ".
		"`team_id` bigint(20) unsigned NOT NULL default '0', ".
		"`queue_id` bigint(20) unsigned NOT NULL default '0', ".
		"PRIMARY KEY  (`team_id`,`queue_id`) ".
		") TYPE=MyISAM;";
		
   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","bigint(20) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("team_id","queue_id"));

	table($TABLE_DEF);
}

function init_table_workstation_routing_tags() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_routing_tags", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_routing_tags` ( ".
		"`tag_id` bigint(20) unsigned NOT NULL default '0', ".
		"`queue_id` bigint(20) unsigned NOT NULL default '0', ".
		"PRIMARY KEY  (`tag_id`,`queue_id`) ".
		") TYPE=MyISAM;";
		
   $TABLE_DEF->fields["tag_id"] = new CER_DB_FIELD("tag_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","bigint(20) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("tag_id","queue_id"));

	table($TABLE_DEF);
}

function init_table_workstation_routing_agents() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_routing_agents", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_routing_agents` ( ".
		"`agent_id` bigint(20) unsigned NOT NULL default '0', ".
		"`queue_id` bigint(20) unsigned NOT NULL default '0', ".
		"PRIMARY KEY  (`agent_id`,`queue_id`) ".
		") TYPE=MyISAM;";
		
   $TABLE_DEF->fields["agent_id"] = new CER_DB_FIELD("agent_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","bigint(20) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("agent_id","queue_id"));

	table($TABLE_DEF);
}

function init_table_workstation_routing_to_tickets() {
	global $cerberus_db;

	$TABLE_DEF = new CER_DB_TABLE("workstation_routing_to_tickets", false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_routing_to_tickets` ( ".
		"`ticket_id` BIGINT UNSIGNED NOT NULL DEFAULT '0', ".
		"`team_id` BIGINT UNSIGNED NOT NULL DEFAULT '0', ".
		"PRIMARY KEY ( `ticket_id` , `team_id`) ".
		");";
		
   $TABLE_DEF->fields["ticket_id"] = new CER_DB_FIELD("ticket_id","bigint(20) unsigned","","PRI","0","");
   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","bigint(20) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("ticket_id", "team_id"));

	table($TABLE_DEF);
}

function update_table_queue() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["queue_ws_mode"])) {
		$TABLE_DEF->drop_field("queue_ws_mode");
	}
	if(isset($TABLE_DEF->fields["queue_addresses_inherit_qid"])) {
		$TABLE_DEF->drop_field("queue_addresses_inherit_qid");
	}
}

function update_table_kb_ratings() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("kb_ratings",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["rating_id"])) {
		$TABLE_DEF->drop_field("rating_id");
	}
}

function update_table_next_step() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("next_step",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["created_by_agent_id"])) {
		$TABLE_DEF->add_field("created_by_agent_id","bigint(20) unsigned NOT NULL default '0'");
	}
	if(isset($TABLE_DEF->fields["assignment_type"])) {
		$TABLE_DEF->drop_field("assignment_type");
	}
	if(isset($TABLE_DEF->fields["assignment_to"])) {
		$TABLE_DEF->drop_field("assignment_to");
	}
	if(isset($TABLE_DEF->fields["priority"])) {
		$TABLE_DEF->drop_field("priority");
	}
	if(isset($TABLE_DEF->fields["assignment_team"])) {
		$TABLE_DEF->drop_field("assignment_team");
	}
	if(isset($TABLE_DEF->fields["defer_to_customer"])) {
		$TABLE_DEF->drop_field("defer_to_customer");
	}
	if(isset($TABLE_DEF->fields["defer_to_date"])) {
		$TABLE_DEF->drop_field("defer_to_date");
	}
	if(isset($TABLE_DEF->fields["date_completed"])) {
		$TABLE_DEF->drop_field("date_completed");
	}
	if(isset($TABLE_DEF->fields["note"]) && strtolower($TABLE_DEF->fields["note"]->field_type) != "text") {
		$sql = "ALTER TABLE `next_step` CHANGE COLUMN `note` `note` TEXT DEFAULT '' NOT NULL";
		$TABLE_DEF->run_sql($sql, "Changing `next_step`.`note` to TEXT");
	}
	
}

function update_table_parser_fail_headers() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("parser_fail_headers",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["message_source_filename"])) {
		$TABLE_DEF->add_field("message_source_filename","char(20) NOT NULL");
	}

	if(isset($TABLE_DEF->fields["header_subject"])) {
		$sql = "ALTER TABLE `parser_fail_headers` CHANGE COLUMN `header_subject` `header_subject` CHAR(64) DEFAULT '' NOT NULL";
		$TABLE_DEF->run_sql($sql, "Changing `parser_fail_headers`.`header_subject` to char(64)");
	}

	if(!isset($TABLE_DEF->fields["message_size"])) {
		$TABLE_DEF->add_field("message_size","int(11) unsigned default '0' NOT NULL");
	}
	
}

function update_table_public_gui_profiles() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_profiles",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["pub_mod_kb_tag_root"])) {
		$TABLE_DEF->add_field("pub_mod_kb_tag_root","bigint(20) unsigned default '0' NOT NULL");
	}
	
	if(isset($TABLE_DEF->fields["pub_queues"])) {
		$TABLE_DEF->drop_field("pub_queues");
	}
	
	if(!isset($TABLE_DEF->fields["pub_teams"])) {
		$TABLE_DEF->add_field("pub_teams","text NOT NULL");
	}
	
}

function update_table_pop3_accounts() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("pop3_accounts",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["max_messages"])) {
		$TABLE_DEF->add_field("max_messages","tinyint(4) unsigned DEFAULT '0' NOT NULL");
	}
	if(!isset($TABLE_DEF->fields["delete"])) {
		$TABLE_DEF->add_field("delete","tinyint(4) unsigned DEFAULT '0' NOT NULL");
	}
	if(!isset($TABLE_DEF->fields["lock_time"])) {
		$TABLE_DEF->add_field("lock_time","bigint(20) unsigned DEFAULT '0' NOT NULL");
	}
	if(!isset($TABLE_DEF->fields["max_size"])) {
		$TABLE_DEF->add_field("max_size","bigint(20) unsigned DEFAULT '0' NOT NULL");
	}
}

function update_table_saved_reports() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("saved_reports",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["report_category"])) {
		$TABLE_DEF->drop_field("report_category");
	}
}

function update_table_team() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("team",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["default_response_time"])) {
		$TABLE_DEF->add_field("default_response_time","tinyint(3) unsigned DEFAULT '0' NOT NULL");
	}
	if(!isset($TABLE_DEF->fields["default_schedule"])) {
		$TABLE_DEF->add_field("default_schedule","bigint(20) unsigned DEFAULT '0' NOT NULL");
	}
}

function update_table_team_members() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("team_members",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["is_watcher"])) {
		$TABLE_DEF->add_field("is_watcher","tinyint(3) unsigned DEFAULT '0' NOT NULL");
	}
}

function update_table_ticket() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["is_closed"])) {
		$TABLE_DEF->add_field("is_closed","tinyint(3) unsigned DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["is_deleted"])) {
		$TABLE_DEF->add_field("is_deleted","tinyint(3) unsigned DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["is_waiting_on_customer"])) {
		$TABLE_DEF->add_field("is_waiting_on_customer","tinyint(3) unsigned DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["opened_by_address_id"])) {
		$TABLE_DEF->add_field("opened_by_address_id","bigint(20) unsigned DEFAULT '0' NOT NULL");
	}
	
	if(isset($TABLE_DEF->fields["ticket_category_id"])) {
		$TABLE_DEF->drop_field("ticket_category_id");
	}
	
	if(!isset($TABLE_DEF->fields["num_flags"])) {
		$TABLE_DEF->add_field("num_flags","tinyint(3) unsigned DEFAULT '0' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["num_teams"])) {
		$TABLE_DEF->add_field("num_teams","tinyint(3) unsigned DEFAULT '0' NOT NULL");
	}
	
	if(!$TABLE_DEF->index_exists("is_closed")) {
		$TABLE_DEF->add_index("is_closed",1,array("is_closed"));
	}
	
	if(!$TABLE_DEF->index_exists("opened_by_address_id")) {
		$TABLE_DEF->add_index("opened_by_address_id",1,array("opened_by_address_id"));
	}
	
	if(!$TABLE_DEF->index_exists("is_deleted")) {
		$TABLE_DEF->add_index("is_deleted",1,array("is_deleted"));
	}
	
	if(!$TABLE_DEF->index_exists("is_waiting_on_customer")) {
		$TABLE_DEF->add_index("is_waiting_on_customer",1,array("is_waiting_on_customer"));
	}
	
	if(!$TABLE_DEF->index_exists("num_flags")) {
		$TABLE_DEF->add_index("num_flags",1,array("num_flags"));
	}
	
	if(!$TABLE_DEF->index_exists("num_teams")) {
		$TABLE_DEF->add_index("num_teams",1,array("num_teams"));
	}
	
	count_team_stats();
}

function count_team_stats() {
	global $cerberus_db;

	$sql = "UPDATE ticket SET `num_teams` = 0, `num_flags` = 0";
	$cerberus_db->query($sql);
	
	$sql = sprintf("SELECT count(team_id) as hits, ticket_id FROM `workstation_routing_to_tickets` wrt GROUP BY ticket_id HAVING hits > 0");
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE `ticket` SET `num_teams` = %d WHERE `ticket_id` = %d",
				$row['hits'],
				$row['ticket_id']
			);
			$cerberus_db->query($sql);
		}
	}
	
	$sql = sprintf("SELECT count(agent_id) as hits, ticket_id FROM `ticket_flags_to_agents` GROUP BY ticket_id HAVING hits > 0");
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res)) {
		while($row = $cerberus_db->fetch_row($res)) {
			$sql = sprintf("UPDATE `ticket` SET `num_flags` = %d WHERE `ticket_id` = %d",
				$row['hits'],
				$row['ticket_id']
			);
			$cerberus_db->query($sql);
		}
	}
	
}

function update_table_ticket_flags_to_agents() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_flags_to_agents",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["flag_id"])) {
		$TABLE_DEF->drop_field("flag_id");
		
		$sql = "ALTER TABLE `ticket_flags_to_agents` ADD PRIMARY KEY (`ticket_id`,`agent_id`)";
		$TABLE_DEF->run_sql($sql, "Adding new primary key to `ticket_flags_to_agents`");
	}
	
	if($TABLE_DEF->index_exists("ticket_id")) {
		$TABLE_DEF->run_sql("ALTER TABLE `ticket_flags_to_agents` DROP INDEX `ticket_id`");
	}
	if($TABLE_DEF->index_exists("agent_id")) {
		$TABLE_DEF->run_sql("ALTER TABLE `ticket_flags_to_agents` DROP INDEX `agent_id`");
	}
}

function update_table_ticket_spotlights_to_agents() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_spotlights_to_agents",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["spotlight_id"])) {
		$TABLE_DEF->drop_field("spotlight_id");
	}
	
	if($TABLE_DEF->index_exists("ticket_id")) {
		$TABLE_DEF->run_sql("ALTER TABLE `ticket_spotlights_to_agents` DROP INDEX `ticket_id`");
	}
	if($TABLE_DEF->index_exists("agent_id")) {
		$TABLE_DEF->run_sql("ALTER TABLE `ticket_spotlights_to_agents` DROP INDEX `agent_id`");
	}
	if(!$TABLE_DEF->index_exists("primary")) {
		$sql = "SELECT ticket_id,agent_id,count(*) as hits FROM `ticket_spotlights_to_agents` GROUP BY ticket_id,agent_id HAVING hits > 1";
		$res = $cerberus_db->query($sql);
		if($cerberus_db->num_rows($res)) {
			while($row = $cerberus_db->fetch_row($res)) {
				$ticket_id = intval($row['ticket_id']);
				$agent_id = intval($row['agent_id']);
				$hits = intval($row['hits']);
				$sql = sprintf("delete from `ticket_spotlights_to_agents` WHERE ticket_id = %d AND agent_id = %d LIMIT %d",
					$ticket_id,
					$agent_id,
					$hits-1
				);
				$cerberus_db->query($sql);
			}
		}
		
		$TABLE_DEF->run_sql("ALTER TABLE `ticket_spotlights_to_agents` ADD PRIMARY KEY (`ticket_id`,`agent_id`)");
	}
}

function update_table_user() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("user",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["user_display_name"])) {
		$TABLE_DEF->add_field("user_display_name","char(32) DEFAULT '' NOT NULL");
	}

	if(isset($TABLE_DEF->fields["user_group_id"])) {
		$TABLE_DEF->drop_field("user_group_id");
	}
	
	if(!isset($TABLE_DEF->fields["user_ws_enabled"])) {
		$TABLE_DEF->add_field("user_ws_enabled","tinyint(3) DEFAULT '0' NOT NULL");
	}
}

function update_table_ticket_views() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_views",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["view_only_assigned"])) {
		$TABLE_DEF->drop_field("view_only_assigned");
	}
	if(isset($TABLE_DEF->fields["view_queues"])) {
		$TABLE_DEF->drop_field("view_queues");
	}
	if(isset($TABLE_DEF->fields["view_hide_statuses"])) {
		$TABLE_DEF->drop_field("view_hide_statuses");
	}
	if(isset($TABLE_DEF->fields["view_private"])) {
		$TABLE_DEF->drop_field("view_private");
	}
	if(!isset($TABLE_DEF->fields["view_params"])) {
		$TABLE_DEF->add_field("view_params","text DEFAULT ''");
	}
	if(!isset($TABLE_DEF->fields["dashboard_id"])) {
		$TABLE_DEF->add_field("dashboard_id","bigint(20) unsigned DEFAULT '0' NOT NULL");
	}
	if(!isset($TABLE_DEF->fields["view_order"])) {
		$TABLE_DEF->add_field("view_order","tinyint(3) unsigned DEFAULT '0' NOT NULL");
	}
}

function update_table_user_prefs() {
	$TABLE_DEF = new CER_DB_TABLE("user_prefs",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["assign_queues"])) {
		$TABLE_DEF->drop_field("assign_queues");
	}
}

function update_table_workstation() {
	$TABLE_DEF = new CER_DB_TABLE("workstation",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["license_id"])) {
		$TABLE_DEF->add_field("license_id","char(32) DEFAULT '' NOT NULL");
	}
	
	if(!isset($TABLE_DEF->fields["enable_jparser"])) {
		$TABLE_DEF->add_field("enable_jparser","tinyint(3) DEFAULT '0' NOT NULL");
	}
}

function update_table_workstation_routing() {
	$TABLE_DEF = new CER_DB_TABLE("workstation_routing",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["dest_type"])) {
		$TABLE_DEF->drop_field("dest_type");
	}
	
	if(isset($TABLE_DEF->fields["dest_id"])) {
		$TABLE_DEF->run_sql("ALTER TABLE `workstation_routing` CHANGE `dest_id` `team_id` BIGINT( 20 ) UNSIGNED DEFAULT '0' NOT NULL ",
			"Renaming `workstation_routing`.`dest_id` to `workstation_routing`.`team_id`");
	}
}

function update_table_workstation_routing_to_tickets() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("workstation_routing_to_tickets",true);
	$TABLE_DEF->check(true);

	if(isset($TABLE_DEF->fields["dest_type"])) {
		$TABLE_DEF->drop_field("dest_type");
	}
	
	if(isset($TABLE_DEF->fields["dest_id"])) {
		$TABLE_DEF->run_sql("ALTER TABLE `workstation_routing_to_tickets` CHANGE `dest_id` `team_id` BIGINT( 20 ) UNSIGNED DEFAULT '0' NOT NULL ",
			"Renaming `workstation_routing`.`dest_id` to `workstation_routing`.`team_id`");
	}
}

function update_table_workstation_tags() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("workstation_tags",true);
	$TABLE_DEF->check(true);

	if(!isset($TABLE_DEF->fields["parent_tag_id"])) {
		$TABLE_DEF->add_field("parent_tag_id","bigint(20) unsigned NOT NULL default '0'");
	}
}

function drop_table_department() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("department",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS department","Dropping table department");
	}
}

function drop_table_department_teams() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("department_teams",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS department_teams","Dropping table department_teams");
	}
}

function drop_table_dispatcher_suggestions() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("dispatcher_suggestions",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS dispatcher_suggestions","Dropping table dispatcher_suggestions");
	}
}

function drop_table_queue_access() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("queue_access",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS queue_access","Dropping table queue_access");
	}
}

function drop_table_queue_group_access() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("queue_group_access",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS queue_group_access","Dropping table queue_group_access");
	}
}

function archive_table_knowledgebase() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("ALTER TABLE `knowledgebase` RENAME `knowledgebase_old` ;","Archiving table knowledgebase");
	}
}

function archive_table_knowledgebase_categories() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_categories",true);
	
	if($TABLE_DEF->check(false)) {
		
		// [JAS]: Hash object for migration of categories->tags
		$categories = array();
		
		$sql = sprintf("INSERT INTO `workstation_tags` (`tag_name`, `parent_tag_id`) ".
			"VALUES (%s,%d) ",
				$cerberus_db->escape("Knowledgebase"),
				0
		);
		$res = $cerberus_db->query($sql);
		$kb_tag_id = $cerberus_db->insert_id();
		
		// [JAS]: KB Root Tag
		$categories[0] = new stdClass();
		$categories[0]->name = "Knowledgebase";
		$categories[0]->old_id = 0;
		$categories[0]->new_id = $kb_tag_id;
		$categories[0]->old_parent_id = 0;
		
		// [JAS]: Pull up the outgoing categories, we'll make them tags
		$sql = "SELECT `kb_category_id`, `kb_category_name`, `kb_category_parent_id` ".
			"FROM `knowledgebase_categories` ";
		$res = $cerberus_db->query($sql);
		
		if($cerberus_db->num_rows($res)) {
			while($row = $cerberus_db->fetch_row($res)) {
				$data = new stdClass();
				$data->name = stripslashes($row['kb_category_name']);
				$data->old_id = $row['kb_category_id'];
				$data->old_parent_id = $row['kb_category_parent_id'];
				
				// [JAS]: Create a tag per old category, and save the new IDs
				$sql = sprintf("INSERT INTO `workstation_tags` (`tag_name`, `parent_tag_id`) ".
					"VALUES (%s, %d)",
						$cerberus_db->escape($data->name),
						0
				);
				$newTagRes = $cerberus_db->query($sql);
				$newTagId = $cerberus_db->insert_id();
				
				$data->new_id = $newTagId;
				
				$categories[$row['kb_category_id']] = $data;
			}
			
			// [JAS]: Reassign inheritence
			foreach($categories as $catId => $category) {
				if($catId == 0)
					continue;
					
				$parent = $categories[$category->old_parent_id];
				$sql = sprintf("UPDATE `workstation_tags` SET `parent_tag_id` = %d WHERE `tag_id` = %d",
					$parent->new_id,
					$category->new_id
				);
				$cerberus_db->query($sql);
			}
			
			// [JAS]: Tag articles using old category data
			$sql = "SELECT `kb_id`,`kb_category_id` FROM `knowledgebase_old`";
			$res = $cerberus_db->query($sql);
			if($cerberus_db->num_rows($res)) {
				while($row = $cerberus_db->fetch_row($res)) {
					$kbId = $row['kb_id'];
					$parentId = $row['kb_category_id'];
				
					if(empty($kbId))
						continue;
						
					// [JAS]: Apply tag
					$sql = sprintf("INSERT INTO `workstation_tags_to_kb` (`kb_id`,`tag_id`) ".
						"VALUES (%d,%d)",
							$kbId,
							$categories[$parentId]->new_id
					);
					$cerberus_db->query($sql);
				}
			}
			
		}
		
		unset($categories);
		
		$TABLE_DEF->run_sql("ALTER TABLE `knowledgebase_categories` RENAME `knowledgebase_categories_old` ;","Archiving table knowledgebase_categories");
	}
}

function archive_table_knowledgebase_problem() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_problem",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("ALTER TABLE `knowledgebase_problem` RENAME `knowledgebase_problem_old` ;","Archiving table knowledgebase_problem");
	}
}

function archive_table_knowledgebase_solution() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_solution",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("ALTER TABLE `knowledgebase_solution` RENAME `knowledgebase_solution_old` ;","Archiving table knowledgebase_solution");
	}
}

function archive_fields_ticket() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["ticket_status"])) {
		
	   // [JAS]: This should move into some kind of migration event API.
	   $sql = sprintf("SELECT me.event, me.outcome FROM `migration_events` me WHERE me.version = %s AND me.event = %s",
	   	$cerberus_db->escape("3.0.0"),
	   	$cerberus_db->escape("owner_to_flag")
	   );
	   $res = $cerberus_db->query($sql);
	   if(0 != $cerberus_db->num_rows($res)) {
			$TABLE_DEF->drop_field("ticket_status");
	   }
	}
	
	if(isset($TABLE_DEF->fields["ticket_assigned_to_id"])) {
	   // [JAS]: This should move into some kind of migration event API.
	   $sql = sprintf("SELECT me.event, me.outcome FROM `migration_events` me WHERE me.version = %s AND me.event = %s",
	   	$cerberus_db->escape("3.0.0"),
	   	$cerberus_db->escape("status_to_tag")
	   );
	   $res = $cerberus_db->query($sql);
	   if(0 != $cerberus_db->num_rows($res)) {
			$TABLE_DEF->drop_field("ticket_assigned_to_id");
	   }
	}
}

function rename_table_knowledgebase_comments() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_comments",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("ALTER TABLE `knowledgebase_comments` RENAME `kb_comments` ;","Renaming table knowledgebase_comments");
	}
}

function rename_table_knowledgebase_ratings() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("knowledgebase_ratings",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("ALTER TABLE `knowledgebase_ratings` RENAME `kb_ratings` ;","Renaming table knowledgebase_solution");
	}
}

function drop_table_license() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("license",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS license","Dropping table license");
	}
}

function drop_table_next_step_worker() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("next_step_worker",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS next_step_worker","Dropping table next_step_worker");
	}
}

function drop_table_ticket_categories() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("ticket_categories",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS ticket_categories","Dropping table ticket_categories");
	}
}

function drop_table_team_queues() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("team_queues",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS team_queues","Dropping table team_queues");
	}
}

function drop_table_user_access_levels() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("user_access_levels",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS user_access_levels","Dropping table user_access_levels");
	}
}

function drop_table_user_extended_info() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("user_extended_info",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS user_extended_info","Dropping table user_extended_info");
	}
}

function drop_table_workstation_bins() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("workstation_bins",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS workstation_bins","Dropping table workstation_bins");
	}
}

function drop_table_workstation_tag_teams() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("workstation_tag_teams",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS workstation_tag_teams","Dropping table workstation_tag_teams");
	}
}

function drop_table_workstation_bin_teams() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("workstation_bin_teams",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS workstation_bin_teams","Dropping table workstation_bin_teams");
	}
}

function drop_table_parser_fail_rawemail() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("parser_fail_rawemail",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS parser_fail_rawemail","Dropping table parser_fail_rawemail");
	}
}

function drop_table_trigram_to_kb() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("trigram_to_kb",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS trigram_to_kb","Dropping table trigram_to_kb");
	}
}

function drop_table_trigram() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("trigram",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS trigram","Dropping table trigram");
	}
}
function drop_table_trigram_stats() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("trigram_stats",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS trigram_stats","Dropping table trigram_stats");
	}
}
function drop_table_trigram_to_ticket() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("trigram_to_ticket",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS trigram_to_ticket","Dropping table trigram_to_ticket");
	}
}
function drop_table_trigram_training() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("trigram_training",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS trigram_training","Dropping table trigram_training");
	}
}

/*

	$TABLE_DEF = new CER_DB_TABLE("skill_to_agent",true);
	
	if($TABLE_DEF->check(false,true)) {
		if(!$TABLE_DEF->check_index_fields('has_skill', 1, array('has_skill', 'agent_id'))) {
		   $query = "ALTER TABLE `skill_to_agent` DROP INDEX `has_skill`";
		   $cerberus_db->query($query);
		}
		
		if(!$TABLE_DEF->index_exists('has_skill')) {
		   $query = "ALTER TABLE `skill_to_agent` ADD INDEX `has_skill` ( `has_skill` , `agent_id` )";
		   $cerberus_db->query($query);
		}
	}

*/

function add_default_scheduled_tasks()
{
	global $cerberus_db;

	$sql = "SELECT `id` FROM `cron_tasks` WHERE `script` = 'pop3.php'";
	$res = $cerberus_db->query($sql);
	if(!$cerberus_db->num_rows($res)) {
		$sql = "INSERT INTO `cron_tasks` ( `id` , `enabled` , `minute` , `hour` , `day_of_month` , `day_of_week` , `title` , `script` , `next_runtime` , `last_runtime` ) ".
			"VALUES ('', '0', '*/5', '*', '*', '*', 'POP3 Mailbox Check', 'pop3.php', '0', '0');";
		$cerberus_db->query($sql);
	}
}

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('2cb22a275bb6162852906ac6cf19f1a9',NOW())"; // 3.0.0 clean
	$cerberus_db->query($sql);
}

// ***************************************************************************
// [JAS]: STANDARD CALLBACKS -- Do not edit unless you know what you're doing.
// ***************************************************************************

function table(&$TABLE_DEF)
{
	global $cerberus_db;
	
	$TBL = new CER_DB_TABLE($TABLE_DEF->table_name);
	
	// [JAS]: Only create and verify a table if it doesn't exist.
	if(!$TBL->check(false)) {
		create_table($TBL,$TABLE_DEF); // create
		verify_table($TBL,$TABLE_DEF); // verify structure
	}
	
}

function create_table(&$TBL,&$TABLE_DEF)
{
	$TBL->run_sql($TABLE_DEF->create_sql,sprintf("<b>Creating table `%s`</b>",$TABLE_DEF->table_name));
	$TBL->_read_table();
}

function verify_table(&$TBL,&$TABLE_DEF)
{
	$verify_fields = array();
	$verify_indexes = array();
	
	// build list of fields to check for
	foreach($TABLE_DEF->fields as $idx => $fld)
		$verify_fields[$fld->field_name] = DB_FIELD;
		
	// build list of indexes to check for
	foreach($TABLE_DEF->indexes as $id => $idx) 
		$verify_indexes[$idx->index_name] = DB_FIELD;

	// check for fields and indexes
	$warn = $TBL->verify_table($verify_fields,$verify_indexes);
	
	if(count($warn[0])) print_extra_fields($TBL,$warn);
	if(count($warn[1])) print_missing_fields($TBL,$warn);
	if(count($warn[2])) print_extra_indexes($TBL,$warn);
	if(count($warn[3])) print_missing_indexes($TBL,$warn);
	
	// check for field validity
	foreach($TABLE_DEF->fields as $idx => $fld)
		$TBL->verify_field($fld->field_name,$fld->field_type,$fld->field_null,$fld->field_key,$fld->field_default,$fld->field_extra);
		
	// check for index validity
	foreach($TABLE_DEF->indexes as $id => $idx) {
		$TBL->verify_index($idx->index_name, $idx->index_non_unique, $idx->index_fields, false);
	}
}

function print_extra_fields(&$tbl,&$warn_fields)
{
	foreach($warn_fields[0] as $idx => $warn)
		$tbl->output(sprintf("<font color='red'><B>WARNING:</B></font> %s.%s is not an official database field.<br>",$tbl->table_name,$idx));
}

function print_missing_fields(&$tbl,&$warn_fields)
{
	foreach($warn_fields[1] as $idx => $warn)
		$tbl->output(sprintf("<font color='red'><B>WARNING:</B></font> %s.%s is required and does not exist.<br>",$tbl->table_name,$idx));
}

function print_extra_indexes(&$tbl,&$warn_fields)
{
	foreach($warn_fields[2] as $idx => $warn)
		$tbl->output(sprintf("<font color='red'><B>WARNING:</B></font> %s.%s is not an official index.<br>",$tbl->table_name,$idx));
}

function print_missing_indexes(&$tbl,&$warn_fields)
{
	foreach($warn_fields[3] as $idx => $warn)
		$tbl->output(sprintf("<font color='red'><B>WARNING:</B></font> %s.%s is a needed index and does not exist.<br>",$tbl->table_name,$idx));
}

// Temporary class space

class cer_TicketStatusesDeprecated {
	
	function cer_TicketStatusesDeprecated() {
		include_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
		include_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");
		
		$cerberus_db = cer_Database::getInstance();
		$this->statuses = array();
		$this->permanent_statuses = array(
				"new"=>"new",
				"awaiting-reply"=>"awaiting-reply",
				"customer-reply"=>"customer-reply",
				"bounced"=>"bounced",
				"resolved"=>"resolved",
				"dead"=>"dead"
			);
		$matches = array();
		
		$sql = "DESCRIBE `ticket` `ticket_status`";
		$res = $cerberus_db->query($sql);
		
		if(!$row = $cerberus_db->grab_first_row($res))
			return;
//			$this->failStatuses();
			
		$status_raw = $row["Type"];
		
		if(empty($status_raw))
			return;
//			$this->failStatuses();
		
		preg_match("/enum\(\'(.*?)\'\)/i",$status_raw,$matches);
		
		if(empty($matches[1]))
			return;
//			return $this->failStatuses();
		
		$statuses = explode("','",$matches[1]);
		
		if(empty($statuses))
			return;
//			$this->failStatuses();
			
		foreach($statuses as $st) {
			$this->statuses[$st] = $st;
		}
	}
	
	function reload() {
		$this->cer_TicketStatuses();
	}
	
	function computeTicketStatusCounts() {

		// [JAS]: [TODO] This really should be a singleton.
		
		$cerberus_db = cer_Database::getInstance();
		$this->status_counts = array();
		
		$sql = sprintf("SELECT count(t.ticket_id) as status_count, t.ticket_status ".
				"FROM `ticket` t ".
				"GROUP BY t.ticket_status "
			);
		$res = $cerberus_db->query($sql);
		
		if($cerberus_db->num_rows($res)) {
			while($row = $cerberus_db->fetch_row($res)) {
				$this->status_counts[stripslashes($row["ticket_status"])] = $row["status_count"];
			}
		}
	}
	
	function getTicketStatuses() {
		if(!empty($this->statuses))
			return $this->statuses;
		else
			return array();
	}
	
	function getTicketStatusCounts() {
		if(!empty($this->status_counts))
			return $this->status_counts;
		else
			return array();
	}
	
	function failStatuses() {
		die("Cerberus [ERROR]: Cannot read or parse `ticket`.`ticket_status`");
	}
	
};
