<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_StripHTML.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/security/CerSecurityUtils.class.php");

/** brief Search creation tools for KB Problems
 *
 *	Classes and methods for search indexing KB Article problem text
 *
 *	\file cer_SearchKB.class.php
 *	\author Ben Halsted, WebGroup Media LLC. <ben@webgroupmedia.com>
 *	\date 2004
 *
 */

/** @addtogroup search
 *
 * @{
 */
 
/** Class for indexing KB problems
 *
 *	This class is used for indexing the problem text of knowledgebase articles.
 */
class cer_SearchIndexKB extends cer_SearchIndex {
	
	/** Constructor
	 *
	 *	Constructer used to fill in the data in the structure.
	 */
	function cer_SearchIndexKB() {
		$this->cer_SearchIndex();
	}
	
	/** Index a single kb article problem
	 *
	 *	Index the problem text of a kb article. 
	 *	\param $kbid The knowledgebase article ID we are going to index
	 *	\return true
	 */	
	function indexSingleArticle($kbid) {
		$sql = sprintf("SELECT `kb`.`title`,c.`content` ".
			"FROM `kb` ".
			"INNER JOIN `kb_content` c ON (`kb`.`id`=c.`kb_id`) ".
			"WHERE `kb`.`id`=%d",
				$kbid
		);
		$content = $this->db->query($sql);

		if($this->db->num_rows($content) && $text = $this->db->fetch_row($content)) {
			$striphtml = new cer_StripHTML();
			
			// get the text
			$string = stripslashes($text["content"]);
			$string = $striphtml->strip_html($string);
			
			$string .= " " . stripslashes($text["title"]); // $text["kb_keywords"]
			
			// standard cleanups/indexing
			$this->indexWords($string);
			$this->removeExcludedKeywords();
			$this->saveWords();
			$this->loadWordIDs();

			// save the indexes to this KB article
			$this->_saveToKB($kbid);
		}

		return true;		
	}

	/** PRIVATE - Saves search IDs to a KB ID
	 *
	 *	Save the search ids in the internal array to the database.
	 *	\param $kb_id The knowledgebase article ID you want to save the indexes to
	 *	\return Nothing
	 *  \see indexSingleArticle
	 */
	function _saveToKB($kb_id=0) {
		if($kb_id && is_array($this->wordarray) && 0<count($this->wordarray)) {
			
			$sql = "INSERT IGNORE INTO search_index_kb (word_id, kb_article_id) VALUES ";
			$sets = array();
			
			$word_ids = array_values($this->wordarray);
			CerSecurityUtils::integerArray($word_ids);
			
			foreach($word_ids as $wi=>$w) {
				$sets[] = sprintf("(%d,%d)",
					$w,
					$kb_id
				);
			}

			$sql .= implode(',', $sets);
			$this->db->query($sql);
		}		
		
	}
	
	/** Delete indexes from KB article.
	 *
	 *	Deletes the indexes associated with a knowledgebase article.
	 *	\param $kb_id The KB article ID you want to delete the indexes from
	 *	\return Nothing
	 *  \see indexSingleArticle
	 */
	function deleteFromArticle($kb_id=0) {
		if(0<$kb_id) {
			$sql = sprintf("DELETE FROM `search_index_kb` WHERE `kb_article_id`=%d",
				$kb_id
			);
			$this->db->query($sql);
		}
	}

	/** Index a range of Articles
	 *
	 *	\return true
	 */
	function reindexArticles($from=0,$count=0)
	{
		$to = $from+$count;
		$sql = sprintf("SELECT kb.id ".
			"FROM `kb` ".
			"WHERE kb.id >= %d ".
			"AND kb.id <= %d ".
			"ORDER BY kb.id ASC",
				$from,
				$to
		);
		$rows = $this->db->query($sql);
		if($this->db->num_rows($rows)) {
			while(null!=($row = $this->db->fetch_row($rows))) {
				$kb_id = $row['id'];
				$this->indexSingleArticle($kb_id);
			}
		}
		return true;
	}
	
	
};

/** @} */

?>