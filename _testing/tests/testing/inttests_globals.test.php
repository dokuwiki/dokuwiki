<?php

/**
 * @group integration
 */
class InttestsGlobalsTest extends PHPUnit_Framework_TestCase {
	/**
	 * Global variables should be restored for every test case.
	 */
	function testFirstRun() {
		$this->assertEquals('87.142.120.6', $_SERVER['REMOTE_ADDR'], 'Global var not set as expected');

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	}

	/**
	 * @depends testFirstRun
	 */
	function testSecondRun() {
		$this->assertEquals('87.142.120.6', $_SERVER['REMOTE_ADDR'], 'Global var not set as expected');
	}
}
