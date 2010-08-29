<?php
/**
 * The default config cascade
 *
 * This array configures the default locations of various files in the
 * DokuWiki directory hierarchy. It can be overriden in inc/preload.php
 */
$config_cascade = array(
        'main' => array(
            'default'   => array(DOKU_CONF.'dokuwiki.php'),
            'local'     => array(DOKU_CONF.'local.php'),
            'protected' => array(DOKU_CONF.'local.protected.php'),
            ),
        'acronyms'  => array(
            'default'   => array(DOKU_CONF.'acronyms.conf'),
            'local'     => array(DOKU_CONF.'acronyms.local.conf'),
            ),
        'entities'  => array(
            'default'   => array(DOKU_CONF.'entities.conf'),
            'local'     => array(DOKU_CONF.'entities.local.conf'),
            ),
        'interwiki' => array(
            'default'   => array(DOKU_CONF.'interwiki.conf'),
            'local'     => array(DOKU_CONF.'interwiki.local.conf'),
            ),
        'license' => array(
            'default'   => array(DOKU_CONF.'license.php'),
            'local'     => array(DOKU_CONF.'license.local.php'),
            ),
        'mediameta' => array(
            'default'   => array(DOKU_CONF.'mediameta.php'),
            'local'     => array(DOKU_CONF.'mediameta.local.php'),
            ),
        'mime'      => array(
            'default'   => array(DOKU_CONF.'mime.conf'),
            'local'     => array(DOKU_CONF.'mime.local.conf'),
            ),
        'scheme'    => array(
            'default'   => array(DOKU_CONF.'scheme.conf'),
            'local'     => array(DOKU_CONF.'scheme.local.conf'),
            ),
        'smileys'   => array(
            'default'   => array(DOKU_CONF.'smileys.conf'),
            'local'     => array(DOKU_CONF.'smileys.local.conf'),
            ),
        'wordblock' => array(
            'default'   => array(DOKU_CONF.'wordblock.conf'),
            'local'     => array(DOKU_CONF.'wordblock.local.conf'),
            ),
        'userstyle' => array(
            'default' => DOKU_CONF.'userstyle.css',
            'print'   => DOKU_CONF.'printstyle.css',
            'feed'    => DOKU_CONF.'feedstyle.css',
            'all'     => DOKU_CONF.'allstyle.css',
            ),
        'userscript' => array(
            'default' => DOKU_CONF.'userscript.js'
            ),
        'acl'       => array(
            'default'   => DOKU_CONF.'acl.auth.php',
            ),
        'plainauth.users' => array(
            'default' => DOKU_CONF.'users.auth.php',
            ),
);

