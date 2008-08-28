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

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_PARSER_FAILED,BITGROUP_2)) {
	die("Permission denied.");
}

$sql = "SELECT count(*) as fail_count FROM `parser_fail_headers` ";
$res = $cerberus_db->query($sql);
$total = $cerberus_db->grab_first_row($res);

$sql = "SELECT `id`,`header_from`,`header_to`,`header_subject`,`date_created`,`error_msg`,`message_size` ".
	"FROM `parser_fail_headers` ".
	"ORDER BY `date_created` DESC ".
	"LIMIT 0,50";
$fail_res = $cerberus_db->query($sql);
$showing = $cerberus_db->num_rows($fail_res);

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>

<table width="98%" border="0" cellspacing="1" cellpadding="2">
  <tr> 
    <td class="boxtitle_orange_glass">Parser Fail Queues</td>
  </tr>

  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <td align="left" bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <b>This section only applies to the default POP3 parser.</b> This is a list of e-mail messages which were rejected by your e-mail parser.  If you feel a message was rejected in 
    error you may choose to manually parse it.  Otherwise you can quickly delete obvious junk/spam.  Clicking the column title for Delete/Ignore 
    will quickly choose that option for every row.
    </td>
  </tr>
  <tr>
  	<td>&nbsp;</td>
  </tr>

<form action="configuration.php?module=parser_fails" name="parserFailForm" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="form_submit" value="parser_fails">
  <tr> 
    <td class="boxtitle_green_glass" align="left"> 
  		Rejected E-mail Messages <?php echo sprintf("(showing %d of %d)", $showing, $total["fail_count"]); ?>
    </td>
  </tr>
  
  <tr bgcolor="#EEEEEE" class="cer_maintable_text"> 
    <td align="left" bgcolor="#BBBBBB" class="cer_maintable_text"> 

    <script>
    function failSelectAll(mode) {
		for(e = 0;e < document.parserFailForm.elements.length; e++) {
			if(document.parserFailForm.elements[e].type == 'radio') {
				if(document.parserFailForm.elements[e].value==mode)
					document.parserFailForm.elements[e].checked = true;
			}
		}
    }
    
    function showFailSource(id) {
		window.open(formatURL('<?php echo $cfg->settings["http_server"] . $cfg->settings["cerberus_gui_path"]; ?>/popup_failed_message_source.php?id=' + id),"showFailMsgSource<?php echo mktime(); ?>","width=500,height=600,resizable=yes"); 
    }
    </script>
    
    	<table cellpadding="3" cellspacing="1" width="100%" bgcolor="#DDDDDD">
    		<tr bgcolor="#DDDDDD">
    			<th class="boxtitle_gray_glass" width="100%" align="left">Headers</th>
    			<th class="boxtitle_gray_glass" width="0%" nowrap="nowrap"><a href="javascript:failSelectAll(3);" class="cer_maintable_subjectLink">Retry</a></th>
    			<th class="boxtitle_gray_glass" width="0%" nowrap="nowrap"><a href="javascript:failSelectAll(2);" class="cer_maintable_subjectLink">Delete</a></th>
    			<th class="boxtitle_gray_glass" width="0%" nowrap="nowrap"><a href="javascript:failSelectAll(0);" class="cer_maintable_subjectLink">Ignore</a></th>
    		</tr>
    		
    		<?php 
    		if($showing)
    		while($row = $cerberus_db->fetch_row($fail_res)) {
    			$radioName = "action_" . $row["id"];
    		?>
    		<input type="hidden" name="fail_ids[]" value="<?php echo $row["id"]; ?>">
    		<tr bgcolor="#FFFFFF">
    			<td class="cer_maintable_text">
	    			<table cellpadding="0" cellspacing="1" width="100%">
	    			
	    				<?php if(!empty($re_fails[$row["id"]])) { ?>
	    				<tr class="cer_maintable_text">
	    					<td colspan="2"><font color="red">Retry Failed: <?php echo $re_fails[$row["id"]]; ?></font></td>
	    				</tr>
	    				<?php } ?>
	    				
	    				<tr class="cer_maintable_text">
	    					<td width="50%"><b>From:</b> <?php echo stripslashes($row["header_from"]); ?><br></td>
	    					<td width="50%"><b>To:</b> <?php echo stripslashes($row["header_to"]); ?></td>
	    				</tr>
	    				<tr class="cer_maintable_text">
	    					<td colspan="2">
	    						<b>Subject:</b> <?php echo stripslashes($row["header_subject"]); ?><br>
	    					</td>
	    				</tr>
	    				<tr class="cer_maintable_text">
	    					<td colspan="2">
	    						<b>Why?</b> <span class="cer_footer_text"><?php echo stripslashes($row["error_msg"]); ?></span><br>
	    					</td>
	    				</tr>
	    				<tr>
	    					<td colspan="2" class="cer_footer_text">
	    					<a href="javascript:showFailSource(<?php echo $row["id"]; ?>);" class="cer_footer_text">view message source</a>
	    					| <?php echo sprintf("%d",$row["message_size"]); ?>KB 
	    					|
	    					<?php
	    					$recvDate = new cer_DateTime($row["date_created"]);
	    					echo $recvDate->getUserDate();
	    					?>
	    					</td>
	    				</tr>
	    			</table>
    			</td>
    			<td align="center"><input type="radio" name="<?php echo $radioName; ?>" value="3"></td>
    			<td align="center"><input type="radio" name="<?php echo $radioName; ?>" value="2"></td>
    			<td align="center"><input type="radio" name="<?php echo $radioName; ?>" value="0" checked></td>
    		</tr>
    		<?php } ?>
    		
    		<tr bgcolor="#EEEEEE">
    			<td align="right" colspan="4">
			    	<input type="submit" value="Submit">
    			</td>
    		</tr>
    		
    	</table>
    </td>
  </tr>
  </form>

 </table>
 


