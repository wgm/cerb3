<?php
/*
 * Important Licensing Note from the Cerberus Helpdesk Team:
 * 
 * Yes, it would be really easy for you to to just cheat and edit this file to 
 * use the software without paying for it.  We're trusting the community to be
 * honest and understand that quality software backed by a dedicated team takes
 * money to develop.  We aren't volunteers over here, and we aren't working 
 * from our bedrooms -- we do this for a living.  This pays our rent, health
 * insurance, and keeps the lights on at the office.  If you're using the 
 * software in a commercial or government environment, please be honest and
 * buy a license.  We aren't asking for much. ;)
 * 
 * Encoding/obfuscating our source code simply to get paid is something we've
 * never believed in -- any copy protection mechanism will inevitably be worked
 * around.  Cerberus development thrives on community involvement, and the 
 * ability of users to adapt the software to their needs.
 * 
 * A legitimate license entitles you to support, access to the developer 
 * mailing list, the ability to participate in betas, the ability to
 * purchase add-on tools (e.g., Workstation, Standalone Parser) and the 
 * warm-fuzzy feeling of doing the right thing.
 *
 * Thanks!
 * -the Cerberus Helpdesk dev team (Jeff, Mike, Jerry, Darren, Brenan)
 * and Cerberus Core team (Luke, Alasdair, Vision, Philipp, Jeremy, Ben)
 *
 * http://www.cerberusweb.com/
 * support@cerberusweb.com
 */

