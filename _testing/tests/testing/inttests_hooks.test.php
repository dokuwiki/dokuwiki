<?php

/**
 * @group integration
 */
class InttestsHooksTest extends DokuWikiTest {

	function testHookTriggering() {
		$request = new TestRequest();

		$hookTriggered = false;
		$request->hook('TPL_CONTENT_DISPLAY', 'AFTER', function() use (&$hookTriggered) {
			$hookTriggered = true;
		});

		$request->execute();

		$this->assertTrue($hookTriggered, 'Hook was not triggered as expected!');
	}
}
