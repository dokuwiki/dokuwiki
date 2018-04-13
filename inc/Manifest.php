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
            if (file_exists(mediaFN(':wiki:favicon.ico'))) {
                $url = ml(':wiki:favicon.ico', '', true, '', true);
                $manifest['icons'][] = [
                    'src' => $url,
                    'sizes' => '16x16',
                ];
            }

            $look = [
                ':wiki:logo.svg',
                ':logo.svg',
                ':wiki:dokuwiki.svg'
            ];

            foreach ($look as $svgLogo) {

                $svgLogoFN = mediaFN($svgLogo);

                if (file_exists($svgLogoFN)) {
                    $url = ml($svgLogo, '', true, '', true);
                    $manifest['icons'][] = [
                        'src' => $url,
                        'sizes' => '17x17 512x512',
                        'type' => 'image/svg+xml',
                    ];
                    break;
                };
            }
        }

        trigger_event('MANIFEST_SEND', $manifest);

        header('Content-Type: application/manifest+json');
        echo json_encode($manifest);
    }
}
