<?php

if (!defined('DOKU_INC')) {
    define('DOKU_INC', __DIR__ . '/../../');
}
require_once(DOKU_INC . 'inc/init.php');

class Manifest {
    public function run() {
        $manifest = retrieveConfig('manifest', [$this, 'jsonToArray']);

        global $conf;

        if (empty($manifest['name'])) {
            $manifest['name'] = $conf['title'];
        }

        if (empty($manifest['short_name'])) {
            $manifest['short_name'] = $conf['title'];
        }

        if (empty($manifest['description'])) {
            $manifest['description'] = $conf['tagline'];
        }

        if (empty($manifest['start_url'])) {
            $manifest['start_url'] = DOKU_REL;
        }

        $styleUtil = new \dokuwiki\StyleUtils();
        $styleIni = $styleUtil->cssStyleini($conf['template']);
        $replacements = $styleIni['replacements'];

        if (empty($manifest['background_color'])) {
            $manifest['background_color'] = $replacements['__background__'];
        }

        if (empty($manifest['theme_color'])) {
            $manifest['theme_color'] = !empty($replacements['__theme_color__']) ? $replacements['__theme_color__'] : $replacements['__background_alt__'];
        }

        if (empty($manifest['icons'])) {
            $manifest['icons'] = [];
            $look = [
                ':wiki:logo.png',
                ':logo.png',
                'images/logo.png',
                ':wiki:apple-touch-icon.png',
                ':apple-touch-icon.png',
                'images/apple-touch-icon.png',
                ':wiki:favicon.svg',
                ':favicon.svg',
                'images/favicon.svg',
                ':wiki:favicon.ico',
                ':favicon.ico',
                'images/favicon.ico',
                ':wiki:logo',
            ];

            $abs = true;
            foreach($look as $img) {
                if($img[0] === ':') {
                    $file    = mediaFN($img);
                    $ismedia = true;
                } else {
                    $file    = tpl_incdir().$img;
                    $ismedia = false;
                }

                if (file_exists($file)) {
                    $imginfo = getimagesize($file);
                    if($ismedia) {
                        $url = ml($img, '', true, '', $abs);
                    } else {
                        $url = tpl_basedir().$img;
                        if($abs) $url = DOKU_URL.substr($url, strlen(DOKU_REL));
                    }
                    $manifest['icons'][] = [
                        'src' => $url,
                        'sizes' => $imginfo[0] . 'x' . $imginfo[1],
                        'type' => $imginfo['mime'],
                    ];
                };
            }
        }

        trigger_event('MANIFEST_SEND', $manifest);

        header('Content-Type: application/manifest+json');
        echo json_encode($manifest);
    }

    public function jsonToArray($file)
    {
        $json = file_get_contents($file);

        $conf = json_decode($json, true);

        $jsonError = json_last_error();
        if (!is_array($conf) && $jsonError !== JSON_ERROR_NONE) {

            switch ($jsonError) {
                case JSON_ERROR_DEPTH:
                    $jsonErrorText = 'The maximum stack depth has been exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $jsonErrorText = 'Invalid or malformed JSON';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $jsonErrorText = 'Control character error, possibly incorrectly encoded';
                    break;
                case JSON_ERROR_SYNTAX:
                    $jsonErrorText = 'Syntax error';
                    break;
                case JSON_ERROR_UTF8:
                    $jsonErrorText = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                case JSON_ERROR_RECURSION:
                    $jsonErrorText = 'One or more recursive references in the value to be encoded';
                    break;
                case JSON_ERROR_INF_OR_NAN:
                    $jsonErrorText = 'One or more NAN or INF values in the value to be encoded';
                    break;
                case JSON_ERROR_UNSUPPORTED_TYPE:
                    $jsonErrorText = 'A value of a type that cannot be encoded was given';
                    break;
                case JSON_ERROR_INVALID_PROPERTY_NAME:
                    $jsonErrorText = 'A property name that cannot be encoded was given';
                    break;
                case JSON_ERROR_UTF16:
                    $jsonErrorText = 'Malformed UTF-16 characters, possibly incorrectly encoded';
                    break;
                default:
                    $jsonErrorText = 'Unknown Error Code';
            }

            trigger_error('JSON decoding error "' . $jsonErrorText . '" for file ' . $file, E_USER_WARNING);
            return [];
        }

        return $conf;
    }
}

$manifest = new Manifest();
$manifest->run();
