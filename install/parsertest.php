<?PHP
die("Not enabled."); // comment to use
?>
<html>
<head>
<title>Test XML + File Post form for Cerberus 3</title>
</head>
<body>
<?php
if(!isset($_POST["host"])) {
        if(empty($PHP_SELF)) {
                $PHP_SELF = $_SERVER["PHP_SELF"];
        }
?>
To use this form you have to post twice so we can get the form set up properly with the testpost.file location.
<form action="<?php echo $PHP_SELF; ?>" method="POST">
Email Host: <input type="text" name="host" value="mail.domain.com">&nbsp;Port: <input type="text" name="port" value="110"><br />
Login User: <input type="text" name="user" value="username"><br />
Login Password: <input type="text" name="pass" value="password"><br />
<input type="submit" value="To step 2!"><br />
</form>
<?php
} else {

define("FILESYSTEM_PATH",getcwd() . '/../');

require_once(FILESYSTEM_PATH . "cerberus-api/pop3/cer_Pop3.class.php");

$client = new cer_Pop3Client($_POST["host"],$_POST["port"],$_POST["user"],$_POST["pass"],10);
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
                                        $params = array('include_bodies' => true, 'decode_bodies' => true, 'decode_headers' => true);

                                        if(FALSE!==$email) {
                                                $decoder = new Mail_mimeDecode($email);
                                                $structure = $decoder->decode($params);
                                                $xml = $decoder->getXML($structure);

?>
<form action="../parser.php" method="POST" enctype="multipart/form-data">
XML To be Sent:<textarea name="xml" cols="80" rows="30">
<?php

                                                echo htmlspecialchars(trim($xml));
?>
</textarea><br />
<input type="submit" value="Test it!"><br />
</form>
<?php
                                        }
                                }
                        }
                }
        }

        $client->pop3_quit();
}

// [bgh] disconnect from the server
$client->disconnect();
} ?>
</body>
</html>
