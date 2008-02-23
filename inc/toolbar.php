<?php
/**
 * Editing toolbar functions
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

  if(!defined('DOKU_INC')) define('DOKU_INC',fullpath(dirname(__FILE__).'/../').'/');

require_once(DOKU_INC.'inc/JSON.php');


/**
 * Prepares and prints an JavaScript array with all toolbar buttons
 *
 * @todo add toolbar plugins
 * @param  string $varname Name of the JS variable to fill
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function toolbar_JSdefines($varname){
    global $ID;
    global $conf;
    global $lang;

    $menu = array();

    $evt = new Doku_Event('TOOLBAR_DEFINE', $menu);
    if ($evt->advise_before()){

        // build button array
        $menu = array_merge($menu, array(
           array(
                'type'   => 'format',
                'title'  => $lang['qb_bold'],
                'icon'   => 'bold.png',
                'key'    => 'b',
                'open'   => '**',
                'close'  => '**',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_italic'],
                'icon'   => 'italic.png',
                'key'    => 'i',
                'open'   => '//',
                'close'  => '//',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_underl'],
                'icon'   => 'underline.png',
                'key'    => 'u',
                'open'   => '__',
                'close'  => '__',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_code'],
                'icon'   => 'mono.png',
                'key'    => 'c',
                'open'   => "''",
                'close'  => "''",
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_strike'],
                'icon'   => 'strike.png',
                'key'    => 'd',
                'open'  => '<del>',
                'close'   => '</del>',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_h1'],
                'icon'   => 'h1.png',
                'key'    => '1',
                'open'   => '====== ',
                'close'  => ' ======\n',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_h2'],
                'icon'   => 'h2.png',
                'key'    => '2',
                'open'   => '===== ',
                'close'  => ' =====\n',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_h3'],
                'icon'   => 'h3.png',
                'key'    => '3',
                'open'   => '==== ',
                'close'  => ' ====\n',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_h4'],
                'icon'   => 'h4.png',
                'key'    => '4',
                'open'   => '=== ',
                'close'  => ' ===\n',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_h5'],
                'icon'   => 'h5.png',
                'key'    => '5',
                'open'   => '== ',
                'close'  => ' ==\n',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_link'],
                'icon'   => 'link.png',
                'key'    => 'l',
                'open'   => '[[',
                'close'  => ']]',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_extlink'],
                'icon'   => 'linkextern.png',
                'open'   => '[[',
                'close'  => ']]',
                'sample' => 'http://example.com|'.$lang['qb_extlink'],
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_ol'],
                'icon'   => 'ol.png',
                'open'   => '  - ',
                'close'  => '\n',
                ),
           array(
                'type'   => 'format',
                'title'  => $lang['qb_ul'],
                'icon'   => 'ul.png',
                'open'   => '  * ',
                'close'  => '\n',
                ),
           array(
                'type'   => 'insert',
                'title'  => $lang['qb_hr'],
                'icon'   => 'hr.png',
                'insert' => '----\n',
                ),
           array(
                'type'   => 'mediapopup',
                'title'  => $lang['qb_media'],
                'icon'   => 'image.png',
                'url'    => DOKU_BASE.'lib/exe/mediamanager.php?ns=',
                'name'   => 'mediaselect',
                'options'=> 'width=750,height=500,left=20,top=20,scrollbars=yes,resizable=yes',
                ),
          array(
                'type'   => 'picker',
                'title'  => $lang['qb_smileys'],
                'icon'   => 'smiley.png',
                'list'   => getSmileys(),
                'icobase'=> 'smileys',
               ),
          array(
                'type'   => 'picker',
                'title'  => $lang['qb_chars'],
                'icon'   => 'chars.png',
                'list'   => explode(' ','À à Á á Â â Ã ã Ä ä Ǎ ǎ Ă ă Å å Ā ā Ą ą Æ æ Ć ć Ç ç Č č Ĉ ĉ Ċ ċ Ð đ ð Ď ď È è É é Ê ê Ë ë Ě ě Ē ē Ė ė Ę ę Ģ ģ Ĝ ĝ Ğ ğ Ġ ġ Ĥ ĥ Ì ì Í í Î î Ï ï Ǐ ǐ Ī ī İ ı Į į Ĵ ĵ Ķ ķ Ĺ ĺ Ļ ļ Ľ ľ Ł ł Ŀ ŀ Ń ń Ñ ñ Ņ ņ Ň ň Ò ò Ó ó Ô ô Õ õ Ö ö Ǒ ǒ Ō ō Ő ő Œ œ Ø ø Ŕ ŕ Ŗ ŗ Ř ř Ś ś Ş ş Š š Ŝ ŝ Ţ ţ Ť ť Ù ù Ú ú Û û Ü ü Ǔ ǔ Ŭ ŭ Ū ū Ů ů ǖ ǘ ǚ ǜ Ų ų Ű ű Ŵ ŵ Ý ý Ÿ ÿ Ŷ ŷ Ź ź Ž ž Ż ż Þ þ ß Ħ ħ ¿ ¡ ¢ £ ¤ ¥ € ¦ § ª ¬ ¯ ° ± ÷ ‰ ¼ ½ ¾ ¹ ² ³ µ ¶ † ‡ · • º ∀ ∂ ∃ Ə ə ∅ ∇ ∈ ∉ ∋ ∏ ∑ ‾ − ∗ √ ∝ ∞ ∠ ∧ ∨ ∩ ∪ ∫ ∴ ∼ ≅ ≈ ≠ ≡ ≤ ≥ ⊂ ⊃ ⊄ ⊆ ⊇ ⊕ ⊗ ⊥ ⋅ ◊ ℘ ℑ ℜ ℵ ♠ ♣ ♥ ♦ α β Γ γ Δ δ ε ζ η Θ θ ι κ Λ λ μ Ξ ξ Π π ρ Σ σ Τ τ υ Φ φ χ Ψ ψ Ω ω ★ ☆ ☎ ☚ ☛ ☜ ☝ ☞ ☟ ☹ ☺ ✔ ✘ × „ “ ” ‚ ‘ ’ « » ‹ › — – … ← ↑ → ↓ ↔ ⇐ ⇑ ⇒ ⇓ ⇔ © ™ ® ′ ″'),
               ),
          array(
                'type'   => 'signature',
                'title'  => $lang['qb_sig'],
                'icon'   => 'sig.png',
                'key'    => 'y',
               ),
        ));
    } // end event TOOLBAR_DEFINE default action
    $evt->advise_after();
    unset($evt);

    // use JSON to build the JavaScript array
    $json = new JSON();
    print "var $varname = ".$json->encode($menu).";\n";
}

/**
 * prepares the signature string as configured in the config
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function toolbar_signature(){
  global $conf;
  global $INFO;

  $sig = $conf['signature'];
  $sig = strftime($sig);
  $sig = str_replace('@USER@',$_SERVER['REMOTE_USER'],$sig);
  $sig = str_replace('@NAME@',$INFO['userinfo']['name'],$sig);
  $sig = str_replace('@MAIL@',$INFO['userinfo']['mail'],$sig);
  $sig = str_replace('@DATE@',strftime($conf['dformat']),$sig);
  $sig = str_replace('\\\\n','\\n',addslashes($sig));
  return $sig;
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
