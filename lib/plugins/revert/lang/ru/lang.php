<?php
/**
 * russian language file
 * @author    Denis Simakov <akinoame1@gmail.com>
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Менеджер откаток';

// custom language strings for the plugin

$lang['filter']   = 'Поиск спам-страниц';
$lang['revert']   = 'Откатить изменения для выбранных страниц';
$lang['reverted'] = '%s откачена к версии %s';
$lang['removed']  = '%s удалена';
$lang['revstart'] = 'Начат процесс откатки. Он может занять много времени. Если скрипт не успевает завершить работу и выдает ошибку, необходимо произвести откатку более маленькими частями.';
$lang['revstop']  = 'Процесс откатки успешно завершен.';
$lang['note1']    = 'Замечание: поиск с учетом регистра';
$lang['note2']    = 'Замечание: страница будет откачена к последней версии, не содержащей спам-термин <i>%s</i>.';

//Setup VIM: ex: et ts=4 enc=utf-8 :
