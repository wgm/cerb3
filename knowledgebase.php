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
| File: knowledgebase.php
|
| Purpose: The knowledgebase system.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|		Ben Halsted (ben@webgroupmedia.com)		[BGH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/general.php");

require_once (FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndex.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/search/cer_SearchIndexKB.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/utility/text/cer_Whitespace.class.php");

require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");

require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationKbTags.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationKb.class.php");

log_user_who_action(WHO_KNOWLEDGEBASE);

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_KB)) { header("Location: " .cer_href("index.php")); }

//######
$root = ((@$_REQUEST["root"]) ? $_REQUEST["root"] : 0);
//######

$kbid = isset($_REQUEST["kbid"]) ? $_REQUEST["kbid"] : "";
$kb_keywords = isset($_REQUEST["kb_keywords"]) ? $_REQUEST["kb_keywords"] : "";
//@$kb_tag_ids = $_REQUEST["kb_tag_ids"];
@$kb_tag_mode = $_REQUEST["kb_tag_mode"];
$kb_public = isset($_REQUEST["kb_public"]) ? $_REQUEST["kb_public"] : 0;
$search_id = isset($_REQUEST["search_id"]) ? trim($_REQUEST["search_id"]) : "";
$form_submit = isset($_REQUEST["form_submit"]) ? $_REQUEST["form_submit"] : "";
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "browse";
$kb_rating = isset($_REQUEST["kb_rating"]) ? $_REQUEST["kb_rating"] : "";
//$kb_comment = isset($_REQUEST["kb_comment"]) ? $_REQUEST["kb_comment"] : "";
//$poster_email = isset($_REQUEST["poster_email"]) ? $_REQUEST["poster_email"] : "";
//$poster_comment = isset($_REQUEST["poster_comment"]) ? $_REQUEST["poster_comment"] : "";
//$kb_comment_id = isset($_REQUEST["kb_comment_id"]) ? $_REQUEST["kb_comment_id"] : "";
//$kb_comment_edit = isset($_REQUEST["kb_comment_edit"]) ? $_REQUEST["kb_comment_edit"] : "";
//$kb_comment_content = isset($_REQUEST["kb_comment_content"]) ? $_REQUEST["kb_comment_content"] : "";
@$kb_title = $_REQUEST["kb_title"];
@$kb_content = $_REQUEST["kb_content"];

$cer_tpl = new CER_TEMPLATE_HANDLER();
$cerberus_db = cer_Database::getInstance();

//$acl = CerACL::getInstance();

//$kbase_format = new cer_formatting_obj; // ***

$cer_search = new cer_SearchIndexKB();

$cer_tpl->assign('kb_comment_edit',$kb_comment_edit);
$cer_tpl->assign('kb_comment',$kb_comment);

//######
$cer_tpl->assign('root',$root);
//######

$tags = new CerWorkstationKbTags();
$wskb = new CerWorkstationKb();

$cer_tpl->assign('remote_addr',$_SERVER["REMOTE_ADDR"]);

if(isset($_REQUEST["form_submit"])) {
	switch($form_submit)
		{
		case "kb_delete":
			{
			if($acl->has_priv(PRIV_KB_DELETE)) {
				$wskb->deleteArticle($kbid);
			}
			$mode = "browse";
			break;
			}
		
		case "kb_search":
			if($acl->has_priv(PRIV_KB)) {
				$mode = "keyword_results";
			}
			break;
			
		case "kb_edit_entry":
			{
			if($acl->has_priv(PRIV_KB_EDIT)) {
				$article = new CerWorkstationKbArticle();
				$article->article_id = intval($kbid);
				$article->article_title = $kb_title;
				$article->article_public = $kb_public;
				$article->article_content = $kb_content;
				$wskb->saveArticle($article);
				if(empty($kbid)) {
					$kbid = $article->article_id; // create
				} else {
					$cer_search->deleteFromArticle($kbid);
				}
				
				$cer_search->indexSingleArticle($kbid);
				$mode = "view_entry";
			}

			break;
		}
		
	}
}
// ***************************************************************************************************************************

$cer_tpl->assign('mode',$mode);

