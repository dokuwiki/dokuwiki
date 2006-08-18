<?php
/**
 * Slovak language file
 *
 * @author Ondrej Végh <ov@vsieti.sk>
 */

$lang['menu'] = 'Správa pluginov';

// custom language strings for the plugin
$lang['download'] = "Stiahnuť a nainštalovať  plugin";
$lang['manage'] = "Nainštalované pluginy";

$lang['btn_info'] = 'info';
$lang['btn_update'] = 'aktualizovať';
$lang['btn_delete'] = 'zmazať';
$lang['btn_settings'] = 'nastavenia';
$lang['btn_download'] = 'Stiahnuť';
$lang['btn_enable'] = 'Uložiť';

$lang['url']              = 'URL';

$lang['installed']        = 'Nainštalovaný:';
$lang['lastupdate']       = 'Aktualizovaný:';
$lang['source']           = 'Zdroj:';
$lang['unknown']          = 'neznámy';

// ..ing = header message
// ..ed = success message

$lang['updating']         = 'Aktualizuje sa ...';
$lang['updated']          = 'Plugin %s bol úspešne zaktualizovaný';
$lang['updates']          = 'Nasledujúce pluginy bol úspešne zaktualizované:';
$lang['update_none']      = 'Neboli nájdené žiadne aktualizácie.';

$lang['deleting']         = 'Vymazáva sa ...';
$lang['deleted']          = 'Plugin %s bol zmazaný.';

$lang['downloading']      = 'Sťahuje sa ...';
$lang['downloaded']       = 'Plugin %s bol úspešne stiahnutý';
$lang['downloads']        = 'Nasledujúce pluginy bol úspešne stiahnuté:';
$lang['download_none']    = 'Neboli nájdené žiadne pluginy, alebo nastal neznámy problém počas sťahovania a inštalácie pluginov.';

// info titles
$lang['plugin']           = 'Plugin:';
$lang['components']       = 'Súčasti';
$lang['noinfo']           = 'Tento plugin neobsahuje žiadne informácie, je možné, že je chybný.';
$lang['name']             = 'názov:';
$lang['date']             = 'Dátum:';
$lang['type']             = 'Typ:';
$lang['desc']             = 'Popis:';
$lang['author']           = 'Autor:';
$lang['www']              = 'Web:';

// error messages
$lang['error']            = 'Nastala neznáma chyba.';
$lang['error_download']   = 'Nie je možné stiahnuť súbor pluginu: %s';
$lang['error_badurl']     = 'Pravdepodobne zlá url adresa - nie je možné z nej určiť meno súboru';
$lang['error_dircreate']  = 'Nie je možné vytvoriť dočasný adresár pre uloženie sťahovaného súboru';
$lang['error_decompress'] = 'Správca pluginov nedokáže dekomprimovať stiahnutý súbor. '.
                            'Môže to byť dôsledok zlého stiahnutia, v tom prípade to skúste znovu, '.
                            'alebo môže ísť o neznámy formát súboru, v tom prípade musíte'.
                            'stiahnuť a nainštalovať plugin manuálne.';
$lang['error_copy']       = 'Nastala chyba kopírovania súboru počas pokusu inštalovať súbory pluginu'.
                            '<em>%s</em>: disk môže byť plný, alebo prístupové práva k súboru môžu byť nesprávne. '.
                            'Toto môže mať za následok čiastočne nainštalovanie pluginu '.
                            'a nestabilitu vašej DokuWiki.';
$lang['error_delete']     = 'Nastala chyba počas pokusu o zmazanie pluginu <em>%s</em>.  '.
                            'Najpravdepodobnejším dôvodom môžu byť nedostatočné prístupové práva pre súbor, alebo adresár';

//Setup VIM: ex: et ts=4 enc=utf-8 :
