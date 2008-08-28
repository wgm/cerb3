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
require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTags.class.php");

class CerWorkstationKbTags extends CerWorkstationTags {
	
	var $only_public = 0;
	
	function CerWorkstationKbTags($only_public=0) {
		$this->CerWorkstationTags();
		$this->only_public = $only_public;
		$this->_loadKbTagTotals();
	}
	
	function _loadKbTagTotals() {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = "SELECT count(t.`tag_id`) as hits, t.`tag_id` ".
			"FROM workstation_tags_to_kb t ".
			"INNER JOIN kb ON (t.kb_id=kb.id) ".
			(($this->only_public) ? "WHERE kb.public = 1 " : " ").
			"GROUP BY `tag_id` ";
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$hits = $row['hits'];
				$tag_id = $row['tag_id'];
//				$this->_addArticlesWithRollup($tag_id, $hits);
				$this->tags[$tag_id]->hits = $hits;
			}
		}
	}
	
//	function _addArticlesWithRollup($tag_id, $hits) {
//		$pid = $tag_id;
//		while($node =& $this->tags[$pid]) {
//			$node->num_articles += $hits;
//			$pid = $node->tag_set_id;
//		}
//		
//		$this->root->num_articles += $hits;
//	}
	
};
