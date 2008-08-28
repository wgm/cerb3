<?php
require_once("../site.config.php");
require_once(FILESYSTEM_PATH . "cerberus-api/pop3/cer_Pop3.class.php");

die("Edit this file to enable it."); // comment to run
// [JAS]: [TODO] Needs IP security here

$client = new cer_Pop3Client("localhost","110","user","pass",10);
$client->setDebug(false);

if($client->connect()) {
	if($client->debug) echo "Connected...<br>";
	if($client->pop3_user()) {
		if($client->debug) echo "Sent User...<br>";
		if($client->pop3_pass()) {
			if($client->debug) echo "Sent Password...<br>";
			if($client->pop3_stat()) {
				if($client->debug) echo "Stat... " . $client->messageCount . " messages on the server.<br>";
				foreach($client->messageListArray as $id => $size) {
					$email = $client->pop3_retr($id);
					$params = array('include_bodies' => false, 'decode_bodies' => false, 'decode_headers' => true);
					
					if(FALSE!==$email) {
						$decoder = new Mail_mimeDecode($email);
						$structure = $decoder->decode($params);
						print_r($structure);
						echo "<hr>";
//						$xml = $decoder->getXML($structure);
//						echo $xml;
					}
				}
			}
		}
	}
	
	$client->pop3_quit();
}

// [bgh] disconnect from the server
$client->disconnect();
