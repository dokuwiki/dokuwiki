<?php
/**
 * DokuWiki Plugin extension (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Hamann <michael@content-space.de>
 */

/**
 * Class helper_plugin_extension_list takes care of creating a HTML list of extensions
 */
class helper_plugin_extension_list extends DokuWiki_Plugin
{
    protected $form = '';
    /** @var  helper_plugin_extension_gui */
    protected $gui;

    /**
     * Constructor
     *
     * loads additional helpers
     */
    public function __construct()
    {
        $this->gui = plugin_load('helper', 'extension_gui');
    }

    /**
     * Initialize the extension table form
     */
    public function startForm()
    {
        $this->form .= '<ul class="extensionList">';
    }

    /**
     * Build single row of extension table
     *
     * @param helper_plugin_extension_extension  $extension The extension that shall be added
     * @param bool                               $showinfo  Show the info area
     */
    public function addRow(helper_plugin_extension_extension $extension, $showinfo = false)
    {
        $this->startRow($extension);
        $this->populateColumn('legend', $this->makeLegend($extension, $showinfo));
        $this->populateColumn('actions', $this->makeActions($extension));
        $this->endRow();
    }

    /**
     * Adds a header to the form
     *
     * @param string $id     The id of the header
     * @param string $header The content of the header
     * @param int    $level  The level of the header
     */
    public function addHeader($id, $header, $level = 2)
    {
        $this->form .='<h'.$level.' id="'.$id.'">'.hsc($header).'</h'.$level.'>'.DOKU_LF;
    }

    /**
     * Adds a paragraph to the form
     *
     * @param string $data The content
     */
    public function addParagraph($data)
    {
        $this->form .= '<p>'.hsc($data).'</p>'.DOKU_LF;
    }

    /**
     * Add hidden fields to the form with the given data
     *
     * @param array $data key-value list of fields and their values to add
     */
    public function addHidden(array $data)
    {
        $this->form .= '<div class="no">';
        foreach ($data as $key => $value) {
            $this->form .= '<input type="hidden" name="'.hsc($key).'" value="'.hsc($value).'" />';
        }
        $this->form .= '</div>'.DOKU_LF;
    }

    /**
     * Add closing tags
     */
    public function endForm()
    {
        $this->form .= '</ul>';
    }

    /**
     * Show message when no results are found
     */
    public function nothingFound()
    {
        global $lang;
        $this->form .= '<li class="notfound">'.$lang['nothingfound'].'</li>';
    }

    /**
     * Print the form
     *
     * @param bool $returnonly whether to return html or print
     */
    public function render($returnonly = false)
    {
        if ($returnonly) return $this->form;
        echo $this->form;
    }

    /**
     * Start the HTML for the row for the extension
     *
     * @param helper_plugin_extension_extension $extension The extension
     */
    private function startRow(helper_plugin_extension_extension $extension)
    {
        $this->form .= '<li id="extensionplugin__'.hsc($extension->getID()).
            '" class="'.$this->makeClass($extension).'">';
    }

    /**
     * Add a column with the given class and content
     * @param string $class The class name
     * @param string $html  The content
     */
    private function populateColumn($class, $html)
    {
        $this->form .= '<div class="'.$class.' col">'.$html.'</div>'.DOKU_LF;
    }

    /**
     * End the row
     */
    private function endRow()
    {
        $this->form .= '</li>'.DOKU_LF;
    }

    /**
     * Generate the link to the plugin homepage
     *
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The HTML code
     */
    public function makeHomepageLink(helper_plugin_extension_extension $extension)
    {
        global $conf;
        $url = $extension->getURL();
        if (strtolower(parse_url($url, PHP_URL_HOST)) == 'www.dokuwiki.org') {
            $linktype = 'interwiki';
        } else {
            $linktype = 'extern';
        }
        $param = array(
            'href'   => $url,
            'title'  => $url,
            'class'  => ($linktype == 'extern') ? 'urlextern' : 'interwiki iw_doku',
            'target' => $conf['target'][$linktype],
            'rel'    => ($linktype == 'extern') ? 'noopener' : '',
        );
        if ($linktype == 'extern' && $conf['relnofollow']) {
            $param['rel'] = implode(' ', [$param['rel'], 'ugc nofollow']);
        }
        $html = ' <a '. buildAttributes($param, true).'>'.
            $this->getLang('homepage_link').'</a>';
        return $html;
    }

