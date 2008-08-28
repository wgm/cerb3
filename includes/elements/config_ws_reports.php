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
|		Jeff Standen   (jeff@webgroupmedia.com)   [JAS]
|		Mike Fogg 		(mike@webgroupmedia.com)	[MDF]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationReports.class.php");

$reports = CerWorkstationReports::getInstance();
$reportList = $reports->getList(0);

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CONFIG,BITGROUP_1)) {
	die("Permission denied.");
}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php?module=ws_reports" method="post" onsubmit="return confirm('Are you sure?');">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="ws_reports_delete">

<table width="100%" border="0" cellspacing="1" cellpadding="1" bgcolor="#FFFFFF">
  <tr> 
    <td class="boxtitle_orange_glass">Desktop Reports</td>
  </tr>
  
	<?php if($acl->has_priv(PRIV_CONFIG)) { ?>
	<tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
		<td>
			<a href="<?php echo cer_href("configuration.php?module=ws_reports&prid=0"); ?>" class="cer_maintable_subjectLink">Upload a New Report</a><br>
		</td>
	</tr>
	<?php } ?>

	<?php
	if(is_array($reportList))
	foreach($reportList as $report)
	{
		echo '<tr bgcolor="#DDDDDD" class="cer_maintable_text">';
 		echo '<td align="left" bgcolor="#DDDDDD" class="cer_maintable_text">';
				
 		if($acl->has_priv(PRIV_CONFIG)) { 
				echo "<input type=\"checkbox\" name=\"rids[]\" value=\"" . $report->report_id . "\">&nbsp;";
			}
		
			echo "<a href=\"" . cer_href("configuration.php?module=ws_reports&prid=" . $report->report_id) . "\" class=\"cer_maintable_subjectLink\">" . $report->report_title . "</a><br>";
    	echo "</td>";
  		echo "</tr>";
	}
	?>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="left">
			<?php if($acl->has_priv(PRIV_CONFIG)) { ?><input type="submit" class="cer_button_face" value="<?php echo  LANG_WORD_DELETE ?>"><?php } ?>&nbsp;
		</td>
	</tr>
</table>

</form>
<br>
