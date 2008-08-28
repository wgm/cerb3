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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

class CerSearchBuilder {
	
	var $criteria = array();
	
	function CerSearchBuilder() {
		
	}

	function reset() {
		$this->criteria = array();
	}
	
	function add($c,$params) {
		if(!is_array($params))
			$params = array($params);
		
		// allow cumulative criteria
		switch($c) {
			case "flag":
				if(empty($params['flag_mode']))
					unset($this->criteria[$c]['flags']);
					// intentionally no break
			case "queue":
			case "new_status":
			case "tags":
			case "workflow":
				foreach($params as $pi => $p2) {
					if(isset($this->criteria[$c][$pi])) { // merge
						if(is_array($this->criteria[$c][$pi]) && is_array($p2)) {
							// [JAS]: Merge arrays and remove dupes
							$combined = array_unique((array)$this->criteria[$c][$pi] + (array)$p2);
							$this->criteria[$c][$pi] = $combined;
						} else {
							$this->criteria[$c][$pi] = $p2;	
						}
					} else { // new
						if(!isset($this->criteria[$c])) {
							$this->criteria[$c] = array();
						}
						$this->criteria[$c][$pi] = $p2;
					}
				}
				break;
			default:
				$this->criteria[$c] = $params;
				break;
		}
		
	}
	
	function toggle($c,$p) {
		if(empty($c) || empty($p))
			return;
			
		if(isset($this->criteria[$c])) {
				if(isset($this->criteria[$c][$p])) {
					$this->criteria[$c][$p]++;
					if($this->criteria[$c][$p] > 2)
						$this->criteria[$c][$p] = 0;

				} else {
					$this->criteria[$c][$p] = 1;
				}
		}
	}
	
	function remove($c,$p="",$a="") {
		if(!isset($this->criteria[$c])) {
			return;
		}
		
		// clearing the whole criteria
		if(empty($p) && empty($a)) {
			unset($this->criteria[$c]);
		}
			
		// clearing criteria param
		if(!empty($p) && empty($a)) {
			unset($this->criteria[$c][$p]);
		}
			
		// clearing criteria param arg
		if(!empty($p) && !empty($a)) {
			unset($this->criteria[$c][$p][$a]);
		}
	}
	
};