    /**
     * Generate the class name for the row of the extension
     *
     * @param helper_plugin_extension_extension $extension The extension object
     * @return string The class name
     */
    public function makeClass(helper_plugin_extension_extension $extension)
    {
        $class = ($extension->isTemplate()) ? 'template' : 'plugin';
        if ($extension->isInstalled()) {
            $class.=' installed';
            $class.= ($extension->isEnabled()) ? ' enabled':' disabled';
            if ($extension->updateAvailable()) $class .= ' updatable';
        }
        if (!$extension->canModify()) $class.= ' notselect';
        if ($extension->isProtected()) $class.=  ' protected';
        //if($this->showinfo) $class.= ' showinfo';
        return $class;
    }

    /**
     * Generate a link to the author of the extension
     *
     * @param helper_plugin_extension_extension $extension The extension object
     * @return string The HTML code of the link
     */
    public function makeAuthor(helper_plugin_extension_extension $extension)
    {
        if ($extension->getAuthor()) {
            $mailid = $extension->getEmailID();
            if ($mailid) {
                $url = $this->gui->tabURL('search', array('q' => 'authorid:'.$mailid));
                $html = '<a href="'.$url.'" class="author" title="'.$this->getLang('author_hint').'" >'.
                    '<img src="//www.gravatar.com/avatar/'.$mailid.
                    '?s=20&amp;d=mm" width="20" height="20" alt="" /> '.
                    hsc($extension->getAuthor()).'</a>';
            } else {
                $html = '<span class="author">'.hsc($extension->getAuthor()).'</span>';
            }
            $html = '<bdi>'.$html.'</bdi>';
        } else {
            $html = '<em class="author">'.$this->getLang('unknown_author').'</em>'.DOKU_LF;
        }
        return $html;
    }

    /**
     * Get the link and image tag for the screenshot/thumbnail
     *
     * @param helper_plugin_extension_extension $extension The extension object
     * @return string The HTML code
     */
    public function makeScreenshot(helper_plugin_extension_extension $extension)
    {
        $screen = $extension->getScreenshotURL();
        $thumb = $extension->getThumbnailURL();

        if ($screen) {
            // use protocol independent URLs for images coming from us #595
            $screen = str_replace('http://www.dokuwiki.org', '//www.dokuwiki.org', $screen);
            $thumb = str_replace('http://www.dokuwiki.org', '//www.dokuwiki.org', $thumb);

            $title = sprintf($this->getLang('screenshot'), hsc($extension->getDisplayName()));
            $img = '<a href="'.hsc($screen).'" target="_blank" class="extension_screenshot">'.
                '<img alt="'.$title.'" width="120" height="70" src="'.hsc($thumb).'" />'.
                '</a>';
        } elseif ($extension->isTemplate()) {
            $img = '<img alt="" width="120" height="70" src="'.DOKU_BASE.
                'lib/plugins/extension/images/template.png" />';
        } else {
            $img = '<img alt="" width="120" height="70" src="'.DOKU_BASE.
                'lib/plugins/extension/images/plugin.png" />';
        }
        $html = '<div class="screenshot" >'.$img.'<span></span></div>'.DOKU_LF;
        return $html;
    }

    /**
     * Extension main description
     *
     * @param helper_plugin_extension_extension $extension The extension object
     * @param bool                              $showinfo  Show the info section
     * @return string The HTML code
     */
    public function makeLegend(helper_plugin_extension_extension $extension, $showinfo = false)
    {
        $html  = '<div>';
        $html .= '<h2>';
        $html .= sprintf(
            $this->getLang('extensionby'),
            '<bdi>'.hsc($extension->getDisplayName()).'</bdi>',
            $this->makeAuthor($extension)
        );
        $html .= '</h2>'.DOKU_LF;

        $html .= $this->makeScreenshot($extension);

        $popularity = $extension->getPopularity();
        if ($popularity !== false && !$extension->isBundled()) {
            $popularityText = sprintf($this->getLang('popularity'), round($popularity*100, 2));
            $html .= '<div class="popularity" title="'.$popularityText.'">'.
                '<div style="width: '.($popularity * 100).'%;">'.
                '<span class="a11y">'.$popularityText.'</span>'.
                '</div></div>'.DOKU_LF;
        }

        if ($extension->getDescription()) {
            $html .= '<p><bdi>';
            $html .=  hsc($extension->getDescription()).' ';
            $html .= '</bdi></p>'.DOKU_LF;
        }

        $html .= $this->makeLinkbar($extension);

        if ($showinfo) {
            $url = $this->gui->tabURL('');
            $class = 'close';
        } else {
            $url = $this->gui->tabURL('', array('info' => $extension->getID()));
            $class = '';
        }
        $html .= ' <a href="'.$url.'#extensionplugin__'.$extension->getID().
            '" class="info '.$class.'" title="'.$this->getLang('btn_info').
            '" data-extid="'.$extension->getID().'">'.$this->getLang('btn_info').'</a>';

        if ($showinfo) {
            $html .= $this->makeInfo($extension);
        }
        $html .= $this->makeNoticeArea($extension);
        $html .= '</div>'.DOKU_LF;
        return $html;
    }

