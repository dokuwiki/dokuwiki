<?php

namespace dokuwiki\Ui;

use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\ChangeLog\MediaChangeLog;
use dokuwiki\Extension\Event;
use dokuwiki\Form\Form;

/**
 * DokuWiki Revisions Interface
 *
 * @package dokuwiki\Ui
 */
class Revisions extends Ui
{
    protected $first;
    protected $media_id;

    /** 
     * Revisions Ui constructor
     *
     * @param int $first  skip the first n changelog lines
     * @param bool|string $media_id  id of media, or false for current page
     */
    public function __construct($first = 0, $media_id = false)
    {
        $this->first    = $first;
        $this->media_id = $media_id;
    }

    /**
     * Display list of old revisions
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Ben Coburn <btcoburn@silicodon.net>
     * @author Kate Arzamastseva <pshns@ukr.net>
     *
     * @triggers HTML_REVISIONSFORM_OUTPUT
     * @return void
     */
    public function show()
    {
        global $ID;
        global $INFO;
        global $conf;
        global $lang;

        $first    = $this->first;
        $media_id = $this->media_id;

        $id = $ID;
        if ($media_id) {
            $id = $media_id;
            $changelog = new MediaChangeLog($id);
        } else {
            $changelog = new PageChangeLog($id);
        }

        /* we need to get one additional log entry to be able to
         * decide if this is the last page or is there another one.
         * see html_recent()
         */

        $revisions = $changelog->getRevisions($first, $conf['recent'] +1);

        if (count($revisions) == 0 && $first != 0) {
            $first = 0;
            $revisions = $changelog->getRevisions($first, $conf['recent'] +1);
        }
        $hasNext = false;
        if (count($revisions) > $conf['recent']) {
            $hasNext = true;
            array_pop($revisions); // remove extra log entry
        }

        // print intro
        if (!$media_id) {
            print p_locale_xhtml('revisions');
            $exists = $INFO['exists'];
            $display_name = useHeading('navigation') ? hsc(p_get_first_heading($id)) : $id;
            if (!$display_name) {
                $display_name = $id;
            }
        } else {
            $exists = file_exists(mediaFN($id));
            $display_name = $id;
        }

        // create the form
        $form = new Form(['id' => 'page__revisions']);
        $form->addClass('changes');
        if ($media_id) {
            $form->attr('action', media_managerURL(array('image' => $media_id), '&'));
        }

        // start listing
        $form->addTagOpen('ul');

        if ($exists && $first == 0) {
            $minor = false;
            if ($media_id) {
                $date = dformat(@filemtime(mediaFN($id)));
                $href = media_managerURL(array('image' => $id, 'tab_details' => 'view'), '&');

                $changelog->setChunkSize(1024);
                $revinfo = $changelog->getRevisionInfo(@filemtime(fullpath(mediaFN($id))));

                $summary = $revinfo['sum'];
                $editor = $revinfo['user'] ?: $revinfo['ip'];
                $sizechange = $revinfo['sizechange'];
            } else {
                $date = dformat($INFO['lastmod']);
                $sizechange = null;
                if (isset($INFO['meta']) && isset($INFO['meta']['last_change'])) {
                    if ($INFO['meta']['last_change']['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) {
                        $minor = true;
                    }
                    if (isset($INFO['meta']['last_change']['sizechange'])) {
                        $sizechange = $INFO['meta']['last_change']['sizechange'];
                    }
                }
                $pagelog = new PageChangeLog($ID);
                $latestrev = $pagelog->getRevisions(-1, 1);
                $latestrev = array_pop($latestrev);
                $href = wl($id, "rev=$latestrev", false, '&');
                $summary = $INFO['sum'];
                $editor = $INFO['editor'];
            }

            $form->addTagOpen('li')->addClass($minor ? 'minor' : '');
            $form->addTagOpen('div')->addClass('li');
            $form->addCheckbox('rev2[]')->val('current');

            $form->addTagOpen('span')->addClass('data');
            $form->addHTML($date);
            $form->addTagClose('span');

            $form->addTag('img')->attrs([
                'src' => DOKU_BASE.'lib/images/blank.gif',
                'width' => 15,
                'height' => 11,
                'alt' => '',
            ]);

            $form->addTagOPen('a')->attr('href', $href)->addClass('wikilink1');
            $form->addHTML($display_name);
            $form->addTagClose('a');

            if ($media_id) $form->addTagOpen('div');

            if ($summary) {
                $form->addTagOpen('span')->addClass('sum');
                if (!$media_id) $form->addHTML(' – ');
                $form->addHTML('<bdi>' . hsc($summary) . '</bdi>');
                $form->addTagClose('span');
            }

            $form->addTagOpen('span')->addClass('user');
            $form->addHTML(
                (empty($editor)) ? ('('.$lang['external_edit'].')') : '<bdi>'.editorinfo($editor).'</bdi>'
            );
            $form->addTagClose('span');


            html_sizechange($sizechange, $form);

            $form->addHTML('('.$lang['current'].')');

            if ($media_id) $form->addTagClose('div');

            $form->addTagClose('div');
            $form->addTagClose('li');
        }

        foreach ($revisions as $rev) {
            $date = dformat($rev);
            $info = $changelog->getRevisionInfo($rev);
            if ($media_id) {
                $exists = file_exists(mediaFN($id, $rev));
            } else {
                $exists = page_exists($id, $rev);
            }

            $class = '';
            if ($info['type'] === DOKU_CHANGE_TYPE_MINOR_EDIT) {
                $class = 'minor';
            }

            $form->addTagOpen('li')->addClass($class);
            $form->addTagOpen('div')->addClass('li');

            if ($exists){
                $form->addCheckbox('rev2[]')->val($rev);
            } else {
                $form->addTag('img')->attrs([
                    'src' => DOKU_BASE.'lib/images/blank.gif',
                    'width' => 15,
                    'height' => 11,
                    'alt' => '',
                ]);
            }

            $form->addTagOpen('span')->addClass('date');
            $form->addHTML($date);
            $form->addTagClose('span');

            if ($exists) {
                if (!$media_id) {
                    $href = wl($id, "rev=$rev,do=diff", false, '&');
                } else {
                    $href = media_managerURL(array('image' => $id, 'rev' => $rev, 'mediado' => 'diff'), '&');
                }
                $form->addTagOpen('a')->attr('href', $href)->addClass('diff_link');
                $form->addTag('img')->attrs([
                    'src'    => DOKU_BASE.'lib/images/diff.png',
                    'width'  => 15,
                    'height' => 11,
                    'title'  => $lang['diff'],
                    'alt'    => $lang['diff'],
                ]);
                $form->addTagClose('a');

                if (!$media_id) {
                    $href = wl($id, "rev=$rev", false, '&');
                } else {
                    $href = media_managerURL(array('image' => $id, 'tab_details' => 'view', 'rev' => $rev), '&');
                }
                $form->addTagOpen('a')->attr('href', $href)->addClass('wikilink1');
                $form->addHTML($display_name);
                $form->addTagClose('a');
            } else {
                $form->addTag('img')->attrs([
                    'src'    => DOKU_BASE.'lib/images/blank.gif',
                    'width'  => 15,
                    'height' => 11,
                    'alt'    => '',
                ]);
                $form->addHTML($display_name);
            }

            if ($media_id) $form->addTagOpen('div');

            if ($info['sum']) {
                $form->addTagOpen('span')->addClass('sum');
                if (!$media_id) $form->addHTML(' – ');
                $form->addHTML('<bdi>'. hsc($info['sum']) .'</bdi>');
                $form->addTagClose('span');
            }

            $form->addTagOpen('span')->addClass('user');
            if ($info['user']) {
                $form->addHTML('<bdi>'. editorinfo($info['user']) .'</bdi>');
                if (auth_ismanager()) {
                    $form->addHTML(' <bdo dir="ltr">('. $info['ip'] .')</bdo>');
                }
            } else {
                $form->addHTML('<bdo dir="ltr">' .$info['ip'] .'</bdo>');
            }
            $form->addTagClose('span');

            html_sizechange($info['sizechange'], $form);

            if ($media_id) $form->addTagClose('div');

            $form->addTagClose('div');
            $form->addTagClose('li');
        }

        // end of revision list
        $form->addTagClose('ul');

        // show button for diff view
        if (!$media_id) {
            $form->addButton('do[diff]', $lang['diff2'])->attr('type', 'submit');
        } else {
            $form->setHiddenField('mediado', 'diff');
            $form->addButton('', $lang['diff2'])->attr('type', 'submit');
        }

        $form->addTagClose('div'); // close div class=no

        // emit HTML_REVISIONSFORM_OUTPUT event, print the form
        Event::createAndTrigger('HTML_REVISIONSFORM_OUTPUT', $form, 'html_form_output', false);

        print DOKU_LF;

        // provide navigation for pagenated revision list (of pages and/or media files)
        print '<div class="pagenav">';
        $last = $first + $conf['recent'];
        if ($first > 0) {
            $first = $first - $conf['recent'];
            if ($first < 0) $first = 0;
            print '<div class="pagenav-prev">';
            if ($media_id) {
                print html_btn('newer',$media_id,"p",media_managerURL(array('first' => $first), '&amp;', false, true));
            } else {
                print html_btn('newer',$id,"p",array('do' => 'revisions', 'first' => $first));
            }
            print '</div>';
        }
        if ($hasNext) {
            print '<div class="pagenav-next">';
            if ($media_id) {
                print html_btn('older',$media_id,"n",media_managerURL(array('first' => $last), '&amp;', false, true));
            } else {
                print html_btn('older',$id,"n",array('do' => 'revisions', 'first' => $last));
            }
            print '</div>';
        }
        print '</div>';

        print DOKU_LF;
    }

}
