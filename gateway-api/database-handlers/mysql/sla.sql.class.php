<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC
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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

/**
 * Database abstraction layer for skills data
 *
 */
class sla_sql
{
   /**
    * Direct connection to DB through ADOdb
    *
    * @var unknown
    */
   var $db;

   /**
    * Class Constructor
    *
    * @param object $db Direct connection to DB through ADOdb
    */
   function sla_sql(&$db) {
      $this->db =& $db;
   }

   /**
    * Get SLA list
    *
    * @param array $params Associative array of parameters
    * @return array data from DB
    */
   function get_slas($params) {
      
		$query ="select sla.id sla_id, sla.name sla_name,
		stt.schedule_id, stt.response_time,
		t.team_id, t.team_name,
		sch.schedule_id,
		sch.sun_open, sch.sun_close, sch.mon_open, sch.mon_close, sch.tue_open, sch.tue_close,
		sch.wed_open, sch.wed_close, sch.thu_open, sch.thu_close, sch.fri_open, sch.fri_close,
		sch.sat_open, sch.sat_close,
		sch.sun_hrs, sch.mon_hrs, sch.tue_hrs, sch.wed_hrs, sch.thu_hrs, sch.fri_hrs, sch.sat_hrs
		FROM sla
		LEFT JOIN sla_to_team stt ON sla.id = stt.sla_id
		LEFT JOIN team t ON stt.team_id = t.team_id
		LEFT JOIN schedule sch ON stt.schedule_id = sch.schedule_id
		ORDER BY sla.name ";
      
      return $this->db->GetAll($query);
   }

}