<?php

require_once DOKU_INC.'inc/init.php';
require_once DOKU_INC.'inc/auth.php';

class auth_admin_test extends UnitTestCase {

    function teardown() {
        global $conf;
        global $AUTH_ACL;
        unset($conf);
        unset($AUTH_ACL);

    }

    function test_ismanager(){
        global $conf;
        $conf['superuser'] = 'john,@admin';
        $conf['manager'] = 'john,@managers,doe';

        // anonymous user
        $this->assertEqual(auth_ismanager('jill', null,false), false);

        // admin or manager users
        $this->assertEqual(auth_ismanager('john', null,false), true);
        $this->assertEqual(auth_ismanager('doe',  null,false), true);

        // admin or manager groups
        $this->assertEqual(auth_ismanager('jill', array('admin'),false), true);
        $this->assertEqual(auth_ismanager('jill', array('managers'),false), true);
    }

    function test_isadmin(){
        global $conf;
        $conf['superuser'] = 'john,@admin,doe,@roots';

        // anonymous user
        $this->assertEqual(auth_ismanager('jill', null,true), false);

        // admin user
        $this->assertEqual(auth_ismanager('john', null,true), true);
        $this->assertEqual(auth_ismanager('doe',  null,true), true);

        // admin groups
        $this->assertEqual(auth_ismanager('jill', array('admin'),true), true);
        $this->assertEqual(auth_ismanager('jill', array('roots'),true), true);
        $this->assertEqual(auth_ismanager('john', array('admin'),true), true);
        $this->assertEqual(auth_ismanager('doe',  array('admin'),true), true);
    }

}

//Setup VIM: ex: et ts=4 :
