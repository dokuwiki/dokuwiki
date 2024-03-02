<?php

use dokuwiki\Extension\CLIPlugin;
use dokuwiki\Extension\AuthPlugin;
use splitbrain\phpcli\Options;
use splitbrain\phpcli\TableFormatter;

/**
 * Class cli_plugin_usermanager
 *
 * Command Line component for the usermanager
 *
 * @license GPL2
 * @author Karsten Kosmala <karsten.kosmala@gmail.com>
 */
class cli_plugin_usermanager extends CLIPlugin
{
    public function __construct()
    {
        parent::__construct();
        auth_setup();
    }

    /** @inheritdoc */
    protected function setup(Options $options)
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
        $options->registerArgument('login', 'Username', true, 'add');
        $options->registerArgument('mail', 'Email address', true, 'add');
        $options->registerArgument('name', 'Full name', false, 'add');
        $options->registerArgument('groups', 'Groups to be added, comma-seperated', false, 'add');
        $options->registerArgument('password', 'Password to set', false, 'add');
        $options->registerOption('notify', 'Notify user', 'n', false, 'add');

        // delete
        $options->registerCommand('delete', 'Deletes user(s) from auth backend');
        $options->registerArgument('name', 'Username(s), comma-seperated', true, 'delete');

        // add to group
        $options->registerCommand('addtogroup', 'Add user to group(s)');
        $options->registerArgument('name', 'Username', true, 'addtogroup');
        $options->registerArgument('group', 'Group(s), comma-seperated', true, 'addtogroup');

        // remove from group
        $options->registerCommand('removefromgroup', 'Remove user from group(s)');
        $options->registerArgument('name', 'Username', true, 'removefromgroup');
        $options->registerArgument('group', 'Group(s), comma-separated', true, 'removefromgroup');
    }

    /** @inheritdoc */
    protected function main(Options $options)
    {
        /** @var AuthPlugin $auth */
        global $auth;

        if (!$auth instanceof AuthPlugin) {
            $this->error($this->getLang('noauth'));
            return 1;
        }

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
        /** @var AuthPlugin $auth */
        global $auth;

        if (!$auth->canDo('getUsers')) {
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
    protected function listUsers(bool $details = false)
    {
        /** @var AuthPlugin $auth */
        global $auth;
        $list = $auth->retrieveUsers();

        $tr = new TableFormatter($this->colors);

        foreach ($list as $username => $user) {
            $content = [$username];
            if ($details) {
                $content[] = $user['name'];
                $content[] = $user['mail'];
                $content[] = implode(", ", $user['grps']);
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
        /** @var AuthPlugin $auth */
        global $auth;

        if (!$auth->canDo('addUser')) {
            $this->error($this->getLang('nosupport'));
            return 1;
        }

        [$login, $mail, $name, $grps, $pass] = $args;
        $grps = array_filter(array_map('trim', explode(',', $grps)));

        if ($auth->canDo('modPass')) {
            if (empty($pass)) {
                if ($notify) {
                    $pass = auth_pwgen($login);
                } else {
                    $this->error($this->getLang('add_fail'));
                    $this->error($this->getLang('addUser_error_missing_pass'));
                    return 1;
                }
            }
        } elseif (!empty($pass)) {
            $this->error($this->getLang('add_fail'));
            $this->error($this->getLang('addUser_error_modPass_disabled'));
            return 1;
        }

        if ($auth->triggerUserMod('create', [$login, $pass, $name, $mail, $grps])) {
            $this->success($this->getLang('add_ok'));
        } else {
            $this->printErrorMessages();
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
        /** @var AuthPlugin $auth */
        global $auth;

        if (!$auth->canDo('delUser')) {
            $this->error($this->getLang('nosupport'));
            return 1;
        }

        $users = explode(',', $args[0]);
        $count = $auth->triggerUserMod('delete', [$users]);

        if ($count != count($users)) {
            $this->printErrorMessages();
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
        /** @var AuthPlugin $auth */
        global $auth;

        [$name, $newgrps] = $args;
        $newgrps = array_filter(array_map('trim', explode(',', $newgrps)));
        $oldinfo = $auth->getUserData($name);
        $changes = [];

        if ($newgrps !== [] && $auth->canDo('modGroups')) {
            $changes['grps'] = $oldinfo['grps'];
            foreach ($newgrps as $group) {
                if (!in_array($group, $oldinfo['grps'])) {
                    $changes['grps'][] = $group;
                }
            }
        }

        if (!empty(array_diff($changes['grps'], $oldinfo['grps']))) {
            if ($auth->triggerUserMod('modify', [$name, $changes])) {
                $this->success($this->getLang('update_ok'));
            } else {
                $this->printErrorMessages();
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
        /** @var AuthPlugin $auth */
        global $auth;

        [$name, $grps] = $args;
        $grps = array_filter(array_map('trim', explode(',', $grps)));
        $oldinfo = $auth->getUserData($name);
        $changes = [];

        if ($grps !== [] && $auth->canDo('modGroups')) {
            $changes['grps'] = $oldinfo['grps'];
            foreach ($grps as $group) {
                if (($pos = array_search($group, $changes['grps'])) == !false) {
                    unset($changes['grps'][$pos]);
                }
            }
        }

        if (!empty(array_diff($oldinfo['grps'], $changes['grps']))) {
            if ($auth->triggerUserMod('modify', [$name, $changes])) {
                $this->success($this->getLang('update_ok'));
            } else {
                $this->printErrorMessages();
                $this->error($this->getLang('update_fail'));
                return 1;
            }
        }

        return 0;
    }

    /**
     * Plugins triggered during user modification may cause failures and output messages via
     * DokuWiki's msg() function
     */
    protected function printErrorMessages()
    {
        global $MSG;
        if (isset($MSG)) {
            foreach ($MSG as $msg) {
                if ($msg['lvl'] === 'error') $this->error($msg['msg']);
            }
        }
    }
}
