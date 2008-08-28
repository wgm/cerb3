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

define("DB_SCRIPT_NAME","Cerberus Helpdesk 3.2 to 3.3 (Build 374) Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20061113");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","fe27bb093ca981ac2cc3ef245eb64f95"); // 3.2 Clean
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	init_table_kb_category();
	init_table_kb_to_category();
	init_table_mailbox_to_support_center();
	init_table_stat_os();
	
//	update_table_address();

	update_table_chat_visitors();
	update_table_chat_visitor_pages();
	update_table_public_gui_profiles();
	update_table_workstation_routing_agents();
	update_table_workstation_tags();

	drop_table_team_tag_sets();
	drop_table_workstation_tag_sets();
	drop_table_workstation_tag_sets_to_teams();

	conform_tags();
	migrate_mail_rule_tags();

	set_precursor_hashes();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

// ***************************************************************************
// `kb_to_category` table
// ***************************************************************************
function init_table_kb_to_category() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("kb_to_category",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `kb_to_category` (".
		"`kb_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',".
		"`kb_category_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',".
		"PRIMARY KEY ( `kb_id` , `kb_category_id` )".
		")";
	  
	$TABLE_DEF->fields["kb_id"] = new CER_DB_FIELD("kb_id","int(11) unsigned","","PRI","0","");
	$TABLE_DEF->fields["kb_category_id"] = new CER_DB_FIELD("kb_category_id","int(11) unsigned","","PRI","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("kb_id","kb_category_id"));

			
	table($TABLE_DEF);
}

function init_table_stat_os() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("stat_os",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `stat_os` (".
		"`os_id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,".
		"`os` CHAR( 128 ) NOT NULL ,".
		"PRIMARY KEY ( `os_id` ) ,".
		"UNIQUE ( `os` )".
		")";
	
	$TABLE_DEF->fields["os_id"] = new CER_DB_FIELD("os_id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["os"] = new CER_DB_FIELD("os","char(128)","","UNI","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("os_id"));
	$TABLE_DEF->indexes["os"] = new CER_DB_INDEX("os","0",array("os"));
	
	table($TABLE_DEF);
}

function init_table_mailbox_to_support_center() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("mailbox_to_support_center",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `mailbox_to_support_center` (".
	  "`id` int(10) unsigned NOT NULL auto_increment,".
	  "`mailbox_id` int(10) unsigned NOT NULL,".
	  "`mailbox_address_id` int(10) unsigned NOT NULL,".
	  "`mailbox_alias` char(64) NOT NULL,".
	  "`profile_id` int(10) unsigned NOT NULL,".
	  "`field_group` int(10) unsigned NOT NULL,".
	  "PRIMARY KEY  (`id`),".
	  "KEY `mailbox_id` (`mailbox_id`),".
	  "KEY `mailbox_address_id` (`mailbox_address_id`),".
	  "KEY `profile_id` (`profile_id`)".
	  ") TYPE=MyISAM;";
	
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","int(10) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["mailbox_id"] = new CER_DB_FIELD("mailbox_id","int(10) unsigned","","MUL","","");
	$TABLE_DEF->fields["mailbox_address_id"] = new CER_DB_FIELD("mailbox_address_id","int(10) unsigned","","MUL","","");
	$TABLE_DEF->fields["mailbox_alias"] = new CER_DB_FIELD("mailbox_alias","char(64)","","","","");
	$TABLE_DEF->fields["field_group"] = new CER_DB_FIELD("field_group","int(10) unsigned","","","","");
	$TABLE_DEF->fields["profile_id"] = new CER_DB_FIELD("profile_id","int(10) unsigned","","MUL","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("id"));
	$TABLE_DEF->indexes["mailbox_id"] = new CER_DB_INDEX("mailbox_id","1",array("mailbox_id"));
	$TABLE_DEF->indexes["mailbox_address_id"] = new CER_DB_INDEX("mailbox_address_id","1",array("mailbox_address_id"));
	$TABLE_DEF->indexes["profile_id"] = new CER_DB_INDEX("profile_id","1",array("profile_id"));
	
	table($TABLE_DEF);
}

// ***************************************************************************
// `kb_category` table
// ***************************************************************************
function init_table_kb_category() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("kb_category",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `kb_category` ( ".
		"`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY , ".
		"`name` VARCHAR( 64 ) NOT NULL , ".
		"`parent_id` INT(11) UNSIGNED default '0' NOT NULL ".
		") ";
	  
	$TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","int(11) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["name"] = new CER_DB_FIELD("name","varchar(64)","","","","");
	$TABLE_DEF->fields["parent_id"] = new CER_DB_FIELD("parent_id","int(11) unsigned","","","0","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
								
	table($TABLE_DEF);
}

function update_table_address() {
	$TBL = new CER_DB_TABLE("address");
	$sql = "UPDATE `address` SET `address_address` = LOWER(`address_address`)";
	$TBL->run_sql($sql,sprintf("<b>Changing `address` table to lowercase</b>"));
}

// [JAS]: Used to migrate public gui profiles, copied in case we refactor later
if(!class_exists('cer_publicguiteam')) {
	class cer_PublicGUITeam
	{
		var $team_id=0;
		var $team_name=null;
		var $team_mask=null;
		var $team_field_group=null;
		var $team_mailbox=null;
	};
}

function update_table_public_gui_profiles() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_profiles",true);
	$TABLE_DEF->check(true);
	
	if(!$TABLE_DEF->field_exists("pub_mod_kb_root")) {
		$TABLE_DEF->add_field("pub_mod_kb_root","int(10) unsigned not null default '0'");
	}
	if($TABLE_DEF->field_exists("pub_mod_kb_tag_sets")) {
		$TABLE_DEF->drop_field("pub_mod_kb_tag_sets");
	}
	if(!$TABLE_DEF->field_exists("pub_url")) {
		$TABLE_DEF->add_field("pub_url","varchar(255) not null default ''");
	}
	
	// NOTE: Make sure this migration script runs last
	if($TABLE_DEF->field_exists("pub_teams")) {
		$mailbox_hash = array();
		
		$sql = sprintf("SELECT queue_addresses_id, queue_id, CONCAT(queue_address,'@',queue_domain) as queue_address FROM queue_addresses");
		$res = $cerberus_db->query($sql);

		if(!$cerberus_db->num_rows($res))
			return;
			
		while($row = $cerberus_db->fetch_row($res)) {
			$mailbox_hash[stripslashes($row['queue_address'])] = array(intval($row['queue_id']),intval($row['queue_addresses_id']));
		}

		$sql = sprintf("SELECT profile_id, pub_teams FROM public_gui_profiles");
		$res = $cerberus_db->query($sql);
		
		if($cerberus_db->num_rows($res))
		while($row = $cerberus_db->fetch_row($res)) {
			$profile_id = intval($row['profile_id']);
			$teams = @unserialize(stripslashes($row['pub_teams']));

			if(is_array($teams)) {
				foreach($teams as $team) { /* @var $team cer_PublicGUITeam */
					$sql = sprintf("INSERT IGNORE INTO mailbox_to_support_center ".
						"(mailbox_id,mailbox_address_id,mailbox_alias,field_group,profile_id) ".
						"VALUES (%d,%d,%s,%d,%d)",
							$mailbox_hash[$team->team_mailbox][0],
							$mailbox_hash[$team->team_mailbox][1],
							$cerberus_db->escape($team->team_mask),
							$team->team_field_group,
							$profile_id
					);
					$cerberus_db->query($sql);
				}
			}
		}
		
		$TABLE_DEF->drop_field("pub_teams");
	}
}

function update_table_chat_visitors() {
	$TABLE_DEF = new CER_DB_TABLE("chat_visitors",true);
	$TABLE_DEF->check(true);
	
	if(!$TABLE_DEF->field_exists("visitor_os_id")) {
		$TABLE_DEF->add_field("visitor_os_id","int(10) unsigned not null default '0'");
	}
	if(!$TABLE_DEF->field_exists("last_page_id")) {
		$TABLE_DEF->add_field("last_page_id","bigint(20) unsigned not null default '0'");
	}
	if($TABLE_DEF->field_exists("visitor_question")) {
		$TABLE_DEF->drop_field("visitor_question");
	}
	if($TABLE_DEF->field_exists("visitor_hash")) {
		$TABLE_DEF->drop_field("visitor_hash");
	}
	if($TABLE_DEF->field_exists("visitor_ip")) {
		$TABLE_DEF->run_sql("ALTER TABLE `chat_visitors` CHANGE COLUMN `visitor_ip` `visitor_ip` INT(11) NOT NULL");
	}
}

function update_table_chat_visitor_pages() {
	$TABLE_DEF = new CER_DB_TABLE("chat_visitor_pages",true);
	$TABLE_DEF->check(true);
	
	if($TABLE_DEF->field_exists("page_name")) {
		$TABLE_DEF->drop_field("page_name");
	}
}

function update_table_workstation_routing_agents() {
	$TABLE_DEF = new CER_DB_TABLE("workstation_routing_agents",true);
	$TABLE_DEF->check(true);
	
	if(!$TABLE_DEF->field_exists("is_flagged")) {
		$TABLE_DEF->add_field("is_flagged","tinyint(3) unsigned not null default '0'");
	}
	
	if($TABLE_DEF->index_exists("primary")) {
		$TABLE_DEF->run_sql("ALTER TABLE `workstation_routing_agents` DROP PRIMARY KEY");
	}
	
	$sql = "ALTER TABLE `workstation_routing_agents` ADD PRIMARY KEY ( `agent_id` , `queue_id` , `is_flagged` )";
	$TABLE_DEF->run_sql($sql,"Updating index on workstation_routing_agents");
}


function update_table_workstation_tags() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("workstation_tags",true);
	$TABLE_DEF->check(true);
	
	if($TABLE_DEF->field_exists("tag_set_id")) {
		$TABLE_DEF->drop_field("tag_set_id");
	}
}

function drop_table_team_tag_sets() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("team_tag_sets",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS team_tag_sets","Dropping table team_tag_sets");
	}
}

function drop_table_workstation_tag_sets() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("workstation_tag_sets",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS workstation_tag_sets","Dropping table workstation_tag_sets");
	}
}

function drop_table_workstation_tag_sets_to_teams() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("workstation_tag_sets_to_teams",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS workstation_tag_sets_to_teams","Dropping table workstation_tag_sets_to_teams");
	}
}

function conform_tags() {
	global $cerberus_db;	

	// [JAS]: Force tag names to lowercase
	$sql = sprintf("UPDATE workstation_tags SET tag_name=LOWER(tag_name)");
	$cerberus_db->query($sql);
}

function migrate_mail_rule_tags() {
	global $cerberus_db;
	
	// [JAS]: This should move into some kind of migration event API.
	$sql = sprintf("SELECT me.event, me.outcome FROM `migration_events` me WHERE me.version = %s AND me.event = %s",
		$cerberus_db->escape("3.3.0"),
		$cerberus_db->escape("mail_rule_tags")
	);
	$res = $cerberus_db->query($sql);
	
	if(0 != $cerberus_db->num_rows($res))
		return;

	$sql = sprintf("SELECT rule_id, action_value FROM rule_action WHERE action_type = 100");
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res))
	while($row = $cerberus_db->fetch_row($res)) {
		$rule_id = intval($row['rule_id']);
		$tags = stripslashes($row['action_value']);
		
		$sql = sprintf("SELECT tag_name FROM workstation_tags WHERE tag_id IN (%s)",
			$tags
		);
		$tres = $cerberus_db->query($sql);
		
		if(!$cerberus_db->num_rows($tres))
			continue;
		
		$tagList = array();
		
		while($trow = $cerberus_db->fetch_row($tres)) {
			$tagList[] = stripslashes($trow['tag_name']);
		}
		
		$sql = sprintf("UPDATE rule_action SET action_value = %s WHERE rule_id = %d AND action_type = 100",
			$cerberus_db->escape(implode(',', $tagList)),
			$rule_id
		);
		$cerberus_db->query($sql);
	}
	
	// success
	$sql = sprintf("INSERT INTO `migration_events` (version,event,outcome) ".
		"VALUES ('%s','%s','%s')",
			"3.3.0",
			"mail_rule_tags",
			"done"
	);
	$cerberus_db->query($sql);
}

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('55d5d3ac21c3d2b43799069469d25a69',NOW())"; // 3.3 Clean
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
