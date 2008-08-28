<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/entity/CerEntityObject.class.php");

class CerNextSteps extends CerEntityObjectController {

	function CerNextSteps($validate=true) {
		$this->CerEntityObjectController("next_step","CerNextStep");
		
		// [TODO]: Make this all a column model later with the _validate() being automatic?
		$this->_addColumn("Id","id","integer");
		$this->_addColumn("TicketId","ticket_id","integer");
		$this->_addColumn("DateCreated","date_created","integer");
		$this->_addColumn("CreatedByAgentId","created_by_agent_id","integer");
		$this->_addColumn("Note","note","string");
		
		if($validate)
			$this->_validate();
	}

	// [JAS]: [TODO] This is a hack
	function getListByTicketSql($id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$tableName = $this->getTableName();
		$columns = $this->getColumns();
		$objs = array();

		/* @var $idCol CerEntityObjectColumn */
		$idCol = $columns["Id"];
		
		/* @var $ticketCol CerEntityObjectColumn */
		$ticketCol = $columns["TicketId"];
		
		if(empty($tableName) || empty($columns) || empty($idCol))
			return null;
			
		$sql = sprintf("SELECT ns.id,ns.ticket_id,ns.date_created,ns.created_by_agent_id,ns.note,u.user_name as created_by_agent_name " . 
			"FROM `%s` ns ".
			"LEFT JOIN `user` u ON (ns.created_by_agent_id=u.user_id) ".
			"WHERE ns.`%s` = %d",
				$tableName,
				$ticketCol->getDbName(),
				$id
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$step = new CerNextStep();
				$step->setId($row["id"]);
				$step->setTicketId($row["ticket_id"]);
				$step->setDateCreated($row["date_created"]);
				$step->setNote($row["note"]);
				$step->setCreatedByAgentId($row["created_by_agent_id"]);
				$step->setCreatedByAgentName(stripslashes($row["created_by_agent_name"]));
				$objs[$row["id"]] = $step;
			}
		}
		
		return $objs;
	}
	
	function getListByTicket($id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		$tableName = $this->getTableName();
		$columns = $this->getColumns();
		$objs = array();

		/* @var $idCol CerEntityObjectColumn */
		$idCol = $columns["Id"];
		
		/* @var $ticketCol CerEntityObjectColumn */
		$ticketCol = $columns["TicketId"];
		
		if(empty($tableName) || empty($columns) || empty($idCol))
			return null;
			
		$sql = sprintf($this->getBaseSelectSql() . 
			"WHERE `%s`.`%s` = %d",
				$tableName,
				$ticketCol->getDbName(),
				$id
		);
		$res = $db->query($sql);
		$objs = $this->_getObjectsFromResult($res);
		
		return $objs;
	}

}
