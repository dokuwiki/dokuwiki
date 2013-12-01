<?php

/**
 *  test wrapper to allow access to private/protected functions/properties
 *
 *  NB: for plugin introspection methods, getPluginType() & getPluginName() to work
 *      this class name needs to start "admin_" and end "_usermanager".  Internally
 *      these methods are used in setting up the class, e.g. for language strings
 */
class admin_mock_usermanager extends admin_plugin_usermanager {

    public $mock_email_notifications = true;
    public $mock_email_notifications_sent = 0;

    public function getImportFailures() {
        return $this->_import_failures;
    }

    public function tryExport() {
        ob_start();
        $this->_export();
        return ob_get_clean();
    }

    public function tryImport() {
        return $this->_import();
    }

    /**
     * @deprecated    remove when dokuwiki requires php 5.3+
     *                also associated unit test & usermanager methods
     */
    public function access_str_getcsv($line){
        return $this->str_getcsv($line);
    }

    // no need to send email notifications (mostly)
    protected function _notifyUser($user, $password, $status_alert=true) {
        if ($this->mock_email_notifications) {
            $this->mock_email_notifications_sent++;
            return true;
        } else {
            return parent::_notifyUser($user, $password, $status_alert);
        }
    }

    protected function _isUploadedFile($file) {
        return file_exists($file);
    }
}

class auth_mock_authplain extends auth_plugin_authplain {

    public function setCanDo($op, $canDo) {
        $this->cando[$op] = $canDo;
    }

}
