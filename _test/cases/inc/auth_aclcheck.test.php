<?php

require_once DOKU_INC.'inc/init.php';
require_once DOKU_INC.'inc/auth.php';

class auth_acl_test extends UnitTestCase {

    function teardown() {
        global $conf;
        global $AUTH_ACL;
        unset($conf);
        unset($AUTH_ACL);

    }

    function test_restricted(){
        global $conf;
        global $AUTH_ACL;
        $conf['superuser'] = 'john';
        $conf['useacl']    = 1;

        $AUTH_ACL = array(
            '*           @ALL           0',
            '*           @user          8',
        );

        // anonymous user
        $this->assertEqual(auth_aclcheck('page',          '',array()), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:page','',array()), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:*',   '',array()), AUTH_NONE);

        // user with no matching group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo')), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo')), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo')), AUTH_NONE);

        // user with matching group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo','user')), AUTH_UPLOAD);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo','user')), AUTH_UPLOAD);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo','user')), AUTH_UPLOAD);

        // super user
        $this->assertEqual(auth_aclcheck('page',          'john',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:page','john',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:*',   'john',array('foo')), AUTH_ADMIN);
    }

    function test_restricted_ropage(){
        global $conf;
        global $AUTH_ACL;
        $conf['superuser'] = 'john';
        $conf['useacl']    = 1;

        $AUTH_ACL = array(
            '*                  @ALL           0',
            '*                  @user          8',
            'namespace:page     @user          1',
        );

        // anonymous user
        $this->assertEqual(auth_aclcheck('page',          '',array()), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:page','',array()), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:*',   '',array()), AUTH_NONE);

        // user with no matching group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo')), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo')), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo')), AUTH_NONE);

        // user with matching group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo','user')), AUTH_UPLOAD);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo','user')), AUTH_READ);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo','user')), AUTH_UPLOAD);

        // super user
        $this->assertEqual(auth_aclcheck('page',          'john',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:page','john',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:*',   'john',array('foo')), AUTH_ADMIN);
    }

    function test_aclexample(){
        global $conf;
        global $AUTH_ACL;
        $conf['superuser'] = 'john';
        $conf['useacl']    = 1;

        $AUTH_ACL = array(
            '*                     @ALL        4',
            '*                     bigboss    16',
            'start                 @ALL        1',
            'marketing:*           @marketing  8',
            'devel:*               @ALL        0',
            'devel:*               @devel      8',
            'devel:*               bigboss    16',
            'devel:funstuff        bigboss     0',
            'devel:*               @marketing  1',
            'devel:marketing       @marketing  2',
        );


        $this->assertEqual(auth_aclcheck('page', ''        ,array())            , AUTH_CREATE);
        $this->assertEqual(auth_aclcheck('page', 'bigboss' ,array('foo'))       , AUTH_DELETE);
        $this->assertEqual(auth_aclcheck('page', 'jill'    ,array('marketing')) , AUTH_CREATE);
        $this->assertEqual(auth_aclcheck('page', 'jane'    ,array('devel'))     , AUTH_CREATE);

        $this->assertEqual(auth_aclcheck('start', ''        ,array())            , AUTH_READ);
        $this->assertEqual(auth_aclcheck('start', 'bigboss' ,array('foo'))       , AUTH_READ);
        $this->assertEqual(auth_aclcheck('start', 'jill'    ,array('marketing')) , AUTH_READ);
        $this->assertEqual(auth_aclcheck('start', 'jane'    ,array('devel'))     , AUTH_READ);

        $this->assertEqual(auth_aclcheck('marketing:page', ''        ,array())            , AUTH_CREATE);
        $this->assertEqual(auth_aclcheck('marketing:page', 'bigboss' ,array('foo'))       , AUTH_DELETE);
        $this->assertEqual(auth_aclcheck('marketing:page', 'jill'    ,array('marketing')) , AUTH_UPLOAD);
        $this->assertEqual(auth_aclcheck('marketing:page', 'jane'    ,array('devel'))     , AUTH_CREATE);


        $this->assertEqual(auth_aclcheck('devel:page', ''        ,array())            , AUTH_NONE);
        $this->assertEqual(auth_aclcheck('devel:page', 'bigboss' ,array('foo'))       , AUTH_DELETE);
        $this->assertEqual(auth_aclcheck('devel:page', 'jill'    ,array('marketing')) , AUTH_READ);
        $this->assertEqual(auth_aclcheck('devel:page', 'jane'    ,array('devel'))     , AUTH_UPLOAD);

        $this->assertEqual(auth_aclcheck('devel:funstuff', ''        ,array())            , AUTH_NONE);
        $this->assertEqual(auth_aclcheck('devel:funstuff', 'bigboss' ,array('foo'))       , AUTH_NONE);
        $this->assertEqual(auth_aclcheck('devel:funstuff', 'jill'    ,array('marketing')) , AUTH_READ);
        $this->assertEqual(auth_aclcheck('devel:funstuff', 'jane'    ,array('devel'))     , AUTH_UPLOAD);

        $this->assertEqual(auth_aclcheck('devel:marketing', ''        ,array())            , AUTH_NONE);
        $this->assertEqual(auth_aclcheck('devel:marketing', 'bigboss' ,array('foo'))       , AUTH_DELETE);
        $this->assertEqual(auth_aclcheck('devel:marketing', 'jill'    ,array('marketing')) , AUTH_EDIT);
        $this->assertEqual(auth_aclcheck('devel:marketing', 'jane'    ,array('devel'))     , AUTH_UPLOAD);

    }

    function test_multiadmin_restricted(){
        global $conf;
        global $AUTH_ACL;
        $conf['superuser'] = 'john,@admin,doe,@roots';
        $conf['useacl']    = 1;

        $AUTH_ACL = array(
            '*           @ALL           0',
            '*           @user          8',
        );

        // anonymous user
        $this->assertEqual(auth_aclcheck('page',          '',array()), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:page','',array()), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:*',   '',array()), AUTH_NONE);

        // user with no matching group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo')), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo')), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo')), AUTH_NONE);

        // user with matching group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo','user')), AUTH_UPLOAD);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo','user')), AUTH_UPLOAD);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo','user')), AUTH_UPLOAD);

        // super user john
        $this->assertEqual(auth_aclcheck('page',          'john',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:page','john',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:*',   'john',array('foo')), AUTH_ADMIN);

        // super user doe
        $this->assertEqual(auth_aclcheck('page',          'doe',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:page','doe',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:*',   'doe',array('foo')), AUTH_ADMIN);

        // user with matching admin group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo','admin')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo','admin')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo','admin')), AUTH_ADMIN);

        // user with matching another admin group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo','roots')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo','roots')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo','roots')), AUTH_ADMIN);
    }

    function test_multiadmin_restricted_ropage(){
        global $conf;
        global $AUTH_ACL;
        $conf['superuser'] = 'john,@admin,doe,@roots';
        $conf['useacl']    = 1;

        $AUTH_ACL = array(
            '*                  @ALL           0',
            '*                  @user          8',
            'namespace:page     @user          1',
        );

        // anonymous user
        $this->assertEqual(auth_aclcheck('page',          '',array()), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:page','',array()), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:*',   '',array()), AUTH_NONE);

        // user with no matching group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo')), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo')), AUTH_NONE);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo')), AUTH_NONE);

        // user with matching group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo','user')), AUTH_UPLOAD);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo','user')), AUTH_READ);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo','user')), AUTH_UPLOAD);

        // super user john
        $this->assertEqual(auth_aclcheck('page',          'john',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:page','john',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:*',   'john',array('foo')), AUTH_ADMIN);

        // super user doe
        $this->assertEqual(auth_aclcheck('page',          'doe',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:page','doe',array('foo')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:*',   'doe',array('foo')), AUTH_ADMIN);

        // user with matching admin group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo','admin')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo','admin')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo','admin')), AUTH_ADMIN);

        // user with matching another admin group
        $this->assertEqual(auth_aclcheck('page',          'jill',array('foo','roots')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:page','jill',array('foo','roots')), AUTH_ADMIN);
        $this->assertEqual(auth_aclcheck('namespace:*',   'jill',array('foo','roots')), AUTH_ADMIN);
    }

}

//Setup VIM: ex: et ts=4 :
