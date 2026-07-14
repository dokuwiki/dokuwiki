<?php

class infoutils_getversiondata_test extends DokuWikiTest {

    /**
     * On a git checkout the reported date and hash must be well-formed.
     *
     * The git call uses --pretty=reference to avoid percent placeholders: on
     * Windows escapeshellarg() replaces them with spaces, so the old
     * "--pretty=format:%h %cd" command made git echo literal text and
     * getVersionData() reported date "h" with an empty hash. This test runs on
     * the Windows CI too, where that old behaviour would fail these assertions.
     */
    function test_gitVersionIsWellFormed() {
        $version = getVersionData();
        if ($version['type'] !== 'Git') {
            $this->markTestSkipped('not running from a git checkout');
        }

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $version['date']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{7,40}$/', $version['sha']);
    }
}
