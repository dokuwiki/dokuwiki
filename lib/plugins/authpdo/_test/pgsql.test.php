<?php

/**
 * pgsql tests for the authpdo plugin
 *
 * @group plugin_authpdo
 * @group plugins
 */
class pgsql_plugin_authpdo_test extends mysql_plugin_authpdo_test {

    protected $driver = 'pgsql';

}
