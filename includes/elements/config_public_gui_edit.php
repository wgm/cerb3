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
| File: config_public_gui_edit.php
|
| Purpose: This config include handles creating and editing Public
|	GUI Profiles.
|
| Developers involved with this file: 
|		Jeff Standen  (jeff@webgroupmedia.com)  [JAS]
|
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/i18n/languages.php");
require_once(FILESYSTEM_PATH . "cerberus-api/queue/cer_Queue.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/public-gui/cer_PublicGUISettings.class.php");
require_once(FILESYSTEM_PATH . "includes/cerberus-api/hash/core.hash.php");
require_once(FILESYSTEM_PATH . "cerberus-api/login/cer_LoginPluginHandler.class.php");
require_once(FILESYSTEM_PATH . "cerberus-api/knowledgebase/CerKnowledgebase.class.php");

$acl = CerACL::getInstance();
if(!$acl->has_priv(PRIV_CFG_SC_PROFILES,BITGROUP_2)) {
	die("Permission denied.");
}

$kb = new CerKnowledgebase(0);
$root = $kb->getRoot();
$pubgui = new cer_PublicGUISettings($pfid);
$queues = new cer_QueueHandler();
$queueAddys = $queues->getQueueAddresses();

if(DEMO_MODE) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_WARNING_DEMO . "</span><br>";
?>
<form action="configuration.php" method="post">
<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
<input type="hidden" name="pfid" value="<?php echo $pfid; ?>">
<input type="hidden" name="module" value="public_gui_profiles">
<input type="hidden" name="form_submit" value="public_gui_profiles">

