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

class CerPop3Accounts extends CerEntityObjectController  {
	
	function CerPop3Accounts() {
		$this->CerEntityObjectController("pop3_accounts","CerPop3Account");
		$this->_addColumn("Id","id","integer");
		$this->_addColumn("Name","name","string");
		$this->_addColumn("Host","host","string");
		$this->_addColumn("Port","port","integer");
		$this->_addColumn("Login","login","string");
		$this->_addColumn("Pass","pass","string");
		$this->_addColumn("Disabled","disabled","integer");
		$this->_addColumn("MaxMessages","max_messages","integer");
		$this->_addColumn("Delete","delete","integer");
		$this->_addColumn("LastPolled","last_polled","integer");
		$this->_addColumn("LockTime","lock_time","integer");
		$this->_addColumn("MaxSize","max_size","integer");
		$this->_validate();
	}
	
}

