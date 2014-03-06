#!/usr/bin/php
<?php
#------------------------------------------------------------------------------
if ('cli' != php_sapi_name()) die();

ini_set('memory_limit','128M');
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../').'/');
require_once DOKU_INC.'inc/init.php';
require_once DOKU_INC.'inc/common.php';
require_once DOKU_INC.'inc/cliopts.php';

#------------------------------------------------------------------------------
function usage($action) {
    switch ( $action ) {
        case 'checkout':
            print "Usage: dwpage.php [opts] checkout <wiki:page> [working_file]

    Checks out a file from the repository, using the wiki id and obtaining
    a lock for the page.
    If a working_file is specified, this is where the page is copied to.
    Otherwise defaults to the same as the wiki page in the current
    working directory.

    EXAMPLE
    $ ./dwpage.php checkout wiki:syntax ./new_syntax.txt

    OPTIONS
        -h, --help=<action>: get help
        -f: force obtaining a lock for the page (generally bad idea)
";
        break;
        case 'commit':
            print "Usage: dwpage.php [opts] -m \"Msg\" commit <working_file> <wiki:page>

    Checks in the working_file into the repository using the specified
    wiki id, archiving the previous version.

    EXAMPLE
    $ ./dwpage.php -m \"Some message\" commit ./new_syntax.txt wiki:syntax

    OPTIONS
        -h, --help=<action>: get help
        -f: force obtaining a lock for the page (generally bad idea)
        -t, trivial: minor change
        -m (required): Summary message describing the change
";
        break;
        case 'lock':
            print "Usage: dwpage.php [opts] lock <wiki:page>

    Obtains or updates a lock for a wiki page

    EXAMPLE
    $ ./dwpage.php lock wiki:syntax

    OPTIONS
        -h, --help=<action>: get help
        -f: force obtaining a lock for the page (generally bad idea)
";
        break;
        case 'unlock':
            print "Usage: dwpage.php [opts] unlock <wiki:page>

    Removes a lock for a wiki page.

    EXAMPLE
    $ ./dwpage.php unlock wiki:syntax

    OPTIONS
        -h, --help=<action>: get help
        -f: force obtaining a lock for the page (generally bad idea)
";
        break;
        default:
            print "Usage: dwpage.php [opts] <action>

    Utility to help command line Dokuwiki page editing, allow
    pages to be checked out for editing then committed after changes

    Normal operation would be;



    ACTIONS
        checkout: see $ dwpage.php --help=checkout
        commit: see $ dwpage.php --help=commit
        lock: see $ dwpage.php --help=lock

    OPTIONS
        -h, --help=<action>: get help
            e.g. $ ./dwpage.php -hcommit
            e.g. $ ./dwpage.php --help=commit
";
        break;
    }
}

#------------------------------------------------------------------------------
function getUser() {
    $user = getenv('USER');
    if (empty ($user)) {
        $user = getenv('USERNAME');
    } else {
        return $user;
    }
    if (empty ($user)) {
        $user = 'admin';
    }
    return $user;
}

#------------------------------------------------------------------------------
function getSuppliedArgument($OPTS, $short, $long) {
    $arg = $OPTS->get($short);
    if ( is_null($arg) ) {
        $arg = $OPTS->get($long);
    }
    return $arg;
}

#------------------------------------------------------------------------------
function obtainLock($WIKI_ID) {

    global $USERNAME;

    if ( !file_exists(wikiFN($WIKI_ID)) ) {
        fwrite( STDERR, "$WIKI_ID does not yet exist\n");
    }

    $_SERVER['REMOTE_USER'] = $USERNAME;
    if ( checklock($WIKI_ID) ) {
        fwrite( STDERR, "Page $WIKI_ID is already locked by another user\n");
        exit(1);
    }

    lock($WIKI_ID);

    $_SERVER['REMOTE_USER'] = '_'.$USERNAME.'_';

    if ( checklock($WIKI_ID) != $USERNAME ) {

        fwrite( STDERR, "Unable to obtain lock for $WIKI_ID\n" );
        exit(1);

    }
}

#------------------------------------------------------------------------------
function clearLock($WIKI_ID) {

    global $USERNAME ;

    if ( !file_exists(wikiFN($WIKI_ID)) ) {
        fwrite( STDERR, "$WIKI_ID does not yet exist\n");
    }

    $_SERVER['REMOTE_USER'] = $USERNAME;
    if ( checklock($WIKI_ID) ) {
        fwrite( STDERR, "Page $WIKI_ID is locked by another user\n");
        exit(1);
    }

    unlock($WIKI_ID);

    if ( file_exists(wikiLockFN($WIKI_ID)) ) {
        fwrite( STDERR, "Unable to clear lock for $WIKI_ID\n" );
        exit(1);
    }

}

#------------------------------------------------------------------------------
function deleteLock($WIKI_ID) {

    $wikiLockFN = wikiLockFN($WIKI_ID);

    if ( file_exists($wikiLockFN) ) {
        if ( !unlink($wikiLockFN) ) {
            fwrite( STDERR, "Unable to delete $wikiLockFN\n" );
            exit(1);
        }
    }

}

