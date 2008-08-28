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
|		Daniel Hildebrandt		(hildy@webgroupmedia.com)		[ddh]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "includes/cerberus-api/database/database_updater.class.php");

define("DB_SCRIPT_NAME","Cerberus Helpdesk 3.4 to 3.5 Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20070222");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","55d5d3ac21c3d2b43799069469d25a69"); // 3.3 Clean
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	update_table_configuration();
	update_table_ticket();
	update_table_user_prefs();

	fix_indexes();
	
	set_precursor_hashes();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

function update_table_ticket() {
	$TABLE_DEF = new CER_DB_TABLE("ticket",true);
	$TABLE_DEF->check(true);
	
	if(!$TABLE_DEF->field_exists("last_wrote_address_id")) {
		$TABLE_DEF->add_field("last_wrote_address_id","bigint(20) unsigned NOT NULL default '0'");
	}

	if(!$TABLE_DEF->index_exists("last_wrote_address_id")) {
		$TABLE_DEF->add_index("last_wrote_address_id",1,array("last_wrote_address_id"));
	}
	
	if($TABLE_DEF->field_exists("last_update_date")) {
		$TABLE_DEF->drop_field("last_update_date");
	}
	
	if(!$TABLE_DEF->field_exists("ticket_last_date")) {
		$TABLE_DEF->add_field("ticket_last_date","datetime NOT NULL default '0000-00-00 00:00:00'");
	}

	if(!$TABLE_DEF->index_exists("ticket_last_date")) {
		$TABLE_DEF->add_index("ticket_last_date",1,array("ticket_last_date"));
	}
	
	migrate_ticket_last_date();
	migrate_ticket_last_wrote();
}

function migrate_ticket_last_date() {
	global $cerberus_db;
	
	echo "Writing a speed-up cache for ticket last wrote date...";
	flush();
	
	$sql = sprintf("SELECT t.ticket_id, th.thread_date ".
		"FROM ticket t ".
		"INNER JOIN thread th ON (t.max_thread_id=th.thread_id) ".
		"WHERE t.ticket_last_date = '0000-00-00 00:00:00' "
	);
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res))
	while($row = $cerberus_db->fetch_row($res)) {
		$sql = sprintf("UPDATE ticket SET ticket_last_date = '%s' WHERE ticket_id = %d",
			$row['thread_date'],
			$row['ticket_id']
		);
		$cerberus_db->query($sql);
	}
	
	echo "OK!<br>";
}

function migrate_ticket_last_wrote() {
	global $cerberus_db;
	
	echo "Writing a speed-up cache for ticket last wrote...";
	flush();
	
	$sql = sprintf("SELECT t.ticket_id, a.address_id ".
		"FROM ticket t ".
		"INNER JOIN thread th ON (t.max_thread_id=th.thread_id) ".
		"INNER JOIN address a ON (th.thread_address_id=a.address_id) ".
		"WHERE t.last_wrote_address_id = 0 "
	);
	$res = $cerberus_db->query($sql);
	
	if($cerberus_db->num_rows($res))
	while($row = $cerberus_db->fetch_row($res)) {
		$sql = sprintf("UPDATE ticket SET last_wrote_address_id = %d WHERE ticket_id = %d",
			$row['address_id'],
			$row['ticket_id']
		);
		$cerberus_db->query($sql);
	}
	
	echo "OK!<br>";
}

function update_table_user_prefs() {
	$TABLE_DEF = new CER_DB_TABLE("user_prefs",true);
	$TABLE_DEF->check(true);
	
	if(!$TABLE_DEF->field_exists("quote_previous")) {
		$TABLE_DEF->add_field("quote_previous","tinyint(1) NOT NULL default '1'");
	}
}

function fix_indexes() {
	$TABLE_DEF = new CER_DB_TABLE("thread",true);
	
	if($TABLE_DEF->index_exists("thread_id")) {
		$TABLE_DEF->run_sql("ALTER TABLE thread DROP INDEX `thread_id`","Dropping duplicate index thread.thread_id");
	}
	if($TABLE_DEF->index_exists("ticket_sender_id")) {
		$TABLE_DEF->run_sql("ALTER TABLE thread DROP INDEX `ticket_sender_id`","Dropping duplicate index thread.ticket_sender_id");
	}
}

function update_table_configuration() {
	$TABLE_DEF = new CER_DB_TABLE("configuration",true);
	$TABLE_DEF->check(true);
	
	if(!$TABLE_DEF->field_exists("smtp_server_user")) {
		$TABLE_DEF->add_field("smtp_server_user","char(64) NOT NULL default ''");
	}
	if(!$TABLE_DEF->field_exists("smtp_server_pass")) {
		$TABLE_DEF->add_field("smtp_server_pass","char(64) NOT NULL default ''");
	}
}

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('7a42a8fbf3bfcce7023b818267412a1f',NOW())"; // 3.5 Clean
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
