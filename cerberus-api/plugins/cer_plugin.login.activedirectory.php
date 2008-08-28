<?php
require_once(FILESYSTEM_PATH . "cerberus-api/login/cer_LoginPlugin.class.php");

define("CER_PLUGIN_NAME","Active Directory Login");
define("CER_PLUGIN_TYPE","login");
define("CER_PLUGIN_AUTHOR","WebGroup Media LLC. (user contributed)");
define("CER_PLUGIN_CLASS","plugin_loginActiveDirectory"); // [JAS]: The function to act as main()

class plugin_loginActiveDirectory extends cer_LoginPlugin {
	// [alb] Modified next 2 lines from public to var
	var $login_string = "Login";
	var $password_string = "Password";

	function plugin_loginActiveDirectory($plugin_id,&$params) {
		$this->cer_LoginPlugin($plugin_id,$params);
		$this->_loadVars();

		$ldapHost = $this->getVar("ldap_host");
		$ldapPort = $this->getVar("ldap_port");
		$ldapDN = $this->getVar("ldap_DN");
		$ldapRDN = $this->getVar("ldap_RDN");
		$ldapPWD = $this->getVar("ldap_PWD");

	}

	// [JAS]: This method should return an array of cer_PluginSetting objects that can be configured
	// by the helpdesk user/admin in the GUI.
	function pluginConfigure() {
		$plugin_settings = array();
		$plugin_settings["ldap_host"] = new cer_PluginSetting("LDAP Host","ldap_host","T",array(64),"Hostname of LDAP server");
		$plugin_settings["ldap_port"] = new cer_PluginSetting("LDAP Port","ldap_port","T",array(5),"Port");
		$plugin_settings["ldap_DN"] = new cer_PluginSetting("LDAP Base DN","ldap_DN","T",array(128),"Base DN");
		$plugin_settings["ldap_RDN"] = new cer_PluginSetting("LDAP RDN","ldap_RDN","T",array(128),"CN of account to bind with");
		$plugin_settings["ldap_PWD"] = new cer_PluginSetting("RDN Password","ldap_PWD","P",array(32),"RDN Password");
		return $plugin_settings;
	}

	// try to authenticate credentials
	function getRemoteUserId() {
		$username = $this->getParam("username");
		$password = $this->getParam("password");

		$ldapHost = $this->getVar("ldap_host");
		$ldapPort = $this->getVar("ldap_port");
		$ldapDN = $this->getVar("ldap_DN");
		$ldapRDN = $this->getVar("ldap_RDN");
		$ldapPWD = $this->getVar("ldap_PWD");

		// fields we want returned
		// [alb] if we are using AD we can just get the distinguished name
		// which is the fully qualified ldap path to the object.
		// saves a lot of messing around later
		$arrReturnAttribs = array("samaccountname", "distinguishedname","mail");

		// search filter
		$ldapFilter = "(&(samaccountname=" . trim($username) . "))";

		// connect to the LDAP server
		$ldapConnect = ldap_connect($ldapHost, $ldapPort);
		$this->_debugMessage("connected " . $ldapHost);

		// bind to the LDAP server
		$ldapBind = $this->_ldapValidate($ldapConnect, $ldapRDN, $ldapPWD);
		//$ldapBind = ldap_bind($ldapConnect, $ldapRDN, $ldapPWD);

		// we can't bind, end it now
		if (!$ldapBind){
			return false;
		}
		$this->_debugMessage("made the bind " );
		// perform the search
		$this->_debugMessage("ldapRDN " . $ldapDN);
		$this->_debugMessage("ldapConnect " . $ldapConnect);
		$this->_debugMessage("ldapFilter " . $ldapFilter);

		$searchResults = ldap_search($ldapConnect, $ldapDN, $ldapFilter, $arrReturnAttribs, 0, 0, 10);
		$this->_debugMessage("search done " . $ldapFilter);
		// store the results
		$arrResults = ldap_get_entries($ldapConnect, $searchResults);

		// count the results
		$resultCount = ldap_count_entries($ldapConnect, $searchResults);
		$this->_debugMessage("result count " . $resultCount);
		// free the results
		ldap_free_result($searchResults);

		// close the LDAP connection
		ldap_close($ldapConnect);

		// if searched correctly, one account should return
		if ($resultCount == 1) {
			foreach ($arrResults as $result) {
				// create CN for the user
				$userRDN = str_replace(",", ",", $result['distinguishedname'][0]);
			}
			// [alb] we only have one result, so we can use it directly

			$userPWD = trim($password);

			//connect to the LDAP server
			$ldapConnect = ldap_connect($ldapHost, $ldapPort);

			//return an error message if we can't connect
			if (!$ldapConnect){
				return false;
			}

			// attempt to bind as a means of authentication
			$ldapBind = $this->_ldapValidate($ldapConnect, $userRDN, $userPWD);

			ldap_close($ldapConnect);

			// if the bind fails, the username and/or password are incorrect
			if (!$ldapBind){
				return false;
			} else {
				// [Aron]: Return an array with username and primary email
				$arrReturn = array();
				$arrReturn["username"] = $username;
				$arrReturn["email"] = $result['mail'][0];
				//print "Returning: ".nl2br( print_r($arrReturn, true))."</br>\n";
				return $arrReturn;

			}

		} else {
			// if the result count is not 1, there was a problem
			return false;
		}

	}

	// attempt to bind using the given connection, DN, and password
	function _ldapValidate($ldapConnect, $ldapDN, $ldapPWD) {
		// AD requires V3
		ldap_set_option($ldapConnect, LDAP_OPT_PROTOCOL_VERSION, 3);
		$this->_debugMessage("in _validate - =============================================");
		// no referrals, so avoid that hassle
		ldap_set_option($ldapConnect, LDAP_OPT_REFERRALS, 0);
		$this->_debugMessage("in _validate - heres the user" . $ldapDN);
		$this->_debugMessage("in _validate here's the password" . $ldapPWD);

		// bind to the LDAP server
		// [alb] changed the call syntax here so if the bind fails we DONT send a warning
		// back to the browser...instead we return 0
		if (@ldap_bind($ldapConnect, $ldapDN, $ldapPWD)){
			$ldapBind = 1;
		}else {
			$ldapBind = 0;
		}
		$this->_debugMessage("leaving _ldapValidate=====================================
" );
		// give the results back
		return $ldapBind;
	}

	// debugging
	function _debugMessage($message) {
		$doDebug = False;
		if($doDebug == TRUE) {
			$fp = fopen("/temp/zzcer_login.ldap.plugin.log", "a");
			$fmessage = date("M-d-y, H:i:s") . ": " . $message . "\n\r";

			if(fwrite($fp, $fmessage) == FALSE) {
				echo "[ERROR]: Unable to write log message: $fmessage!";
				echo " Attempted to write to: /tmp/cer_login.ldap.plugin.log";
				exit;
			}
		}
	}

};

?>
