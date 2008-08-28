<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2003, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
|
| File: core.hash.php
|
| Purpose: Cache queries to reduce redundant database queries
|
| Developers involved with this file:
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/utility/bits/bitflags.php");

define("HASH_USER_ANY_Q",""); // return all users
define("HASH_COMPANY_ALL",""); // return all companies


class CER_HASH_CONTAINER
{
	var $_db;
	var $_objects = array(
						 'companies' => null,
						 'statuses' => null,
						 'users' => null,
						 'views' => null
						 );
						 
	function CER_HASH_CONTAINER()
	{
		$this->_db = cer_Database::getInstance();
	}
	
	function get_user_hash($q_filter=HASH_USER_ANY_Q,$sorting="user_login")
	{
		// [JAS]: If we don't have a hash for this object yet, get one.
		if($this->_objects['users'] == null)
			$this->_objects['users'] = new CER_HASH_USERS($this,$sorting);
			
		$users = $this->_objects['users']->users;
			
		return($users);
	}

	function get_company_hash($q_filter=HASH_COMPANY_ALL)
	{
		// [JAS]: If we don't have a hash for this object yet, get one.
		if($this->_objects['companies'] == null)
			$this->_objects['companies'] = new CER_HASH_COMPANIES($this);
			
		$users = $this->_objects['companies']->companies;
			
		return($users);
	}

	function get_view_hash()
	{
		// [JAS]: If we don't have a hash for this object yet, get one.
		if($this->_objects['views'] == null)
			$this->_objects['views'] = new CER_HASH_VIEWS($this);
			
		$views = $this->_objects['views']->views;
			
		return($views);
	}

    // [JSJ]: Added function to get the string priority hash
    function get_priority_hash()
    {
          // [JSJ]: If we don't have a hash for this object yet, get one.
          if($this->_objects['priorities'] == null)
                  $this->_objects['priorities'] = new CER_HASH_PRIORITIES($this);

          $priorities = $this->_objects['priorities']->priorities;

          return($priorities);
    }

};


class CER_HASH_VIEWS
{
	var $_db;
	var $_parent;
	var $views = array();

	function CER_HASH_VIEWS(&$parent)
	{
		$this->_db = cer_Database::getInstance();
		$this->_parent = &$parent;
     	
		$sql = "SELECT v.view_id,v.view_name FROM ticket_views v ORDER BY v.view_name";
      	$result = $this->_db->query($sql,false);

      	if($this->_db->num_rows($result))
    	{
    		while($vr = $this->_db->fetch_row($result))
    		{
    			$view_item = new CER_HASH_VIEWS_ITEM;
    			$view_item->view_id = $vr[0];
    			$view_item->view_name = stripslashes($vr[1]);
    			$this->views[$view_item->view_id] = $view_item;
    		}
    	}		
	}
	
};


class CER_HASH_VIEWS_ITEM
{
	var $view_id;
	var $view_name;
};


class CER_HASH_COMPANIES
{
	var $_db;
	var $_parent;
	var $companies = array();
	
	function CER_HASH_COMPANIES(&$parent)
	{
    	$this->_db = cer_Database::getInstance();
    	$this->_parent = &$parent;
		
		$sql = "SELECT c.id As company_id, c.name As company_name ".
    		"FROM company c ORDER BY company_name";
    	$result = $this->_db->query($sql);
    	if($this->_db->num_rows($result))
    	{
    		while($cr = $this->_db->fetch_row($result))
    		{
    			$company_item = new CER_HASH_COMPANIES_ITEM;
    			$company_item->company_id = $cr["company_id"];
    			$company_item->company_name = stripslashes($cr["company_name"]);
    			$this->companies[$company_item->company_id] = $company_item;
    		}
    	}		
	}
	
};


class CER_HASH_COMPANIES_ITEM
{
	var $company_id;
	var $company_name;
};


// [JSJ]: Added hash for priority strings
class CER_HASH_PRIORITIES
{
      var $_db;
      var $_parent;
      var $priorities = array();

      function CER_HASH_PRIORITIES(&$parent)
      {
          global $session; // [JSJ]: Clean up
          global $priority_options; // clean
          global $cerberus_translate;
          $this->_db = cer_Database::getInstance();
          $this->_parent = &$parent;

          foreach($priority_options as $priority_id => $priority_name) {
               $this->priorities[$priority_id] = $priority_name;
          }
      }
};


class CER_HASH_USERS
{
	var $_db;
	var $_parent;
	var $users = array();
	
	function CER_HASH_USERS(&$parent,$sorting="user_login")
	{
		global $session;
		$this->_db = cer_Database::getInstance();
		$this->_parent = &$parent;
		$user_cache = array();
		
		$sql = sprintf("SELECT u.user_id, u.user_login, u.user_name FROM  `user` u ORDER BY u.`%s`",
			$sorting
		);
		$u_res = $this->_db->query($sql);
		
		if($this->_db->num_rows($u_res))
		{
			while($u_row = $this->_db->fetch_row($u_res))
			{
				$user_item = new CER_HASH_USERS_ITEM;
				$user_item->user_id = $u_row["user_id"];
				$user_item->user_name = stripslashes($u_row["user_name"]);
				$user_item->user_login = $u_row["user_login"];
				$user_cache[$user_item->user_id] = $user_item;
			}
		}
		
		foreach($user_cache as $idx => $u)
		{
			array_push($this->users,$u);
			unset($user_cache[$idx]);
		}
	}
};


class CER_HASH_USERS_ITEM
{
	var $user_id;
	var $user_name;
	var $user_login;
};


?>