<?php

/**
 * The default config cascade
 *
 * This array configures the default locations of various files in the
 * DokuWiki directory hierarchy. It can be overriden in inc/preload.php
 */

$config_cascade = array_merge(
    [
        'main' => [
            'default' => [DOKU_CONF . 'dokuwiki.php'],
            'local' => [DOKU_CONF . 'local.php'],
            'protected' => [DOKU_CONF . 'local.protected.php']
        ],
        'acronyms' => [
            'default' => [DOKU_CONF . 'acronyms.conf'],
            'local' => [DOKU_CONF . 'acronyms.local.conf']
        ],
        'entities' => [
            'default' => [DOKU_CONF . 'entities.conf'],
            'local' => [DOKU_CONF . 'entities.local.conf']
        ],
        'interwiki' => [
            'default' => [DOKU_CONF . 'interwiki.conf'],
            'local' => [DOKU_CONF . 'interwiki.local.conf']
        ],
        'license' => [
            'default' => [DOKU_CONF . 'license.php'],
            'local' => [DOKU_CONF . 'license.local.php']
        ],
        'manifest' => [
            'default' => [DOKU_CONF . 'manifest.json'],
            'local' => [DOKU_CONF . 'manifest.local.json']
        ],
        'mediameta' => [
            'default' => [DOKU_CONF . 'mediameta.php'],
            'local' => [DOKU_CONF . 'mediameta.local.php']
        ],
        'mime' => [
            'default' => [DOKU_CONF . 'mime.conf'],
            'local' => [DOKU_CONF . 'mime.local.conf']
        ],
        'scheme' => [
            'default' => [DOKU_CONF . 'scheme.conf'],
            'local' => [DOKU_CONF . 'scheme.local.conf']
        ],
        'smileys' => [
            'default' => [DOKU_CONF . 'smileys.conf'],
            'local' => [DOKU_CONF . 'smileys.local.conf']
        ],
        'wordblock' => [
            'default' => [DOKU_CONF . 'wordblock.conf'],
            'local' => [DOKU_CONF . 'wordblock.local.conf']
        ],
        'userstyle' => [
            'screen' => [DOKU_CONF . 'userstyle.css', DOKU_CONF . 'userstyle.less'],
            'print' => [DOKU_CONF . 'userprint.css', DOKU_CONF . 'userprint.less'],
            'feed' => [DOKU_CONF . 'userfeed.css', DOKU_CONF . 'userfeed.less'],
            'all' => [DOKU_CONF . 'userall.css', DOKU_CONF . 'userall.less']
        ],
        'userscript' => [
            'default' => [DOKU_CONF . 'userscript.js']
        ],
        'styleini' => [
            'default' => [DOKU_INC . 'lib/tpl/%TEMPLATE%/' . 'style.ini'],
            'local' => [DOKU_CONF . 'tpl/%TEMPLATE%/' . 'style.ini']
        ],
        'acl' => [
            'default' => DOKU_CONF . 'acl.auth.php'
        ],
        'plainauth.users' => [
            'default' => DOKU_CONF . 'users.auth.php',
            'protected' => ''
        ],
        'plugins' => [
            'default' => [DOKU_CONF . 'plugins.php'],
            'local' => [DOKU_CONF . 'plugins.local.php'],
            'protected' => [DOKU_CONF . 'plugins.required.php', DOKU_CONF . 'plugins.protected.php']
        ],
        'lang' => [
            'core' => [DOKU_CONF . 'lang/'],
            'plugin' => [DOKU_CONF . 'plugin_lang/'],
            'template' => [DOKU_CONF . 'template_lang/']
        ]
    ],
    $config_cascade
);
