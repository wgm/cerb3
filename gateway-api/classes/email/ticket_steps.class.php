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

require_once(FILESYSTEM_PATH . "gateway-api/classes/html/html.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/compatibility.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/constants.php");
require_once(FILESYSTEM_PATH . "gateway-api/functions/arrays.inc.php");

require_once(FILESYSTEM_PATH . "cerberus-api/workflow/CerNextSteps.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/entity/model/CerNextStep.class.php");

class ticket_steps
{
	/**
    * DB abstraction layer handle
    *
    * @var object
    */
	var $db;

	function ticket_steps() {
		$this->db =& database_loader::get_instance();
	}


	/*
	 * 
	 */
	function get_steps_for_ticket($ticket_id) { 
		/*
		$nextStepController = new CerNextSteps();
		$steps = $nextStepController->getListByTicket($ticket_id);
		return $steps;
		*/
		
		$result =& $this->db->get("ticket", "get_ticket_steps", array("ticket_id"=>$ticket_id));
		$steps = array();
		$previous_step_id = "NONE"; 
		
		$currentStepIndex = -1;
		
		for($i=0; $i < sizeof($result); $i++) {
			if($previous_step_id != $result[$i]['id']) {
				$currentStepIndex++;
				
				$steps[$currentStepIndex]->id = $result[$i]['id'];
				$steps[$currentStepIndex]->dateCreated = $result[$i]['date_created'];
				$steps[$currentStepIndex]->createdByAgentId = $result[$i]['created_by_agent_id'];
				$steps[$currentStepIndex]->createdByAgentName = $result[$i]['created_by_agent_name'];
				$steps[$currentStepIndex]->note = $result[$i]['note'];
				
			}
			
			$previous_step_id = $result[$i]['id'];
		}
		
		return $steps;
		
	}
	
	/*
		[mdf]: creates a new CerNextStep and returns the new object, or null if the insert fails
	*/
	function add_step($ticket_id, $note, $createdByAgentId) {
		$rightNow = time();
		
		/* @var $nextStep CerNextStep */
		$nextStep = new CerNextStep();
		$nextStep->setId(0);
		$nextStep->setTicketId($ticket_id);
		$nextStep->setDateCreated($rightNow);
		$nextStep->setCreatedByAgentId($createdByAgentId);
		$nextStep->setNote($note);
		
		$nextStepController = new CerNextSteps();

		$newId = $nextStepController->save($nextStep);
		
		if($newId == null) {
			return null;
		}
		else {
			return $nextStep;
		}
		
	}
	
	/*
		[mdf]: creates a new CerNextStep and returns the new id
	*/
	function modify_step($step_id, $note) {
		/* @var nextStep CerNextStep*/
		
		$nextStepController = new CerNextSteps();
		$nextStep = $nextStepController->getById($step_id);
		if($nextStep == null)
			return null;
			
		$nextStep->setNote($note);
		$newId = $nextStepController->save($nextStep);
		
		if($newId == null)
			return null;
		else {
			return $nextStep;
		}
		
	}
	
	/*
	[mdf]: Deletes a next step, and returns null if the deletion failed
	*/
	function delete_step($step_id) {
		$nextStepController = new CerNextSteps();
		$affectedRows = $nextStepController->delete($step_id);

		return $affectedRows;
	}

}

