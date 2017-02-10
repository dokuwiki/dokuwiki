<?php

class action_minimumPermissions extends DokuWikiTest {



    public function dataProvider() {
        return array (
            array('Login', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Logout', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Search', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Recent', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            //array('Profile', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            //array('Profile_delete', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            //array('Index', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Sitemap', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Denied', AUTH_NONE, array('exists' => true, 'ismanager' => false)),

            array('Check', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Diff', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Show', AUTH_READ, array('exists' => true, 'ismanager' => false)),
            array('Subscribe', AUTH_READ, array('exists' => true, 'ismanager' => false)),



            /*
            array('', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            */

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

        $classname = 'dokuwiki\\Action\\'.$name;
        /** @var \dokuwiki\Action\AbstractAction $class */
        $class = new $classname();

        $this->assertSame($expected, $class->minimumPermission());
    }
}
