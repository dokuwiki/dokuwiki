<?php
/**
 * The default config cascade
 *
 * This array configures the default locations of various files in the
 * DokuWiki directory hierarchy. It can be overriden in inc/preload.php
 */
$config_cascade = array_merge(
    array(
        'main' => array(
            'default'   => array(DOKU_CONF . 'dokuwiki.php'),
            'local'     => array(DOKU_CONF . 'local.php'),
            'protected' => array(DOKU_CONF . 'local.protected.php'),
        ),
        'acronyms' => array(
            'default'   => array(DOKU_CONF . 'acronyms.conf'),
            'local'     => array(DOKU_CONF . 'acronyms.local.conf'),
        ),
        'entities' => array(
            'default'   => array(DOKU_CONF . 'entities.conf'),
            'local'     => array(DOKU_CONF . 'entities.local.conf'),
        ),
        'interwiki' => array(
            'default'   => array(DOKU_CONF . 'interwiki.conf'),
            'local'     => array(DOKU_CONF . 'interwiki.local.conf'),
        ),
        'license' => array(
            'default'   => array(DOKU_CONF . 'license.php'),
            'local'     => array(DOKU_CONF . 'license.local.php'),
        ),
        'manifest' => array(
            'default'   => array(DOKU_CONF . 'manifest.json'),
            'local'     => array(DOKU_CONF . 'manifest.local.json'),
        ),
        'mediameta' => array(
            'default'   => array(DOKU_CONF . 'mediameta.php'),
            'local'     => array(DOKU_CONF . 'mediameta.local.php'),
        ),
        'mime' => array(
            'default'   => array(DOKU_CONF . 'mime.conf'),
            'local'     => array(DOKU_CONF . 'mime.local.conf'),
        ),
        'scheme' => array(
            'default'   => array(DOKU_CONF . 'scheme.conf'),
            'local'     => array(DOKU_CONF . 'scheme.local.conf'),
        ),
        'smileys' => array(
            'default'   => array(DOKU_CONF . 'smileys.conf'),
            'local'     => array(DOKU_CONF . 'smileys.local.conf'),
        ),
        'wordblock' => array(
            'default'   => array(DOKU_CONF . 'wordblock.conf'),
            'local'     => array(DOKU_CONF . 'wordblock.local.conf'),
        ),
        'userstyle' => array(
            'screen'    => array(DOKU_CONF . 'userstyle.css', DOKU_CONF . 'userstyle.less'),
            'print'     => array(DOKU_CONF . 'userprint.css', DOKU_CONF . 'userprint.less'),
            'feed'      => array(DOKU_CONF . 'userfeed.css', DOKU_CONF . 'userfeed.less'),
            'all'       => array(DOKU_CONF . 'userall.css', DOKU_CONF . 'userall.less')
        ),
        'userscript' => array(
            'default'   => array(DOKU_CONF . 'userscript.js')
        ),
        'acl' => array(
            'default'   => DOKU_CONF . 'acl.auth.php',
        ),
        'plainauth.users' => array(
            'default'   => DOKU_CONF . 'users.auth.php',
            'protected' => '' // not used by default
        ),
        'plugins' => array(
            'default'   => array(DOKU_CONF . 'plugins.php'),
            'local'     => array(DOKU_CONF . 'plugins.local.php'),
            'protected' => array(
                DOKU_CONF . 'plugins.required.php',
                DOKU_CONF . 'plugins.protected.php',
            ),
        ),
        'lang' => array(
            'core'      => array(DOKU_CONF . 'lang/'),
            'plugin'    => array(DOKU_CONF . 'plugin_lang/'),
            'template'  => array(DOKU_CONF . 'template_lang/')
        )
    ),
    $config_cascade
);

