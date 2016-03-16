<?php
namespace plugin\struct\types;

use plugin\struct\meta\StructException;
use plugin\struct\meta\ValidationException;

class User extends AbstractMultiBaseType {

    protected $config = array(
        'fullname' => true,
        'autocomplete' => array(
            'mininput' => 2,
            'maxresult' => 5,
        ),
    );

    /**
     * @param string $value the user to validate
     * @return int|string|void
     */
    public function validate($value) {
        $value = parent::validate($value);

        /** @var \DokuWiki_Auth_Plugin $auth */
        global $auth;
        $info = $auth->getUserData($value, false);
        if($info === false) throw new ValidationException('User not found', $value);
        return $value;
    }

    /**
     * @param string $value the user to display
     * @param \Doku_Renderer $R
     * @param string $mode
     * @return bool
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        if($mode == 'xhtml') {
            $name = userlink($value);
            $R->doc .= $name;
        } else {
            $name = userlink($value, true);
            $R->cdata($name);
        }
        return true;
    }

    /**
     * Autocompletion for user names
     *
     * @todo should we have any security mechanism? Currently everybody can look up users
     * @return array
     */
    public function handleAjax() {
        /** @var \DokuWiki_Auth_Plugin $auth */
        global $auth;
        global $INPUT;

        if(!$auth->canDo('getUsers')) {
            throw new StructException('The user backend can not search for users');
        }

        // check minimum length
        $lookup = trim($INPUT->str('search'));
        if(utf8_strlen($lookup) < $this->config['autocomplete']['mininput']) return array();

        // results wanted?
        $max = $this->config['autocomplete']['maxresult'];
        if($max <= 0) return array();

        // find users by login, fill up with names if wanted
        $logins = (array) $auth->retrieveUsers(0, $max, array('user' => $lookup));
        if((count($logins) < $max) && $this->config['fullname']) {
            $logins = array_merge($logins, (array) $auth->retrieveUsers(0, $max, array('name' => $lookup)));
        }

        // reformat result for jQuery UI Autocomplete
        $users = array();
        foreach($logins as $login => $info) {
            $users[] = array(
                'label' => $info['name'] . ' [' . $login . ']',
                'value' => $login
            );
        }

        return $users;
    }

}
