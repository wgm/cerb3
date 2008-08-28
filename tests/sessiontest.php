<?php
session_start();
setcookie("testcookie","testvalue");

if(empty($_SESSION['count'])) {
	$count = 0;
	session_register($count);
}

$_SESSION['count']++;

echo 'Counter: ' . $_SESSION['count'] . "<BR>";

print_r($_COOKIE);
?>