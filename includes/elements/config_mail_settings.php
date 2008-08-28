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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

//require_once(FILESYSTEM_PATH . "cerberus-api/i18n/languages.php");
//require_once(FILESYSTEM_PATH . "cerberus-api/utility/datetime/cer_Timezone.class.php");

// Verify that the connecting user has access to modify configuration values
$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_HD_SETTINGS,BITGROUP_2)) {
	die("Permission denied.");
}

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="module" value="mail_settings">
<input type="hidden" name="form_submit" value="mail_settings">
<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
  <tr class="cer_maintable_text"> 
    <td class="cer_maintable_text" bgcolor="#FFFFFF"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
		  <tr> 
			<td class="boxtitle_blue_glass" colspan="2">Mail Settings</td>
		  </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Mail Enabled:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_sendmail" value="1" <?php echo (($cfg->settings["sendmail"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Allow the system to send mail.  This should always be <B>checked</B> on live systems. This
              can be set false for demo systems.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Mail Delivery:</td>
            <td width="81%" class="cer_maintable_text">
              <input type="radio" name="cfg_mail_delivery" value="smtp" <?php echo (($cfg->settings["mail_delivery"]=="smtp")?"checked":""); ?>> SMTP 
              <input type="radio" name="cfg_mail_delivery" value="mail" <?php echo (($cfg->settings["mail_delivery"]=="mail")?"checked":""); ?>> Mail
              <br>
              <span class="cer_footer_text"><B>smtp</B> is preferred if your system supports it.  If replies, comments or new tickets 
              from the GUI aren't working, set it to <B>mail</B>.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">SMTP Server:</td>
            <td width="81%">
              <input type="text" name="cfg_smtp_server" size="32" maxlength="64" value="<?php echo $cfg->settings["smtp_server"]; ?>"><br>
              <span class="cer_footer_text">The domain name of the mail server.  <b>localhost</b> should work if the mail server
              resides on the same machine.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">SMTP Server User:</td>
            <td width="81%">
              <input type="text" name="cfg_smtp_server_user" size="32" maxlength="64" value="<?php echo $cfg->settings["smtp_server_user"]; ?>"><br>
              <span class="cer_footer_text">The username (if required) to log in to the mail server.  Otherwise leave blank.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">SMTP Server Password:</td>
            <td width="81%">
              <input type="password" name="cfg_smtp_server_pass" size="32" maxlength="64" value="<?php echo $cfg->settings["smtp_server_pass"]; ?>"><br>
              <span class="cer_footer_text">The password (if required) for the above user.  Otherwise leave blank.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Show IDs/Masks in E-mail Subject:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_subject_ids" value="1" <?php echo (($cfg->settings["subject_ids"])?"checked":""); ?>><br>
              <span class="cer_footer_text">This prefixes the "[mailbox #XXX-12345-678]:" ticket ID or mask on outgoing replies.  This makes it easy 
              for the customer to locate their ticket ID.  As of Cerberus 3.1 the ID is no longer required in the subject to group  
              replies into tickets.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Cut Line:</td>
            <td width="81%">
              <input type="text" name="cfg_cut_line" size="32" maxlength="64" value="<?php echo $cfg->settings["cut_line"]; ?>"><br>
              <span class="cer_footer_text">If not blank, this text will be added to the top of every message sent out, and this text and all text below 
              it will not be displayed in the ticket thread.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD"> 
            <td width="19%" class="cer_maintable_heading" valign="top">Flood Protection Timer:</td>
            <td width="81%">
              <input type="text" name="cfg_warcheck_secs" size="5" maxlength="5" value="<?php echo $cfg->settings["warcheck_secs"]; ?>"><span class="cer_footer_text"> (seconds)</span><br>
              <span class="cer_footer_text">Protection from autoresponder wars.  If a new ticket with the 
              same sender address, subject and destination queue is received within this many <b>seconds</b> of an identical message
              then do not send another autoresponse.  (Default is <b>10</b> seconds)<br>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Auto-Add Cc Requesters:</td>
            <td width="81%" class="cer_maintable_text">
              <input type="radio" name="cfg_auto_add_cc_reqs" value="1" <?php echo (($cfg->settings["auto_add_cc_reqs"])?"checked":""); ?>> true
              <input type="radio" name="cfg_auto_add_cc_reqs" value="0" <?php echo ((!$cfg->settings["auto_add_cc_reqs"])?"checked":""); ?>> false
              <br>
              <span class="cer_footer_text">Automatically add CC'd addresses to ticket requesters list for incoming mail.</span></td>
          </tr>
          
		  <tr> 
			<td class="boxtitle_green_glass" colspan="2">Parser Settings</td>
		  </tr>
		  <input type="hidden" name="cfg_parser_version" value="<?php echo $cfg->settings["parser_version"]; ?>">
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Secure Mode:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_parser_secure_enabled" value="1" <?php echo (($cfg->settings["parser_secure_enabled"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Require a login and password to run the parser.  If this is enabled, you <b>*must*</b> have this login and password defined in your parser's <b>config.xml</b> file.  Default is disabled.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Parser User:</td>
            <td width="81%">
              <input type="text" name="cfg_parser_secure_user" size="20" maxlength="64" value="<?php echo $cfg->settings["parser_secure_user"]; ?>"><br>
              <span class="cer_footer_text">If Secure Mode is enabled, this login must match the one in your parser's <b>config.xml</b> file.  Otherwise leave blank.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Parser Password:</td>
            <td width="81%">
              <input type="text" name="cfg_parser_secure_password" size="20" maxlength="64" value="<?php echo $cfg->settings["parser_secure_password"]; ?>"><br>
              <span class="cer_footer_text">If Secure Mode is enabled, this password must match the one in your parser's <b>config.xml</b> file.  Otherwise leave blank.</span></td>
          </tr>
          
		  <tr> 
			<td class="boxtitle_gray_glass_dk" colspan="2">Watcher Settings</td>
		  </tr>

		 	<tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Bcc Watchers:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_bcc_watchers" value="1" <?php echo (($cfg->settings["bcc_watchers"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Bcc emails to watchers. Hides the watcher's email addresses.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Enable Watcher Delivery only to Assigned Techs:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_watcher_assigned_tech" value="1" <?php echo (($cfg->settings["watcher_assigned_tech"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Send watcher emails only to watchers who are assigned to the ticket instead of to all watchers.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Save message_source.xml attachments:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_save_message_xml" value="1" <?php echo (($cfg->settings["save_message_xml"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">Save message_source.xml / message_headers.txt attachments. Disable this option to decrease total data size saved to database.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Do not send system attachments to watchers:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_watcher_no_system_attach" value="1" <?php echo (($cfg->settings["watcher_no_system_attach"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">Do not send system attachments ("message_source.xml", "message_headers.txt" and/or "html_mime_part.html") to the watchers.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Don't send watcher emails to email sender:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_not_to_self" value="1" <?php echo (($cfg->settings["not_to_self"])?"checked":""); ?>><br>
              <span class="cer_footer_text">Do not send watcher emails to the watcher who was the sender of the email.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Set Watcher From: address to user's email address:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_watcher_from_user" value="1" <?php echo (($cfg->settings["watcher_from_user"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">Set watcher email's from: address to the address of the original email sender.</span></td>
          </tr>
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">&quot;Send Precedence: bulk&quot;:</td>
            <td width="81%">
              <input type="checkbox" name="cfg_send_precedence_bulk" value="1" <?php echo (($cfg->settings["send_precedence_bulk"])?"checked":""); ?>><br>
			  <span class="cer_footer_text">Include &quot;Precedence: bulk&quot; in mail header for outgoing email.</span></td>
          </tr>
		  
          
		<tr bgcolor="#B0B0B0" class="cer_maintable_text">
			<td align="right" colspan="2">
				<input type="submit" class="cer_button_face" value="<?php echo LANG_BUTTON_SUBMIT; ?>">
			</td>
		</tr>
        </table>
    </td>
  </tr>
</table>
</form>
<br>
