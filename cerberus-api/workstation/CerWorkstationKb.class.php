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

class CerWorkstationKb {
	
	var $tags = array();
	var $only_public = 0;
	
	/**
	* @return CerWorkstationKb
	* @desc 
	*/
	function CerWorkstationKb($only_public=0) {
		$this->only_public = $only_public;
	}

	function getArticlesByTags($tag_ids) {
		if(!is_array($tag_ids))
			return FALSE;
		
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$articles = array();
		
		$tag_mode = "spec";
		foreach($tag_ids as $tag_id) {
			if($tag_id == '0') {
				$tag_mode = "none";
				break;
			}
			if($tag_id == '*') {
				$tag_mode = "any";
				break;
			}
		}
		
		if($tag_mode == "any") { // all
			$where_sql = "WHERE 1 ";
		} elseif($tag_mode == "none") { // none
			$where_sql = "WHERE t.`tag_id` IS NULL ";
		} else { // specific
			$where_sql = "WHERE t.`tag_id` IN (%s) ";
		}
		
		$sql = sprintf("SELECT count(t.`kb_id`) as matches, `kb`.`id`, `kb`.`title`, kb.`rating`,kb.`votes`,kb.`public`,kb.`views` ".
			"FROM `kb` ".
			"LEFT JOIN `workstation_tags_to_kb` `t` ON (t.`kb_id`=kb.`id`) ".
			$where_sql .
			(($this->only_public) ? "AND kb.public = 1 " : " ").
			"GROUP BY kb.`id` ".
			"ORDER BY matches DESC",
				implode(',', $tag_ids)
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$article = new CerWorkstationKbArticle();
				$article->article_id = intval($row['id']);
				$article->article_title = stripslashes($row['title']);
				$article->article_public = intval($row['public']);
				$article->article_views = intval($row['views']);
				$article->article_rating = sprintf("%01.1f",$row['rating']);
				$article->article_votes = intval($row['votes']);
				$article->article_relevance = min(100,floor(100 * ($row['matches']/count($tag_ids)))); // no over 100
				$articles[$article->article_id] = $article;
			}

			$this->_addTagsToKbHeaders($articles);
		}
		
