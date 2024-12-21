<?php

namespace dokuwiki\plugin\extension;

/**
 * Manages info about installation of extensions
 */
class Manager
{
    /** @var Extension The managed extension */
    protected Extension $extension;

    /** @var string path to the manager.dat */
    protected string $path;

    /** @var array the data from the manager.dat */
    protected array $data = [];

    /**
     * Initialize the Manager
     *
     * @param Extension $extension
     */
    public function __construct(Extension $extension)
    {
        $this->extension = $extension;
        $this->path = $this->extension->getInstallDir() . '/manager.dat';
        $this->data = $this->readFile();
    }

    /**
     * This updates the timestamp and URL in the manager.dat file
     *
     * It is called by Installer when installing or updating an extension
     *
     * @param $url
     */
    public function storeUpdate($url)
    {
        $this->data['downloadurl'] = $url;
        if (isset($this->data['installed'])) {
            // it's an update
            $this->data['updated'] = date('r');
        } else {
            // it's a new install
            $this->data['installed'] = date('r');
        }

        $data = '';
        foreach ($this->data as $k => $v) {
            $data .= $k . '=' . $v . DOKU_LF;
        }
        io_saveFile($this->path, $data);
    }


    /**
     * Reads the manager.dat file and fills the managerInfo array
     */
    protected function readFile()
    {
        $data = [];
        if (!is_readable($this->path)) return $data;

        $file = (array)@file($this->path);
        foreach ($file as $line) {
            [$key, $value] = sexplode('=', $line, 2, '');
            $key = trim($key);
            $value = trim($value);
            // backwards compatible with old plugin manager
            if ($key == 'url') $key = 'downloadurl';
            $data[$key] = $value;
        }

        return $data;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastUpdate()
    {
        $date = $this->data['updated'] ?? $this->data['installed'] ?? '';
        if (!$date) return null;
        try {
            return new \DateTime($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getDownloadURL()
    {
        return $this->data['downloadurl'] ?? '';
    }

    /**
     * @return \DateTime|null
     */
    public function getInstallDate()
    {
        $date = $this->data['installed'] ?? '';
        if (!$date) return null;
        try {
            return new \DateTime($date);
        } catch (\Exception $e) {
            return null;
        }
    }
}
