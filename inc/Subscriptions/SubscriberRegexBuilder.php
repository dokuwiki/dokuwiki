<?php

namespace dokuwiki\Subscriptions;

use Exception;

class SubscriberRegexBuilder
{

    /**
     * Construct a regular expression for parsing a subscription definition line
     *
     * @param string|array $user
     * @param string|array $style
     * @param string|array $data
     *
     * @return string complete regexp including delimiters
     * @throws Exception when no data is passed
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     */
    public function buildRegex($user = null, $style = null, $data = null)
    {
        // always work with arrays
        $user = (array)$user;
        $style = (array)$style;
        $data = (array)$data;

        // clean
        $user = array_filter(array_map('trim', $user));
        $style = array_filter(array_map('trim', $style));
        $data = array_filter(array_map('trim', $data));

        // user names are encoded
        $user = array_map('auth_nameencode', $user);

        // quote
        $user = array_map('preg_quote_cb', $user);
        $style = array_map('preg_quote_cb', $style);
        $data = array_map('preg_quote_cb', $data);

        // join
        $user = join('|', $user);
        $style = join('|', $style);
        $data = join('|', $data);

        // any data at all?
        if ($user . $style . $data === '') {
            throw new Exception('no data passed');
        }

        // replace empty values, set which ones are optional
        $sopt = '';
        $dopt = '';
        if ($user === '') {
            $user = '\S+';
        }
        if ($style === '') {
            $style = '\S+';
            $sopt = '?';
        }
        if ($data === '') {
            $data = '\S+';
            $dopt = '?';
        }

        // assemble
        return "/^($user)(?:\\s+($style))$sopt(?:\\s+($data))$dopt$/";
    }
}