		return $articles;
	}
	
	function getArticlesByParams($params) {
		include_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexKB.class.php");
		
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
//		print_r($params);
		
		// ==========================================
		// TAGS
		// ==========================================
		$tag_sql = false; // phew
		if(isset($params['criteria']['workflow']) && count($params['criteria']['workflow']['tags'])) {
			$tag_ary = array();
			if(!empty($params['criteria']['workflow']['tags_match']) && $params['criteria']['workflow']['tags_match']==1) { // MATCH ALL
				if(is_array($params['criteria']['workflow']['tags']))
				foreach($params['criteria']['workflow']['tags'] as $tagId => $tag)	{
					$tag_ary[] = sprintf("INNER JOIN workstation_tags_to_kb tkb%d ON (tkb%d.kb_id = k.id AND tkb%d.tag_id=%d) ",
						$tagId,
						$tagId,
						$tagId,
						$tagId
					);
				}
				$tag_sql = implode('',$tag_ary);
			} elseif(!empty($params['criteria']['workflow']['tags_match']) && $params['criteria']['workflow']['tags_match']==2) { // MATCH NONE
				$heapTable = sprintf("not_tags");
				
				$db->query(sprintf("DROP TABLE IF EXISTS `not_tags%d`",$user_id));
				
				$db->query(sprintf("CREATE TABLE `not_tags%d` (`kb_id` BIGINT(20) unsigned NOT NULL, PRIMARY KEY (kb_id)) TYPE=HEAP ",$user_id));
				
				$union_sql = sprintf("INSERT INTO `not_tags%d` SELECT tkb.kb_id FROM workstation_tags_to_kb tkb WHERE tkb.tag_id IN (%s) GROUP BY tkb.kb_id",
					$user_id,
					implode(',', array_keys($params['criteria']['workflow']['tags']))
				);
				$db->query($union_sql);
				
				$tag_sql = sprintf("LEFT JOIN `not_tags%d` ntu ON (k.id = ntu.kb_id) ",
					$user_id
				);
				$tag_where_sql = sprintf("AND ntu.kb_id IS NULL ",$user_id);
			} else { // MATCH ANY
				$tag_sql = sprintf("INNER JOIN workstation_tags_to_kb tkb ON (tkb.kb_id = k.id AND tkb.tag_id IN (%s)) ",
					implode(',', array_keys($params['criteria']['workflow']['tags']))
				);
			}
		}
		
		$keyword_sql = false;
		if(isset($params['criteria']['keyword'])) {
			include_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexKB.class.php");
			$findStr = $params['criteria']['keyword']['keyword'];
			
			$kbIdx = new cer_SearchIndexKB();
			$kbIdx->indexWords($findStr,1,1);
			$kbIdx->loadWordIDs(1);
			
			$word_ids = "0";
			if(!empty($kbIdx->wordarray)) {
				$keyword_sql = sprintf("INNER JOIN `search_index_kb` i ON (i.kb_article_id=k.id AND i.word_id IN (%s)) ",
					implode(',', $kbIdx->wordarray)
				);
			} else {
				$keyword_sql = sprintf("INNER JOIN `search_index_kb` i ON (i.kb_article_id=k.id AND i.word_id IN (-1)) ");
			}
					
		}
		
		// ==========================================
		// BASE SQL
		// ==========================================
		$base_sql = sprintf("SELECT %s k.id, k.title, k.public, k.rating, k.votes, k.views ".
					"FROM (kb k) ".
					(($keyword_sql) ? $keyword_sql : "") .
					(($tag_sql) ? $tag_sql : "") .
					"WHERE 1 ".
					((!empty($tag_where_sql)) ? $tag_where_sql : "").
					" GROUP BY k.id ".
					" %s ",
				(($keyword_sql) ? "count(i.`kb_article_id`) as matches," : ""),
				(($keyword_sql) ? "ORDER BY matches DESC" : "")
			);

		$res = $db->query($base_sql);

//		print_r($base_sql);

		$articles = array();
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$article = new CerWorkstationKbArticle();
				$article->article_id = intval($row['id']);
				$article->article_title = stripslashes($row['title']);
				$article->article_public = intval($row['public']);
				$article->article_views = intval($row['views']);
				$article->article_rating = sprintf("%01.1f",$row['rating']);
				$article->article_votes = intval($row['votes']);
				$article->article_relevance = (isset($row['matches'])) ? min(100,floor(100 * $row['matches'] / count($kbIdx->wordarray))) . '%' : '--';
				$articles[$article->article_id] = $article;
			}
			
			$this->_addTagsToKbHeaders($articles);
		}
		
