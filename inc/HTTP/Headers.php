<?php

namespace dokuwiki\HTTP;

/**
 * Utilities to send HTTP Headers
 */
class Headers
{
    /**
     * Send a Content-Security-Polica Header
     *
     * Expects an associative array with individual policies and their values
     *
     * @param array $policy
     */
    static public function contentSecurityPolicy($policy)
    {
        foreach ($policy as $key => $values) {
            // if the value is not an array, we also accept newline terminated strings
            if (!is_array($values)) $values = explode("\n", $values);
            $values = array_map('trim', $values);
            $values = array_unique($values);
            $values = array_filter($values);
            $policy[$key] = $values;
        }

        $cspheader = 'Content-Security-Policy:';
        foreach ($policy as $key => $values) {
            if ($values) {
                $cspheader .= " $key " . join(' ', $values) . ';';
            } else {
                $cspheader .= " $key;";
            }
        }

        header($cspheader);
    }
}
