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

define("DB_SCRIPT_NAME","Cerberus Helpdesk 3.0 to 3.1 (Build 294) Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20060710");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","ae76f0a157a5bade2dd90d97b2744d4f"); // 3.0 Clean
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
//	init_table_team_queues();
	
	update_table_configuration();
	update_table_pop3_accounts();
	update_table_team();
	update_table_ticket();
	update_table_field_group_values();
	update_table_workstation();

	drop_table_ticket_due_dates();
	drop_table_workstation_routing();
	drop_table_workstation_routing_to_tickets();

	set_precursor_hashes();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

//function init_table_workstation_tag_sets() {
//	global $cerberus_db;
//	
//   $TABLE_DEF = new CER_DB_TABLE("workstation_tag_sets",false);
//   
//	$TABLE_DEF->create_sql = "CREATE TABLE `workstation_tag_sets` ( ".
//		"`id` int(10) unsigned NOT NULL auto_increment, ".
//		"`name` char(32) NOT NULL, ".
//		"PRIMARY KEY  (`id`) ".
//		")";
//	
//   $TABLE_DEF->fields["id"] = new CER_DB_FIELD("id","int(10) unsigned","","PRI","","auto_increment");
//   $TABLE_DEF->fields["name"] = new CER_DB_FIELD("name","char(32)","","","","");
//	
//   $TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","id");
//
//   table($TABLE_DEF);
//}


function update_table_configuration() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("configuration",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["gui_version"])) {
		$TABLE_DEF->drop_field("gui_version");
	}
	if(isset($TABLE_DEF->fields["enable_panel_stats"])) {
		$TABLE_DEF->drop_field("enable_panel_stats");
	}
	if(!isset($TABLE_DEF->fields["subject_ids"])) {
		$TABLE_DEF->add_field("subject_ids","TINYINT(3) UNSIGNED DEFAULT '1' NOT NULL");
	}
	
}

function update_table_pop3_accounts() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("pop3_accounts",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["host"])) {
		$TABLE_DEF->run_sql("ALTER TABLE `pop3_accounts` CHANGE COLUMN `host` `host` VARCHAR(255) NOT NULL","Expanding pop3_accounts.host limit to 255 characters");
	}
	if(isset($TABLE_DEF->fields["login"])) {
		$TABLE_DEF->run_sql("ALTER TABLE `pop3_accounts` CHANGE COLUMN `login` `login` VARCHAR(255) NOT NULL","Expanding pop3_accounts.login limit to 255 characters");
	}
}

function update_table_team() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("team",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["default_response_time"])) {
		$TABLE_DEF->drop_field("default_response_time");
	}
	if(isset($TABLE_DEF->fields["default_schedule"])) {
		$TABLE_DEF->drop_field("default_schedule");
	}
}

function update_table_field_group_values() {
	global $cerberus_db;
	include_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
	
	$TABLE_DEF = new CER_DB_TABLE("field_group_values",true);
	$TABLE_DEF->check(true);
	
	$sql = "SELECT cf.field_id,v.field_value,v.group_instance_id FROM fields_custom cf INNER JOIN field_group_values v ON (cf.field_id=v.field_id) WHERE cf.field_type = 'E'";
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res))
	while($row = $cerberus_db->fetch_row($res)) {
		if(is_numeric($row['field_value'])) continue;
		$n = sscanf($row['field_value'],"%d/%d/%d",$m,$d,$y);
		$str = $m.'/'.$d.'/'.$y;
		if(3 != $n) continue;
		$date = new cer_DateTime($str);
		$sql = sprintf("UPDATE `field_group_values` SET field_value = %s WHERE field_id = %d AND group_instance_id = %d",
			$cerberus_db->escape($date->mktime_datetime),
			$row['field_id'],
			$row['group_instance_id']
		);
		$cerberus_db->query($sql);
	}
}

function update_table_workstation() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("workstation",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["max_users"])) {
		$TABLE_DEF->run_sql("ALTER TABLE `workstation` CHANGE COLUMN `max_users` `max_desktop_users` INT(11) UNSIGNED NOT NULL DEFAULT '0'","Altering `workstation`.`max_users`");
	}
	if(!isset($TABLE_DEF->fields["max_web_users"])) {
		$TABLE_DEF->add_field("max_web_users","INT(11) UNSIGNED NOT NULL DEFAULT '0'");
	}
}

function update_table_ticket() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["num_teams"])) {
		$TABLE_DEF->drop_field("num_teams");
	}
}

function drop_table_workstation_routing() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("workstation_routing",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS workstation_routing","Dropping table workstation_routing");
	}
}

function drop_table_workstation_routing_to_tickets() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("workstation_routing_to_tickets",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS workstation_routing_to_tickets","Dropping table workstation_routing_to_tickets");
	}
}

function drop_table_ticket_due_dates() {
	global $cerberus_db;
	$TABLE_DEF = new CER_DB_TABLE("ticket_due_dates",true);
	
	if($TABLE_DEF->check(false)) {
		$TABLE_DEF->run_sql("DROP TABLE IF EXISTS ticket_due_dates","Dropping table ticket_due_dates");
	}
}

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('ea23c778bf2625923df11b9d269da27f',NOW())"; // 3.1 Clean
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