<?php if(isset($form_submit)) echo "<span class=\"cer_configuration_updated\">" . LANG_CERB_SUCCESS_CONFIG . "</span><br>"; ?>
<table width="98%" border="0" cellspacing="1" cellpadding="2" bgcolor="#FFFFFF">
  <tr class="cer_maintable_text"> 
    <td class="cer_maintable_text"> 
        <table width="100%" border="0" cellspacing="1" cellpadding="2">
        
		  <tr> 
			<td class="boxtitle_orange_glass" colspan="2"><?php echo ((!$pfid) ? "Create Support Center Profile" : "Edit Support Center Profile '".$pubgui->settings["profile_name"]."'"); ?></td>
		  </tr>
		  
		  <?php if(!empty($pfid)) { ?>
          <tr bgcolor="#EEEEEE">
            <td width="19%" class="cer_maintable_heading" valign="top">Profile ID:</td>
            <td width="81%" class="cer_maintable_text">
              <B><?php echo @$pubgui->settings["profile_id"]; ?></B><br>
              <span class="cer_footer_text">The ID above should be set as your <b>PROFILE_ID</b> in the Support Center's <b>config.php</b> file.</span>
            </td>
          </tr>
          <?php } ?>
          
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Profile Name:</td>
            <td width="81%">
              <input type="text" name="profile_name" size="45" maxlength="128" value="<?php echo @$pubgui->settings["profile_name"]; ?>"><br>
              <span class="cer_footer_text">The name of this Support Center Profile. For example: "Main Public Helpdesk".</span>
            </td>
          </tr>

          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Profile URL:</td>
            <td width="81%">
              <input type="text" name="pub_url" size="45" maxlength="255" value="<?php echo @$pubgui->settings["pub_url"]; ?>"><input type='button' onclick='window.open(this.form.pub_url.value+"?mod=0","Profile","width=640,height=480,scrollbars=yes,toolbar=no");' value='Test URL'><br>
              <span class="cer_footer_text">This URL is used to direct customers to your public Support Center from Cerberus helpdesk functionality. This should include the full URL and index page, for example: <b><i>http://www.website.com/index.php</i></b><br>
              <br>
              The 'Test URL' button should display the front page of your Support Center.<br>
              <b>Note:</b> If you haven't already installed the Support Center you can fill this value in later.</span>
            </td>
          </tr>
          
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Company Name:</td>
            <td width="81%">
              <input type="text" name="pub_company_name" size="45" maxlength="128" value="<?php echo @$pubgui->settings["pub_company_name"]; ?>"><br>
              <span class="cer_footer_text">The company name to be displayed on the Support Center. For example: WebGroup Media, LLC.</span>
            </td>
          </tr>
          
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Company Email:</td>
            <td width="81%">
              <input type="text" name="pub_company_email" size="45" maxlength="128" value="<?php echo @$pubgui->settings["pub_company_email"]; ?>"><br>
              <span class="cer_footer_text">The email address that will be used on all outgoing Support Center email. For example: support@webgroupmedia.com</span>
            </td>
          </tr>
          
          <tr bgcolor="#DDDDDD">
            <td width="19%" class="cer_maintable_heading" valign="top">Login Plugin:</td>
            <td width="81%">
              <select name="login_plugin_id">
              	<option value="0">Cerberus Helpdesk (default)
              	<?php
              	$login_mgr = new cer_LoginPluginHandler();
				
              	$plugins = $login_mgr->hash->getPluginsByType("login");
              	foreach($plugins as $idx => $plug) {
              		if($plug->plugin_enabled) {
              			echo sprintf("<option value='%s'%s>%s\r\n",
              					$plug->plugin_id,
              					(($plug->plugin_id == $pubgui->settings["login_plugin_id"]) ? " SELECTED" : ""),
              					$plug->plugin_name
              				);
              		}
              	}
              	?>
			  </select>
			  <br>
              <span class="cer_footer_text">Using a Login Plugin will save your customers the hassle of remembering yet another login.  The
              plugins allow your customers to log in to the Cerberus Support Center using accounts they already have in one of your other 
              systems.  For example: a forum, billing software, LDAP, a custom system, etc.  By default the Support Center will use Cerberus
              Public User logins.  When using any other plugin the 'Register' and 'Forgot Password' modules will be disabled.</span>
            </td>
          </tr>
          
		  <tr> 
			<td class="boxtitle_green_glass" colspan="2">Support Center - Mailboxes</td>
		  </tr>
          
          <tr bgcolor="#DDDDDD">
            <td colspan="2"><span class="cer_footer_text">
            Select the mailboxes to be shown in the Support Center when a customer is creating a new ticket. For each mailbox you can enter 
            a mask which will change the displayed name for the Support Center only. This can be used to make obscure 
            mailbox names more understandable for your public users. Custom Field Groups allow you to group specific custom fields 
            and display them for each mailbox in the Support Center. For example, you may want to show certain fields for the Sales team 
            (First Name, Phone Number) and a completely different set for a Support team (Domain Name, Customer ID, etc). Field 
            Groups are defined in the Custom Field Groups link in the Support Center section of the menu to the left.</span><br>
            <br>
            
				<table cellpadding="3" cellspacing="1" border="0" bgcolor="#FFFFFF">
					<tr> 
						<td class="boxtitle_gray_glass_dk">Mailbox Alias</td>
						<td class="boxtitle_gray_glass_dk">Mailbox</td>
						<td class="boxtitle_gray_glass_dk">Custom Fields Group</td>
						<td class="boxtitle_gray_glass_dk">Remove</td>
					</tr>
				
				<?php
				
