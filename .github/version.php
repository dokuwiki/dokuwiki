<?php
/**
 * Command line tool to check proper version strings
 *
 * Expects a tag as first argument. Used in release action to ensure proper formats
 * in VERSION file and git tag.
 */

if (!isset($argv[1])) {
    echo "::error::No git tag given, this action should not have run\n";
    exit(1);
}
$TAG = $argv[1];
$TAG = str_replace('refs/tags/', '', $TAG);

if (!file_exists(__DIR__ . '/../VERSION')) {
    echo "::error::No VERSION file found\n";
    exit(1);
}
$FILE = trim(file_get_contents(__DIR__ . '/../VERSION'));
$FILE = explode(' ', $FILE)[0];


if(!preg_match('/^release_(stable|candidate)_((\d{4})-(\d{2})-(\d{2})([a-z])?)$/', $TAG, $m)) {
    echo "::error::Git tag does not match expected format: $TAG\n";
    exit(1);
} else {
    $TAGTYPE = $m[1];
    $TAGVERSION = $m[2];
}

if(!preg_match('/^(rc)?((\d{4})-(\d{2})-(\d{2})([a-z])?)$/', $FILE, $m)) {
    echo "::error::VERSION file does not match expected format: $FILE\n";
    exit(1);
} else {
    $FILETYPE = $m[1] == 'rc' ? 'candidate' : 'stable';
    $FILEVERSION = $m[2];
    $TGZVERSION = $m[0];
}

if($TAGTYPE !== $FILETYPE) {
    echo "::error::Type of release mismatches between git tag and VERSION file: $TAGTYPE <-> $FILETYPE\n";
    exit(1);
}

if($TAGVERSION !== $FILEVERSION) {
    echo "::error::Version date mismatches between git tag and VERSION file: $TAGVERSION <-> $FILEVERSION\n";
    exit(1);
}

// still here? all good, export Version
echo "::set-output name=VERSION::$TGZVERSION\n";