// ***************************************************************************************************************************
$cer_tpl->assign('session_id',$session->session_id);
$cer_tpl->assign('track_sid',((@$cfg->settings["track_sid_url"]) ? "true" : "false"));
$cer_tpl->assign('user_login',$session->vars["login_handler"]->user_login);

$cer_tpl->assign_by_ref('acl',$acl);
$cer_tpl->assign_by_ref('cfg',$cfg);
$cer_tpl->assign_by_ref('session',$session);

// [JAS]: Do we have unread PMs?
if($session->vars["login_handler"]->has_unread_pm)
	$cer_tpl->assign('unread_pm',$session->vars["login_handler"]->has_unread_pm);

include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTickets.class.php");
$counts = CerWorkstationTickets::getAgentCounts($session->vars['login_handler']->user_id);
$cer_tpl->assign("header_flagged",$counts['flagged']);
$cer_tpl->assign("header_suggested",$counts['suggested']);
	
$urls = array('preferences' => cer_href("my_cerberus.php"),
			  'logout' => cer_href("logout.php"),
			  'home' => cer_href("index.php"),
			  'search_results' => cer_href("ticket_list.php"),
			  'knowledgebase' => cer_href("knowledgebase.php"),
			  'configuration' => cer_href("configuration.php"),
			  'mycerb_pm' => cer_href("my_cerberus.php?mode=messages&pm_folder=ib"),
			  'clients' => cer_href("clients.php"),
			  'reports' => cer_href("reports.php")
			  );
$cer_tpl->assign_by_ref('urls',$urls);

$page = "knowledgebase.php";
$cer_tpl->assign("page",$page);

// ***************************************************************************************************************************

//$cer_tpl->assign_by_ref('kb',$kb);
$cer_tpl->assign_by_ref('wskb',$wskb);
$cer_tpl->assign_by_ref('tags',$tags);

switch($mode) {
	default:
	case "browse": {
		include_once(FILESYSTEM_PATH . "cerberus-api/search/CerSearchBuilder.class.php");
		$searchBuilder =& $session->vars['kbsearch_builder']; /* @var $searchBuilder CerSearchBuilder */
		$params = array("criteria"=>$searchBuilder->criteria);
		$articles = $wskb->getArticlesByParams($params);
		$cer_tpl->assign_by_ref('articles',$articles);
		
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		$kb = new CerKnowledgebase();
		$categories = $kb->getCategories();
		// [JAS]: If root isn't set or is 0, use root.  Otherwise a category branch
		if($root) {
			$kb_root = $categories[$root];
		} else {
			$kb_root = $kb->getRoot();
		}
		$cer_tpl->assign_by_ref('kb',$kb);
		$cer_tpl->assign_by_ref('kb_root',$kb_root);
		
		break;
	}
	case "view_entry": {
		include_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");
		$resource = new CerKnowledgebaseArticle($kbid);
		$resource->reload();
		$resource_tags = $resource->getTags();
		
		$article = $wskb->getArticleById($kbid);
		$cer_tpl->assign_by_ref('article',$article);
		$cer_tpl->assign_by_ref('resource',$resource);
		$cer_tpl->assign_by_ref('tags',$resource_tags);
		
		$relatedIds = $tags->getRelatedArticles($kbid,10);
		if(!empty($relatedIds)) {
			$relatedArticles = $wskb->getArticlesByIds(array_keys($relatedIds),false,true);
			$cer_tpl->assign_by_ref('related_articles',$relatedArticles);
		}
		
		break;
	}
	case "edit_entry": {
		$article = $wskb->getArticleById($kbid);
		$cer_tpl->assign_by_ref('article',$article);
		break;
	}
	case "keyword_results": {
		$articles = $wskb->getArticlesByKeywords($kb_keywords);
		$cer_tpl->assign('kb_keyword_string',$kb_keywords);
		$cer_tpl->assign_by_ref('articles',$articles);
		break;
	}
	case "tag_results": {
		break;
	}
}

$cer_tpl->display('knowledgebase.tpl.php');

//**** WARNING: Do not remove the following lines or the session system *will* break.  [JAS]
if(isset($session) && method_exists($session,"save_session"))
{ $session->save_session(); }
//*********************************
?>
