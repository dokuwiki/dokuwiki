<?php

namespace dokuwiki;

/**
 * The preference cookie is used to store small user preference data
 *
 * The cookie is written from PHP (using this class) and from JavaScript (using the DokuCookie object).
 *
 * Data is stored as key#value#key#value string, with all keys and values being urlencoded
 */
class PrefCookie
{
    const COOKIENAME = 'DOKU_PREFS';

    /** @var string[] */
    protected array $data = [];

    /**
     * Initialize the class from the cookie data
     */
    public function __construct()
    {
        $this->data = $this->decodeData($_COOKIE[self::COOKIENAME] ?? '');
    }

    /**
     * Get a preference from the cookie
     *
     * @param string $pref The preference to read
     * @param mixed $default The default to return if no preference is set
     * @return mixed
     */
    public function get(string $pref, $default = null)
    {
        return $this->data[$pref] ?? $default;
    }

    /**
     * Set a preference
     *
     * This will trigger a setCookie header and needs to be called before any output is sent
     *
     * @param string $pref The preference to set
     * @param string|null $value The value to set. Null to delete a value
     * @return void
     */
    public function set(string $pref, ?string $value): void
    {
        if ($value === null) {
            if (isset($this->data[$pref])) {
                unset($this->data[$pref]);
            }
        } else {
            $this->data[$pref] = $value;
        }

        $this->sendCookie();
    }

    /**
     * Set the cookie header
     *
     * @return void
     */
    protected function sendCookie(): void
    {
        global $conf;

        ksort($this->data); // sort by key
        $olddata = $_COOKIE[self::COOKIENAME] ?? '';
        $newdata = self::encodeData($this->data);

        // no need to set a cookie when it's the same as before
        if ($olddata == $newdata) return;

        // update the cookie data for the current request
        $_COOKIE[self::COOKIENAME] = $newdata;

        // no cookies to set when running on CLI
        if (PHP_SAPI === 'cli') return;

        // set the cookie header
        setcookie(self::COOKIENAME, $newdata, [
            'expires' => time() + 365 * 24 * 3600,
            'path' => empty($conf['cookiedir']) ? DOKU_REL : $conf['cookiedir'],
            'secure' => ($conf['securecookie'] && Ip::isSsl()),
            'samesite' => 'Lax'
        ]);
    }

    /**
     * Decode the cookie data (if any)
     *
     * @return array the cookie data as associative array
     */
    protected function decodeData(string $rawdata): array
    {
        $data = [];
        if ($rawdata === '') return $data;
        $parts = explode('#', $rawdata);
        $count = count($parts);

        for ($i = 0; $i < $count; $i += 2) {
            if (!isset($parts[$i + 1])) {
                Logger::error('Odd entries in user\'s pref cookie', $rawdata);
                continue;
            }

            // if the entry was duplicated, it will be overwritten. Takes care of #2721
            $data[urldecode($parts[$i])] = urldecode($parts[$i + 1]);
        }

        return $data;
    }

    /**
     * Encode the given cookie data
     *
     * @param array $data the cookie data as associative array
     * @return string the raw string to save in the cookie
     */
    protected function encodeData(array $data): string
    {
        $parts = [];

        foreach ($data as $key => $val) {
            $val = (string)$val; // we only store strings
            $parts[] = implode('#', [rawurlencode($key), rawurlencode($val)]);
        }

        return implode('#', $parts);
    }
}
