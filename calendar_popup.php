<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: calendar_popup.php
|
| Purpose: A general purpose calendar pop-up for simplified date
|	entry.
|
| Developers involved with this file:
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/i18n/languages.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/calendar.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_TimestampSelect.class.php");

@$show_time = $_REQUEST["show_time"];

$cer_tpl = new CER_TEMPLATE_HANDLER();

$timestamp_select = new cer_TimestampSelect();
$cerberus_translate = new cer_translate;

$cer_tpl->assign("timestamp_select",$timestamp_select);
$cer_tpl->assign("show_time",$show_time);

// [JAS]: Set up the local variables from the scope objects
@$timestamp = $_REQUEST["timestamp"];
@$field = $_REQUEST["field"];
@$label = $_REQUEST["label"];
@$mo_offset = $_REQUEST["mo_offset"];
@$date_dmy = $_REQUEST["date_dmy"];
@$date_hr = $_REQUEST["date_hr"];
@$date_min = $_REQUEST["date_min"];
@$date_ampm = $_REQUEST["date_ampm"];

if(!empty($timestamp) && is_numeric($timestamp)) {
	$date = new cer_DateTime($timestamp);
	$mo_date = $date->getUserDate("%m/%d/%Y");
	$mo_hr = $date->getUserDate("%I");
	$mo_min = $date->getUserDate("%M");
	$mo_ampm = strtolower($date->getUserDate("%p"));
	$cer_tpl->assign("mo_date",$mo_date);
	$cer_tpl->assign("mo_hr",$mo_hr);
	$cer_tpl->assign("mo_min",$mo_min);
	$cer_tpl->assign("mo_ampm",$mo_ampm);
}

// [JAS]: Set up the calendar ==========================
class calendar_callback
{
	function calendar_draw_day_links(&$o_day,$month,$year)
	{
		if($o_day == null) return true;
		
		$o_day->day_url = sprintf("javascript:updateDate(%d,%d,%d)",
			$o_day->day,
			$month,
			$year
		);
			
		return($o_day);
	}

	function calendar_draw_month_links($mo_offset=0,$prev_mo=-1,$next_mo=1)
	{
		global $field, $label, $show_time, $timestamp;
		
		$o_links = array();
		
		$o_links["prev_mo"] = cer_href($_SERVER["PHP_SELF"] . "?field=$field&label=$label&mo_offset=$prev_mo&show_time=$show_time&timestamp=$timestamp");
		$o_links["next_mo"] = cer_href($_SERVER["PHP_SELF"] . "?field=$field&label=$label&mo_offset=$next_mo&show_time=$show_time&timestamp=$timestamp");
		
		return($o_links);
	}
};

$cer_tpl->assign('timestamp',$timestamp);
$cer_tpl->assign('label',$label);
$cer_tpl->assign('field',$field);

if(empty($date_dmy))
{
	// ======================================================
	$cal_callbacks = new calendar_callback();
	$cal = new CER_CALENDAR($mo_offset);
	$cal->register_callback_day_links("calendar_draw_day_links",$cal_callbacks);
	$cal->register_callback_month_links("calendar_draw_month_links",$cal_callbacks);
	$cal->populate_calendar_matrix();
	$cer_tpl->assign_by_ref('cal',$cal);
	$cer_tpl->assign('date_chosen',false);
	// ======================================================
}
else {
	$str = sprintf("%s %d:%d%s",
		$date_dmy,
		$date_hr,
		$date_min,
		$date_ampm
	);
	$date = new cer_DateTime(strtotime($str));
	$cer_tpl->assign('chosenDisplay',$date->getUserDate());
	$cer_tpl->assign('chosenTimestamp',$date->mktime_datetime);
	$cer_tpl->assign('date_chosen',true);
}

$cer_tpl->display("calendar_popup.tpl.php");

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************

