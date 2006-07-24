<?php
/**
 *  Dokuwiki installation assistance
 *
 *  @author      Chris Smith <chris@jalakai.co.uk>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__)).'/');
if(!defined('DOKU_CONF')) define('DOKU_CONF',DOKU_INC.'conf/');
if(!defined('DOKU_LOCAL')) define('DOKU_LOCAL',DOKU_INC.'conf/');

if(!defined('DEBUG')) define('DEBUG', false);

// ------------------------------------------------------------------------------------
// important settings ...
// installation dependent local config file list
$config_files = array(
  'local' => DOKU_LOCAL.'local.php',
  'users' => DOKU_LOCAL.'users.auth.php',
  'auth'  => DOKU_LOCAL.'acl.auth.php'
);

// other installation dir/file permission requirements
$install_permissions = array(
  'data'      => 'data',
  'pages'     => 'data/pages',
  'attic'     => 'data/attic',
  'media'     => 'data/media',
  'meta'      => 'data/meta',
  'cache'     => 'data/cache',
  'locks'     => 'data/locks',
  'changelog' => 'data/changes.log'
);

// array use to verify unchanged dokuwiki.php files, 'version' => 'md5 hash'
$dokuwiki_php = DOKU_CONF.'dokuwiki.php';
$dokuwiki_hash = array(
  '2005-09-22' => 'e33223e957b0b0a130d0520db08f8fb7',
  '2006-03-05' => '51295727f79ab9af309a2fd9e0b61acc',
  '2006-03-09' => '51295727f79ab9af309a2fd9e0b61acc',
);

// language strings

// ------------------------------------------------------------------------------------
// initialise variables ...

$msg = array();
$error = array();
$debug = array();
$process_form = false;

// form variables with default values
$title       = "";
$location    = true;
$data        = "./data";
$changeslog  = true;
$acl         = true;
$superuser   = "";
$fullname    = "";
$email       = "";

// check for dokuwiki 
// (for now assume included with Dokuwiki install & resident in dokuwiki root folder)

// ------------------------------------------------------------------------------------
// check for virgin dokuwiki installation 
$virgin_install = true;

  // $config_files mustn't exist
  foreach ($config_files as $file) {
    if (@file_exists($file)) {
      $virgin_install = false;
      $file = str_replace($_SERVER['DOCUMENT_ROOT'],'{DOCUMENT_ROOT}', $file);
      $msg[] = "<span class=\"file\">$file</span> exists"; }
  }

  // main dokuwiki config file (conf/dokuwiki.php) must not have been modified
  $installation_hash = md5(@file_get_contents($dokuwiki_php));
  if (!in_array($installation_hash, $dokuwiki_hash)) {
    $virgin_install = false;
    $msg[] = "unrecognised or modified dokuwiki.php -- hash=$installation_hash";
  }
// ------------------------------------------------------------------------------------
// check for other basic installation & configuration details (to be nice)

$changeslog_exists = @file_exists(DOKU_INC.'data/changes.log');

if (!is_writable(DOKU_CONF)) {
  $file = str_replace($_SERVER['DOCUMENT_ROOT'],'{DOCUMENT_ROOT}', DOKU_CONF);
  $error[] = "<span class=\"file\">$file</span> must be writable by the web server.";
}

//-------------------------------------------------------------------------------------
// utility functions

/**
 * remove magic quotes recursivly
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function remove_magic_quotes(&$array) {
  foreach (array_keys($array) as $key) {
    if (is_array($array[$key])) {
      remove_magic_quotes($array[$key]);
    }else {
      $array[$key] = stripslashes($array[$key]);
    }
  }
}

function cleanText($var, $default, $regex, $msg) {
  global $error;

  $value = isset($_REQUEST[$var]) ? $_REQUEST[$var] : $default;

  if ($regex) {
    if (!preg_match($regex, $value)) {
      $error[] = "$var - illegal/unrecognised value";
    }
  }
  return $value;
}

function fileWrite($name, $filename, $data) {
  global $error;

  if (($fp = @fopen($filename, 'wb')) === false) {
    $filename = str_replace($_SERVER['DOCUMENT_ROOT'],'{DOCUMENT_ROOT}', $filename);
    $error[] = "Unable to create $name (<span class=\"file\">$filename</span>).  You will need to check directory/file permissions and create the file manually.";
    return false;
  }

  if (!empty($data)) { fwrite($fp, $data);  }
  fclose($fp);
  return true;
}

// ------------------------------------------------------------------------------------
// form processing ...
if (isset($_REQUEST['submit'])) {
  if (!$virgin_install) {
    $msg[] = "unable to apply updates, installation already modified";

  } else {
    // apply updates per form instructions
    $process_form = true;

    if (get_magic_quotes_gpc()) {
      if (!empty($_REQUEST)) remove_magic_quotes($_REQUEST);
    }

    $title = cleanText('title', '', '');
    $location = isset($_REQUEST['location']);
    $data = cleanText('data', '', '');
    $changeslog = isset($_REQUEST['changeslog']);
    $acl = isset($_REQUEST['acl']);
    $superuser = cleanText('superuser','','/\S+/', );
    $password = cleanText('password','','/\S+/');
    $confirm = cleanText('confirm','','/^'.preg_quote($password,'/').'$/');
    $fullname = cleanText('fullname','','');
    $email = cleanText('email','','');

    $debug = compact('title','location','data','changeslog','acl','superuser','password','confirm');

    if (empty($error)) {
      // all incoming data is ok ... lets do ...
      // create changes.log
      if (!$changeslog_exists) {
        $filename = realpath((empty($data) || ($data{0} != "/")) ? DOKU_INC.$data : $data).'/changes.log';
        fileWrite('changeslog',$filename, '');
      }

      // create local.php
      $output = "";
      if (!empty($title)) $output .= '$conf[\'title\'] = \''.addslashes($title)."';\n";
      if (!empty($data)) $output .= '$conf[\'data\'] = \''.$data."';\n";
      if ($acl) $output .= '$conf[\'useacl\'] = 1'.";\n";
      if (!empty($superuser)) $output .= '$conf[\'superuser\'] = \''.$superuser."';\n";

      if (!empty($output)) {
        $output = '<'.'?php
/*
 * Dokuwiki\'s Main Configuration File - Local Settings 
 * Auto-generated by install script 
 * Date: '.date('r').'
 */'."\n".$output;
           fileWrite('local configuration settings file',DOKU_LOCAL.'local.php',$output);
       }

      if ($acl) {
        // create users.auth.php
        // --- user:MD5password:Real Name:email:groups,comma,seperated
        $output = (!empty($superuser)) ? join(":",array($superuser, md5($password), $fullname, $email, 'users')) : "";
        $output = @file_get_contents(DOKU_CONF.'users.auth.php.dist')."\n$output\n";

        fileWrite('acl user file', DOKU_LOCAL.'users.auth.php', $output);

        // create acl.auth.php
        $output = @file_get_contents(DOKU_CONF.'acl.auth.php.dist');
        fileWrite('acl authorisations file', DOKU_LOCAL.'acl.auth.php', $output);
      }
    }
  }
}
//-------------------------------------------------------------------------------------

