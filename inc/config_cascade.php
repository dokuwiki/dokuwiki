<?php

/**
 * The default config cascade
 *
 * This array configures the default locations of various files in the
 * EasyWiki directory hierarchy. It can be overriden in inc/preload.php
 */

$config_cascade = array_merge(
    [
        'main' => [
            'default' => [WIKI_CONF . 'easywiki.php'],
            'local' => [WIKI_CONF . 'local.php'],
            'protected' => [WIKI_CONF . 'local.protected.php']
        ],
        'acronyms' => [
            'default' => [WIKI_CONF . 'acronyms.conf'],
            'local' => [WIKI_CONF . 'acronyms.local.conf']
        ],
        'entities' => [
            'default' => [WIKI_CONF . 'entities.conf'],
            'local' => [WIKI_CONF . 'entities.local.conf']
        ],
        'interwiki' => [
            'default' => [WIKI_CONF . 'interwiki.conf'],
            'local' => [WIKI_CONF . 'interwiki.local.conf']
        ],
        'license' => [
            'default' => [WIKI_CONF . 'license.php'],
            'local' => [WIKI_CONF . 'license.local.php']
        ],
        'manifest' => [
            'default' => [WIKI_CONF . 'manifest.json'],
            'local' => [WIKI_CONF . 'manifest.local.json']
        ],
        'mediameta' => [
            'default' => [WIKI_CONF . 'mediameta.php'],
            'local' => [WIKI_CONF . 'mediameta.local.php']
        ],
        'mime' => [
            'default' => [WIKI_CONF . 'mime.conf'],
            'local' => [WIKI_CONF . 'mime.local.conf']
        ],
        'scheme' => [
            'default' => [WIKI_CONF . 'scheme.conf'],
            'local' => [WIKI_CONF . 'scheme.local.conf']
        ],
        'smileys' => [
            'default' => [WIKI_CONF . 'smileys.conf'],
            'local' => [WIKI_CONF . 'smileys.local.conf']
        ],
        'wordblock' => [
            'default' => [WIKI_CONF . 'wordblock.conf'],
            'local' => [WIKI_CONF . 'wordblock.local.conf']
        ],
        'userstyle' => [
            'screen' => [WIKI_CONF . 'userstyle.css', WIKI_CONF . 'userstyle.less'],
            'print' => [WIKI_CONF . 'userprint.css', WIKI_CONF . 'userprint.less'],
            'feed' => [WIKI_CONF . 'userfeed.css', WIKI_CONF . 'userfeed.less'],
            'all' => [WIKI_CONF . 'userall.css', WIKI_CONF . 'userall.less']
        ],
        'userscript' => [
            'default' => [WIKI_CONF . 'userscript.js']
        ],
        'styleini' => [
            'default' => [WIKI_INC . 'lib/tpl/%TEMPLATE%/' . 'style.ini'],
            'local' => [WIKI_CONF . 'tpl/%TEMPLATE%/' . 'style.ini']
        ],
        'acl' => [
            'default' => WIKI_CONF . 'acl.auth.php'
        ],
        'plainauth.users' => [
            'default' => WIKI_CONF . 'users.auth.php',
            'protected' => ''
        ],
        'plugins' => [
            'default' => [WIKI_CONF . 'plugins.php'],
            'local' => [WIKI_CONF . 'plugins.local.php'],
            'protected' => [WIKI_CONF . 'plugins.required.php', WIKI_CONF . 'plugins.protected.php']
        ],
        'lang' => [
            'core' => [WIKI_CONF . 'lang/'],
            'plugin' => [WIKI_CONF . 'plugin_lang/'],
            'template' => [WIKI_CONF . 'template_lang/']
        ]
    ],
    $config_cascade
);
