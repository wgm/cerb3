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

define("DB_SCRIPT_NAME","Cerberus Helpdesk 3.1 to 3.2 (Build 317) Database Update");
define("DB_SCRIPT_AUTHOR","WebGroup Media LLC.");
define("DB_SCRIPT_DATE","20060809");
define("DB_SCRIPT_ONE_RUN","true");
define("DB_SCRIPT_PRECURSOR","ea23c778bf2625923df11b9d269da27f"); // 3.1 Clean
define("DB_SCRIPT_TYPE","upgrade");

function cer_init()
{
	update_table_address();
	update_table_configuration();
	update_table_queue();
	update_table_queue_addresses();
	update_table_team_queues();
	update_table_ticket();

	set_precursor_hashes();
	
	echo "<br><font color='green'><b>Successfully updated!</b></font><br>";
	return true;
}

function update_table_address() {
	$TBL = new CER_DB_TABLE("address");
	$sql = "UPDATE `address` SET `address_address` = LOWER(`address_address`)";
	$TBL->run_sql($sql,sprintf("<b>Changing `address` table to lowercase</b>"));
}

function update_table_configuration() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("configuration",true);
	$TABLE_DEF->check(true);
	
	if(isset($TABLE_DEF->fields["show_kb"])) {
		$TABLE_DEF->drop_field("show_kb");
	}
	
}

function update_table_queue_addresses() {
	$TBL = new CER_DB_TABLE("address");
	$sql = "UPDATE `queue_addresses` SET `queue_address` = LOWER(`queue_address`), `queue_domain` = LOWER(`queue_domain`)";
	$TBL->run_sql($sql,sprintf("<b>Changing `queue_addresses` table to lowercase</b>"));
}

function update_table_queue() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("queue",true);
	$TABLE_DEF->check(true);
	
	if(!isset($TABLE_DEF->fields["queue_reply_to"])) {
		$TABLE_DEF->add_field("queue_reply_to","CHAR(128) NOT NULL");
		
		// [JAS]: Populate the new field using the first queue address from each queue
		$sql = sprintf("select qa.queue_id, concat(qa.queue_address,'@',qa.queue_domain) as address ".
			"FROM queue_addresses qa ".
			"GROUP BY qa.queue_id ".
			"ORDER BY qa.queue_id ASC"
		);
		$res = $cerberus_db->query($sql);
		
		if($cerberus_db->num_rows($res))
		while($row = $cerberus_db->fetch_row($res)) {
			$queue_id = intval($row['queue_id']);
			$address = stripslashes($row['address']);
			$sql = sprintf("UPDATE `queue` SET `queue_reply_to` = %s WHERE `queue_id` = %d",
				$cerberus_db->escape($address),
				$queue_id
			);
			$cerberus_db->query($sql);
		}
	}
}

function update_table_team_queues() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("team_queues",true);
	$TABLE_DEF->check(true);
	
	if(!$TABLE_DEF->field_exists("quick_assign")) {
		$TABLE_DEF->add_field("quick_assign","tinyint(3) unsigned default '0' not null");
		
		// [JAS]: Default all team mailboxes to quick assign (as things functioned in < 3.1)
		$sql = "UPDATE `team_queues` SET `quick_assign` = 1";
		$cerberus_db->query($sql);
	}
}

function update_table_ticket() {
	global $cerberus_db;
	
	$TABLE_DEF = new CER_DB_TABLE("ticket",true);
	$TABLE_DEF->check(true);
	
	if($TABLE_DEF->field_exists("queue_addresses_id")) {
		$TABLE_DEF->drop_field("queue_addresses_id");
	}
}

function set_precursor_hashes()
{
	global $cerberus_db;

	$sql = "REPLACE INTO db_script_hash(script_md5,run_date) VALUES('fe27bb093ca981ac2cc3ef245eb64f95',NOW())"; // 3.2 Clean
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
