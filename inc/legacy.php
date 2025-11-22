<?php

/**
 * We map legacy class names to the new namespaced versions here
 *
 * These are names that we will probably never change because they have been part of EasyWiki's
 * public interface for years and renaming would break just too many plugins
 *
 * Note: when adding to this file, please also add appropriate actions to _test/rector.php
 */

class_alias('\easywiki\Extension\EventHandler', 'Doku_Event_Handler');
class_alias('\easywiki\Extension\Event', 'Doku_Event');

class_alias('\easywiki\Extension\ActionPlugin', 'EasyWiki_Action_Plugin');
class_alias('\easywiki\Extension\AdminPlugin', 'EasyWiki_Admin_Plugin');
class_alias('\easywiki\Extension\AuthPlugin', 'EasyWiki_Auth_Plugin');
class_alias('\easywiki\Extension\CLIPlugin', 'EasyWiki_CLI_Plugin');
class_alias('\easywiki\Extension\Plugin', 'EasyWiki_Plugin');
class_alias('\easywiki\Extension\RemotePlugin', 'EasyWiki_Remote_Plugin');
class_alias('\easywiki\Extension\SyntaxPlugin', 'EasyWiki_Syntax_Plugin');

class_alias('\easywiki\Feed\FeedParser', 'FeedParser');
