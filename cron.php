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
| 		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|       http://www.cerberusweb.com        http://www.webgroupmedia.com/
***********************************************************************/

define("NO_SESSION",true); // [JAS]: Leave this true
define("CER_CRON_RUNTIME",true); // [JAS]: Leave this true

@set_time_limit(600); // try to give ourselves plenty of time to run

require_once("site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/cron/CerCron.class.php");

$cron = new CerCron();
$verbose = !empty($_REQUEST['verbose']) ? 1 : 0;
// [ddh]: to further protect the force-check, you can set the "true" string to a secret password.
$force = ($_REQUEST['force'] == "true") ? 1 : 0;

// [JAS]: Ip Security Check
if(!$cron->isValidIp($_SERVER['REMOTE_ADDR'])) {
	echo sprintf("Cerberus [ERROR]: Your IP %s is not authorized to run scheduled tasks.  Please notify your administrator.",
		$_SERVER['REMOTE_ADDR']
	);
	exit;
}

// [JAS]: Collision check
if(($cron->getLockTime() + (5*60)) > mktime()) {
	echo "Locked by another process.<br>";
	exit;
}

if(is_array($cron->tasks))
foreach($cron->tasks as $task) { /* @var $task CerCronTask */
	if(!$task->getEnabled())
		continue;
		
	if($task->getNextRuntime() < mktime() || $force == 1) {
		if($verbose) echo "I need to run '" . $task->getTitle() . "'.<BR>";
		if($verbose) echo "Running " . $task->getScript() . "...<BR>";
		$cronScript = FILESYSTEM_PATH . "includes/cron/" . str_replace(array('\\','/'),"",$task->getScript()); // protect against paths
		if(file_exists($cronScript))
			include_once($cronScript);
		$nextDate = $cron->calculateNextRuntime($task);
		if($verbose) echo "I will run '" . $task->getTitle() . "' again on " . date("Y-m-d H:i",$nextDate) . " (from " . date("Y-m-d H:i",mktime()) . ")<br>";
		$task->setNextRuntime($nextDate);
		$task->setLastRuntime(mktime());
		$cron->saveTask($task);
	} else {
		if($verbose) echo "I don't need to run '" . $task->getTitle() . "' until " . date("Y-m-d H:i",$task->getNextRuntime()) . " .<BR>";
	}
}