if(!isset($module)) $module = "";
switch ($module)
{
	case "addresses":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_addresses.php");
			break;
		}
	case "global":
	case "settings":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_global_settings.php");
			break;
		}
	case "statuses":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_custom_statuses.php");
			break;
		}
	case "mail_settings":
		include(FILESYSTEM_PATH . "includes/elements/config_mail_settings.php");
		break;
	case "pop3":
		{
			if(isset($pgid) && $pgid!="")
			include(FILESYSTEM_PATH . "includes/elements/config_pop3_edit.php");
			else
			include(FILESYSTEM_PATH . "includes/elements/config_pop3.php");

			break;
		}
	case "cron_config":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_cron.php");
			break;
		}
	case "cron_tasks":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_cron_tasks_edit.php");
			break;
		}
	case "parser_manual":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_parser_manual.php");
			break;
		}
	case "parser_fails":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_parser_fails.php");
			break;
		}
	case "log":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_parser_log.php");
			break;
		}
	case "plugins":
		{
			if(isset($pgid) && $pgid!="")
			include(FILESYSTEM_PATH . "includes/elements/config_plugins_edit.php");
			else
			include(FILESYSTEM_PATH . "includes/elements/config_plugins.php");

			break;
		}
	case "rules":
		{
			if(isset($prid) && $prid!="")
			{ include(FILESYSTEM_PATH . "includes/elements/config_parser_rules_edit.php"); }
			else
			{ include(FILESYSTEM_PATH . "includes/elements/config_parser_rules.php"); }
			break;
		}
	case "queues":
		{
			if(isset($pqid) && $pqid!="")
			{ include(FILESYSTEM_PATH . "includes/elements/config_queues_edit.php"); }
			else if(isset($qids) && !isset($destination_queue))
			{ include(FILESYSTEM_PATH . "includes/elements/config_queues_delete.php"); }
			else
			{ include(FILESYSTEM_PATH . "includes/elements/config_queues.php"); }
			break;
		}
	case "queue_catchall":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_queue_catchall.php");
			break;
		}
	case "search_index":
	case "searchindex":
		{
			switch($action)
			{
				case "threads":
					include(FILESYSTEM_PATH . "includes/elements/config_reindex_threads.php");
					break;
				case "articles":
					include(FILESYSTEM_PATH . "includes/elements/config_reindex_articles.php");
					break;
			}
			break;
		}
	case "workflow":
	case "users":
		{
			if(isset($puid) && $puid!="")
			{ include(FILESYSTEM_PATH . "includes/elements/config_users_edit.php"); }
			else
			{ include(FILESYSTEM_PATH . "includes/elements/config_users.php"); }
			break;
		}
	case "sla":
		{
			if(isset($pslid) && $pslid!="")
			{ include(FILESYSTEM_PATH . "includes/elements/config_sla_edit.php"); }
			else
			{ include(FILESYSTEM_PATH . "includes/elements/config_sla.php"); }
			break;
		}
	case "schedules":
		{
			if(isset($pslid) && $pslid!="")
			{ include(FILESYSTEM_PATH . "includes/elements/config_schedule_edit.php"); }
			else
			{ include(FILESYSTEM_PATH . "includes/elements/config_schedule.php"); }
			break;
		}
	case "customfields":
	case "custom_fields":
		{
			if(isset($pgid) && $pgid != "")
			include(FILESYSTEM_PATH . "includes/elements/config_custom_field_groups_edit.php");
			else
			include(FILESYSTEM_PATH . "includes/elements/config_custom_field_groups.php");

			break;
		}
	case "custom_field_bindings":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_custom_field_bindings.php");
			break;
		}
	case "kbase_comments":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_kbase_comments.php");
			break;
		}
	case "branding":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_branding.php");
			break;
		}
	case "workstation":
	case "ws_key":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_ws_key.php");
			break;
		}
	case "ws_config":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_ws.php");
			break;
		}
	case "teams":
	case "ws_teams":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_ws_teams.php");
			break;
		}
	case "tags":
	case "ws_tags":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_ws_tags.php");
			break;
		}
	case "ws_routing":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_ws_routing.php");
			break;
		}
	case "ws_sla":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_ws_sla.php");
			break;
		}
	case "ws_reports":
		if(isset($prid) && $prid != "")
			include(FILESYSTEM_PATH . "includes/elements/config_ws_reports_edit.php");
		else
			include(FILESYSTEM_PATH . "includes/elements/config_ws_reports.php");
		
		break;
	case "key":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_key.php");
			break;
		}
	case "maintenance":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_maintenance.php");
			break;
		}
	case "maint":
	case "maintenance_optimize":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_maintenance_optimize.php");
			break;
		}
	case "maintenance_repair":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_maintenance_repair.php");
			break;
		}
	case "maintenance_tempdir":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_maintenance_tempdir.php");
			break;
		}
	case "maintenance_attachments":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_maintenance_attachments.php");
			break;
		}
	case "development":
	case "bug":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_report_bug.php");
			break;
		}
	case "feedback":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_give_feedback.php");
			break;
		}
	case "export":
		{
			include(FILESYSTEM_PATH . "includes/elements/config_address_export.php");
			break;
		}
	case "public_gui_profiles":
		{
			if(isset($pfid) && $pfid!="")
			{ include(FILESYSTEM_PATH . "includes/elements/config_public_gui_edit.php"); }
			else
			{ include(FILESYSTEM_PATH . "includes/elements/config_public_gui.php"); }
			break;
		}
	case "public_gui_fields":
		{
			if(isset($pfid) && $pfid!="")
			{ include(FILESYSTEM_PATH . "includes/elements/config_public_gui_fields_edit.php"); }
			else
			{ include(FILESYSTEM_PATH . "includes/elements/config_public_gui_fields.php"); }
			break;
		}
	default:
		{
			/* @var $cerberus_db cer_Database */

//			$sql = "SELECT count(*) as comment_count FROM kb_comments WHERE kb_comment_approved = 0";
//			$com_result = $cerberus_db->query($sql);
//			$com_data = $cerberus_db->fetch_row($com_result);

			$sql = "SELECT count(*) as fail_count from `parser_fail_headers`";
			$fail_res = $cerberus_db->query($sql);
			$fail_data = $cerberus_db->grab_first_row($fail_res);

			$sql = "SELECT count(*) as ticket_count FROM ticket WHERE is_deleted = 1";
			$tik_result = $cerberus_db->query($sql);
			$tik_data = $cerberus_db->fetch_row($tik_result);

			include_once(FILESYSTEM_PATH . "cerberus-api/utility/tempdir/cer_Tempdir.class.php");
			$cer_tempdir = new cer_Tempdir();

			include_once(FILESYSTEM_PATH . "cerberus-api/attachments/cer_AttachmentManager.class.php");
			$cer_attachments = new cer_AttachmentManager();

			if(!isset($MACHTYPE)) $MACHTYPE = "";
			?>
			<span class="cer_display_header"><?php echo LANG_CONFIG_GROUPS_EDIT_CONFIG; ?></span><br>
			<span class="cer_maintable_text"><?php echo LANG_CONFIG_MENU_NOTE; ?></span><br><br>

			<table cellpadding="4" cellspacing="1" border="0" width="550" bgcolor="BABABA">
			<tr><td class="boxtitle_gray_glass">&nbsp;Helpdesk Environment</td></tr>
			<tr><td bgcolor="#ECECEC">

			<?php
			// [PK]: Philipp Kolmann (kolmann@zid.tuwien.ac.at)
			// Make purge infos a href if user has permission to clear tickets/tempfiles
			?>
			<span class="cer_maintable_text">&nbsp;<img alt="Failed Parses" src="includes/images/crystal/16x16/mail_delete.gif" align="middle">&nbsp; <b><?php echo ((@$fail_data["fail_count"])?$fail_data["fail_count"]:"0"); ?></b> pending messages rejected by the e-mail
			<?php echo (($acl->has_priv(PRIV_CFG_PARSER_FAILED,BITGROUP_2)) ? "<a href=\"" . cer_href("configuration.php?module=parser_fails") . "\" class=\"cer_maintable_text\">parser</a>":"parser") . "</span><br>"; ?>
			<span class="cer_maintable_text">&nbsp;<img alt="Tickets in Trash" src="includes/images/crystal/16x16/icon_trashcan.gif" align="middle">&nbsp; <b><?php echo ((@$tik_data["ticket_count"])?$tik_data["ticket_count"]:"0"); ?></b> dead tickets pending 
			<?php echo (($acl->has_priv(PRIV_CFG_MAINT_PURGE,BITGROUP_2)) ? "<a href=\"" . cer_href("configuration.php?module=maintenance")."\" class=\"cer_maintable_text\">purge</a>":"purge") . "</span><br>"; ?>
			<span class="cer_maintable_text">&nbsp;<img alt="Temp Files" src="includes/images/crystal/16x16/icon_file.gif" align="middle">&nbsp; <b><?php echo number_format($cer_tempdir->total_files,0,"",","); ?></b> temporary files (<?php echo display_bytes_size($cer_tempdir->total_sizes); ?>) pending
			<?php echo (($acl->has_priv(PRIV_CFG_MAINT_PURGE,BITGROUP_2)) ? "<a href=\"" . cer_href("configuration.php?module=maintenance_tempdir")."\" class=\"cer_maintable_text\">purge</a>":"purge") . "</span><br>"; ?>
			<span class="cer_maintable_text">&nbsp;<img alt="Attachments" src="includes/images/crystal/16x16/icon_attachment_tar.gif" align="middle">&nbsp; <b><?php echo number_format($cer_attachments->getTotalAttachments(),0,"",","); ?></b> attachments (<?php echo display_bytes_size($cer_attachments->getTotalAttachmentsSize()); ?>) pending 
			<?php echo (($acl->has_priv(PRIV_CFG_MAINT_ATTACH,BITGROUP_2)) ? "<a href=\"" . cer_href("configuration.php?module=maintenance_attachments")."\" class=\"cer_maintable_text\">clean-up</a>":"clean-up") . "</span><br>"; ?>
<?php /*
			<span class="cer_maintable_text">&nbsp;<img alt="Knowledgebase Comments" src="includes/images/crystal/16x16/icon_new_comment.gif" align="middle">&nbsp; <b><?php echo ((@$com_data["comment_count"])?$com_data["comment_count"]:"0"); ?></b> knowledgebase comments pending 
			<?php echo (($acl->has_priv(PRIV_KB) && $cfg->settings["show_kb"]) ? "<a href=\"" . cer_href("configuration.php?module=kbase_comments")."\" class=\"cer_maintable_text\">review</a>":"review") . "</span><br>"; ?>
*/ ?>
			</td></tr>
			</table><br>
			
			<table cellpadding="4" cellspacing="1" border="0" width="550" bgcolor="BABABA">
			<tr><td class="boxtitle_gray_glass">&nbsp;License</td></tr>
			<tr><td bgcolor="#ECECEC" class='cer_maintable_text'>
			<?php
			if(!$cerlicense->hasLicense()) {
			?>
			<font color='red'><b>FREE MODE - Please Register at http://www.cerberusweb.com/</b></font><br>
			Cerberus Helpdesk is free for 3 users with 1 inbound e-mail address.  Discounts are available for educational/nonprofit/opensource use.<br>
			
			<?php
			}
			if($cerlicense->hasLicense()) { ?>
	    	<table style="border:1px solid #CCCCCC;">
	    		<tr>
	    			<td colspan="2" class="boxtitle_green_glass"><b>Cerberus Helpdesk&trade; - Web Edition</b></td>
	    		</tr>
	    		<tr>
	    			<td class="cer_maintable_heading" align="right">Licensed to:</td>
	    			<td class="cer_maintable_text"><?php echo $cerlicense->getLicensee(); ?></td>
	    		</tr>
	    		<tr>
	    			<td class="cer_maintable_heading" align="right">License ID:</td>
	    			<td class="cer_maintable_text"><?php echo $cerlicense->getLicenseId(); ?></td>
	    		</tr>
	    		<tr>
	    			<td class="cer_maintable_heading" align="right">Max. Web Users:</td>
	    			<td class="cer_maintable_text"><?php echo ($cerlicense->getMaxWebUsers()) ? $cerlicense->getMaxWebUsers() : "No limit"; ?></td>
	    		</tr>
	    		<tr>
	    			<td colspan="2" class="boxtitle_green_glass"><b>Cerberus Workstation&trade; - Desktop Edition</b></td>
	    		</tr>
	    		<tr>
	    			<td class="cer_maintable_heading" align="right">Max. Desktop Users:</td>
	    			<td class="cer_maintable_text"><?php echo $cerlicense->getMaxDesktopUsers(); ?></td>
	    		</tr>
	    		<tr>
	    			<td class="cer_maintable_heading" align="right">Expires:</td>
	    			<td class="cer_maintable_text"><?php echo $cerlicense->getExpiration(); ?></td>
	    		</tr>
	    		<tr>
	    			<td colspan="2" class="boxtitle_green_glass"><b>Cerbmail&trade; - High-Volume E-mail Gateway/Parser</b></td>
	    		</tr>
	    		<tr>
	    			<td class="cer_maintable_heading" align="right">Enabled:</td>
	    			<td class="cer_maintable_text"><?php echo ($cerlicense->getEnableJParser()) ? "Yes" : "No"; ?></td>
	    		</tr>
	    	</table>
			<?php } ?>
			<?php if($acl->has_priv(PRIV_CONFIG)) { ?>
			<form action="configuration.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="sid" value="<?php echo $session->session_id; ?>">
			<input type="hidden" name="module" value="">
			<input type="hidden" name="form_submit" value="ws_key">
			<b>License File:</b> <input type="file" name="ws_license_file" size="25"><input type="submit" value="Upload"><br>
			</form>
			<?php } ?>
			</td></tr>
			</table><br>
			
			<table cellpadding="4" cellspacing="1" border="0" width="550" bgcolor="BABABA">
			<tr><td class="boxtitle_gray_glass">&nbsp;Client/Server Environment</td></tr>
			<tr><td bgcolor="#ECECEC">
			<span class="cer_maintable_heading"><?php echo LANG_CONFIG_GUI_VERSION; ?>: </span><span class="cer_maintable_text"><?php echo GUI_VERSION; ?></span><br>
			<span class="cer_maintable_heading">Cerberus Parser Version:</span> <span class="cer_maintable_text"><?php echo @$cfg->settings["parser_version"]; ?></span><br>
			<span class="cer_maintable_heading"><?php echo LANG_CONFIG_SERVER_SOFTWARE; ?>: </span><span class="cer_maintable_text"><?php echo @$_SERVER["SERVER_SOFTWARE"] . "  MySQL/ " . @mysql_get_client_info(); ?></span><br>
			<span class="cer_maintable_heading"><?php echo LANG_CONFIG_MACHINE_TYPE; ?>: </span><span class="cer_maintable_text"><?php echo @PHP_OS; ?></span><br>
			<span class="cer_maintable_heading"><?php echo LANG_CONFIG_CLIENT_BROWSER; ?>: </span><span class="cer_maintable_text"><?php echo @$_SERVER["HTTP_USER_AGENT"]; ?></span><br>
			</td></tr>
			</table><br>
			<table cellpadding="4" cellspacing="1" border="0" width="550" bgcolor="BABABA">
			<tr><td class="boxtitle_gray_glass">&nbsp;Developers</td></tr>
			<tr><td bgcolor="#ECECEC">
			<span class="cer_maintable_heading">Jeff Standen :</span><span class="cer_maintable_text">  Project Manager, Lead GUI/PHP Developer</span><br>
			<span class="cer_maintable_heading">Mike Fogg :</span><span class="cer_maintable_text">  Developer, Java/PHP</span><br>
			<span class="cer_maintable_heading">Dan Hildebrandt :</span><span class="cer_maintable_text">  Developer, JParser/Java/PHP</span><br>
			<span class="cer_maintable_heading">Jerry Kanoholani :</span><span class="cer_maintable_text">  Sales, Q/A Tester</span><br>
			<span class="cer_maintable_heading">Darren Sugita :</span><span class="cer_maintable_text">  Support, Q/A Tester</span><br>
			<span class="cer_maintable_heading">Brenan Cavish :</span><span class="cer_maintable_text">  Q/A Tester, Support</span><br>
			<span class="cer_maintable_heading">Ben Halsted :</span><span class="cer_maintable_text">  Developer, Parser Binary</span><br>
			<span class="cer_maintable_heading">Jeremy Johnstone :</span><span class="cer_maintable_text">  Developer, XML/Framework</span><br>
			<span class="cer_maintable_heading">Trent Ramseyer :</span><span class="cer_maintable_text">  Developer, Web Site</span><br>
			<br>
			Core Team (Community Liaisons):<br>
			<span class="cer_maintable_heading">Luke Foley</span><br>
			<span class="cer_maintable_heading">Alasdair Stewart</span><br>
			<span class="cer_maintable_heading">Philipp Kolmann</span><br>
			</td></tr>
			</table><br>
			<span class="cer_maintable_heading">Useful Links</span><br>
			<a href="http://www.cerberusweb.com/" target="_blank" class="cer_maintable_text">Cerberus Helpdesk Website</a><br>
			<a href="http://www.wgmdev.com/jira/" target="_blank" class="cer_maintable_text">Cerberus Helpdesk Project Portal (Roadmap, Bugs, Wishlist)</a><br>
			<a href="http://forum.cerberusweb.com/" target="_blank" class="cer_maintable_text">Cerberus Helpdesk Forums</a><br>
			<a href="http://www.cerberusweb.com/download_fetch.php?fv=cerb-docs" target="_blank" class="cer_maintable_text">Cerberus Helpdesk Online Manual</a><br>
			<a href="http://www.webgroupmedia.com/" target="_blank" class="cer_maintable_text">WebGroup Media, LLC. Website</a><br>
			<a href="http://www.php.net/" target="_blank" class="cer_maintable_text">PHP Website</a><br>
			<a href="http://www.mysql.com/" target="_blank" class="cer_maintable_text">MySQL Website</a><br>
			<a href="http://smarty.php.net/" target="_blank" class="cer_maintable_text">Smarty Templates Website</a><br>
			<a href="http://php.weblogs.com/adodb/" target="_blank" class="cer_maintable_text">ADODB Website</a><br>
			<a href="http://www.iconexperience.com/" target="_blank" class="cer_maintable_text">IconExperience.com (Artwork)</a><br>
			<a href="http://www.everaldo.com/crystal.html" target="_blank" class="cer_maintable_text">Everaldo.com (Graphic Artist)</a><br>
			<br><span class="cer_maintable_heading">Copyright (c) 2006, WebGroup Media LLC.  All rights reserved.</span><br><br>
			
			<?php if(DEMO_MODE) {?><span class="cer_configuration_updated"><?php echo LANG_CERB_WARNING_DEMO; ?></span><br><?php } ?>
			
			<?php
			break;
		}
}
