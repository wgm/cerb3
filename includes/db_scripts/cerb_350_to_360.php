<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2007, WebGroup Media LLC
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

define("DB_SCRIPT_NAME","Cerberus Helpdesk 3.5 to 3.6 Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20070608");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","7a42a8fbf3bfcce7023b818267412a1f"); // 3.5 Clean
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	init_table_ticket_status();
	
	update_table_configuration();
	update_table_thread();
	update_table_ticket();

	set_precursor_hashes();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

// ***************************************************************************
// `ticket_status` table
// ***************************************************************************
function init_table_ticket_status()
{
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket_status",false);
	
	$TABLE_DEF->create_sql = "CREATE TABLE `ticket_status` (".
								"  `ticket_status_id` tinyint(4) unsigned NOT NULL auto_increment,".
								"  `ticket_status_text` char(255) NOT NULL default '',".
								"  PRIMARY KEY  (`ticket_status_id`)".
								") TYPE=MyISAM;";
	  
	$TABLE_DEF->fields["ticket_status_id"] = new CER_DB_FIELD("ticket_status_id","tinyint(4) unsigned","","PRI","","auto_increment");
	$TABLE_DEF->fields["ticket_status_text"] = new CER_DB_FIELD("ticket_status_text","char(255)","","","","");
	
	$TABLE_DEF->indexes["primary"] = new CER_DB_INDEX("primary","0","ticket_status_id");
	
	return table($TABLE_DEF);
}

function update_table_ticket() {
	$TABLE_DEF = new CER_DB_TABLE("ticket",true);
	$TABLE_DEF->check(true);
	
	if(!$TABLE_DEF->field_exists("ticket_status_id")) {
		$TABLE_DEF->add_field("ticket_status_id","tinyint(4) unsigned NOT NULL default '0'");
	}
}

function update_table_thread() {
	$TABLE_DEF = new CER_DB_TABLE("thread",true);
	$TABLE_DEF->check(true);
	
	if(!$TABLE_DEF->field_exists("is_hidden")) {
		$TABLE_DEF->add_field("is_hidden","tinyint(4) NOT NULL default '0'");
	}
	if(!$TABLE_DEF->field_exists("thread_bcc")) {
		$TABLE_DEF->add_field("thread_bcc","CHAR(255) default ''");
	}
	
	if($TABLE_DEF->field_exists("thread_to")) {
		$TABLE_DEF->run_sql("ALTER TABLE `thread` CHANGE COLUMN `thread_to` `thread_to` CHAR(255) default ''");
	}
	if($TABLE_DEF->field_exists("thread_cc")) {
		$TABLE_DEF->run_sql("ALTER TABLE `thread` CHANGE COLUMN `thread_cc` `thread_cc` CHAR(255) default ''");
	}
	if($TABLE_DEF->field_exists("thread_replyto")) {
		$TABLE_DEF->run_sql("ALTER TABLE `thread` CHANGE COLUMN `thread_replyto` `thread_replyto` CHAR(255) default ''");
	}
}

function update_table_configuration() {
	$TABLE_DEF = new CER_DB_TABLE("configuration",true);
	$TABLE_DEF->check(true);
	
	if(!$TABLE_DEF->field_exists("cut_line")) {
		$TABLE_DEF->add_field("cut_line","char(64) NOT NULL default ''");
	}
}

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('a646f96e8f3bd6f7ced1737d389b1239',NOW())"; // 3.6 Clean
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
