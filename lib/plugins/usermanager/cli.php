<?php

use splitbrain\phpcli\TableFormatter;

/**
 * Class cli_plugin_usermanager
 *
 * Command Line component for the usermanager
 *
 * @license GPL2
 * @author Karsten Kosmala <karsten.kosmala@gmail.com>
 */
class cli_plugin_usermanager extends DokuWiki_CLI_Plugin
{
    protected $auth = null;        // auth object

    public function __construct()
    {
        parent::__construct();
        global $auth;

        /** @var DokuWiki_Auth_Plugin $auth */
        auth_setup();
        $this->auth = $auth;

        $this->setupLocale();
    }

    /** @inheritdoc */
    protected function setup(\splitbrain\phpcli\Options $options)
    {
        // general setup
        $options->setHelp(
            "Manage users for this DokuWiki instance\n\n"
        );

        // list
        $options->registerCommand('list', 'List users');
        $options->registerOption('verbose', 'Show detailed user information', 'v', false, 'list');

        // add
        $options->registerCommand('add', 'Add an user to auth backend');
        $options->registerArgument('name', 'Username', true, 'add');
        $options->registerArgument('mail', 'mail address', true, 'add');
        $options->registerArgument('full_name', 'Full name', false, 'add');
        $options->registerArgument('groups', 'groups to be added', false, 'add');
        $options->registerArgument('password', 'password of user', false, 'add');
        $options->registerOption('notify', 'notify user', 'n', false, 'add');

        // delete
        $options->registerCommand('delete', 'Delete user from auth backend');
        $options->registerArgument('name', 'Username', true, 'delete');
    }

    /** @inheritdoc */
    protected function main(\splitbrain\phpcli\Options $options)
    {
        switch ($options->getCmd()) {
            case 'list':
                $ret = $this->cmdList($options->getOpt('verbose'));
                break;
            case 'add':
                $ret = $this->cmdAdd($options->getOpt('notify'), $options->getArgs());
                break;
            case 'delete':
                $ret = $this->cmdDelete($options->getArgs());
                break;

            default:
                echo $options->help();
                $ret = 0;
        }

        exit($ret);
    }

    /**
     * @param bool $showdetails
     * @return int
     * @throws \splitbrain\phpcli\Exception
     */
    protected function cmdList($showdetails)
    {
        if (!$this->auth->canDo('getUsers')) echo 'Authentication backend not available';

        $list = $this->getUsers();
        $this->listUsers($list, $showdetails);

        return 0;
    }

    /**
     * Get all users
     *
     * @return array
     */
    protected function getUsers()
    {
        return $this->auth->retrieveUsers();
    }

    /**
     * List the given users
     *
     * @param string[] list display details
     * @param bool $details display details
     * @throws \splitbrain\phpcli\Exception
     */
    protected function listUsers($list, bool $details = False)
    {
        $tr = new TableFormatter($this->colors);

        foreach ($list as $username => $user) {
            $content = [$username];
            if ($details) {
                array_push($content, $user['name']);
                array_push($content, $user['mail']);
                array_push($content, implode(", ", $user['grps']));
            }
            echo $tr->format(
                [15, 25, 25, 15],
                $content
            );
        }
    }

    protected function cmdAdd(bool $notify, array $args)
    {
        if (!$this->auth->canDo('addUser')) return false;

        $user = $args[0];
        $name = $args[1];
        $mail = $args[2];
        $grps = explode(',', $args[3]);
        $pass = $args[4];

        if ($this->auth->canDo('modPass')) {
            if (empty($pass)) {
                if ($notify) {
                    $pass = auth_pwgen($user);
                } else {
                    $this->error($this->lang['add_fail']);
                    $this->error($this->lang['addUser_error_missing_pass']);
                    return false;
                }
            }
        } else {
            if (!empty($pass)) {
                $this->error($this->lang['add_fail']);
                $this->error($this->lang['addUser_error_modPass_disabled']);
                return false;
            }
        }

        if (!$this->auth->triggerUserMod('create', array($user, $pass, $name, $mail, $grps))) {
            $this->error($this->lang['add_fail']);
            $this->error($this->lang['addUser_error_create_event_failed']);
            return false;
        }

        return 0;
    }

    protected function cmdDelete(array $args)
    {
        if (!$this->auth->canDo('delUser')) return false;
        $users = explode(',', $args[0]);

        $count = $this->auth->triggerUserMod('delete', array($users));

        if (!($count == count($users))) {
            $part1 = str_replace('%d', $count, $this->lang['delete_ok']);
            $part2 = str_replace('%d', (count($users)-$count), $this->lang['delete_fail']);
            $this->error("$part1, $part2");
        }

        return 0;
    }
}