$show_form = !$process_form && $virgin_install && empty($error);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
<title>Dokuwiki Installer</title>
<style type="text/css">
<!--/*--><![CDATA[/*><!--*/

html {margin: 0; padding: 0;}
body {
  width: 90%;
  margin: 0 auto;
  font: 84% Verdana, Helvetica, Arial, sans-serif;
}

a {
  white-space: nowrap;
}

img {
  border: none;
}

.abbr {
  border-bottom: 2px dotted #444;
}

.alert .file {
  color: #a03333;
}

.error .file {
  color: #c33;
}

h1 img {
  vertical-align: middle;
}

form, fieldset {
  margin: 1em 0;
  padding: 0;
  border: none;
  width: 100%;
}

ul {
  font-size: 80%;
}

form .field {
  margin: 0.5em 0;
}

label {
  display: block;
}

label span {
  display: block;
}

label input.text {
  width: 95%;
}

#instructions {
  float: right;
  width: 34%;
}

#details {
  float: left;
  width: 58%;
}

#process {
  margin: 1.5em 0;
}

#debug, #footer {
  clear: both;
}

#acl, #files {
  border: 1px solid #ccc;
  padding: 0.5em 0 1em 0;
}

fieldset.dependent {
  margin-left: 2em;
}


/*]]>*/-->
</style>
<script type="text/javascript">
<!--//--><![CDATA[//><!--


//--><!]]>
</script>
</head>
<body>
<h1><img src="http://wiki.splitbrain.org/_media/wiki:dokuwiki-64.png" alt="" />Dokuwiki Installer</h1>
<div id="instructions">
  <p>This page assists in the installation and configuration of <a href="http://wiki.splitbrain.org">Dokuwiki</a>.</p>
  <p>Dokuwiki uses ordinary files for the storage of wiki pages and other information associated with those pages 
  (e.g. images, search indexes, old revisions, etc).  In order to operate successfully Dokuwiki <strong>must</strong>
  have write access to the directories that hold those files.  This installer is not capable of setting up directory 
  permissions, that normally needs to be done directly or if you are using hosting, through your hosting 
  control panel (e.g. cPanel).</p>
  <p>This installer will setup your Dokuwiki configuration for <span class="abbr" title="access control list">ACL</span>, 
  which in turn allows administrator login and access to Dokuwiki's admin menu for installing plugins, managing 
  users, managing access to wiki pages and alteration of configuration settings.  It isn't required for Dokuwiki to 
  operate, however it will make Dokuwiki easier to administer.</p>
  <p>Use these links for details concerning <a href="http://wiki.splitbrain.org/wiki:installation">installation instructions</a>
  and <a href="http://wiki.splitbrain.org/wiki:configuration">configuration settings</a>.</p>
