<?php

class HooksTest extends PHPUnit_Framework_TestCase {

	var $hookTriggered = false;

	function hookTriggered() {
		$this->hookTriggered = true;
	}

	function testHookTriggering() {
		global $EVENT_HANDLER;
		$EVENT_HANDLER->register_hook('TPL_CONTENT_DISPLAY', 'AFTER', $this, 'hookTriggered');

		$request = new TestRequest();
		$request->execute();

		$this->assertTrue($this->hookTriggered, 'Hook was not triggered as expected!');
	}
}