    /**
     * Generate the link bar HTML code
     *
     * @param helper_plugin_extension_extension $extension The extension instance
     * @return string The HTML code
     */
    public function makeLinkbar(helper_plugin_extension_extension $extension)
    {
        global $conf;
        $html  = '<div class="linkbar">';
        $html .= $this->makeHomepageLink($extension);

        $bugtrackerURL = $extension->getBugtrackerURL();
        if ($bugtrackerURL) {
            if (strtolower(parse_url($bugtrackerURL, PHP_URL_HOST)) == 'www.dokuwiki.org') {
                $linktype = 'interwiki';
            } else {
                $linktype = 'extern';
            }
            $param = array(
                'href'   => $bugtrackerURL,
                'title'  => $bugtrackerURL,
                'class'  => 'bugs',
                'target' => $conf['target'][$linktype],
                'rel'    => ($linktype == 'extern') ? 'noopener' : '',
            );
            if ($conf['relnofollow']) {
                $param['rel'] = implode(' ', [$param['rel'], 'ugc nofollow']);
            }
            $html .= ' <a '.buildAttributes($param, true).'>'.
                  $this->getLang('bugs_features').'</a>';
        }
        if ($extension->getTags()) {
            $first = true;
            $html .= ' <span class="tags">'.$this->getLang('tags').' ';
            foreach ($extension->getTags() as $tag) {
                if (!$first) {
                    $html .= ', ';
                } else {
                    $first = false;
                }
                $url = $this->gui->tabURL('search', ['q' => 'tag:'.$tag]);
                $html .= '<bdi><a href="'.$url.'">'.hsc($tag).'</a></bdi>';
            }
            $html .= '</span>';
        }
        $html .= '</div>'.DOKU_LF;
        return $html;
    }

    /**
     * Notice area
     *
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The HTML code
     */
    public function makeNoticeArea(helper_plugin_extension_extension $extension)
    {
        $html = '';
        $missing_dependencies = $extension->getMissingDependencies();
        if (!empty($missing_dependencies)) {
            $html .= '<div class="msg error">' .
                sprintf(
                    $this->getLang('missing_dependency'),
                    '<bdi>' . implode(', ', $missing_dependencies) . '</bdi>'
                ) .
                '</div>';
        }
        if ($extension->isInWrongFolder()) {
            $html .= '<div class="msg error">' .
                sprintf(
                    $this->getLang('wrong_folder'),
                    '<bdi>' . hsc($extension->getInstallName()) . '</bdi>',
                    '<bdi>' . hsc($extension->getBase()) . '</bdi>'
                ) .
                '</div>';
        }
        if (($securityissue = $extension->getSecurityIssue()) !== false) {
            $html .= '<div class="msg error">'.
                sprintf($this->getLang('security_issue'), '<bdi>'.hsc($securityissue).'</bdi>').
                '</div>';
        }
        if (($securitywarning = $extension->getSecurityWarning()) !== false) {
            $html .= '<div class="msg notify">'.
                sprintf($this->getLang('security_warning'), '<bdi>'.hsc($securitywarning).'</bdi>').
                '</div>';
        }
        if ($extension->updateAvailable()) {
            $html .=  '<div class="msg notify">'.
                sprintf($this->getLang('update_available'), hsc($extension->getLastUpdate())).
                '</div>';
        }
        if ($extension->hasDownloadURLChanged()) {
            $html .= '<div class="msg notify">' .
                sprintf(
                    $this->getLang('url_change'),
                    '<bdi>' . hsc($extension->getDownloadURL()) . '</bdi>',
                    '<bdi>' . hsc($extension->getLastDownloadURL()) . '</bdi>'
                ) .
                '</div>';
        }
        return $html.DOKU_LF;
    }

