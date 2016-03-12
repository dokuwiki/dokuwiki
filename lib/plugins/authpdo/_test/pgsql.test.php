<?php

/**
 * pgsql tests for the authpdo plugin
 *
 * @group plugin_authpdo
 * @group plugins
 */
class pgsql_plugin_authpdo_test extends mysql_plugin_authpdo_test {

    protected $driver = 'pgsql';

    public function test_requirements() {
        parent::test_requirements();

        if(!function_exists('hash_pbkdf2') || !in_array('sha256', hash_algos())){
            $this->markTestSkipped("Skipped {$this->driver} tests. Missing pbkdf2 hash support to check passwords");
        }
    }

}
