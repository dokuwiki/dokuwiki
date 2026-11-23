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
     * @param string $url the download URL
     */
    public function storeUpdate($url)
    {
        $this->data['downloadurl'] = $this->cleanURL($url);
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
     * Remove credentials, query and fragment from a URL
     *
     * The manager.dat file can be readable on the web, so it must not contain credentials or tokens.
     *
     * @param string $url
     * @return string
     */
    protected function cleanURL($url)
    {
        $parts = parse_url($url);
        $clean = $parts['scheme'] . '://' . $parts['host'];
        if (isset($parts['port'])) $clean .= ':' . $parts['port'];
        return $clean . ($parts['path'] ?? '');
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
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Get the URL of the last download
     *
     * @return string empty when no download is recorded
     */
    public function getDownloadURL()
    {
        $url = $this->data['downloadurl'] ?? '';
        if (!$url) return '';
        return $this->cleanURL($url);
    }

    /**
     * Check if the given URL differs from the URL of the last download
     *
     * The comparison ignores credentials, query and fragment.
     *
     * @param string $url the current download URL
     * @return bool false when one of the URLs is empty
     */
    public function hasChangedURL($url)
    {
        $last = $this->getDownloadURL();
        if (!$last || !$url) return false;
        return $last !== $this->cleanURL($url);
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
        } catch (\Exception) {
            return null;
        }
    }
}