    /**
     * Create a link from the given URL
     *
     * Shortens the URL for display
     *
     * @param string $url
     * @return string  HTML link
     */
    public function shortlink($url)
    {
        $link = parse_url($url);

        $base = $link['host'];
        if (!empty($link['port'])) $base .= $base.':'.$link['port'];
        $long = $link['path'];
        if (!empty($link['query'])) $long .= $link['query'];

        $name = shorten($base, $long, 55);

        $html = '<a href="'.hsc($url).'" class="urlextern">'.hsc($name).'</a>';
        return $html;
    }

    /**
     * Plugin/template details
     *
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The HTML code
     */
    public function makeInfo(helper_plugin_extension_extension $extension)
    {
        $default = $this->getLang('unknown');
        $html = '<dl class="details">';

        $html .= '<dt>'.$this->getLang('status').'</dt>';
        $html .= '<dd>'.$this->makeStatus($extension).'</dd>';

        if ($extension->getDonationURL()) {
            $html .= '<dt>'.$this->getLang('donate').'</dt>';
            $html .= '<dd>';
            $html .= '<a href="'.$extension->getDonationURL().'" class="donate">'.
                $this->getLang('donate_action').'</a>';
            $html .= '</dd>';
        }

        if (!$extension->isBundled()) {
            $html .= '<dt>'.$this->getLang('downloadurl').'</dt>';
            $html .= '<dd><bdi>';
            $html .= ($extension->getDownloadURL()
                ? $this->shortlink($extension->getDownloadURL())
                : $default);
            $html .= '</bdi></dd>';

            $html .= '<dt>'.$this->getLang('repository').'</dt>';
            $html .= '<dd><bdi>';
            $html .= ($extension->getSourcerepoURL()
                ? $this->shortlink($extension->getSourcerepoURL())
                : $default);
            $html .= '</bdi></dd>';
        }

        if ($extension->isInstalled()) {
            if ($extension->getInstalledVersion()) {
                $html .= '<dt>'.$this->getLang('installed_version').'</dt>';
                $html .= '<dd>';
                $html .= hsc($extension->getInstalledVersion());
                $html .= '</dd>';
            }
            if (!$extension->isBundled()) {
                $html .= '<dt>'.$this->getLang('install_date').'</dt>';
                $html .= '<dd>';
                $html .= ($extension->getUpdateDate()
                    ? hsc($extension->getUpdateDate())
                    : $this->getLang('unknown'));
                $html .= '</dd>';
            }
        }
        if (!$extension->isInstalled() || $extension->updateAvailable()) {
            $html .= '<dt>'.$this->getLang('available_version').'</dt>';
            $html .= '<dd>';
            $html .= ($extension->getLastUpdate()
                ? hsc($extension->getLastUpdate())
                : $this->getLang('unknown'));
            $html .= '</dd>';
        }

        $html .= '<dt>'.$this->getLang('provides').'</dt>';
        $html .= '<dd><bdi>';
        $html .= ($extension->getTypes()
            ? hsc(implode(', ', $extension->getTypes()))
            : $default);
        $html .= '</bdi></dd>';

        if (!$extension->isBundled() && $extension->getCompatibleVersions()) {
            $html .= '<dt>'.$this->getLang('compatible').'</dt>';
            $html .= '<dd>';
            foreach ($extension->getCompatibleVersions() as $date => $version) {
                $html .= '<bdi>'.$version['label'].' ('.$date.')</bdi>, ';
            }
            $html = rtrim($html, ', ');
            $html .= '</dd>';
        }
        if ($extension->getDependencies()) {
            $html .= '<dt>'.$this->getLang('depends').'</dt>';
            $html .= '<dd>';
            $html .= $this->makeLinkList($extension->getDependencies());
            $html .= '</dd>';
        }

        if ($extension->getSimilarExtensions()) {
            $html .= '<dt>'.$this->getLang('similar').'</dt>';
            $html .= '<dd>';
            $html .= $this->makeLinkList($extension->getSimilarExtensions());
            $html .= '</dd>';
        }

        if ($extension->getConflicts()) {
            $html .= '<dt>'.$this->getLang('conflicts').'</dt>';
            $html .= '<dd>';
            $html .= $this->makeLinkList($extension->getConflicts());
            $html .= '</dd>';
        }
        $html .= '</dl>'.DOKU_LF;
        return $html;
    }

