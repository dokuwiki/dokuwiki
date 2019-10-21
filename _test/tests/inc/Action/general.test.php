<?php

use dokuwiki\Action\AbstractAclAction;
use dokuwiki\Action\AbstractUserAction;
use dokuwiki\Action\Exception\ActionAclRequiredException;
use dokuwiki\Action\Exception\ActionDisabledException;
use dokuwiki\Action\Exception\ActionUserRequiredException;

class action_general extends DokuWikiTest {

    public function dataProvider() {
        return array(
            array('Login', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Logout', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Search', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Recent', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Profile', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('ProfileDelete', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Index', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Sitemap', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Denied', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Register', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Resendpwd', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Backlink', AUTH_NONE, array('exists' => true, 'ismanager' => false)),

            array('Revert', AUTH_ADMIN, array('exists' => true, 'ismanager' => false)),
            array('Revert', AUTH_EDIT, array('exists' => true, 'ismanager' => true)),

            array('Admin', AUTH_READ, array('exists' => true, 'ismanager' => false)), // let in, check later again
            array('Admin', AUTH_READ, array('exists' => true, 'ismanager' => true)), // let in, check later again

            array('Check', AUTH_READ, array('exists' => true, 'ismanager' => false)), // sensible?
            array('Diff', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Show', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Subscribe', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Locked', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Source', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Export', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Media', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Revisions', AUTH_READ, array('exists' => true, 'ismanager' => false)),

            array('Draftdel', AUTH_EDIT, array('exists' => true, 'ismanager' => false)),

            // aliases
            array('Cancel', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Recover', AUTH_NONE, array('exists' => true, 'ismanager' => false)),

            // EDITING existing page
            array('Save', AUTH_EDIT, array('exists' => true, 'ismanager' => false)),
            array('Conflict', AUTH_EDIT, array('exists' => true, 'ismanager' => false)),
            array('Draft', AUTH_EDIT, array('exists' => true, 'ismanager' => false)),
            //the edit function will check again and do a source show
            //when no AUTH_EDIT available:
            array('Edit', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Preview', AUTH_READ, array('exists' => true, 'ismanager' => false)),

            // EDITING new page
            array('Save', AUTH_CREATE, array('exists' => false, 'ismanager' => false)),
            array('Conflict', AUTH_CREATE, array('exists' => false, 'ismanager' => false)),
            array('Draft', AUTH_CREATE, array('exists' => false, 'ismanager' => false)),
            array('Edit', AUTH_CREATE, array('exists' => false, 'ismanager' => false)),
            array('Preview', AUTH_CREATE, array('exists' => false, 'ismanager' => false)),
        );
    }

    /**
     * @dataProvider dataProvider
     * @param $name
     * @param $expected
     * @param $info
     */
    public function testMinimumPermissions($name, $expected, $info) {
        global $INFO;
        $INFO = $info;

        $classname = 'dokuwiki\\Action\\' . $name;
        /** @var \dokuwiki\Action\AbstractAction $class */
        $class = new $classname();

        $this->assertSame($expected, $class->minimumPermission());
    }

    /**
     * All actions should handle the disableactions setting
     *
     * @dataProvider dataProvider
     * @param $name
     */
    public function testBaseClassActionOkPermission($name) {
        $this->assertTrue(true); // mark as not risky
        if($name == 'Show') return; // disabling show does not work

        $classname = 'dokuwiki\\Action\\' . $name;
        /** @var \dokuwiki\Action\AbstractAction $class */
        $class = new $classname();

        global $conf;
        $conf['useacl'] = 1;
        $conf['subscribers'] = 1;
        $conf['disableactions'] = '';
        $_SERVER['REMOTE_USER'] = 'someone';

        try {
            \dokuwiki\ActionRouter::getInstance(true)->checkAction($class);
        } catch(\Exception $e) {
            $this->assertNotSame(ActionDisabledException::class, get_class($e));
        }

        $conf['disableactions'] = $class->getActionName();

        try {
            \dokuwiki\ActionRouter::getInstance(true)->checkAction($class);
        } catch(\Exception $e) {
            $this->assertSame(ActionDisabledException::class, get_class($e), $e);
        }
    }

    /**
     * Actions inheriting from AbstractAclAction should have an ACL enabled check
     *
     * @dataProvider dataProvider
     * @param $name
     */
    public function testBaseClassAclPermission($name) {
        $classname = 'dokuwiki\\Action\\' . $name;
        /** @var \dokuwiki\Action\AbstractAction $class */
        $class = new $classname();
        $this->assertTrue(true); // mark as not risky
        if(!is_a($class, AbstractAclAction::class)) return;

        global $conf;
        $conf['useacl'] = 1;
        $conf['subscribers'] = 1;

        try {
            $class->checkPreconditions();
        } catch(\Exception $e) {
            $this->assertNotSame(ActionAclRequiredException::class, get_class($e));
        }

        $conf['useacl'] = 0;

        try {
            $class->checkPreconditions();
        } catch(\Exception $e) {
            $this->assertSame(ActionAclRequiredException::class, get_class($e));
        }
    }

    /**
     * Actions inheriting from AbstractUserAction should have user check
     *
     * @dataProvider dataProvider
     * @param $name
     */
    public function testBaseClassUserPermission($name) {
        $classname = 'dokuwiki\\Action\\' . $name;
        /** @var \dokuwiki\Action\AbstractAction $class */
        $class = new $classname();
        $this->assertTrue(true); // mark as not risky
        if(!is_a($class, AbstractUserAction::class)) return;

        global $conf;
        $conf['useacl'] = 1;
        $conf['subscribers'] = 1;
        $_SERVER['REMOTE_USER'] = 'test';

        try {
            $class->checkPreconditions();
        } catch(\Exception $e) {
            $this->assertNotSame(ActionUserRequiredException::class, get_class($e));
        }

        unset($_SERVER['REMOTE_USER']);

        try {
            $class->checkPreconditions();
        } catch(\Exception $e) {
            $this->assertSame(ActionUserRequiredException::class, get_class($e));
        }
    }
}
