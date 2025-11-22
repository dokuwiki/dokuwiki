<?php

namespace easywiki\Action;

use easywiki\Action\Exception\ActionAbort;
use easywiki\Extension\Event;

/**
 * Class Export
 *
 * Handle exporting by calling the appropriate renderer
 *
 * @package easywiki\Action
 */
class Export extends AbstractAction
{
    /** @inheritdoc */
    public function minimumPermission()
    {
        return AUTH_READ;
    }

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
     * @inheritdoc
     */
    public function preProcess()
    {
        global $ID;
        global $REV;
        global $conf;
        global $lang;

        $pre = '';
        $post = '';
        $headers = [];

        // search engines: never cache exported docs! (Google only currently)
        $headers['X-Robots-Tag'] = 'noindex';

        $mode = substr($this->actionname, 7);
        switch ($mode) {
            case 'raw':
                $headers['Content-Type'] = 'text/plain; charset=utf-8';
                $headers['Content-Disposition'] = 'attachment; filename=' . noNS($ID) . '.txt';
                $output = rawWiki($ID, $REV);
                break;
            case 'xhtml':
                $pre .= '<!DOCTYPE html>' . WIKI_LF;
                $pre .= '<html lang="' . $conf['lang'] . '" dir="' . $lang['direction'] . '">' . WIKI_LF;
                $pre .= '<head>' . WIKI_LF;
                $pre .= '  <meta charset="utf-8" />' . WIKI_LF; // FIXME improve wrapper
                $pre .= '  <title>' . $ID . '</title>' . WIKI_LF;

                // get metaheaders
                ob_start();
                tpl_metaheaders();
                $pre .= ob_get_clean();

                $pre .= '</head>' . WIKI_LF;
                $pre .= '<body>' . WIKI_LF;
                $pre .= '<div class="easywiki export">' . WIKI_LF;

                // get toc
                $pre .= tpl_toc(true);

                $headers['Content-Type'] = 'text/html; charset=utf-8';
                $output = p_wiki_xhtml($ID, $REV, false);

                $post .= '</div>' . WIKI_LF;
                $post .= '</body>' . WIKI_LF;
                $post .= '</html>' . WIKI_LF;
                break;
            case 'xhtmlbody':
                $headers['Content-Type'] = 'text/html; charset=utf-8';
                $output = p_wiki_xhtml($ID, $REV, false);
                break;
            case 'metadata':
                // metadata should not be exported
                break;
            default:
                $output = p_cached_output(wikiFN($ID, $REV), $mode, $ID);
                $headers = p_get_metadata($ID, "format $mode");
                break;
        }

        // prepare event data
        $data = [];
        $data['id'] = $ID;
        $data['mode'] = $mode;
        $data['headers'] = $headers;
        $data['output'] =& $output;

        Event::createAndTrigger('ACTION_EXPORT_POSTPROCESS', $data);

        if (!empty($data['output'])) {
            if (is_array($data['headers'])) foreach ($data['headers'] as $key => $val) {
                header("$key: $val");
            }
            echo $pre . $data['output'] . $post;
            exit;
        }

        throw new ActionAbort();
    }
}