//				include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationTeams.class.php");
//				$wsteams = new CerWorkstationTeams();
//				$teamList = $wsteams->getTeams();
				$pg_group = new cer_PublicGUIFieldGroups();
				
				foreach($pubgui->mailboxes as $mailboxId => $mailbox) { ?>
					<tr bgcolor="#DDDDDD">
					<input type="hidden" name="pub_mbox[]" value="<?php echo $mailboxId; ?>">
					<td><input type="text" name="pub_mbox_mask[]" size="24" maxlength="128" value="<?php echo @$mailbox['mailbox_alias']; ?>"></td>
					<td><select name="pub_mbox_id[]">
					<option value=''>
					<?php
					if(is_array($queueAddys))
					foreach($queueAddys as $qa) { /* @var $qa CerQueueAddress */
						$sel = ($qa->address_id == $mailbox['mailbox_address_id']) ? 1 : 0;
					?>
						<option value="<?php echo $qa->address; ?>" <?php if($sel) echo "SELECTED"; ?>><?php echo $qa->address; ?>
					<?php } ?>
					</select></td>
					<td>
						<select name='pub_mbox_field_group[]'>
							<option value='' <?php if(@$mailbox['field_group'] == 0) echo "SELECTED"; ?>>None
							<?php 
								foreach($pg_group->groups as $g) {
									$sel = (@$mailbox['field_group'] == $g->group_id) ? 1 : 0;
								?>
								<option value="<?php echo $g->group_id; ?>" <?php if($sel) echo "SELECTED"; ?>><?php echo $g->group_name; ?>
							<?php } ?>
						</select>
					</td>
					<td align="center"><input type="checkbox" name="pub_mbox_del[]" value="<?php echo $mailboxId; ?>"></td>
					</tr>					
					
					<?php
				}

				// [JAS]: Always give 5 blank lines for data entry
				for($x=0;$x<5;$x++) { ?>
					<tr bgcolor="#DDDDDD">
					<td><input type="text" name="add_mask[]" size="24" maxlength="128" value=""></td>
					<td><select name="add_mbox[]">
					<option value=''>
					<?php
					if(is_array($queueAddys))
					foreach($queueAddys as $qa) { /* @var $qa CerQueueAddress */
					?>
						<option value="<?php echo $qa->address; ?>"><?php echo $qa->address; ?>
					<?php } ?>
					</select></td>
					<td>
						<select name='add_field_group[]'>
							<option value=''>None
							<?php 
								foreach($pg_group->groups as $g) {
									$sel = (@$mailbox['field_group'] == $g->group_id) ? 1 : 0;
								?>
								<option value="<?php echo $g->group_id; ?>"><?php echo $g->group_name; ?>
							<?php } ?>
						</select>
					</td>
					<td align="center"></td>
					</tr>					
					
					<?php
				}
				
				?>
				
				</table>
	            <br>
	            <b>NOTE:</b> If you need to enter more than five mailboxes, save the profile to add more.<br>
				
				
            </td>
          </tr>
		  
		  <tr> 
			<td class="boxtitle_blue_glass" colspan="2">Modules</td>
		  </tr>
		  
          <tr bgcolor="#DDDDDD">
            <td colspan="2" align="center">
            
            <br>
            
              <table border="0" cellpadding="2" cellspacing="1" bgcolor="#BABABA" width="90%">
				  <tr bgcolor="#ECECEC"> 
					<td class="cer_maintable_text" align="center">
			            Modules control what your clients are able to do from the customer interface on your website. To enable 
			            a module, check the 'Enable:' box.<br>
					</td>
				  </tr>
			  </table>            
			  
			  <br>
					
              <table border="0" cellpadding="2" cellspacing="1" bgcolor="#BABABA" width="90%">
				  <tr> 
					<td class="boxtitle_gray_glass_dk">Registration</td>
				  </tr>
				  
				  <tr>
				  	<td bgcolor="#F5F5F5">
		              <table border="0" cellpadding="2" cellspacing="0" width="90%">
						  <tr> 
							<td class="cer_maintable_text" colspan="2">
								<B>Enable:</B> <input type="checkbox" name="pub_mod_registration" value="1" <?php echo (($pubgui->settings["pub_mod_registration"])?"checked":""); ?>>
								<br>
								This module allows customers to register new support accounts from the customer interface.  <B>This is only used if no Login Plugin is set.</B><br>
								<br>
					            <table cellspacing="0" cellpadding="2" border="0">
					            	<tr>
					            		<td><img alt="" src="includes/images/spacer.gif" width="15" height="0"></td>
					            		<td>
											<span class="cer_maintable_heading">Registration Mode:</span><br>
								            
							            	<select name="pub_mod_registration_mode">
							            		<option value="pass" <?php echo (($pubgui->settings["pub_mod_registration_mode"]=="pass")?"selected":""); ?>>Generate Random Confirmation Code
							            	</select>
							            	<br>
								              <span class="cer_footer_text">These options determine how users are permitted to make new helpdesk accounts in the Support Center.<br>
								              <b>Generate Random Confirmation Code</b>: Any user is able to register provided they enter a confirmation code that has been e-mailed to them
								              to verify their e-mail address.
								        </td>
								    </tr>
								</table>
							</td>
						  </tr>
		              </table>
				  	</td>
				  </tr>
              </table>
              
			<br>

              <table border="0" cellpadding="2" cellspacing="1" bgcolor="#BABABA" width="90%">
				  <tr> 
					<td class="boxtitle_gray_glass_dk">Knowledgebase</td>
				  </tr>
				  
				  <tr>
				  	<td bgcolor="#F5F5F5">
		              <table border="0" cellpadding="2" cellspacing="0" width="90%">
						  <tr> 
							<td class="cer_maintable_text" colspan="2">
								<B>Enable:</B> <input type="checkbox" name="pub_mod_kb" value="1" <?php echo (($pubgui->settings["pub_mod_kb"])?"checked":""); ?>>
								<br>
								This module allows customers to search and view public knowledgebase articles in the customer interface.  You can choose the 
								knowledgebase root for this Support Center below.  This allows you to break up articles (e.g. by company, by product) and display appropriate  
								articles in your Support Center front-ends.  Since articles can exist in multiple categories, it is suggested you create a top-level 
								category for each portal and share articles where appropriate.<br>
								<br>
								<b>Knowledgebase Root:</b><br>
							    <select name="pub_mod_kb_root">
							      <option value="0">None (Top Level Category)
							      
							      <?php
							      $cats = $root->getDescendents();
							      foreach($cats as $catId) { 
							      if($catId > 0) {
							      	$parent = $kb->flat_categories[$catId];
							      	echo '<option value="' . $parent->id . '" ' . (($pubgui->settings['pub_mod_kb_root']==$parent->id) ? "selected":"") . '>';
							      	if(null != $parent->id) {
								      	if($parent->level > 2) {
								      		// Repeat spacer
								      		echo str_repeat("&nbsp; &nbsp;",$parent->level-2);
								      	}
								      	if($parent->level > 1) {
								      		echo "---&nbsp;";
								      	}
										echo $parent->name;
							      	}
							      }
							      }
							      ?>
							    </select>
							</td>
						  </tr>
		              </table>
				  	</td>
				  </tr>
              </table>
			  
              <br>
              
              <table border="0" cellpadding="2" cellspacing="1" bgcolor="#BABABA" width="90%">
				  <tr> 
					<td class="boxtitle_gray_glass_dk">My Account</td>
				  </tr>
				  
				  <tr>
				  	<td bgcolor="#F5F5F5">
		              <table border="0" cellpadding="2" cellspacing="0" width="90%">
						  <tr> 
							<td class="cer_maintable_text" colspan="2">
								<B>Enable:</B> <input type="checkbox" name="pub_mod_my_account" value="1" <?php echo (($pubgui->settings["pub_mod_my_account"])?"checked":""); ?>>
								<br>
								This module allows customers to view and update their contact information in the customer interface.<br>
							</td>
						  </tr>
		              </table>
				  	</td>
				  </tr>
              </table>
              
              <br>
              
              <table border="0" cellpadding="2" cellspacing="1" bgcolor="#BABABA" width="90%">
				  <tr> 
					<td class="boxtitle_gray_glass_dk">Open Ticket</td>
				  </tr>
				  
				  <tr>
				  	<td bgcolor="#F5F5F5">
		              <table border="0" cellpadding="2" cellspacing="0" width="90%">
						  <tr> 
							<td class="cer_maintable_text" colspan="2">
								<B>Enable:</B> <input type="checkbox" name="pub_mod_open_ticket" value="1" <?php echo (($pubgui->settings["pub_mod_open_ticket"])?"checked":""); ?>>
								<br>
								This module allows customers to open new tickets in the customer interface.<br>
								<br>
								
					            <table cellspacing="0" cellpadding="2" border="0">
					            	<tr>
					            		<td><img alt="" src="includes/images/spacer.gif" width="15" height="0"></td>
					            		<td>
											<input type="checkbox" name="pub_mod_open_ticket_locked" value="1" <?php echo (($pubgui->settings["pub_mod_open_ticket_locked"])?"checked":""); ?>>
											<span class="cer_maintable_heading">Must be Logged In</span><br>
											
								              <span class="cer_footer_text">If enabled, a user must be logged in to submit a ticket from the public interface.  Turning this
								               off will allow anyone to send in tickets, but will still require a login to check ticket statuses.</span>
								        </td>
								    </tr>
								</table>
							</td>
						  </tr>
		              </table>
				  	</td>
				  </tr>
              </table>
              
              <br>
              
              <table border="0" cellpadding="2" cellspacing="1" bgcolor="#BABABA" width="90%">
				  <tr> 
					<td class="boxtitle_gray_glass_dk">Track Open Tickets</td>
				  </tr>
				  
				  <tr>
				  	<td bgcolor="#F5F5F5">
		              <table border="0" cellpadding="2" cellspacing="0" width="90%">
						  <tr> 
							<td class="cer_maintable_text" colspan="2">
								<B>Enable:</B> <input type="checkbox" name="pub_mod_track_tickets" value="1" <?php echo (($pubgui->settings["pub_mod_track_tickets"])?"checked":""); ?>>
								<br>
								This module allows customers to track and update their open tickets in the customer interface.<br>
							</td>
						  </tr>
		              </table>
				  	</td>
				  </tr>
              </table>
              
              <br>
              
              <table border="0" cellpadding="2" cellspacing="1" bgcolor="#BABABA" width="90%">
				  <tr> 
					<td class="boxtitle_gray_glass_dk">Welcome</td>
				  </tr>
				  
				  <tr>
				  	<td bgcolor="#F5F5F5">
		              <table border="0" cellpadding="2" cellspacing="0" width="90%">
						  <tr> 
							<td class="cer_maintable_text" colspan="2">
								<B>Enable:</B> <input type="checkbox" name="pub_mod_welcome" value="1" <?php echo (($pubgui->settings["pub_mod_welcome"])?"checked":""); ?>>
								<br>
								
								This module is displayed on the home page of the customer interface, welcoming your customers.<br>
								<br>
								
					            <table cellspacing="0" cellpadding="2" border="0">
					            	<tr>
					            		<td><img alt="" src="includes/images/spacer.gif" width="15" height="0"></td>
					            		<td>
											<span class="cer_maintable_heading">Welcome Title:</span><br>
											<input type="text" size="60" maxlength="64" name="pub_mod_welcome_title" value="<?php echo htmlspecialchars($pubgui->settings["pub_mod_welcome_title"]); ?>"><br>
											<br>
											
											<span class="cer_maintable_heading">Welcome HTML:</span><br>
											<textarea name="pub_mod_welcome_text" rows="8" cols="80"><?php echo htmlspecialchars($pubgui->settings["pub_mod_welcome_text"]); ?></textarea><br>
											
											<span class="cer_footer_text">The HTML-formatted text to be shown in the Welcome box.<br>
											<b>&lt;br&gt;</b> = line break<br>
											<b>&lt;b&gt;text&lt;/b&gt;</b> = <b>text</b><br>
											<b>&lt;i&gt;text&lt;/i&gt;</b> = <i>text</i><br>
											<b>&lt;font color="red"&gt;text&lt;/font&gt;</b> = <font color="red">text</font><br>
											etc.
											</span>
								        </td>
								    </tr>
								</table>
							</td>
						  </tr>
		              </table>
				  	</td>
				  </tr>
              </table>
              
              <br>
              
              <table border="0" cellpadding="2" cellspacing="1" bgcolor="#BABABA" width="90%">
				  <tr> 
					<td class="boxtitle_gray_glass_dk">Contact</td>
				  </tr>
				  
				  <tr>
				  	<td bgcolor="#F5F5F5">
		              <table border="0" cellpadding="2" cellspacing="0" width="90%">
						  <tr> 
							<td class="cer_maintable_text" colspan="2">
								<B>Enable:</B> <input type="checkbox" name="pub_mod_contact" value="1" <?php echo (($pubgui->settings["pub_mod_contact"])?"checked":""); ?>>
								<br>
								
								This module is displayed on the home page of the customer interface, providing business contact information to your customers.<br>
								<br>
								
					            <table cellspacing="0" cellpadding="2" border="0">
					            	<tr>
					            		<td><img alt="" src="includes/images/spacer.gif" width="15" height="0"></td>
					            		<td>
											<span class="cer_maintable_heading">Contact HTML:</span><br>
											<textarea name="pub_mod_contact_text" rows="8" cols="80"><?php echo htmlspecialchars($pubgui->settings["pub_mod_contact_text"]); ?></textarea><br>
											
											<span class="cer_footer_text">The HTML-formatted text to be shown in the Contact Us box.<br>
											<b>&lt;br&gt;</b> = line break<br>
											<b>&lt;b&gt;text&lt;/b&gt;</b> = <b>text</b><br>
											<b>&lt;i&gt;text&lt;/i&gt;</b> = <i>text</i><br>
											<b>&lt;font color="red"&gt;text&lt;/font&gt;</b> = <font color="red">text</font><br>
											etc.
											</span>
								        </td>
								    </tr>
								</table>
								
							</td>
						  </tr>
		              </table>
				  	</td>
				  </tr>
              </table>
              
              <br>
              
            </td>
          </tr>
          
          
		  <tr> 
			<td class="boxtitle_blue_glass_dk" colspan="2">Support Center Templates</td>
		  </tr>
		  
          <tr bgcolor="#DDDDDD">
            <td colspan="2" class="cer_maintable_text">
				
			<table cellpadding="3" cellspacing="1" border="0" width="100%" bgcolor="#FFFFFF">
				
			  <tr> 
				<td class="boxtitle_gray_glass_dk" colspan="2">Template: Confirmation E-mail (if enabled)</td>
			  </tr>
			  
	          <tr bgcolor="#DDDDDD">
	            <td width="1%" class="cer_maintable_heading" valign="top" nowrap>Available Tokens:</td>
	            <td width="99%">
	              <span class="cer_footer_text">
						<B>##site_url##</B> - An automatically generated URL back to the Support Center main page.<br>
						<B>##confirm_url##</B> - An automatically generated URL back to the Support Center confirmation screen.<br>
						<B>##confirm_email##</B> - The e-mail address entered by the public user.<br>
						<B>##confirm_code##</B> - The randomly generated confirmation code to send.<br>
						<B>##company_name##</B> - The name of your company, as entered above.<br>
						<B>##company_email##</B> - Your company's e-mail contact address, as entered above.<br>
	              </span>
	            </td>
	          </tr>
	          
	          <tr bgcolor="#DDDDDD">
	            <td width="1%" class="cer_maintable_heading" valign="top" nowrap>Subject:</td>
	            <td width="99%">
	              <input type="text" name="pub_confirmation_subject" size="65" maxlength="128" value="<?php echo @$pubgui->settings["pub_confirmation_subject"]; ?>"><br>
	              <span class="cer_footer_text">The subject for the confirmation e-mail.</span>
	            </td>
	          </tr>
			  
	          <tr bgcolor="#DDDDDD">
	            <td width="1%" class="cer_maintable_heading" valign="top" nowrap>E-mail Body:</td>
	            <td width="99%">
	              <textarea name="pub_confirmation_body" rows="15" cols="100%"><?php echo @$pubgui->settings["pub_confirmation_body"]; ?></textarea><br>
	              <span class="cer_footer_text">The body of the confirmation e-mail.</span>
	            </td>
	          </tr>
	          
	      </table>
	      </td>
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
