<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2005, WebGroup Media LLC
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
|		Ben Halsted			(ben@webgroupmedia.com)			[BGH]
|		Jeff Standen		(jeff@webgroupmedia.com)		[JAS]
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

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

require_once(FILESYSTEM_PATH . 'cerberus-api/mail/mimeDecode.php');

class cer_Pop3Client {
	var $host;
	var $port;
	var $user;
	var $pass;
	var $state;
	var $tls;
	var $socket;
	var $error;
	var $lineBuffer;
	var $readBuffer;
	var $messageCount;
	var $debug;
	var $timeout;
	var $messageListArray;
	var $lineTerminator;
		
	function cer_Pop3Client($host, $port, $user, $pass, $timeout, $terminator="\r\n") {
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
		$this->timeout = $timeout;
		
		$this->tls = $tls;
		$this->socket = null;
		$this->tls = false;
		$this->state = 0;
		$this->clearBuffers();
		$this->debug = false;
		$this->messageListArray = array();
		$this->lineTerminator = $terminator;
	}
	
	function setDebug($bool) {
		$this->debug = $bool;
	}
	
	function clearBuffers() {
		if($this->debug) { echo "Clearing Buffers... <br>"; flush(); }
		$this->lineBuffer = array();
		$this->readBuffer = "";
	}
	
	function socketError() {
//		$this->error = "Could not create the socket, the error code is: " . socket_last_error() . ",error message is: " . socket_strerror(socket_last_error());
		$this->error = "-ERR Could not create the socket";
		if($this->debug) { echo "socketError() called... <br>"; echo $this->error . "<br>"; flush(); }
	}
	
	function splitBuffer() {
		if($this->debug) { echo "Trying to split the buffer of length " . strlen($this->readBuffer) . "... <br>"; flush(); }
		if(0<strlen($this->readBuffer)) {
			if($this->debug) { print_r(htmlentities($this->readBuffer)); flush(); }
			$linearray = explode($this->lineTerminator,$this->readBuffer);
//			if($this->debug) { print_r($linearray); flush(); }
			if(is_array($linearray)) {
				$this->readBuffer = array_pop($linearray);
				$this->lineBuffer = $this->lineBuffer + $linearray;
			}
		}
	}
	
	function socketRead() {
		if($this->debug) { echo "Inside socketRead()...<br>"; flush(); }
		$smbuff = "";
		$sockres;
		
//		$readers = array($this->socket);
		
//		stream_set_timeout($this->socket, $this->timeout);

//		$changeCount = socket_select($readers, $writers = NULL, $exceptions = NULL, $this->timeout);
		if(!$this->socket || feof($this->socket)) {
			if($this->debug) { echo "Had a socketRead() error...<br>"; flush(); }
			$changeCount = false;
		} else {
			$changeCount = 1;
		}
		
		if(FALSE===$changeCount) {
			$this->socketError();
			return false;
		}
		else if (0<$changeCount) {
//			if(FALSE!==($smbuff=socket_read($this->socket,1024,PHP_BINARY_READ))) {
			if(FALSE!==($smbuff=fgets($this->socket,1024))) {
				$smbuff = str_replace(array("\r","\n"),"",$smbuff);
				$smbuff .= "\r\n";
				if($this->debug) { echo "socket_read (" . $sockres . "): " . htmlentities($smbuff) . "<br>"; flush(); }
				$this->readBuffer .= $smbuff;
			} else {
				$this->socketError();
				if($this->debug) { echo "socketError in socketRead()<br>"; flush(); }
				return false;
			}
		} else {
			// [bgh] timeout
			$this->error = "-ERR The socket read timed out.";
			if($this->debug) { echo $this->error . "<br>"; flush(); }
			return false;
		}
		
		return true;
	}
	
	
	function socketWrite($buffer) {
		
		$length = strlen($buffer);
		
		if($this->debug) { echo "Inside socketWrite()...<br>"; flush(); }
		if($this->debug) { echo "Writing '" . htmlentities($buffer) . "'...<br>"; flush(); }
		
		while(0<$length) {
//			$sentCount = socket_write($this->socket, $buffer);
			$sentCount = fputs($this->socket, $buffer);
			
			if(FALSE === $sentCount) {
				$this->socketError();
				return false;
			}
			
			if($this->debug) { echo "  Wrote " . $sentCount . " bytes of " . $length . "...<br>"; flush(); }
			$length-=$sentCount;
			$buffer = substr($buffer,$sentCount);
		}
		
		return true;
	}
	
	
	function commandResult() {
		if($this->debug) { echo "commandResult() Need a line to check... <br>"; flush(); }
		// $line = $this->socketReadLine();
		$line = fgets($this->socket);
		if($this->isError($line)) {
			return false;
		}
		
		return true;
	}
	
	function socketReadLine() {
		if($this->debug) { echo "Inside socketReadLine()... " . count($this->lineBuffer) . " lines ready for reading.<br>"; flush(); }
		while(0==count($this->lineBuffer)) {
//			if($this->debug) { echo "lineBuffer: "; print_r($this->lineBuffer); echo "<br>"; flush(); }
			if($this->debug) { echo "readBuffer: "; print_r($this->readBuffer); echo "<br>"; flush(); }
			if($this->debug) { echo "socketReadLine() Need to read more to make a line... <br>"; flush(); }
			if($this->socketRead()) {
				if($this->debug) { echo "socketReadLine() got more! Splitting... <br>"; flush(); }
				$this->splitBuffer();
			} else {
				if($this->debug) { echo "socketReadLine() had an error =( ... <br>"; flush(); }
				return null;
			}
		}
		
		return array_shift($this->lineBuffer);
	}
	
