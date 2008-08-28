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
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/cron/CerCron.class.php");

// Verify that the connecting user has access
$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_SCHED_TASKS,BITGROUP_2)) {
	die("Permission denied.");
}

if(!isset($tid)) {
	die("Invalid Scheduled Task ID.");
}	

$cron = new CerCron();
$task = $cron->getTaskById($tid); /* @var $task CerCronTask */

if(empty($task)) {
	$task = new CerCronTask();
}

$scriptList = array();
if ($handle = opendir(FILESYSTEM_PATH . "includes/cron/")) {
    while (false !== ($file = readdir($handle))) { 
    	switch(strtolower($file)) {
    		case ".":
    		case "..":
    		case ".svn":
    		case "cvs":
    			break;
    		default:
    			if(substr($file,-4) == ".php")
    				$scriptList[] = $file;
    			break;
    	}
    }
    @closedir($handle);
}

?>

<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="ptid" value="<?php echo $tid; ?>">
<input type="hidden" name="module" value="cron_tasks">
<input type="hidden" name="form_submit" value="cron_tasks_edit">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . "Scheduled Task Saved!" . "</span><br>"; ?>

<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#FFFFFF">
<?php
if(0==$tid) {
?>
  <tr>
    <td class="boxtitle_orange_glass">New Scheduled Task</td>
  </tr>
<?php
}
else {
?>
  <tr> 
    <td class="boxtitle_orange_glass">Edit Scheduled Task: <?php echo $task->getTitle(); ?></td>
  </tr>
<?php
}
?>
  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <td bgcolor="#EEEEEE" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="2" bgcolor="#FFFFFF">
          <tr bgcolor="#EEEEEE"> 
            <td width="0%" nowrap="nowrap" class="cer_maintable_heading">Enabled:</td>
            <td width="100%" class="cer_maintable_text">
            	<label><input type="radio" name="task_enabled" value="1" <?php echo ($task->getEnabled() ? "CHECKED" : ""); ?>> <?php echo LANG_WORD_TRUE; ?></label> 
            	<label><input type="radio" name="task_enabled" value="0" <?php echo (!$task->getEnabled() ? "CHECKED" : ""); ?>> <?php echo LANG_WORD_FALSE; ?></label>
          </tr>
          
          <tr bgcolor="#EEEEEE"> 
            <td width="0%" nowrap="nowrap" class="cer_maintable_heading">Title:</td>
            <td width="100%">
              <input type="text" name="task_title" size="32" maxlength="64" value="<?php echo htmlspecialchars($task->getTitle()); ?>">
            </td>
          </tr>
          
          <tr bgcolor="#EEEEEE"> 
            <td width="0%" nowrap="nowrap" class="cer_maintable_heading">Script:</td>
            <td width="100%">
            	<select name="task_script">
            		<?php
            		if(is_array($scriptList))
            		foreach($scriptList as $script) {
            			$sel = false;
            			if(0 == strcasecmp($script,$task->getScript()))
            				$sel = true;
            		?>
            		<option value="<?php echo $script; ?>" <?php echo ($sel) ? "SELECTED":""; ?>><?php echo $script; ?>
            		<?php } ?>
            	</select>
              <span class="cer_footer_text">(in directory <b>includes/cron/</b>)</span>
            </td>
          </tr>
          
          <tr>
          	<td class="boxtitle_green_glass" colspan="2">Task Schedule:</td>
          </tr>
          
          <tr bgcolor="#EEEEEE"> 
            <td width="0%" nowrap="nowrap" class="cer_maintable_heading" valign="top">Day:</td>
            <td width="100%" class="cer_maintable_text">
            	<select name="task_day">
	            	<?php
	            	// Options
	            	$day_opts = array();
	            	$day_opts["*"] = "Every Day";
	         		$day_opts["w0"] = "Every Sunday";
	         		$day_opts["w1"] = "Every Monday";
	         		$day_opts["w2"] = "Every Tuesday";
	         		$day_opts["w3"] = "Every Wednesday";
	         		$day_opts["w4"] = "Every Thursday";
	         		$day_opts["w5"] = "Every Friday";
	         		$day_opts["w6"] = "Every Saturday";
	         		for($x=1;$x<=31;$x++) {
	         			$str = sprintf("%02d",$x);
	         			$day_opts["d".$x] = $str;
	         		}
	         		
	         		// Sel
	         		if($task->getDayOfWeek() != "*") {
	         			$sel_opt = "w" . $task->getDayOfWeek();
	         		} else if($task->getDayOfMonth() != "*") {
	         			$sel_opt = "d" . $task->getDayOfMonth();
	         		} else {
	         			$sel_opt = "*";
	         		}
         		
            		foreach($day_opts as $opt_key => $opt_val) {
            			$sel = (0==strcmp($opt_key,$sel_opt)) ? "SELECTED" : "";
            			echo "<option value=\"$opt_key\" $sel>$opt_val\r\n";
            		}
            		?>
            	</select>
            	<br>
            </td>
          </tr>
          
          <tr bgcolor="#EEEEEE"> 
            <td width="0%" nowrap="nowrap" class="cer_maintable_heading" valign="top">Hour:</td>
            <td width="100%" class="cer_maintable_text">
            	<select name="task_hour">
            		<?php
	            	// Options
	            	$hr_opts = array();
	            	$hr_opts["*"] = "Every Hour";
            		for($x=0;$x<24;$x++) {
	         			$str = sprintf("%02d",$x);
	         			$hr_opts["".$x] = $str;
            		}
            		
            		// Sel
            		if($task->getHour() == "*") {
            			$sel_opt = "*";
            		} else { 
            			$sel_opt = $task->getHour();
            		}
            		
            		foreach($hr_opts as $opt_key => $opt_val) {
            			$sel = (0==strcmp($opt_key,$sel_opt)) ? "SELECTED" : "";
            			echo "<option value=\"$opt_key\" $sel>$opt_val\r\n";
            		}
            		?>
            	</select>
            </td>
          </tr>

          <tr bgcolor="#EEEEEE"> 
            <td width="0%" nowrap="nowrap" class="cer_maintable_heading" valign="top">Minute:</td>
            <td width="100%" class="cer_maintable_text">
            	<select name="task_minute">
            		<?php
	            	// Options
	            	$min_opts = array();
	            	$min_opts["*/1"] = "Every Minute";
	            	$min_opts["*/5"] = "Every 5 Minutes";
	            	$min_opts["*/10"] = "Every 10 Minutes";
	            	$min_opts["*/15"] = "Every 15 Minutes";
	            	$min_opts["*/20"] = "Every 20 Minutes";
	            	$min_opts["*/30"] = "Every 30 Minutes";
	            	
            		for($x=0;$x<=59;$x++) {
	         			$str = sprintf("%02d",$x);
	         			$min_opts["".$x] = $str;
            		}
            		
            		// Sel
           			$sel_opt = $task->getMinute();

            		foreach($min_opts as $opt_key => $opt_val) {
            			$sel = (0==strcmp($opt_key,$sel_opt)) ? "SELECTED" : "";
            			echo "<option value=\"$opt_key\" $sel>$opt_val\r\n";
            		}
            		?>
            	</select>
            	<br>
            </td>
          </tr>
          
		</table>
		</td>
	</tr>

	<tr bgcolor="#CCCCCC" class="cer_maintable_text">
		<td align="right">
			<input type="submit" class="cer_button_face" value="<?php echo  LANG_BUTTON_SAVE; ?>">
		</td>
	</tr>
</table>
</form>
<br>
