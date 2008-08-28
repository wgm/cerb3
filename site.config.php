<?php
/* 
   Only uncomment and set the following path if auto detection doesn't work
   Path to the cerberus-gui files, *MUST* include a trailing slash '/'.
   i.e.: define("FILESYSTEM_PATH","/www/htdocs/cerberus-gui/");
   NOTE: If you run a Windows server enter paths escaped, such as: 
		c:\\Inetpub\\wwwroot\\cerberus-gui\\ or c:/Inetpub/wwwroot/cerberus-gui/
*/
//define("FILESYSTEM_PATH","/www/htdocs/cerberus-gui/");

// If you want to override the automatic hostname detection, set the
// HOST_NAME constant to the full URL to cerberus-gui.  No trailing slash.
// Otherwise leave blank for auto detection.
// For Example:
// If your URL is: http://localhost/cerberus-gui/
// Use: define("HOST_NAME","http://localhost");
define("HOST_NAME","");

// This will hide the GUI's XSP settings in Config->Global Settings.
// Unless you know what you're doing, this should be left at default (false).
define("HIDE_XSP_SETTINGS",false);

// This enables use of something like the mod_auth_ldap module in apache for
// single sign no.  Leave as default (false) for normal behavior.
define("EXTERNAL_AUTH",false);

// Demo mode won't save any configuration values, it should NOT be enabled
// on live/production sites.  Use this to display the helpdesk as a public
// demo.  Default is false.
define("DEMO_MODE",false);

// [ UPGRADE SECURITY OPTIONS ]==================================================================================
define("UPGRADE_SECURE_MODE",true); // Set this to 'true' to require IP matching on upgrade.php

/*=====================================================================
!!!  WARNING:  DO NOT EDIT ANYTHING BELOW THIS LINE.
=====================================================================*/

// [JSJ]: If we didn't set the filesystem path manually above, then auto-detect it
if(!defined('FILESYSTEM_PATH')) {
   define("FILESYSTEM_PATH", dirname(__FILE__) . "/");
}

define("DB_PLATFORM","mysql");

// [JAS]: Set global error handling
include_once(FILESYSTEM_PATH . "includes/functions/error_trapping.php");
set_error_handler('cer_error_handler');

include_once(FILESYSTEM_PATH . "config.php");
include_once(FILESYSTEM_PATH . "cerberus-api/compatibility/compatibility.php");

if(!defined('DB_SERVER') || !defined('DB_NAME') || !defined('DB_USER') || DB_NAME == '' || DB_SERVER == '' || DB_USER == '') {
   $configgen_path = "/siteconfig/index.php";
   if (substr($_SERVER["PHP_SELF"],strlen($configgen_path) * -1) != $configgen_path) { 
      if(strstr($_SERVER["PHP_SELF"], "install") === FALSE) {
         header("Location: install/siteconfig/index.php"); 
      }
      else {
         header("Location: siteconfig/index.php");
      }
   }
}

// [JSJ]: Setup the default system default priority names
$priority_options = Array("0"=>"None",
		"25"=>"Lowest",
		"50"=>"Low",
		"75"=>"Moderate",
		"90"=>"High",
		"100"=>"Highest");

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

$install_path = "/install/index.php";
$is_install_page = (substr($_SERVER["PHP_SELF"],strlen($install_path) * -1) == $install_path);

if(!$is_install_page) { 
	include_once(FILESYSTEM_PATH . "cerberus-api/workstation/CerWorkstationLicense.class.php");
	$cerlicense = new CerWorkstationLicense();

	include_once(FILESYSTEM_PATH . "includes/functions/session_init.php");
}
