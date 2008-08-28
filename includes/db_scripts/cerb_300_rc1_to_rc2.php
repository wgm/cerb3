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

define("DB_SCRIPT_NAME","Cerberus Helpdesk 3.0 RC1 to 3.0 Release (228) Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20060530");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","c0c47e3871612409bf4b33ba22022192");
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	init_table_team_queues();
	init_table_team_tag_sets();
	init_table_workstation_tag_sets();
	init_table_workstation_tag_sets_to_teams();
	init_table_jasper_report_categories();
	
	update_table_dashboard();
	update_table_public_gui_profiles();
	update_table_pop3_accounts();
	update_table_workstation_tags();
	update_table_jasper_reports();

//	drop_table_department();

	set_precursor_hashes();
	set_data_jasper_report_categories();
	clear_layout_prefs();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

function init_table_workstation_tag_sets() {
	global $cerberus_db;
	
   $TABLE_DEF = new CER_DB_TABLE("workstation_tag_sets",false);
   
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_tag_sets` ( ".
		"`id` int(10) unsigned NOT NULL auto_increment, ".
		"`name` char(32) NOT NULL, ".
		"PRIMARY KEY  (`id`) ".
		")";
	
   $TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["name"] = new CER_DB_FIELD("name","char(32)","","","","");
	
   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");

   table($TABLE_DEF);
}

function init_table_workstation_tag_sets_to_teams() {
	global $cerberus_db;
	
   $TABLE_DEF = new CER_DB_TABLE("workstation_tag_sets_to_teams",false);
   
	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_tag_sets_to_teams` ( ".
		"`set_id` int(10) unsigned NOT NULL, ".
		"`team_id` int(10) unsigned NOT NULL, ".
		"PRIMARY KEY  (`set_id`,`team_id`) ".
		")";
	
   $TABLE_DEF->fields["set_id"] = new CER_DB_FIELD("set_id","int(10) unsigned","","PRI","","");
   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","int(10) unsigned","","PRI","","");
	
   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("set_id","team_id"));

   table($TABLE_DEF);
}

function init_table_team_queues() {
	global $cerberus_db;
	
   $TABLE_DEF = new CER_DB_TABLE("team_queues",false);
   
	$TABLE_DEF->create_sql = "CREATE TABLE `team_queues` ( ".
		"`team_id` int(10) unsigned NOT NULL, ".
		"`queue_id` int(10) unsigned NOT NULL, ".
		"PRIMARY KEY  (`team_id`,`queue_id`) ".
		")";
	
   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","int(10) unsigned","","PRI","","");
   $TABLE_DEF->fields["queue_id"] = new CER_DB_FIELD("queue_id","int(10) unsigned","","PRI","","");
	
   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("team_id","queue_id"));

   table($TABLE_DEF);
}

function init_table_team_tag_sets() {
	global $cerberus_db;
	
   $TABLE_DEF = new CER_DB_TABLE("team_tag_sets",false);
   
	$TABLE_DEF->create_sql = "CREATE TABLE `team_tag_sets` ( ".
		"`team_id` int(10) unsigned NOT NULL, ".
		"`set_id` int(10) unsigned NOT NULL, ".
		"PRIMARY KEY  (`team_id`,`set_id`) ".
		")";
	
   $TABLE_DEF->fields["team_id"] = new CER_DB_FIELD("team_id","int(10) unsigned","","PRI","","");
   $TABLE_DEF->fields["set_id"] = new CER_DB_FIELD("set_id","int(10) unsigned","","PRI","","");
	
   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("team_id","set_id"));

   table($TABLE_DEF);
}

function init_table_jasper_report_categories() {
	global $cerberus_db;
	
   $TABLE_DEF = new CER_DB_TABLE("jasper_report_categories",false);
   
	$TABLE_DEF->create_sql = "CREATE TABLE `jasper_report_categories` ( ".
		"`category_id` int(10) unsigned NOT NULL auto_increment, ".
		"`name` char(64) NOT NULL default '', ".
		"PRIMARY KEY  (`category_id`)".
		")";
	
   $TABLE_DEF->fields["category_id"] = new CER_DB_FIELD("category_id","int(10) unsigned","","PRI","","auto_increment");
   $TABLE_DEF->fields["name"] = new CER_DB_FIELD("name","char(64)","","","","");
	
   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0",array("category_id"));

   table($TABLE_DEF);
}

function set_data_jasper_report_categories() {
	global $cerberus_db;
	$sql = "SELECT name FROM jasper_report_categories WHERE category_id = 1 ";
	$res = $cerberus_db->query($sql);
	
	if(!$cerberus_db->num_rows($res)) {
		$sql = "INSERT INTO jasper_report_categories (category_id, name) VALUES (1, 'Default')";
		$cerberus_db->query($sql);
	}	
}