    /**
     * Generate a list of links for extensions
     *
     * @param array $ext The extensions
     * @return string The HTML code
     */
    public function makeLinkList($ext)
    {
        $html = '';
        foreach ($ext as $link) {
            $html .= '<bdi><a href="'.
                $this->gui->tabURL('search', array('q'=>'ext:'.$link)).'">'.
                hsc($link).'</a></bdi>, ';
        }
        return rtrim($html, ', ');
    }

    /**
     * Display the action buttons if they are possible
     *
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The HTML code
     */
    public function makeActions(helper_plugin_extension_extension $extension)
    {
        global $conf;
        $html   = '';
        $errors = '';

        if ($extension->isInstalled()) {
            if (($canmod = $extension->canModify()) === true) {
                if (!$extension->isProtected()) {
                    $html .= $this->makeAction('uninstall', $extension);
                }
                if ($extension->getDownloadURL()) {
                    if ($extension->updateAvailable()) {
                        $html .= $this->makeAction('update', $extension);
                    } else {
                        $html .= $this->makeAction('reinstall', $extension);
                    }
                }
            } else {
                $errors .= '<p class="permerror">'.$this->getLang($canmod).'</p>';
            }

            if (!$extension->isProtected() && !$extension->isTemplate()) { // no enable/disable for templates
                if ($extension->isEnabled()) {
                    $html .= $this->makeAction('disable', $extension);
                } else {
                    $html .= $this->makeAction('enable', $extension);
                }
            }

            if ($extension->isGitControlled()) {
                $errors .= '<p class="permerror">'.$this->getLang('git').'</p>';
            }

            if ($extension->isEnabled() &&
                in_array('Auth', $extension->getTypes()) &&
                $conf['authtype'] != $extension->getID()
            ) {
                $errors .= '<p class="permerror">'.$this->getLang('auth').'</p>';
            }
        } else {
            if (($canmod = $extension->canModify()) === true) {
                if ($extension->getDownloadURL()) {
                    $html .= $this->makeAction('install', $extension);
                }
            } else {
                $errors .= '<div class="permerror">'.$this->getLang($canmod).'</div>';
            }
        }

        if (!$extension->isInstalled() && $extension->getDownloadURL()) {
            $html .= ' <span class="version">'.$this->getLang('available_version').' ';
            $html .= ($extension->getLastUpdate()
                    ? hsc($extension->getLastUpdate())
                    : $this->getLang('unknown')).'</span>';
        }

        return $html.' '.$errors.DOKU_LF;
    }

    /**
     * Display an action button for an extension
     *
     * @param string                            $action    The action
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The HTML code
     */
    public function makeAction($action, $extension)
    {
        $title = '';

        switch ($action) {
            case 'install':
            case 'reinstall':
                $title = 'title="'.hsc($extension->getDownloadURL()).'"';
                break;
        }

        $classes = 'button '.$action;
        $name    = 'fn['.$action.']['.hsc($extension->getID()).']';

        $html = '<button class="'.$classes.'" name="'.$name.'" type="submit" '.$title.'>'.
            $this->getLang('btn_'.$action).'</button> ';
        return $html;
    }

    /**
     * Plugin/template status
     *
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The description of all relevant statusses
     */
    public function makeStatus(helper_plugin_extension_extension $extension)
    {
        $status = array();

        if ($extension->isInstalled()) {
            $status[] = $this->getLang('status_installed');
            if ($extension->isProtected()) {
                $status[] = $this->getLang('status_protected');
            } else {
                $status[] = $extension->isEnabled()
                    ? $this->getLang('status_enabled')
                    : $this->getLang('status_disabled');
            }
        } else {
            $status[] = $this->getLang('status_not_installed');
        }
        if (!$extension->canModify()) $status[] = $this->getLang('status_unmodifiable');
        if ($extension->isBundled()) $status[] = $this->getLang('status_bundled');
        $status[] = $extension->isTemplate()
            ? $this->getLang('status_template')
            : $this->getLang('status_plugin');
        return implode(', ', $status);
    }
}
