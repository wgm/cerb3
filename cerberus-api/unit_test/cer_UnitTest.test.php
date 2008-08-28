<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
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
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

require_once(FILESYSTEM_PATH . "cerberus-api/unit_test/cer_UnitTest.class.php");

class cer_Assert_Test extends cer_TestCase
{
	function cer_Assert_Test($name)
	{
		$this->cer_TestCase($name); // [JAS]: Call the parent object constructor.
	}
	
	function testAssert()
	{
		$boolean = true;
		$this->assert($boolean);
	}	

	function testAssertEquals()
	{
		$a = 5;
		$b = 5;
		$this->assertEquals($a,$b);
	}	

	function testAssertRegexp()
	{
	    $this->assertRegexp('/fo+ba[^a-m]/', 'foobar');
	}
	
};

?>