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
| File: config_queues_edit.php
|
| Purpose: The configuration include that facilitates the queue creation and
|			modification of properties.
|
| Developers involved with this file: 
|		Ben Halsted (ben@webgroupmedia.com) [BGH]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_QUEUES_DELETE,BITGROUP_2)) {
	die("Permission denied.");
}

if(!isset($qids))
{ echo LANG_CONFIG_QUEUE_EDIT_NOID; exit(); }	

$queue_info = array();

if(0 != sizeof($qids))
{ 
	$sql = "SELECT q.queue_id, q.queue_name " .
  		" FROM queue q ".
  		" ORDER BY q.queue_name ASC";
  	$result = $cerberus_db->query($sql);
  
  	if($cerberus_db->num_rows($result)==0)
  	{	
  		echo LANG_CERB_ERROR_ACCESS;
  		exit();
  	}

  	while($queue_data = $cerberus_db->fetch_row($result))
  		$queue_info[$queue_data["queue_id"]] = $queue_data["queue_name"];
}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post" onsubmit="return confirm('<?php echo  LANG_CONFIG_QUEUE_WARNING ?>');">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="queues">
<input type="hidden" name="form_submit" value="queues_delete">
<table width="98%" border="0" cellspacing="1" cellpadding="2" bordercolor="B5B5B5">
  <tr class="boxtitle_green_glass">
    <td><span class="text_title_white">Mailbox Delete</span></td>
  </tr>
  <tr bgcolor="#DDDDDD" class="cer_maintable_text"> 
    <td  bgcolor="#DDDDDD" class="cer_maintable_text"> 
      <div align="right" class="cer_maintable_text"></div>
      <div align="left">
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr> 
            <td width="19%" class="cer_maintable_heading">Deleting mailboxes:</td>
            <td width="81%" class="cer_maintable_text">
							<?php
							foreach ($queue_info as $key => $q) {
								if(isset($qids[$key])) {
									echo "<input type=\"checkbox\" name=\"qids[]\" value=\"" . $key . "\" checked> " . $q . "<br>";
								}
							}
							?>
          	</td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading">&nbsp;</td>
            <td width="81%">&nbsp;</td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading" colspan="2">Which mailbox would you like to move the existing tickets to?</td>
          </tr>
          <tr> 
            <td width="19%" class="cer_maintable_heading">&nbsp;</td>
            <td width="81%" class="cer_maintable_text">
							<?php
							$dstqcount=0;
							foreach ($queue_info as $key => $q) {
								if(!isset($qids[$key])) {
									$dstqcount++;
									$checked = "";
									if($dstqcount==1) {
										$checked = " checked";
									}
									echo "<label><input type=\"radio\" name=\"destination_queue\" value=\"" . $key . "\"" . $checked . "> " . $q . "</label><br>";
								}
							}
							if(0==$dstqcount) {
								echo "There are no mailboxes to move the tickets to. Cannot delete mailbox.";
							}
							?>
          </tr>
          <tr> 
            <td colspan="2" class="cer_maintable_heading">&nbsp;</td>
          </tr>
        </table>
      </div>
    </td>
  </tr>
	<tr bgcolor="#B0B0B0" class="cer_maintable_text">
		<td align="right">
				<?php
				if(0!=$dstqcount) {
					echo "<input type=\"submit\" class=\"cer_button_face\" value=\"" . LANG_BUTTON_SUBMIT . "\">";
				}
				?>
		</td>
	</tr>
</table>
</form>
<br>
