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
|		Mike Fogg 	  (mike@webgroupmedia.com)  [MDF]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_DateTime.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationReports.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CONFIG,BITGROUP_1)) {
	die("Permission denied.");
}

if(!isset($rid)) {
	die("Invalid ID");
}

/* @var $agent CerAgent */
$reports = CerWorkstationReports::getInstance();
$teams = CerWorkstationTeams::getInstance();

$teamList = $teams->getTeams();

if(0 == $rid || empty($rid)) {
	$report = new CerWorkstationReport();
} else {
	$report = $reports->getById($rid);
}

?>

<form action="configuration.php" method="post" name="" enctype="multipart/form-data">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="prid" value="<?php echo $rid ?>">
<input type="hidden" name="module" value="ws_reports">
<input type="hidden" name="form_submit" value="ws_reports_edit">
<?php if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>"; ?>
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_success\">" . LANG_WORD_SUCCESS . "!</span><br>"; ?>

<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#FFFFFF">
<?php
if(0==$rid) {
?>
  <tr> 
    <td class="boxtitle_orange_glass">Upload a New Report</td>
  </tr>
<?php
}
else {
?>
  <tr> 
    <td class="boxtitle_orange_glass">Edit Report: <?php echo $report->report_title; ?></td>
  </tr>
<?php
}
?>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td bgcolor="#DDDDDD" class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
          
        	<tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Report Title:</td>
            <td width="81%">
              <input type="text" name="report_title" size="45" maxlength="255" value="<?php echo htmlentities($report->report_title); ?>">
          </tr>
        	<tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Report File:</td>
            <td width="81%">
            	<?php echo (!empty($report->report_blob)) ? "Exists" : "No report data" ; ?><br>
              <input type="file" name="report_data" size="45">
          </tr>
        	<tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Report Scriptlet:</td>
            <td width="81%">
            	<?php echo (!empty($report->scriptlet_blob)) ? "Exists" : "No scriptlet data" ; ?><br>
              <input type="file" name="report_scriptlet" size="45">
              <span class="cer_footer_text">(optional)</span>
          </tr>
          
          <!--- ACL --->
        	<tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top" nowrap="nowrap">Team Permissions:</td>
            <td width="81%" nowrap="nowrap">
            	<?php
            	if(is_array($teamList))
            	foreach($teamList as $teamId => $team) {
            		?>
            		<label><input type="checkbox" name="report_acl[]" value="<?php echo $teamId; ?>" <?php if($report->teams[$teamId]) echo "CHECKED"; ?>> <?php echo $team->name; ?></label><br>
            		<?php
            	}
            	?>
            </td>
          </tr>
          
        </table>
    </td>
  </tr>
  
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
				<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SUBMIT ?>">
		</td>
	</tr>
</table>
</form>
<br>