function migrate_nested_tags_to_sets() {
	global $cerberus_db;
	
	// Clear any previous attempts
	$sql = "DELETE FROM `workstation_tag_sets`";
	$cerberus_db->query($sql);
	
	$sql = sprintf("SELECT t.tag_id, t.tag_name, t.parent_tag_id ".
		"FROM workstation_tags t ".
		"ORDER BY t.tag_name ASC"
	);
	$res = $cerberus_db->query($sql);
	
	$names = array();
	$list = array();
	$tops = array();

	// Make our list
	if($cerberus_db->num_rows($res))
	while($row = $cerberus_db->fetch_row($res)) {
		$pid = intval($row['parent_tag_id']);
		$tid = intval($row['tag_id']);
		if(!isset($pids[$pid])) $pids[$pid] = array();
		$list[$tid] = $pid;
		$names[$tid] = stripslashes($row['tag_name']);
		if(0 == $pid) {
			$tops[$tid] = array();
		}
	}
	
	// Flatten everything under the top level categories
	foreach($list as $t => $p) {
		$q = array();
		$ptr =& $t;
		while(0 != $list[$ptr]) {
			$q[$ptr] = $ptr;
			if(0 == $list[$ptr]) break;
			$ptr =& $list[$ptr];
		}
		
//		echo $t," has top level ",$ptr,"<BR>";
		$tops[$ptr][$t] = $t;
	}
	
	foreach($tops as $t => $ta) {
		$sql = sprintf("INSERT INTO `workstation_tag_sets` (`name`) VALUES (%s)",
			$cerberus_db->escape($names[$t])
		);
		$cerberus_db->query($sql);
		$set_id = $cerberus_db->insert_id();
		
		if(is_array($ta) && !empty($set_id)) {
			$sql = sprintf("UPDATE `workstation_tags` SET `tag_set_id` = %d WHERE `parent_tag_id` = 0 AND `tag_id` = %d",
				$set_id,
				$t
			);
			$cerberus_db->query($sql);
			
			foreach($ta as $tv) {
				// update old tag to use the new set rather than a parent
				$sql = sprintf("UPDATE `workstation_tags` SET `tag_set_id` = %d WHERE `parent_tag_id` = %d",
					$set_id,
					$tv
				);
				$cerberus_db->query($sql);
			}
		}
	}
	
	return true;
}

function update_table_pop3_accounts() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("pop3_accounts",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["port"])) {
		$TABLE_DEF->add_field("port","int(11) unsigned NOT NULL default '110'");
	}
}

function update_table_dashboard() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("dashboard",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["hide_queues"])) {
		$TABLE_DEF->add_field("hide_queues","text default ''");
	}
	if(!isset($TABLE_DEF->fields["hide_teams"])) {
		$TABLE_DEF->add_field("hide_teams","text default ''");
	}
	if(!isset($TABLE_DEF->fields["reload_mins"])) {
		$TABLE_DEF->add_field("reload_mins","tinyint(3) unsigned not null default '0'");
	}
}

function update_table_public_gui_profiles() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("public_gui_profiles",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["pub_mod_kb_tag_sets"])) {
		$TABLE_DEF->add_field("pub_mod_kb_tag_sets","text default ''");
	}
	if(isset($TABLE_DEF->fields["pub_mod_kb_tag_root"])) {
		$TABLE_DEF->drop_field("pub_mod_kb_tag_root");
	}
}

function update_table_workstation_tags() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("workstation_tags",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["tag_set_id"])) {
		$TABLE_DEF->add_field("tag_set_id","int(11) unsigned NOT NULL default '0'");
		if(migrate_nested_tags_to_sets()) {
			$TABLE_DEF->drop_field("parent_tag_id");
		}
	}
}

//function drop_table_department() {
//	global $cerberus_db;
//	$TABLE_DEF = new CER_DB_TABLE("department",true);
//	
//	if($TABLE_DEF->check(false)) {
//		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS department","Dropping table department");
//	}
//}

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('ae76f0a157a5bade2dd90d97b2744d4f',NOW())"; // 3.0 Release Clean
	$cerberus_db->query($sql);
}

function update_table_jasper_reports() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("jasper_reports",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["guid"])) {
		$TABLE_DEF->add_field("guid","int(10) default NULL");
	}
	if(!isset($TABLE_DEF->fields["summary"])) {
		$TABLE_DEF->add_field("summary","text");
	}
	if(!isset($TABLE_DEF->fields["version"])) {
		$TABLE_DEF->add_field("version","varchar(20) default NULL");
	}
	if(!isset($TABLE_DEF->fields["author"])) {
		$TABLE_DEF->add_field("author","varchar(255) default NULL");
	}
	if(!isset($TABLE_DEF->fields["category_id"])) {
		$TABLE_DEF->add_field("category_id","int(10) default NULL");
	}
	if(!isset($TABLE_DEF->fields["report_source"])) {
		$TABLE_DEF->add_field("report_source","mediumtext");
	}
	if(!isset($TABLE_DEF->fields["scriptlet_source"])) {
		$TABLE_DEF->add_field("scriptlet_source","mediumtext");
	}
}

function clear_layout_prefs() {
	$TABLE_DEF = new CER_DB_TABLE("user_layout",false);
	$TABLE_DEF->run_sql("DELETE FROM `user_layout`","Clearing layout preferences in 2.0 format");
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
