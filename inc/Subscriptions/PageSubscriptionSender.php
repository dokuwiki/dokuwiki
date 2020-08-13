<?php


namespace dokuwiki\Subscriptions;


use Diff;
use InlineDiffFormatter;
use UnifiedDiffFormatter;

class PageSubscriptionSender extends SubscriptionSender
{

    /**
     * Send the diff for some page change
     *
     * @param string   $subscriber_mail The target mail address
     * @param string   $template        Mail template ('subscr_digest', 'subscr_single', 'mailtext', ...)
     * @param string   $id              Page for which the notification is
     * @param int|null $rev             Old revision if any
     * @param string   $summary         Change summary if any
     * @param int|null $current_rev     New revision if any
     *
     * @return bool                     true if successfully sent
     */
    public function sendPageDiff($subscriber_mail, $template, $id, $rev = null, $summary = '', $current_rev = null)
    {
        global $DIFF_INLINESTYLES;

        // prepare replacements (keys not set in hrep will be taken from trep)
        $trep = [
            'PAGE' => $id,
            'NEWPAGE' => wl($id, $current_rev?('rev='.$current_rev):'', true, '&'),
            'SUMMARY' => $summary,
            'SUBSCRIBE' => wl($id, ['do' => 'subscribe'], true, '&'),
        ];
        $hrep = [];

        if ($rev) {
            $subject = 'changed';
            $trep['OLDPAGE'] = wl($id, "rev=$rev", true, '&');

            $old_content = rawWiki($id, $rev);
            $new_content = rawWiki($id);

            $df = new Diff(
                explode("\n", $old_content),
                explode("\n", $new_content)
            );
            $dformat = new UnifiedDiffFormatter();
            $tdiff = $dformat->format($df);

            $DIFF_INLINESTYLES = true;
            $df = new Diff(
                explode("\n", $old_content),
                explode("\n", $new_content)
            );
            $dformat = new InlineDiffFormatter();
            $hdiff = $dformat->format($df);
            $hdiff = '<table>' . $hdiff . '</table>';
            $DIFF_INLINESTYLES = false;
        } else {
            $subject = 'newpage';
            $trep['OLDPAGE'] = '---';
            $tdiff = rawWiki($id);
            $hdiff = nl2br(hsc($tdiff));
        }

        $trep['DIFF'] = $tdiff;
        $hrep['DIFF'] = $hdiff;

        $headers = ['Message-Id' => $this->getMessageID($id)];
        if ($rev) {
            $headers['In-Reply-To'] = $this->getMessageID($id, $rev);
        }

        return $this->send(
            $subscriber_mail,
            $subject,
            $id,
            $template,
            $trep,
            $hrep,
            $headers
        );
    }

}
