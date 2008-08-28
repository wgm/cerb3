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

require_once(FILESYSTEM_PATH . "cerberus-api/database/cer_Database.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");

class CerWorkstationTicketTags extends CerWorkstationTags {
	
	var $total_hits = 0;
	
	function CerWorkstationTicketTags() {
		$this->CerWorkstationTags();
		$this->_loadTicketTagTotals();
	}
	
	// [JAS]: [TODO] This "NOT IN" needs to be optimized out.
	function _loadTicketTagTotals() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = "SELECT count(t.`tag_id`) as hits, t.`tag_id` ".
			"FROM workstation_tags_to_tickets t ".
			"INNER JOIN ticket ON (t.ticket_id=ticket.ticket_id) ".
			"WHERE ticket.is_closed = 0 && ticket.is_waiting_on_customer = 0 && ticket.is_deleted = 0 ".
			"GROUP BY `tag_id` ";
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$hits = $row['hits'];
				$tag_id = $row['tag_id'];
//				$this->_addTicketsWithRollup($tag_id, $hits);
				$this->tags[$tag_id]->hits = $hits;
				$this->total_hits += $hits;
			}
		}
	}
	
};
