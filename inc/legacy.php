<?php
/**
 * We map legacy class names to the new namespaced versions here
 *
 * These are names that we will probably never change because they have been part of DokuWiki's
 * public interface for years and renaming would break just too many plugins
 */

class_alias('\dokuwiki\Extension\EventHandler', 'Doku_Event_Handler');
class_alias('\dokuwiki\Extension\Event', 'Doku_Event');

class_alias('\dokuwiki\Extension\ActionPlugin', 'DokuWiki_Action_Plugin');
class_alias('\dokuwiki\Extension\AdminPlugin', 'DokuWiki_Admin_Plugin');
class_alias('\dokuwiki\Extension\AuthPlugin', 'DokuWiki_Auth_Plugin');
class_alias('\dokuwiki\Extension\CLIPlugin', 'DokuWiki_CLI_Plugin');
class_alias('\dokuwiki\Extension\Plugin', 'DokuWiki_Plugin');
class_alias('\dokuwiki\Extension\RemotePlugin', 'DokuWiki_Remote_Plugin');
class_alias('\dokuwiki\Extension\SyntaxPlugin', 'DokuWiki_Syntax_Plugin');
