<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\QueryBuilder;
use dokuwiki\plugin\struct\meta\StructException;
use dokuwiki\plugin\struct\meta\ValidationException;

class User extends AbstractMultiBaseType {

    protected $config = array(
        'existingonly' => true,
        'autocomplete' => array(
            'fullname' => true,
            'mininput' => 2,
            'maxresult' => 5,
        ),
    );

    /**
     * @param string $rawvalue the user to validate
     * @return int|string|void
     */
    public function validate($rawvalue) {
        $rawvalue = parent::validate($rawvalue);

        if($this->config['existingonly']) {
            /** @var \DokuWiki_Auth_Plugin $auth */
            global $auth;
            $info = $auth->getUserData($rawvalue, false);
            if($info === false) throw new ValidationException('User not found', $rawvalue);
        }

        return $rawvalue;
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
        if((count($logins) < $max) && $this->config['autocomplete']['fullname']) {
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

    /**
     * When handling `%lasteditor%` get the data from the `titles` table instead the `data_` table.
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $alias
     */
    public function select(QueryBuilder $QB, $tablealias, $colname, $alias) {
        if(is_a($this->context,'dokuwiki\plugin\struct\meta\UserColumn')) {
            $rightalias = $QB->generateTableAlias();
            $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");
            $QB->addSelectStatement("$rightalias.lasteditor", $alias);
            return;
        }

        parent::select($QB, $tablealias, $colname, $alias);
    }

    /**
     * When sorting `%lasteditor%`, then sort the data from the `titles` table instead the `data_` table.
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $order
     */
    public function sort(QueryBuilder $QB, $tablealias, $colname, $order) {
        if(is_a($this->context,'dokuwiki\plugin\struct\meta\UserColumn')) {
            $rightalias = $QB->generateTableAlias();
            $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");
            $QB->addOrderBy("$rightalias.lasteditor $order");
            return;
        }

        $QB->addOrderBy("$tablealias.$colname $order");
    }

    /**
     * When using `%lasteditor%`, we need to compare against the `title` table.
     *
     * @param QueryBuilder $QB
     * @param string $tablealias
     * @param string $colname
     * @param string $comp
     * @param string|\string[] $value
     * @param string $op
     */
    public function filter(QueryBuilder $QB, $tablealias, $colname, $comp, $value, $op) {
        if(is_a($this->context,'dokuwiki\plugin\struct\meta\UserColumn')) {
            $rightalias = $QB->generateTableAlias();
            $QB->addLeftJoin($tablealias, 'titles', $rightalias, "$tablealias.pid = $rightalias.pid");

            // compare against page and title
            $sub = $QB->filters()->where($op);
            $pl = $QB->addValue($value);
            $sub->whereOr("$rightalias.lasteditor $comp $pl");
            return;
        }

        parent::filter($QB, $tablealias, $colname, $comp, $value, $op);
    }

}
