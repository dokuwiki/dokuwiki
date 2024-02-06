<?php

namespace dokuwiki;

/**
 * Basic Information about DokuWiki
 *
 * @todo much of infoutils should be moved here
 */
class Info
{
    /**
     * Parse the given version string into its parts
     *
     * @param string $version
     * @return array
     * @throws \Exception
     */
    public static function parseVersionString($version)
    {
        $return = [
            'type' => '', // stable, rc
            'date' => '', // YYYY-MM-DD
            'hotfix' => '', // a, b, c, ...
            'version' => '', // sortable, full version string
            'codename' => '', // codename
            'raw' => $version, // raw version string as given
        ];

        if (preg_match('/^(rc)?(\d{4}-\d{2}-\d{2})([a-z]*)/', $version, $matches)) {
            $return['date'] = $matches[2];
            if ($matches[1] == 'rc') {
                $return['type'] = 'rc';
            } else {
                $return['type'] = 'stable';
            }
            if ($matches[3]) {
                $return['hotfix'] = $matches[3];
            }
        } else {
            throw new \Exception('failed to parse version string');
        }

        [, $return['codename']] = sexplode(' ', $version, 2);
        $return['codename'] = trim($return['codename'], ' "');

        $return['version'] = $return['date'];
        $return['version'] .= $return['type'] == 'rc' ? 'rc' : $return['hotfix'];

        return $return;
    }
}
