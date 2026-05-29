<?php

/**
 * Tests for the nginx X-Accel-Redirect URL construction
 *
 * @see http_xaccel_url()
 * @see https://github.com/dokuwiki/dokuwiki/issues/2895
 */
class httputils_xaccel_test extends DokuWikiTest
{
    /**
     * Files inside the DokuWiki directory keep their path relative to the root.
     * This is the default layout and must match the historic behaviour.
     */
    public function test_file_inside_dokuwiki()
    {
        $file = DOKU_INC . 'data/media/wiki/dokuwiki.png';
        $this->assertEquals(
            DOKU_REL . 'data/media/wiki/dokuwiki.png',
            http_xaccel_url($file)
        );
    }

    /**
     * lib/ files (also inside the DokuWiki directory) work too.
     */
    public function test_libfile_inside_dokuwiki()
    {
        $file = DOKU_INC . 'lib/images/fileicons/png.png';
        $this->assertEquals(
            DOKU_REL . 'lib/images/fileicons/png.png',
            http_xaccel_url($file)
        );
    }

    /**
     * A media directory moved out of the DokuWiki root is mapped to the URL it
     * would have by default (below data/media/). This is the regression in
     * issue #2895, where the previous blind substr() produced a broken path.
     */
    public function test_relocated_mediadir()
    {
        global $conf;
        $conf['mediadir'] = '/srv/dokuwiki-media';
        $file = '/srv/dokuwiki-media/wiki/dokuwiki.png';
        $this->assertEquals(
            DOKU_REL . 'data/media/wiki/dokuwiki.png',
            http_xaccel_url($file)
        );
    }

    /**
     * The cache directory (used for resized media, compiled CSS/JS and the
     * sitemap) is mapped as well when relocated.
     */
    public function test_relocated_cachedir()
    {
        global $conf;
        $conf['cachedir'] = '/var/cache/dokuwiki';
        $file = '/var/cache/dokuwiki/a/abcdef0123.css';
        $this->assertEquals(
            DOKU_REL . 'data/cache/a/abcdef0123.css',
            http_xaccel_url($file)
        );
    }

    /**
     * The most specific (longest) configured directory must win, so a file
     * below a relocated mediadir is not swallowed by a relocated savedir.
     */
    public function test_most_specific_dir_wins()
    {
        global $conf;
        $conf['savedir'] = '/srv/dwdata';
        $conf['mediadir'] = '/srv/dwdata/media';
        $file = '/srv/dwdata/media/wiki/dokuwiki.png';
        $this->assertEquals(
            DOKU_REL . 'data/media/wiki/dokuwiki.png',
            http_xaccel_url($file)
        );
    }

    /**
     * A file outside DokuWiki and all data directories (e.g. served by a
     * plugin from an arbitrary location) is emitted as its absolute path
     * behind the dedicated opt-in prefix.
     */
    public function test_arbitrary_file_uses_escape_hatch()
    {
        $file = '/opt/secret-downloads/report.pdf';
        $this->assertEquals(
            DOKU_REL . '_x_accel_redirect/opt/secret-downloads/report.pdf',
            http_xaccel_url($file)
        );
    }

    /**
     * File names with spaces or other special characters must be URL-encoded,
     * because nginx URL-decodes the X-Accel-Redirect target.
     */
    public function test_special_characters_are_encoded()
    {
        $file = DOKU_INC . 'data/media/wiki/some file & more.png';
        $this->assertEquals(
            DOKU_REL . 'data/media/wiki/some%20file%20%26%20more.png',
            http_xaccel_url($file)
        );
    }
}
