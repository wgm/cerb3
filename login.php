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
| File: login.php
|
| Purpose: The login page, handling input and sending to do_login.php path.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/templates/templates.php");

@$redir = $_REQUEST["redir"];
if(!empty($redir)) $redir = urldecode($redir);

$cer_tpl = new CER_TEMPLATE_HANDLER();
$cer_tpl->assign("redir",$redir);

$cer_tpl->display('login.tpl.php');
?>