//		print_r($articles);
		
		return $articles;
	}
	
	// [JAS]: [TODO] This should allow paging
	function getArticlesByKeywords($str,$only_in_cats=array()) {
		include_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexKB.class.php");
		
		$db = cer_Database::getInstance(); /* @var $db cer_Database */
		
		$kbIdx = new cer_SearchIndexKB();
		$kbIdx->indexWords($str,1,1);
		$kbIdx->loadWordIDs(1);
		
		$articles = array();

		$word_ids = "0";
		if(!empty($kbIdx->wordarray)) {
			$word_ids = implode(',', $kbIdx->wordarray);
		}
		
		$sql = sprintf("SELECT count(i.`kb_article_id`) as matches, `kb`.`id`, `kb`.`title`, kb.`rating` ,kb.`votes`, kb.`public`,kb.`views` ".
			"FROM `search_index_kb` i ".
			"INNER JOIN `kb` ON (`kb`.`id`=i.`kb_article_id`) ".
			((!empty($only_in_cats)) ? "INNER JOIN `kb_to_category` ktc ON (ktc.kb_id=kb.id) " : " ").
			"WHERE i.`word_id` IN (%s) ".
			(($this->only_public) ? "AND kb.public = 1 " : " ").
			((!empty($only_in_cats)) ? sprintf("AND ktc.kb_category_id IN (%s) ",implode(',',$only_in_cats)) : " ").
			"GROUP BY kb.`id` ".
			"ORDER BY matches DESC",
				$word_ids
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$article = new CerWorkstationKbArticle();
				$article->article_id = intval($row['id']);
				$article->article_title = stripslashes($row['title']);
				$article->article_public = intval($row['public']);
				$article->article_views = intval($row['views']);
				$article->article_rating = sprintf("%01.1f",$row['rating']);
				$article->article_votes = intval($row['votes']);
				$article->article_relevance = min(100,floor(100 * $row['matches'] / count($kbIdx->wordarray))); // no over 100				
				$articles[$article->article_id] = $article;
			}

			$this->_addTagsToKbHeaders($articles);
		}
		
		return $articles;
	}
	
	function deleteArticle($article_id) {
		if(empty($article_id))
			return;
		
		include_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexKB.class.php");
		
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$kbIdx = new cer_SearchIndexKB();
			
		$sql = sprintf("DELETE FROM kb WHERE id = %d", $article_id);
		$db->query($sql);
		
		$sql = sprintf("DELETE FROM kb_to_category WHERE kb_id = %d", $article_id);
		$db->query($sql);
	
		$sql = sprintf("DELETE FROM kb_content WHERE kb_id = %d", $article_id);
		$db->query($sql);
	
		$sql = sprintf("DELETE FROM kb_ratings WHERE kb_article_id = %d", $article_id);
		$db->query($sql);
	
		$sql = sprintf("DELETE FROM kb_comments WHERE kb_article_id = %d", $article_id);
		$db->query($sql);
		
		$sql = sprintf("DELETE FROM workstation_tags_to_kb WHERE kb_id = %d", $article_id);
		$db->query($sql);
		
		$kbIdx->deleteFromArticle($article_id);
	}
	
	function _addTagsToKbHeaders(&$articles) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(!is_array($articles))
			return FALSE;
		
		$sql = sprintf("SELECT t.`kb_id`, t.`tag_id`, wt.`tag_name` ".
			"FROM `workstation_tags_to_kb` t ".
			"INNER JOIN `workstation_tags` wt USING (`tag_id`) ".
//			"LEFT JOIN `workstation_tag_sets` ts ON (wt.tag_set_id = ts.id) ".
			"WHERE t.`kb_id` IN (%s) ".
			"ORDER BY wt.`tag_name` ",
			implode(',', array_keys($articles))
		);
		//echo $sql;exit();
		$tag_res = $db->query($sql);
		
		if($db->num_rows($tag_res)) {
			while($tag_row = $db->fetch_row($tag_res)) {
				$articles[$tag_row['kb_id']]->tags[$tag_row['tag_id']]->name = stripslashes($tag_row['tag_name']);
			}
		}
		
		return TRUE;
	}
	
	function _addCategoriesToKbHeaders(&$articles) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(!is_array($articles))
			return FALSE;
		
		$sql = sprintf("SELECT ktc.kb_id,c.id,c.name ".
			"FROM `kb_to_category` ktc ".
			"INNER JOIN `kb_category` c ON (ktc.kb_category_id=c.id) ".
			"WHERE ktc.kb_id IN (%s) ".
			"GROUP BY ktc.kb_id,ktc.kb_category_id ",
			implode(',', array_keys($articles))
		);
		$cat_res = $db->query($sql);
		
		if($db->num_rows($cat_res)) {
			while($cat_row = $db->fetch_row($cat_res)) {
				$articles[$cat_row['kb_id']]->categories[$cat_row['id']]->name = stripslashes($cat_row['name']);
			}
		}
		
		return TRUE;
	}
	
	function getArticlesByIds($ids,$with_content=true,$preserve_order=false) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$articles = array();
		
		$sql = sprintf("SELECT kb.`id`, kb.`title`, kb.`rating`,kb.`votes`,kb.`public`, kbc.`content`,kb.`views` ".
			"FROM `kb` ".
			"INNER JOIN `kb_content` kbc ON (kb.`id`=kbc.`kb_id`) ".
			"WHERE `kb`.`id` IN (%s) ".
			(($this->only_public) ? "AND kb.public = 1 " : " "),
				implode(',', $ids)
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res)) {
			while($row = $db->fetch_row($res)) {
				$article = new CerWorkstationKbArticle();
				$article->article_id = intval($row['id']);
				$article->article_title = stripslashes($row['title']);
				$article->article_public = intval($row['public']);
				$article->article_views = intval($row['views']);
				$article->article_rating = sprintf("%01.1f",$row['rating']);
				$article->article_votes = intval($row['votes']);
				$article->article_relevance = 100;
				if($with_content) $article->article_content = stripslashes($row['content']);
	
				$articles[$article->article_id] = $article;
			}

			$this->_addTagsToKbHeaders($articles);
			$this->_addCategoriesToKbHeaders($articles);
		}
		
		if($preserve_order) {
			$tmp_articles = array();
			
			foreach($ids as $idx=>$id) {
				if(!isset($articles[$id]))
					continue;
				$tmp_articles[$id] = $articles[$id];
			}
			
			$articles = $tmp_articles;
			unset($tmp_articles);
		}
		
		return $articles;
	}
	
	function getArticleById($id) {
		$articles = $this->getArticlesByIds(array($id));
		return @$articles[$id];
	}
	
	function addTagsToArticleId($tags, $article_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		if(!is_array($tags) || empty($article_id))
			return FALSE;
		
		foreach($tags as $tag_id) {
			$sql = sprintf("INSERT INTO `workstation_tags_to_kb` (`kb_id`,`tag_id`) ".
				"VALUES (%d,%d)",
					$article_id,
					$tag_id
			);
			$db->query($sql);
		}

		return TRUE;
	}
	
	function removeTagsFromArticleId($tags, $article_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(!is_array($tags) || empty($article_id))
			return FALSE;
		
		$sql = sprintf("DELETE FROM `workstation_tags_to_kb` WHERE `kb_id` = %d AND `tag_id` IN (%s)",
			$article_id,
			implode(',', $tags)
		);
		$db->query($sql);
		
		return TRUE;
	}
	
	function clearTagsFromArticleId($article_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		if(empty($article_id))
			return FALSE;

		$sql = sprintf("DELETE FROM `workstation_tags_to_kb` WHERE `kb_id` = %d",
			$article_id
		);
		$db->query($sql);
			
		return TRUE;
	}
	
	function saveArticle(&$article) {
		/* @var $article CerWorkstationKbArticle */

		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		if(!is_a($article,"cerworkstationkbarticle"))
			return FALSE;

		if(empty($article->article_id)) {
			// insert
			$sql = sprintf("INSERT INTO `kb` (`title`) VALUES ('')");
			$res = $db->query($sql);
			$article->article_id = $db->insert_id();
		}
		
		// article
		$sql = sprintf("UPDATE `kb` ".
			"SET `title`=%s,`public`=%d ".
			"WHERE `id` = %d ",
				$db->escape($article->article_title),
				$article->article_public,
				$article->article_id
		);
		$res = $db->query($sql);
		
		// content
		$sql = sprintf("REPLACE INTO `kb_content` (`kb_id`,`content`) ".
			"VALUES (%d,%s) ",
				$article->article_id,
				$db->escape($article->article_content)
		);
		$res = $db->query($sql);
		
		return TRUE;
	}
	
	function ipHasVoted($ip,$article_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$sql = sprintf("SELECT `kb_article_id` FROM `kb_ratings` WHERE `ip_addr` = %s AND `kb_article_id` = %d",
			$db->escape($ip),
			$article_id
		);
		$res = $db->query($sql);
		
		if($db->num_rows($res))
			return TRUE;
			
		return FALSE;
	}
	
	function addArticleRating($article_id,$rating,$ip) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();

		$rating = min(intval($rating),5); // don't let people cheat
		$rating = max($rating,0); // no negatives
		
		if(!$this->ipHasVoted($ip,$article_id)) {
			$sql = sprintf("INSERT INTO `kb_ratings` (`kb_article_id`,`ip_addr`,`rating`) ".
				"VALUES (%d,%s,%d) ",
					$article_id,
					$db->escape($ip),
					intval($rating)
			);
			$db->query($sql);
			
			$sql = sprintf("SELECT AVG(`rating`) as avg_rating, COUNT(`rating`) as num_ratings ".
				"FROM `kb_ratings` WHERE `kb_article_id` = %d",
					$article_id
			);
			$res = $db->query($sql);
			
			if($row = $db->grab_first_row($res)) {
				$sql = sprintf("UPDATE `kb` SET `votes` = %d, `rating`='%01.1f' WHERE `id` = %d",
					$row['num_ratings'],
					$row['avg_rating'],
					$article_id
				);
				$db->query($sql);
			}
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	function addArticleView($article_id) {
		/* @var $db cer_Database */
		$db = cer_Database::getInstance();
		
		$sql = sprintf("UPDATE `kb` SET `views` = `views` + 1 WHERE `id` = %d",
			$article_id
		);
		$db->query($sql);
	}
	
};

class CerWorkstationKbArticle {
	var $article_id;
	var $article_title;
	var $article_public;
	var $article_rating;
	var $article_votes;
	var $article_views;
	var $article_content;
	var $article_relevance;
	var $tags = array();
	var $categories = array();
};