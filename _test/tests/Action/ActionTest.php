<?php

namespace dokuwiki\test\Action;

use dokuwiki\Action\AbstractAclAction;
use dokuwiki\Action\AbstractUserAction;
use dokuwiki\Action\Exception\ActionAclRequiredException;
use dokuwiki\Action\Exception\ActionDisabledException;
use dokuwiki\Action\Exception\ActionUserRequiredException;
use dokuwiki\Action\Exception\NoActionException;
use dokuwiki\Action\Export;
use dokuwiki\Action\Media;
use dokuwiki\Action\ProfileDelete;

class ActionTest extends \DokuWikiTest
{

    public function dataProvider()
    {
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
            array('Authtoken', AUTH_NONE, array('exists' => true, 'ismanager' => false)),
            array('Plugin', AUTH_NONE, array('exists' => true, 'ismanager' => false)),

            array('Revert', AUTH_EDIT, array('exists' => true, 'ismanager' => false)),
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
            array('Redirect', AUTH_NONE, array('exists' => true, 'ismanager' => false)),

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
    public function testMinimumPermissions($name, $expected, $info)
    {
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
    public function testBaseClassActionOkPermission($name)
    {
        $this->assertTrue(true); // mark as not risky
        if ($name == 'Show') return; // disabling show does not work

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
        } catch (\Exception $e) {
            $this->assertNotSame(ActionDisabledException::class, get_class($e));
        }

        $conf['disableactions'] = $class->getActionName();

        try {
            \dokuwiki\ActionRouter::getInstance(true)->checkAction($class);
        } catch (\Exception $e) {
            $this->assertSame(ActionDisabledException::class, get_class($e), $e);
        }
    }

    /**
     * The class names of all actions, the base classes they inherit from excluded
     *
     * Using this instead of a hand kept list means a newly added action is covered by
     * the tests it feeds without anyone having to remember them.
     *
     * @return array
     */
    public function actionClassProvider()
    {
        $data = [];
        foreach (glob(DOKU_INC . 'inc/Action/*.php') as $file) {
            $name = basename($file, '.php');
            if (!(new \ReflectionClass('dokuwiki\\Action\\' . $name))->isInstantiable()) continue;
            $data[$name] = [$name];
        }
        return $data;
    }

    /**
     * Every action is reachable by the name it reports and no two of them share a name
     *
     * A class name spelled differently from the action name is not caught here, because
     * class names match case insensitively once the class has been loaded.
     *
     * @dataProvider actionClassProvider
     * @param string $name the class name of the action
     */
    public function testActionNameResolvesToItsClass($name)
    {
        $classname = 'dokuwiki\\Action\\' . $name;
        /** @var \dokuwiki\Action\AbstractAction $class */
        $class = new $classname();

        $loaded = \dokuwiki\ActionRouter::getInstance(true)->loadAction($class->getActionName());
        $this->assertSame($classname, get_class($loaded));
    }

    /**
     * A disabled action may not be reached by spelling its name differently
     *
     * None of the variants names an action today, so they are rejected before the disable
     * check is ever reached. Should one of them start to resolve, the disable check has to
     * catch it, which is why both outcomes are accepted here.
     *
     * @dataProvider actionClassProvider
     * @param string $name the class name of the action
     */
    public function testDisabledActionNameVariants($name)
    {
        $this->assertTrue(true); // mark as not risky
        if ($name == 'Show') return; // disabling show does not work

        $classname = 'dokuwiki\\Action\\' . $name;
        /** @var \dokuwiki\Action\AbstractAction $class */
        $class = new $classname();

        $actionname = $class->getActionName();

        $variants = [
            $actionname . '_',
            '_' . $actionname,
            '__' . $actionname . '__',
            $actionname . '_zzz',
        ];
        // export_zzz is a renderer mode of its own, so it is not the disabled export
        if ($class instanceof Export) array_pop($variants);

        global $conf;
        $conf['useacl'] = 1;
        $conf['subscribers'] = 1;
        $conf['disableactions'] = $actionname;
        $_SERVER['REMOTE_USER'] = 'someone';

        $router = \dokuwiki\ActionRouter::getInstance(true);
        foreach ($variants as $variant) {
            try {
                $router->checkAction($router->loadAction($variant));
                $this->fail("'$variant' was accepted while '$actionname' is disabled");
            } catch (NoActionException | ActionDisabledException $e) {
                $this->assertTrue(true); // mark as not risky
            }
        }
    }

    /**
     * These are all the shapes an action name may have
     */
    public function testLoadActionAcceptedNames()
    {
        $router = \dokuwiki\ActionRouter::getInstance(true);

        $this->assertInstanceOf(Media::class, $router->loadAction('media'));
        $this->assertInstanceOf(ProfileDelete::class, $router->loadAction('profile_delete'));
        $this->assertInstanceOf(Export::class, $router->loadAction('export_raw'));

        // renderer plugins may provide components, the mode is taken from the action name
        $export = $router->loadAction('export_odt_book');
        $this->assertInstanceOf(Export::class, $export);
        $this->assertSame('export_odt_book', $export->getActionName());
    }

    /**
     * Names that look like an action but are none
     *
     * @return array
     */
    public function invalidNameProvider()
    {
        return [
            ['media_'],
            ['_media'],
            ['__media__'],
            ['media_zzz'],
            ['me_dia'],
            ['medi_a'],
            ['export_raw_'],
            ['_export_raw'],
            ['export__raw'],
            ['profile_delete_'],
        ];
    }

    /**
     * Anything not matching the accepted shapes is no action at all
     *
     * @dataProvider invalidNameProvider
     * @param $name
     */
    public function testLoadActionRejectedNames($name)
    {
        $this->expectException(NoActionException::class);
        \dokuwiki\ActionRouter::getInstance(true)->loadAction($name);
    }

    /**
     * The base classes the actions inherit from are no actions themselves
     *
     * Should a base class ever be named without the abstract prefix, this fails and the
     * exclusion in loadAction() needs to be adjusted.
     */
    public function testLoadActionRejectedBaseClasses()
    {
        $router = \dokuwiki\ActionRouter::getInstance(true);

        $checked = 0;
        foreach (glob(DOKU_INC . 'inc/Action/*.php') as $file) {
            $name = basename($file, '.php');
            $class = 'dokuwiki\\Action\\' . $name;
            // the reflection autoloads the class, so a lowercase spelling of it resolves from here on
            if ((new \ReflectionClass($class))->isInstantiable()) continue;
            $checked++;

            try {
                $router->loadAction(strtolower($name));
                $this->fail("base class $class was accepted as an action");
            } catch (NoActionException $e) {
                $this->assertTrue(true); // mark as not risky
            }
        }

        $this->assertGreaterThan(0, $checked, 'no base classes were found to check');
    }

    /**
     * Actions inheriting from AbstractAclAction should have an ACL enabled check
     *
     * @dataProvider dataProvider
     * @param $name
     */
    public function testBaseClassAclPermission($name)
    {
        $classname = 'dokuwiki\\Action\\' . $name;
        /** @var \dokuwiki\Action\AbstractAction $class */
        $class = new $classname();
        $this->assertTrue(true); // mark as not risky
        if (!is_a($class, AbstractAclAction::class)) return;

        global $conf;
        $conf['useacl'] = 1;
        $conf['subscribers'] = 1;

        try {
            $class->checkPreconditions();
        } catch (\Exception $e) {
            $this->assertNotSame(ActionAclRequiredException::class, get_class($e));
        }

        $conf['useacl'] = 0;

        try {
            $class->checkPreconditions();
        } catch (\Exception $e) {
            $this->assertSame(ActionAclRequiredException::class, get_class($e));
        }
    }

    /**
     * Actions inheriting from AbstractUserAction should have user check
     *
     * @dataProvider dataProvider
     * @param $name
     */
    public function testBaseClassUserPermission($name)
    {
        $classname = 'dokuwiki\\Action\\' . $name;
        /** @var \dokuwiki\Action\AbstractAction $class */
        $class = new $classname();
        $this->assertTrue(true); // mark as not risky
        if (!is_a($class, AbstractUserAction::class)) return;

        global $conf;
        $conf['useacl'] = 1;
        $conf['subscribers'] = 1;
        $_SERVER['REMOTE_USER'] = 'test';

        try {
            $class->checkPreconditions();
        } catch (\Exception $e) {
            $this->assertNotSame(ActionUserRequiredException::class, get_class($e));
        }

        unset($_SERVER['REMOTE_USER']);

        try {
            $class->checkPreconditions();
        } catch (\Exception $e) {
            $this->assertSame(ActionUserRequiredException::class, get_class($e));
        }
    }
}
