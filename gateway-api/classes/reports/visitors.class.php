<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/math/statistics/cer_WeightedAverage.class.php");

class reports_visitors
{
   /**
    * DB abstraction layer handle
    *
    * @var object
    */
   var $db;

   function reports_visitors() {
      $this->db =& database_loader::get_instance();
      $xml =& xml_output::get_instance();
      $data =& $xml->get_child("data", 0);
      $this->reports =& $data->add_child("reports", xml_object::create("reports"));
   }

   function get_traffic_report() {
      $this->build_chart_visitors_24h();
      $this->build_chart_visitors_24h_average_over_7d();
      $this->build_chart_visitors_day_totals_over_7d();
      $this->build_chart_visitors_daily_averages_per_day_over_month();

      return TRUE;
   }

   function get_referrer_report($range, $host_limit, $url_limit) {
      $this->build_chart_referrer_hosts($range, $host_limit);
      $this->build_chart_referrer_urls($range, $url_limit);

      return TRUE;
   }

   function build_chart_visitors_24h() {
      $report =& $this->reports->add_child("chart_visitors_24h", xml_object::create("chart_visitors_24h"));
      $db_data = $this->db->Get("reports", "visitor_per_hour", array("days"=>1));
      $totals = array();
      for($i=0;$i<24;$i++) {
         $totals[$i] = 0;
      }
      while(NULL !== $db_row = array_shift($db_data)) {
         $totals[(int) $db_row['visit_hour']] = $db_row['num_visits'];
      }
      foreach($totals as $hour=>$num) {
         $row =& $report->add_child("row", xml_object::create("row"));
         $row->add_child("key", xml_object::create("key", $hour));
         $row->add_child("value", xml_object::create("value", $num));
      }
   }

   function build_chart_visitors_24h_average_over_7d() {
      $report =& $this->reports->add_child("chart_visitors_7d", xml_object::create("chart_visitors_7d"));
      $db_data = $this->db->Get("reports", "visitor_per_hour_average", array("days"=>7));
      $avgs = array();
      for($i=0;$i<24;$i++) {
         $avgs[$i] = new cer_WeightedAverage();
      }
      while(NULL !== $db_row = array_shift($db_data)) {
      	// [JAS]: The following lines didn't use the (int) to strip 0 padding, so hours from 
      	//	00 to 09 only received 1 days worth of samples, not the week avg as intended.
//         if(!isset($avgs[$db_row['visit_hour']])) {
//            $avgs[(int) $db_row['visit_hour']] = new cer_WeightedAverage();
//         }
         $avgs[(int) $db_row["visit_hour"]]->addSample($db_row["num_visits"],1);
      }
      foreach($avgs as $hr=>$avg) {
         $row =& $report->add_child("row", xml_object::create("row"));
         $row->add_child("key", xml_object::create("key", $hr));
         $row->add_child("value", xml_object::create("value", floor($avg->getAverage())));
      }
   }

   function build_chart_visitors_day_totals_over_7d() {
      $report =& $this->reports->add_child("chart_visitors_day_7d", xml_object::create("chart_visitors_day_7d"));
      $db_data = $this->db->Get("reports", "visitor_per_day", array("range"=>7, "scope"=>"DAY"));
      for($i=0;$i<7;$i++) {
         $totals[$i] = 0;
      }
      while(NULL !== $db_row = array_shift($db_data)) {
         $totals[$this->day_number($db_row['visit_day'])] = $db_row['num_visits'];
      }
      foreach($totals as $day=>$num) {
         $row =& $report->add_child("row", xml_object::create("row"));
         $row->add_child("key", xml_object::create("key", $day));
         $row->add_child("value", xml_object::create("value", $num));
      }
   }

   function build_chart_visitors_daily_averages_per_day_over_month() {
      $report =& $this->reports->add_child("chart_visitors_day_1mo", xml_object::create("chart_visitors_day_1mo"));
      $db_data = $this->db->Get("reports", "visitor_per_day_average", array("range"=>1, "scope"=>"MONTH"));
      $avgs = array();
      for($i=0;$i<7;$i++) {
         $avgs[$i] = new cer_WeightedAverage();
      }
      while(NULL !== $db_row = array_shift($db_data)) {
         if(!isset($avgs[$this->day_number($db_row['visit_day'])])) {
            $avgs[$this->day_number($db_row['visit_day'])] = new cer_WeightedAverage();
         }
         $avgs[$this->day_number($db_row['visit_day'])]->addSample($db_row["num_visits"],1);
      }
      foreach($avgs as $day=>$avg) {
         $row =& $report->add_child("row", xml_object::create("row"));
         $row->add_child("key", xml_object::create("key", $day));
         $row->add_child("value", xml_object::create("value", floor($avg->getAverage())));
      }
   }

   function build_chart_referrer_hosts($range, $limit) {
      $exclude_hosts = $this->build_exclude_hosts();
      $report =& $this->reports->add_child("chart_top_referrers", xml_object::create("chart_top_referrers", NULL, array("range"=>$range, "limit"=>$limit)));
      $db_data = $this->db->Get("reports", "referrer_hosts", array("range"=>$range, "limit"=>$limit, "exclude_hosts"=>$exclude_hosts));
      if(is_array($db_data))
      while(NULL !== $db_row = array_shift($db_data)) {
         $row =& $report->add_child("row", xml_object::create("row"));
         $row->add_child("name", xml_object::create("name", $db_row['host']));
         $row->add_child("value", xml_object::create("value", $db_row['num_referrals']));
      }
   }

   function build_chart_referrer_urls($range, $limit) {
      $exclude_hosts = $this->build_exclude_hosts();
      $report =& $this->reports->add_child("table_referrers", xml_object::create("table_referrers", NULL, array("range"=>$range, "limit"=>$limit)));
      $db_data = $this->db->Get("reports", "referrer_urls", array("range"=>$range, "limit"=>$limit, "exclude_hosts"=>$exclude_hosts));
      if(is_array($db_data))
      while(NULL !== $db_row = array_shift($db_data)) {
         $row =& $report->add_child("row", xml_object::create("row"));
         $row->add_child("name", xml_object::create("name", $db_row['url']));
         $row->add_child("value", xml_object::create("value", $db_row['num_referrals']));
      }
   }

   function build_exclude_hosts() {
      if(!defined('OWN_SITE_DOMAINS')) {
         return NULL;
      }
      $domains = explode(',', OWN_SITE_DOMAINS);
      if(is_array($domains) && count($domains) > 0) {
         foreach($domains as $domain) {
            $clean_domains[] = str_replace(array("'", ";"), "", trim($domain));
         }
         return $clean_domains;
      }
      else {
         return NULL;
      }
   }

   function day_number($day_name) {
      switch($day_name) {
         case 'Sun': return 0;
         case 'Mon': return 1;
         case 'Tue': return 2;
         case 'Wed': return 3;
         case 'Thu': return 4;
         case 'Fri': return 5;
         case 'Sat': return 6;
      }
   }

   function day_name($day_num) {
      switch($day_num) {
         case '0': return 'Sun';
         case '1': return 'Mon';
         case '2': return 'Tue';
         case '3': return 'Wed';
         case '4': return 'Thu';
         case '5': return 'Fri';
         case '6': return 'Sat';
      }
   }

}