	function socketUnReadLine($line) {
		if(is_string($line)) {
			array_unshift($this->lineBuffer, $line);	
		}
	}
	
	/**
	 * @return true or false
	 */
	function connect() {
//		if($this->debug) { echo "About to create the socket... <br>"; flush(); }
//		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//
//		if(FALSE === $this->socket) {
//			$this->socketError();
//			return false;
//		}
//		
//		if(!socket_connect($this->socket, $this->host, $this->port)) {
//			$this->socketError();
//			return false;
//		}

		$this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 15);
//		socket_set_blocking($this->socket,0); // don't block
		
		if(!$this->socket) {
			die($errstr . " [$errno]");
			return false;
		}

		return $this->commandResult();
	}
	
	function disconnect() {
		if($this->debug) echo "About to disconnect... <br>";
		
//		socket_shutdown($this->socket, 2);
//		socket_close($this->socket);

		fclose($this->socket);
	}
	
	function pop3_dele($message_id) {
		if($this->debug) { echo "Sending dele command... <br>"; flush(); }
		$buf = "DELE " . $message_id . "\r\n";
		
		if(!$this->socketWrite($buf)) {
			return false;
		} 
		
		return $this->commandResult();
	}
	
	/**
	 * @return true or false if it is an error
	 */
	function isError($msg) {
		
		if(0==strncasecmp($msg,"-err",4)) {
			return true;
		}
		
		return false;
	}
	
	function pop3_pass() {
		$buf = "PASS " . $this->pass . "\r\n";
		$this->socketWrite($buf);
		
		return $this->commandResult();
	}
	
	function pop3_quit() {
		$buf = "QUIT\r\n";
		$this->socketWrite($buf);
		
		return $this->commandResult();
	}
	
	function pop3_retr($messId) {
		$buf = "RETR " . $messId . "\r\n";
		$this->socketWrite($buf);
		
		$this->clearBuffers();
		
		$line = $this->socketReadLine();
		
		if(null != $line && !$this->isError($line)) {
			$email = "";
			while(true) {
				if($this->debug) { echo "Reading a line...<br>"; flush(); }
				$line = $this->socketReadLine();
				flush();
				if(null === $line || 0==strcmp(".",$line)) {
					if($this->debug) { echo "Found the message terminator '.'<br>"; flush(); }
					$email = str_replace(chr(0),"",$email);
					return $email;
				}
				if ($email == ""							// $email is empty (this is the first line)
					&& substr($line, 0, 5) == "From ") {	// beginning of $line is "From "
					// Do nothing... this is an invalid header line.
				} else {
					$email .= $line . $this->lineTerminator;
				}
			}
		}
		return FALSE;
	}
	
	/**
	 * @return returns true for has messages and 0 for no messages
	 */
	function pop3_stat() {
		$buf = "STAT\r\n";
		$this->socketWrite($buf);
		
		$line = $this->socketReadLine();
		
		list($status, $count, $bytes) = sscanf($line, "%s %d %d");
		
		if($this->isError($status)) {
			return false;
		}
		
		if(0<$count) {
			$buf = "LIST\r\n";
			$this->socketWrite($buf);
			if($this->commandResult()) {
				for($x=0; $x<$count; $x++) {
					$line = $this->socketReadLine();
					list($id, $bytes) = sscanf($line, "%d %d");
					$this->messageListArray[$id] = $bytes;
					if($this->debug) { echo "Message " . $id . " is " . $bytes . " bytes... <br>"; flush(); }
				}
			}
		}

//		for($x=1; $x<=$count; $x++) {
////			$line = $this->socketReadLine();
////			list($id, $bytes) = sscanf($line, "%d %d");
//			$this->messageListArray[$x] = 0;
////			if($this->debug) { echo "Message " . $id . " is " . $bytes . " bytes... <br>"; flush(); }
////			if($this->debug) { echo "Message " . $id . " is " . $bytes . " bytes... <br>"; flush(); }
//		}
		
		return true;
	}
	
	function pop3_user() {
		if($this->debug) { echo "Sending user name... <br>"; flush(); }
		$buf = "USER " . $this->user . "\r\n";
		
		if(!$this->socketWrite($buf)) {
			return false;
		} 
		
		return $this->commandResult();
	}
}


class cer_Pop3Parser {
	var $clients;
	
	function cer_Pop3 () {
		 $this->clients = array();
	}

	function canTLS() {
		// [bgh] TODO: implement
	}

	function addClient($clientInfo) {
		$this->clients[] = $clientInfo;
	}
	
	function next() {
		
	}
	
	function run() {
		print_r($clients);
		foreach ($this->clients as $client) {
			// [bgh] connect
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
								$params = array('include_bodies' => true, 'decode_bodies' => true, 'decode_headers' => true);
								
								if(FALSE!==$email) {
									$decoder = new Mail_mimeDecode($email);
									$structure = $decoder->decode($params);	
									$xml = $decoder->getXML($structure);
									$parser = new cer_EmailParser();
									$parser->parseXml($xml);
								}
							}
						}
					}	
				}
				
				$client->pop3_quit();
			}
			
			// [bgh] disconnect from the server
			$client->disconnect();
		}	
	}
}