</div>
<div id="details">  
<?php if (!$virgin_install) { ?>
  <p>Modified installation detected.</p>
  <ul class="alert">
<?php   foreach ($msg as $text) { ?>
    <li><?php echo $text?></li>
<?php   } ?>
  </ul>
  <p>For security reasons this script will only work with a new &amp; unmodified Dokuwiki installation. 
  You should either re-extract the files from the downloaded package or consult the complete 
  <a href="http://wiki.splitbrain.org/wiki:install">Dokuwiki installation instructions</a></p>
<?php } /* end if (!virgin_install) */ ?>
<?php if (!$process_form && !empty($error)) { ?>
  <p>One or more incorrect directory/file permissions were found.</p>
  <ul class="error">
<?php   foreach ($error as $text) { ?>
    <li><?php echo $text ?></li>
<?php   } ?>
  </ul>
  <p>In order to complete this installation the above directories and files need to have their 
    permissions altered as indicated. Please correct the above problems before trying again.</p>
<?php } /* end if (!$process_form && !empty($error)) */ ?>
<?php if ($process_form) { ?>
<?php   if (empty($error)) { ?>
  <p>Configuration updated successfully.</p>
  <p>Now that your initial dokuwiki configuration has been set you should delete this file to prevent its further use
  which may damage your dokuwiki installation and/or configuration.</p>
  <p>Use this link to visit your new <a href="doku.php" title="my new dokuWiki">wiki</a></p>
<?php   } else { ?>
  <p>The following errors were encountered ... </p>
  <ul class="error">
<?php     foreach ($error as $text) { ?>
    <li><?php echo $text?></li>
<?php     } ?>
  </ul>
  <p>return to <a href="install.php">installation form</a></p>
<?php   } ?>
<?php } ?>
<?php if ($show_form) { ?>
  <form action="" method="post">
    <fieldset id="wiki">
      <div class="field"><label><span> Wiki Name </span><input class="text" type="text" name="title" value="<?php echo $title ?>" /></label></div>
      <fieldset id="acl">
      <div class="field"><label><input class="checkbox" type="checkbox" name="acl" <?php echo(($acl ? 'checked="checked"' : ''));?> /> Enable ACL </label></div>
        <fieldset class="dependent">
          <div class="field"><label><span> Superuser </span><input class="text" type="text" name="superuser" value="<?php echo $superuser ?>" /></label></div>
          <div class="field"><label><span> Full name </span><input class="text" type="text" name="fullname" value="<?php echo $fullname ?>" /></label></div>
          <div class="field"><label><span> Email Address </span><input class="text" type="text" name="email" value="<?php echo $email ?>" /></label></div>
          <div class="field"><label><span> Superuser password </span><input class="text" type="password" name="password" /></label></div>
          <div class="field"><label><span> Confirm password </span><input class="text" type="password" name="confirm" /></label></div>
        </fieldset>
      </fieldset>
      <fieldset id="files">
        <div class="field"><label><input class="checkbox" type="checkbox" name="location" <?php echo(($location ? 'checked="checked"' : ''));?> />Use default wiki location</label></div>
        <fieldset class="dependent">
          <div class="field"><label><span> Wiki Location </span><input class="text" type="text" name="data" value="<?php echo $data ?>" /></label></div>
        </fieldset>
      </fieldset>
<?php if (!$changeslog_exists) { ?>
      <div class="field"><label><input class="checkbox" type="checkbox" name="changeslog" <?php echo(($changeslog ? 'checked="checked"' : ''));?> />Create changes.log file</label></div>
<?php } ?>
    </fieldset>
    <fieldset id="process">
      <input class="button" type="submit" name="submit" value="Process Configuration Changes" />
    </fieldset>
  </form>
<?php } ?>
</div><!-- #details -->
<?php if (DEBUG) { ?>
<div id="debug">
  <pre>
  <?php print_r($_REQUEST); print_r($debug); print_r($error); ?>
  </pre>
</div>
<?php } ?>
<div id="footer">
  <a href="http://wiki.splitbrain.org"><img src="lib/tpl/default/images/button-dw.png" alt="powered by dokuwiki" /></a>
  <a href="http://www.php.net"><img src="lib/tpl/default/images/button-php.gif" alt="powered by php" /></a>
</div>
</body>
</html>