<?php
/**
 * Created by IntelliJ IDEA.
 * User: andi
 * Date: 2/11/17
 * Time: 11:10 AM
 */

namespace dokuwiki\Action;

use dokuwiki\Action\Exception\ActionAbort;

class Export extends AbstractAction {

    /** @inheritdoc */
    function minimumPermission() {
        return AUTH_READ;
    }

    // FIXME proper mode should be checked

    /**
     * Export a wiki page for various formats
     *
     * Triggers ACTION_EXPORT_POSTPROCESS
     *
     *  Event data:
     *    data['id']      -- page id
     *    data['mode']    -- requested export mode
     *    data['headers'] -- export headers
     *    data['output']  -- export output
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Michael Klier <chi@chimeric.de>
     */
    public function preProcess() {
        global $ID;
        global $REV;
        global $conf;
        global $lang;

        $pre = '';
        $post = '';
        $headers = array();

        // search engines: never cache exported docs! (Google only currently)
        $headers['X-Robots-Tag'] = 'noindex';

        $mode = substr('FIXME', 7); // FIXME how to pass the proper mode?
        switch($mode) {
            case 'raw':
                $headers['Content-Type'] = 'text/plain; charset=utf-8';
                $headers['Content-Disposition'] = 'attachment; filename=' . noNS($ID) . '.txt';
                $output = rawWiki($ID, $REV);
                break;
            case 'xhtml':
                $pre .= '<!DOCTYPE html>' . DOKU_LF;
                $pre .= '<html lang="' . $conf['lang'] . '" dir="' . $lang['direction'] . '">' . DOKU_LF;
                $pre .= '<head>' . DOKU_LF;
                $pre .= '  <meta charset="utf-8" />' . DOKU_LF; // FIXME improve wrapper
                $pre .= '  <title>' . $ID . '</title>' . DOKU_LF;

                // get metaheaders
                ob_start();
                tpl_metaheaders();
                $pre .= ob_get_clean();

                $pre .= '</head>' . DOKU_LF;
                $pre .= '<body>' . DOKU_LF;
                $pre .= '<div class="dokuwiki export">' . DOKU_LF;

                // get toc
                $pre .= tpl_toc(true);

                $headers['Content-Type'] = 'text/html; charset=utf-8';
                $output = p_wiki_xhtml($ID, $REV, false);

                $post .= '</div>' . DOKU_LF;
                $post .= '</body>' . DOKU_LF;
                $post .= '</html>' . DOKU_LF;
                break;
            case 'xhtmlbody':
                $headers['Content-Type'] = 'text/html; charset=utf-8';
                $output = p_wiki_xhtml($ID, $REV, false);
                break;
            default:
                $output = p_cached_output(wikiFN($ID, $REV), $mode, $ID);
                $headers = p_get_metadata($ID, "format $mode");
                break;
        }

        // prepare event data
        $data = array();
        $data['id'] = $ID;
        $data['mode'] = $mode;
        $data['headers'] = $headers;
        $data['output'] =& $output;

        trigger_event('ACTION_EXPORT_POSTPROCESS', $data);

        if(!empty($data['output'])) {
            if(is_array($data['headers'])) foreach($data['headers'] as $key => $val) {
                header("$key: $val");
            }
            print $pre . $data['output'] . $post;
            exit;
        }

        throw new ActionAbort();
    }

}
