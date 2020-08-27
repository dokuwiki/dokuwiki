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
    public function __construct()
    {
        parent::__construct();

        /** @var DokuWiki_Auth_Plugin $auth */
        auth_setup();
    }

    /** @inheritdoc */
    protected function setup(\splitbrain\phpcli\Options $options)
    {
        // general setup
        $options->setHelp(
            "Manage users for this DokuWiki instance\n"
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
        $options->registerCommand('delete', 'Delete user(s) from auth backend');
        $options->registerArgument('name', 'Username(s), comma-seperated', true, 'delete');

        // add to group
        $options->registerCommand('addtogroup', 'Add user to group(s)');
        $options->registerArgument('name', 'Username, comma-seperated', true, 'addtogroup');
        $options->registerArgument('group', 'Group(s), comma-seperated', true, 'addtogroup');

        // remove from group
        $options->registerCommand('removefromgroup', 'Remove user from group(s)');
        $options->registerArgument('name', 'Username, comma-seperated', true, 'removefromgroup');
        $options->registerArgument('group', 'Group(s), comma-seperated', true, 'removefromgroup');
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
            case 'addtogroup':
                $ret = $this->cmdAddToGroup($options->getArgs());
                break;
            case 'removefromgroup':
                $ret = $this->cmdRemoveFromGroup($options->getArgs());
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
     */
    protected function cmdList(bool $showdetails)
    {
        global $auth;

        if (!isset($auth)) {
            $this->error($this->getLang('noauth'));
            return 1;
        } elseif (!$auth->canDo('getUsers')) {
            $this->error($this->getLang('nosupport'));
            return 1;
        } else {
            $this->listUsers($showdetails);
        }

        return 0;
    }

    /**
     * List the given users
     *
     * @param bool $details display details
     */
    protected function listUsers(bool $details = False)
    {
        global $auth;
        $list = $auth->retrieveUsers();

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

    /**
     * Adds an user
     *
     * @param bool $notify display details
     * @param array $args
     * @return int
     */
    protected function cmdAdd(bool $notify, array $args)
    {
        global $auth;

        if (!$auth->canDo('addUser')) {
            $this->error($this->getLang('nosupport'));
            return 1;
        }

        list($user, $name, $mail, $grps, $pass) = $args;
        $grps = array_filter(array_map('trim', explode(',', $grps)));

        if ($auth->canDo('modPass')) {
            if (empty($pass)) {
                if ($notify) {
                    $pass = auth_pwgen($user);
                } else {
                    $this->error($this->getLang('add_fail'));
                    $this->error($this->getLang('addUser_error_missing_pass'));
                    return 1;
                }
            }
        } else {
            if (!empty($pass)) {
                $this->error($this->getLang('add_fail'));
                $this->error($this->getLang('addUser_error_modPass_disabled'));
                return 1;
            }
        }

        if (!$auth->triggerUserMod('create', array($user, $pass, $name, $mail, $grps))) {
            $this->error($this->getLang('add_fail'));
            $this->error($this->getLang('addUser_error_create_event_failed'));
            return 1;
        }

        return 0;
    }

    /**
     * Deletes users
     * @param array $args
     * @return int
     */
    protected function cmdDelete(array $args)
    {
        global $auth;

        if (!$auth->canDo('delUser')) {
            $this->error($this->getLang('nosupport'));
            return 1;
        }

        $users = explode(',', $args[0]);
        $count = $auth->triggerUserMod('delete', array($users));

        if (!($count == count($users))) {
            $part1 = str_replace('%d', $count, $this->getLang('delete_ok'));
            $part2 = str_replace('%d', (count($users) - $count), $this->getLang('delete_fail'));
            $this->error("$part1, $part2");

            return 1;
        }

        return 0;
    }

    /**
     * Adds an user to group(s)
     *
     * @param array $args
     * @return int
     */
    protected function cmdAddToGroup(array $args)
    {
        global $auth;

        list($name, $newgrps) = $args;
        $newgrps = array_filter(array_map('trim', explode(',', $newgrps)));
        $oldinfo = $auth->getUserData($name);
        $changes = array();

        if (!empty($newgrps) && $auth->canDo('modGroups')) {
            $changes['grps'] = $oldinfo['grps'];
            foreach ($newgrps as $group) {
                if (!in_array($group, $oldinfo['grps'])) {
                    array_push($changes['grps'], $group);
                }
            }
        }

        if (!empty(array_diff($changes['grps'], $oldinfo['grps']))) {
            if ($ok = $auth->triggerUserMod('modify', array($name, $changes))) {
                $this->info($this->getLang('update_ok'));
            } else {
                $this->error($this->getLang('update_fail'));
                return 1;
            }
        }

        return 0;
    }

    /**
     * Removes an user from group(s)
     *
     * @param array $args
     * @return int
     */
    protected function cmdRemoveFromGroup(array $args)
    {
        global $auth;

        list($name, $grps) = $args;
        $grps = array_filter(array_map('trim', explode(',', $grps)));
        $oldinfo = $auth->getUserData($name);
        $changes = array();

        if (!empty($grps) && $auth->canDo('modGroups')) {
            $changes['grps'] = $oldinfo['grps'];
            foreach ($grps as $group) {
                if (($pos = array_search($group, $changes['grps'])) ==! false) {
                    unset($changes['grps'][$pos]);
                }
            }
        }

        if (!empty(array_diff($oldinfo['grps'], $changes['grps']))) {
            if ($ok = $auth->triggerUserMod('modify', array($name, $changes))) {
                $this->info($this->getLang('update_ok'));
            } else {
                $this->error($this->getLang('update_fail'));
                return 1;
            }
        }

        return 0;
    }
}
