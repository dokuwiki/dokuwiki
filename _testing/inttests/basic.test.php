<?php

class BasicTest extends PHPUnit_Framework_TestCase {
	function testSimpleRun() {
		$request = new TestRequest();

		$response = $request->execute();

		$this->assertTrue(
			strpos($response->getContent(), 'DokuWiki') >= 0,
			'DokuWiki was not a word in the output'
		);
	}
}
