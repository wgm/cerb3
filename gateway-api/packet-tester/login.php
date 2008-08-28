<html>
<body onload="javascript: document.login_xml.submit();">
<form name="login_xml" method="post" action="../../gateway.php">
<input type="hidden" name="xml" 
value="<cerberus_xml><channel>general</channel><module>authentication</module><command>login</command><data><username><?php echo $_REQUEST['username'];?></username><password><?php echo $_REQUEST['password'];?></password></data></cerberus_xml>"
>
</form>
</body>
</html>
