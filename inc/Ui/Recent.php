<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki Recent Interface
 *
 * @package dokuwiki\Ui
 */
class Recent extends Ui
{
    protected $first;
    protected $show_changes;

    /** 
     * Recent Ui constructor
     *
     * @param int $first  skip the first n changelog lines
     * @param string $show_changes  type of changes to show; pages, mediafiles, or both
     */
    public function __construct($first = 0, $show_changes = 'both')
    {
        $this->first        = $first;
        $this->show_changes = $show_changes;
    }

    /**
     * Display recent changes
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Matthias Grimm <matthiasgrimm@users.sourceforge.net>
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Kate Arzamastseva <pshns@ukr.net>
     *
     * @triggers HTML_RECENTFORM_OUTPUT
     * @return void
     */
    public function show()
    {
        global $conf;
        global $lang;
        global $ID;

        $first = $this->first;
        $show_changes = $this->show_changes;

        /* we need to get one additionally log entry to be able to
         * decide if this is the last page or is there another one.
         * This is the cheapest solution to get this information.
         */
        $flags = 0;
        if ($show_changes == 'mediafiles' && $conf['mediarevisions']) {
            $flags = RECENTS_MEDIA_CHANGES;
        } elseif ($show_changes == 'pages') {
            $flags = 0;
        } elseif ($conf['mediarevisions']) {
            $show_changes = 'both';
            $flags = RECENTS_MEDIA_PAGES_MIXED;
        }

        $recents = getRecents($first, $conf['recent'] + 1, getNS($ID), $flags);
        if (count($recents) == 0 && $first != 0) {
            $first = 0;
            $recents = getRecents($first, $conf['recent'] + 1, getNS($ID), $flags);
        }

        $hasNext = false;
        if (count($recents) > $conf['recent']) {
            $hasNext = true;
            array_pop($recents); // remove extra log entry
        }

        // print intro
        print p_locale_xhtml('recent');

        if (getNS($ID) != '') {
            print '<div class="level1"><p>'
                . sprintf($lang['recent_global'], getNS($ID), wl('', 'do=recent'))
                .'</p></div>';
        }

        // create the form
        $form = new Form(['id' => 'dw__recent', 'method' => 'GET', 'action'=>wl($ID)]);
        $form->addClass('changes');
        $form->addTagOpen('div')->addClass('no');
        $form->setHiddenField('sectok', null);
        $form->setHiddenField('do', 'recent');
        $form->setHiddenField('id', $ID);

        // show dropdown selector, whether include not only recent pages but also recent media files?
        if ($conf['mediarevisions']) {
            $form->addTagOpen('div')->addClass('changeType');
            $options = array(
                            'pages'      => $lang['pages_changes'],
                            'mediafiles' => $lang['media_changes'],
                            'both'       => $lang['both_changes'],
            );
            $form->addDropdown('show_changes', $options, $lang['changes_type'])
                ->val($show_changes)->addClass('quickselect');
            $form->addButton('do[recent]', $lang['btn_apply'])->attr('type','submit');
            $form->addTagClose('div');
        }

        // start listing
        $form->addTagOpen('ul');

        foreach ($recents as $recent) {
            $date = dformat($recent['date']);
            $class = ($recent['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) ? 'minor': '';

            $form->addTagOpen('li')->addClass($class);
            $form->addTagOpen('div')->addClass('li');

            if (!empty($recent['media'])) {
                $form->addHTML(media_printicon($recent['id']));
            } else {
                $form->addTag('img')->attrs([
                        'src' => DOKU_BASE .'lib/images/fileicons/file.png',
                        'alt' => $recent['id']
                ])->addClass('icon');
            }

            $form->addTagOpen('span')->addClass('date');
            $form->addHTML($date);
            $form->addTagClose('span');

            $diff = false;
            $href = '';

            if (!empty($recent['media'])) {
                $changelog = new MediaChangeLog($recent['id']);
                $revs = $changelog->getRevisions(0, 1);
                $diff = (count($revs) && file_exists(mediaFN($recent['id'])));
                if ($diff) {
                    $href = media_managerURL(
                        array(
                            'tab_details' => 'history',
                            'mediado' => 'diff',
                            'image' => $recent['id'],
                            'ns' => getNS($recent['id'])
                        ), '&'
                    );
                }
            } else {
                $href = wl($recent['id'], "do=diff", false, '&');
            }

            if (!empty($recent['media']) && !$diff) {
                $form->addTag('img')->attrs([
                        'src'    => DOKU_BASE .'lib/images/blank.gif',
                        'width'  => 15,
                        'height' => 11,
                        'alt' => '',
                ]);
            } else {
                $form->addTagOpen('a')->attr('href', $href)->addClass('diff_link');
                $form->addTag('img')->attrs([
                        'src'    => DOKU_BASE .'lib/images/diff.png',
                        'width'  => 15,
                        'height' => 11,
                        'title'  => $lang['diff'],
                        'alt'    => $lang['diff'],
                ]);
                $form->addTagClose('a');
            }

            if (!empty($recent['media'])) {
                $href = media_managerURL(
                    array(
                        'tab_details' => 'history',
                        'image' => $recent['id'],
                        'ns' => getNS($recent['id'])
                    ), '&'
                );
            } else {
                $href = wl($recent['id'], "do=revisions", false, '&');
            }
            $form->addTagOpen('a')->attr('href', $href)->addClass('revisions_link');
            $form->addTag('img')->attrs([
                    'src'    => DOKU_BASE .'lib/images/history.png',
                    'width'  => 12,
                    'height' => 14,
                    'title'  => $lang['btn_revs'],
                    'alt'    => $lang['btn_revs']
            ]);
            $form->addTagClose('a');

            if (!empty($recent['media'])) {
                $href = media_managerURL(
                    array(
                        'tab_details' => 'view',
                        'image' => $recent['id'],
                        'ns' => getNS($recent['id'])
                    ), '&'
                );
                $class = file_exists(mediaFN($recent['id'])) ? 'wikilink1' : 'wikilink2';
                $form->addTagOpen('a')->attr('href', $href)->addClass($class);
                $form->addHTML($recent['id']);
                $form->addTagClose('a');
            } else {
                $form->addHTML(html_wikilink(':'. $recent['id'], useHeading('navigation') ? null : $recent['id']));
            }
            $form->addTagOpen('span')->addClass('sum');
            $form->addHTML(' – '. hsc($recent['sum']));
            $form->addTagClose('span');

            $form->addTagOPen('span')->addClass('user');
            if ($recent['user']) {
                $form->addHTML('<bdi>'. editorinfo($recent['user']) .'</bdi>');
                if (auth_ismanager()) {
                    $form->addHTML(' <bdo dir="ltr">('. $recent['ip'] .')</bdo>');
                }
            } else {
                $form->addHTML('<bdo dir="ltr">'. $recent['ip'] .'</bdo>');
            }
            $form->addTagClose('span');

            html_sizechange($recent['sizechange'], $form);

            $form->addTagClose('div');
            $form->addTagClose('li');
        }

        $form->addTagClose('ul');

        // provide navigation for pagenated cecent list (of pages and/or media files)
        $form->addTagOpen('div')->addClass('pagenav');
        $last = $first + $conf['recent'];
        if ($first > 0) {
            $first = $first - $conf['recent'];
            if ($first < 0) $first = 0;
            $form->addTagOpen('div')->addClass('pagenav-prev');
            $form->addTagOpen('button')->attrs([
                    'type'      => 'submit',
                    'name'      => 'first['. $first .']',
                    'accesskey' => 'n',
                    'title'     => $lang['btn_newer'] .' [N]',
            ])->addClass('button show');
            $form->addHTML($lang['btn_newer']);
            $form->addTagClose('button');
            $form->addTagClose('div');
        }
        if ($hasNext) {
            $form->addTagOpen('div')->addClass('pagenav-next');
            $form->addTagOpen('button')->attrs([
                    'type'      => 'submit',
                    'name'      => 'first['. $last .']',
                    'accesskey' => 'p',
                    'title'     => $lang['btn_older'] .' [P]',
            ])->addClass('button show');
            $form->addHTML($lang['btn_older']);
            $form->addTagClose('button');
            $form->addTagClose('div');
        }
        $form->addTagClose('div');

        $form->addTagClose('div'); // close div class=no

        // emit HTML_CRECENTFORM_OUTPUT event
        Event::createAndTrigger('HTML_RECENTFORM_OUTPUT', $form, null, false);
        print $form->toHTML();

        print DOKU_LF;
    }

}
