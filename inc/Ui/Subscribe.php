<?php

namespace dokuwiki\Ui;

use dokuwiki\Form\Form;

/**
 * DokuWiki Subscribe Interface
 *
 * @package dokuwiki\Ui
 */
class Subscribe extends Ui
{
    /**
     * Display the subscribe form
     *
     * @author Adrian Lang <lang@cosmocode.de>
     *
     * @return void
     */
    public function show()
    {
        global $INPUT;
        global $INFO;
        global $ID;
        global $lang;
        global $conf;
        $stime_days = $conf['subscribe_time'] / 60 / 60 / 24;

        // print intro
        echo p_locale_xhtml('subscr_form');

        // list up current subscriptions
        echo '<h2>'.$lang['subscr_m_current_header'].'</h2>';
        echo '<div class="level2">';
        if ($INFO['subscribed'] === false) {
            echo '<p>'.$lang['subscr_m_not_subscribed'].'</p>';
        } else {
            echo '<ul>';
            foreach ($INFO['subscribed'] as $sub) {
                echo '<li><div class="li">';
                if ($sub['target'] !== $ID) {
                    echo '<code class="ns">'.hsc(prettyprint_id($sub['target'])).'</code>';
                } else {
                    echo '<code class="page">'.hsc(prettyprint_id($sub['target'])).'</code>';
                }
                $sstl = sprintf($lang['subscr_style_'.$sub['style']], $stime_days);
                if (!$sstl) $sstl = hsc($sub['style']);
                echo ' ('.$sstl.') ';

                echo '<a href="'.wl(
                    $ID,
                    array(
                         'do'        => 'subscribe',
                         'sub_target'=> $sub['target'],
                         'sub_style' => $sub['style'],
                         'sub_action'=> 'unsubscribe',
                         'sectok'    => getSecurityToken()
                    )
                ).
                    '" class="unsubscribe">'.$lang['subscr_m_unsubscribe'].
                    '</a></div></li>';
            }
            echo '</ul>';
        }
        echo '</div>';

        // Add new subscription form
        echo '<h2>'.$lang['subscr_m_new_header'].'</h2>';
        echo '<div class="level2">';
        $ns      = getNS($ID).':';
        $targets = [
            $ID => '<code class="page">'.prettyprint_id($ID).'</code>',
            $ns => '<code class="ns">'.prettyprint_id($ns).'</code>',
        ];
        $styles = [
            'every'  => $lang['subscr_style_every'],
            'digest' => sprintf($lang['subscr_style_digest'], $stime_days),
            'list'   => sprintf($lang['subscr_style_list'], $stime_days),
        ];

        // create the form
        $form = new Form(['id' => 'subscribe__form']);
        $form->addTagOpen('div')->addClass('no');
        $form->setHiddenField('id', $ID);
        $form->setHiddenField('do', 'subscribe');
        $form->setHiddenField('sub_action', 'subscribe');

        $form->addFieldsetOpen($lang['subscr_m_subscribe']);
        $value = (array_key_exists($INPUT->post->str('sub_target'), $targets)) ?
                 $INPUT->str('sub_target') : key($targets);
        foreach ($targets as $val => $label) {
            $data = ($value === $val) ? ['checked' => 'checked'] : [];
            $form->addRadioButton('sub_target', $label)->val($val)->attrs($data);
        }
        $form->addFieldsetClose();

        $form->addFieldsetOpen($lang['subscr_m_receive']);
        $value = (array_key_exists($INPUT->post->str('sub_style'), $styles)) ?
                 $INPUT->str('sub_style') : key($styles);
        foreach ($styles as $val => $label) {
            $data = ($value === $val) ? ['checked' => 'checked'] : [];
            $form->addRadioButton('sub_style', $label)->val($val)->attrs($data);
        }
        $form->addFieldsetClose();

        $form->addButton('do[subscribe]', $lang['subscr_m_subscribe'])->attr('type', 'submit');
        $form->addTagClose('div');

        print $form->toHTML('Subscribe');

        echo '</div>';
    }

}
