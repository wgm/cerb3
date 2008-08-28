<?php
/***********************************************************************
| Cerberus Helpdesk (tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2004, WebGroup Media LLC
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
|		Mike Fogg    (mike@webgroupmedia.com)   [mdf]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/xml/xml.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/general/users.class.php");
require_once(FILESYSTEM_PATH . "gateway-api/classes/reports/jasper_report_list.class.php");
require_once(FILESYSTEM_PATH . 'includes/third_party/pclzip-2-5/pclzip.lib.php');

if(!defined('VALID_INCLUDE') || VALID_INCLUDE != 1) exit();

/**
 * This class handles logins
 *
 */
class get_report_source_handler extends xml_parser
{
   /**
    * XML data packet from client GUI
    *
    * @var object
    */
   var $xml;
   
   /**
    * Class constructor
    *
    * @param object $xml
    * @return login_handler
    */
   function get_report_source_handler(&$xml) {
      $this->xml =& $xml;
   }
   
   /**
    * main() function for this class. 
    *
    */
   function process() {
   	
   		$this->db =& database_loader::get_instance();
   	
   		$report_elm =& $this->xml->get_child("report", 0);
   		$id = $report_elm->get_attribute("id", FALSE);

   		$sql = sprintf("SELECT jasper_report_id, report_source, scriptlet_source
   		FROM jasper_reports
   		WHERE jasper_report_id = %d
   		", $id);
   		//echo $sql;exit();
   		$result = $this->db->direct->GetRow($sql);
   		//print_r($db);exit();
		//print_r($result);exit();
   		
   		if(is_array($result)) {
   			//parse the report source to determine the report name and scriptlet name for file naming
   			if($result['report_source'] == "") {
   				die("Error: no source exists for this report");
   			}
   			$report_source_arr = $this->parseReportSource($result['report_source']);
   			if($report_source_arr['report_name'] == '') {
   				die("Error: Couldn't read a report name from report source");
   			}
   			
   			$tmp_dir = FILESYSTEM_PATH . "tempdir/";
   			
   			//the archive will be <reportname>.zip
   			$archive_filename = $report_source_arr['report_name'] . '.zip';
   			$archive_filepath = $tmp_dir . $archive_filename;

   			//the report source file will be <reportname>.jrxml
   			$report_path = $tmp_dir . $report_source_arr['report_name'] . '.jrxml';
   			
   			//init the list of files to be zipped (later the scriptlet will be appended if necessary)
   			$ziplist = $report_path;
	   		
   			$report_src_file = fopen($report_path, 'w');
	   		fwrite($report_src_file, $result['report_source']);
			fclose($report_src_file);
			
			//if a scriptlet exists then create that file also
	   		if($result['scriptlet_source'] != "") {
	   			if($report_source_arr['scriptlet_name'] != '') {
			   		$scriptlet_path = $tmp_dir . $report_source_arr['scriptlet_name'].'.java';
			   		$ziplist .= ',' . $scriptlet_path;
		   			$scriptlet_src_file = fopen($scriptlet_path, 'w');
			   		fwrite($scriptlet_src_file, $result['scriptlet_source']);
			   		fclose($scriptlet_src_file);
	   			}	   			

   			}

   			$archive = new PclZip($archive_filepath);

   			//create the zip file
   			$v_list = $archive->create($ziplist, PCLZIP_OPT_REMOVE_ALL_PATH);
	   		
	   		if ($v_list == 0) {
   				 die("Error : ".$archive->errorInfo(true));
  			}
	   		//print_r($archive);exit();
	   		//print_r($v_list);exit();
   		
			header("Content-Type: application/force-download");
			header("Content-transfer-encoding: binary");
			header(sprintf('Content-Disposition: attachment; filename="%s"', $archive_filename));
			header(sprintf("Content-Length: %d", filesize($archive_filepath)));
	
			if(@readfile($archive_filepath) === FALSE) {
				die("Error reading temporary file " . $archive_filepath);
			}	   		

			@unlink($report_path);
			if(!empty($scriptlet_path)) {
				@unlink($scriptlet_path);
			}
      		@unlink($archive_filepath);
			exit();
			
   		}

   }        
   
   		
   		/**
   		 * Returns an array containing the name for the scriptlet and the report obtained
   		 * from the actual jasper report xml
   		 */
   		function parseReportSource($report_source) {
			$xml_parser =& new xml_parser();
			$xml_object =& $xml_parser->expat_parse($report_source);
			
			$report_xml =& $xml_object->get_child("jasperReport", 0);
			$returnVal['report_name'] = str_replace(' ', '_', $report_xml->get_attribute("name", false));
			$returnVal['scriptlet_name'] = $report_xml->get_attribute("scriptletClass", false);
			
			return $returnVal;
   		}   
   
   
}