#------------------------------------------------------------------------------
$USERNAME = getUser();
$CWD = getcwd();
$SYSTEM_ID = '127.0.0.1';

#------------------------------------------------------------------------------
$OPTS = Doku_Cli_Opts::getOptions(
    __FILE__,
    'h::fm:u:s:t',
    array(
        'help==',
        'user=',
        'system=',
        'trivial',
        )
);

if ( $OPTS->isError() ) {
    print $OPTS->getMessage()."\n";
    exit(1);
}

if ( $OPTS->has('h') or $OPTS->has('help') or !$OPTS->hasArgs() ) {
    usage(getSuppliedArgument($OPTS,'h','help'));
    exit(0);
}

if ( $OPTS->has('u') or $OPTS->has('user') ) {
    $USERNAME = getSuppliedArgument($OPTS,'u','user');
}

if ( $OPTS->has('s') or $OPTS->has('system') ) {
    $SYSTEM_ID = getSuppliedArgument($OPTS,'s','system');
}

#------------------------------------------------------------------------------
switch ( $OPTS->arg(0) ) {

    #----------------------------------------------------------------------
    case 'checkout':

        $WIKI_ID = $OPTS->arg(1);

        if ( !$WIKI_ID ) {
            fwrite( STDERR, "Wiki page ID required\n");
            exit(1);
        }

        $WIKI_FN = wikiFN($WIKI_ID);

        if ( !file_exists($WIKI_FN) ) {
            fwrite( STDERR, "$WIKI_ID does not yet exist\n");
            exit(1);
        }

        $TARGET_FN = $OPTS->arg(2);

        if ( empty($TARGET_FN) ) {
            $TARGET_FN = getcwd().'/'.utf8_basename($WIKI_FN);
        }

        if ( !file_exists(dirname($TARGET_FN)) ) {
            fwrite( STDERR, "Directory ".dirname($TARGET_FN)." does not exist\n");
            exit(1);
        }

        if ( stristr( realpath(dirname($TARGET_FN)), realpath($conf['datadir']) ) !== false ) {
            fwrite( STDERR, "Attempt to check out file into data directory - not allowed\n");
            exit(1);
        }

        if ( $OPTS->has('f') ) {
            deleteLock($WIKI_ID);
        }

        obtainLock($WIKI_ID);

        # Need to lock the file first?
        if ( !copy($WIKI_FN, $TARGET_FN) ) {
            fwrite( STDERR, "Unable to copy $WIKI_FN to $TARGET_FN\n");
            clearLock($WIKI_ID);
            exit(1);
        }

        print "$WIKI_ID > $TARGET_FN\n";
        exit(0);

    break;

    #----------------------------------------------------------------------
    case 'commit':

        $TARGET_FN = $OPTS->arg(1);

        if ( !$TARGET_FN ) {
            fwrite( STDERR, "Target filename required\n");
            exit(1);
        }

        if ( !file_exists($TARGET_FN) ) {
            fwrite( STDERR, "$TARGET_FN does not exist\n");
            exit(1);
        }

        if ( !is_readable($TARGET_FN) ) {
            fwrite( STDERR, "Cannot read from $TARGET_FN\n");
            exit(1);
        }

        $WIKI_ID = $OPTS->arg(2);

        if ( !$WIKI_ID ) {
            fwrite( STDERR, "Wiki page ID required\n");
            exit(1);
        }

        if ( !$OPTS->has('m') ) {
            fwrite( STDERR, "Summary message required\n");
            exit(1);
        }

        if ( $OPTS->has('f') ) {
            deleteLock($WIKI_ID);
        }

        $_SERVER['REMOTE_USER'] = $USERNAME;
        if ( checklock($WIKI_ID) ) {
            fwrite( STDERR, "$WIKI_ID is locked by another user\n");
            exit(1);
        }

        obtainLock($WIKI_ID);

        saveWikiText($WIKI_ID, file_get_contents($TARGET_FN), $OPTS->get('m'), $OPTS->has('t'));

        clearLock($WIKI_ID);

        exit(0);

    break;

    #----------------------------------------------------------------------
    case 'lock':

        $WIKI_ID = $OPTS->arg(1);

        if ( !$WIKI_ID ) {
            fwrite( STDERR, "Wiki page ID required\n");
            exit(1);
        }

        if ( $OPTS->has('f') ) {
            deleteLock($WIKI_ID);
        }

        obtainLock($WIKI_ID);

        print "Locked : $WIKI_ID\n";
        exit(0);

    break;

    #----------------------------------------------------------------------
    case 'unlock':

        $WIKI_ID = $OPTS->arg(1);

        if ( !$WIKI_ID ) {
            fwrite( STDERR, "Wiki page ID required\n");
            exit(1);
        }

        if ( $OPTS->has('f') ) {
            deleteLock($WIKI_ID);
        } else {
            clearLock($WIKI_ID);
        }

        print "Unlocked : $WIKI_ID\n";
        exit(0);

    break;

    #----------------------------------------------------------------------
    default:

        fwrite( STDERR, "Invalid action ".$OPTS->arg(0)."\n" );
        exit(1);

    break;

}

