<?php

namespace dokuwiki;

class Manifest
{
    public function sendManifest()
    {
        $manifest = retrieveConfig('manifest', 'jsonToArray');

        global $conf;

        $manifest['scope'] = DOKU_REL;

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
            foreach ($look as $img) {
                if ($img[0] === ':') {
                    $file = mediaFN($img);
                    $ismedia = true;
                } else {
                    $file = tpl_incdir() . $img;
                    $ismedia = false;
                }

                if (file_exists($file)) {
                    $imginfo = getimagesize($file);
                    if ($ismedia) {
                        $url = ml($img, '', true, '', $abs);
                    } else {
                        $url = tpl_basedir() . $img;
                        if ($abs) {
                            $url = DOKU_URL . substr($url, strlen(DOKU_REL));
                        }
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
}
