<?php
/**
 * DokuWiki Plugin extension (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Hamann <michael@content-space.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class helper_plugin_extension_list takes care of creating a HTML list of extensions
 */
class helper_plugin_extension_list extends DokuWiki_Plugin {
    protected $form = '';
    /** @var  helper_plugin_extension_gui */
    protected $gui;

    /**
     * Constructor
     *
     * loads additional helpers
     */
    public function __construct(){
        $this->gui = plugin_load('helper', 'extension_gui');
    }

    function start_form() {
        $this->form .= '<form id="extension__list" accept-charset="utf-8" method="post" action="">';
        $hidden = array(
            'do'=>'admin',
            'page'=>'extension',
            'sectok'=>getSecurityToken()
        );
        $this->add_hidden($hidden);
        $this->form .= '<ul class="extensionList">';
    }
    /**
     * Build single row of extension table
     * @param helper_plugin_extension_extension  $extension The extension that shall be added
     * @param bool                               $showinfo  Show the info area
     */
    function add_row(helper_plugin_extension_extension $extension, $showinfo = false) {
        $this->start_row($extension);
        $this->populate_column('legend', $this->make_legend($extension, $showinfo));
        $this->populate_column('actions', $this->make_actions($extension));
        $this->end_row();
    }

    /**
     * Adds a header to the form
     *
     * @param string $id     The id of the header
     * @param string $header The content of the header
     * @param int    $level  The level of the header
     */
    function add_header($id, $header, $level = 2) {
        $this->form .='<h'.$level.' id="'.$id.'">'.hsc($header).'</h'.$level.'>';
    }

    /**
     * Adds a paragraph to the form
     *
     * @param string $data The content
     */
    function add_p($data) {
        $this->form .= '<p>'.hsc($data).'</p>';
    }

    /**
     * Add hidden fields to the form with the given data
     * @param array $array
     */
    function add_hidden(array $array) {
        $this->form .= '<div class="no">';
        foreach ($array as $key => $value) {
            $this->form .= '<input type="hidden" name="'.hsc($key).'" value="'.hsc($value).'" />';
        }
        $this->form .= '</div>';
    }

    /**
     * Add closing tags
     */
    function end_form() {
        $this->form .= '</ul>';
        $this->form .= '</form>';
    }

    /**
     * Print the form
     */
    function render() {
        echo $this->form;
    }

    /**
     * Start the HTML for the row for the extension
     *
     * @param helper_plugin_extension_extension $extension The extension
     */
    private function start_row(helper_plugin_extension_extension $extension) {
        $this->form .= '<li id="extensionplugin__'.hsc($extension->getID()).'" class="'.$this->make_class($extension).'">';
    }

    /**
     * Add a column with the given class and content
     * @param string $class The class name
     * @param string $html  The content
     */
    private function populate_column($class, $html) {
        $this->form .= '<div class="'.$class.' col">'.$html.'</div>';
    }

    /**
     * End the row
     */
    private function end_row() {
        $this->form .= '</li>'.DOKU_LF;
    }

    /**
     * Generate the link to the plugin homepage
     *
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The HTML code
     */
    function make_homepagelink(helper_plugin_extension_extension $extension) {
        $text = $this->getLang('homepage_link');
        $url = hsc($extension->getURL());
        return '<a href="'.$url.'" title="'.$url.'" class ="urlextern">'.$text.'</a> ';
    }

    /**
     * Generate the class name for the row of the extensio
     *
     * @param helper_plugin_extension_extension $extension The extension object
     * @return string The class name
     */
    function make_class(helper_plugin_extension_extension $extension) {
        $class = ($extension->isTemplate()) ? 'template' : 'plugin';
        if($extension->isInstalled()) {
            $class.=' installed';
            $class.= ($extension->isEnabled()) ? ' enabled':' disabled';
        }
        if(!$extension->canModify()) $class.= ' notselect';
        if($extension->isProtected()) $class.=  ' protected';
        //if($this->showinfo) $class.= ' showinfo';
        return $class;
    }

    /**
     * Generate a link to the author of the extension
     *
     * @param helper_plugin_extension_extension $extension The extension object
     * @return string The HTML code of the link
     */
    function make_author(helper_plugin_extension_extension $extension) {
        global $ID;

        if($extension->getAuthor()) {

            $mailid = $extension->getEmailID();
            if($mailid){
                $url = $this->gui->tabURL('search', array('q' => 'authorid:'.$mailid));
                return '<a href="'.$url.'" class="author" title="'.$this->getLang('author_hint').'" ><img src="//www.gravatar.com/avatar/'.$mailid.'?s=20&d=mm" width="20" height="20"> '.hsc($extension->getAuthor()).'</a>';

            }else{
                return '<span class="author">'.hsc($extension->getAuthor()).'</span>';
            }
        }
        return "<em class=\"author\">".$this->getLang('unknown_author')."</em>";
    }

    /**
     * Get the link and image tag for the screenshot/thumbnail
     *
     * @param helper_plugin_extension_extension $extension The extension object
     * @return string The HTML code
     */
    function make_screenshot(helper_plugin_extension_extension $extension) {
        if($extension->getScreenshotURL()) {
            $img = '<a title="'.hsc($extension->getDisplayName()).'" href="'.hsc($extension->getScreenshotURL()).'" target="_blank" class="extension_screenshot">'.
                '<img alt="'.hsc($extension->getDisplayName()).'" width="120" height="70" src="'.hsc($extension->getThumbnailURL()).'" />'.
                '</a>';
        } elseif($extension->isTemplate()) {
            $img = '<img alt="template" width="120" height="70" src="'.DOKU_BASE.'lib/plugins/extension/images/template.png" />';

        } else {
            $img = '<img alt="plugin" width="120" height="70" src="'.DOKU_BASE.'lib/plugins/extension/images/plugin.png" />';
        }
        return '<div class="screenshot" >'.$img.'<span></span></div>';
    }

    /**
     * Extension main description
     *
     * @param helper_plugin_extension_extension $extension The extension object
     * @param bool                              $showinfo  Show the info section
     * @return string The HTML code
     */
    function make_legend(helper_plugin_extension_extension $extension, $showinfo = false) {
        $return  = '<div>';
        $return .= '<h2>';
        $return .= sprintf($this->getLang('extensionby'), hsc($extension->getDisplayName()), $this->make_author($extension));
        $return .= '</h2>';

        $return .= $this->make_screenshot($extension);

        $popularity = $extension->getPopularity();
        if ($popularity !== false && !$extension->isBundled()) {
            $popularityText = sprintf($this->getLang('popularity'), $popularity);
            $return .= '<div class="popularity" title="'.$popularityText.'"><div style="width: '.($popularity * 100).'%;"><span></span></div></div>';
        }

        $return .= '<p>';
        if($extension->getDescription()) {
            $return .=  hsc($extension->getDescription()).' ';
        }
        $return .= '</p>';

        $return .= $this->make_linkbar($extension);

        if($showinfo){
            $url = $this->gui->tabURL('');
            $class = 'close';
        }else{
            $url = $this->gui->tabURL('', array('info' => $extension->getID()));
            $class = '';
        }
        $return .= '<a href="'.$url.'#extensionplugin__'.$extension->getID().'" class="info '.$class.'" data-extid="'.$extension->getID().'">'.$this->getLang('btn_info').'</a>';

        if ($showinfo) {
            $return .= $this->make_info($extension);
        }
        $return .= $this->make_noticearea($extension);
        $return .= '</div>';
        return $return;
    }

    /**
     * Generate the link bar HTML code
     *
     * @param helper_plugin_extension_extension $extension The extension instance
     * @return string The HTML code
     */
    function make_linkbar(helper_plugin_extension_extension $extension) {
        $return  = '<span class="linkbar">';
        $return .= $this->make_homepagelink($extension);
        if ($extension->getBugtrackerURL()) {
            $return .= ' <a href="'.hsc($extension->getBugtrackerURL()).'" title="'.hsc($extension->getBugtrackerURL()).'" class ="interwiki iw_dokubug">'.$this->getLang('bugs_features').'</a> ';
        }
        if($extension->getTags()){
            $first = true;
            $return .= '<span class="tags">';
            foreach ($extension->getTags() as $tag) {
                if(!$first){
                    $return .= ', ';
                }else{
                    $first = false;
                }
                $url = $this->gui->tabURL('search', array('q' => 'tag:'.$tag));
                $return .= '<a href="'.$url.'">'.hsc($tag).'</a>';
            }
            $return .= '</span>';
        }
        $return .= '</span>';
        return $return;
    }

    /**
     * Notice area
     *
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The HTML code
     */
    function make_noticearea(helper_plugin_extension_extension $extension) {
        $return = '';
        $missing_dependencies = $extension->getMissingDependencies();
        if(!empty($missing_dependencies)) {
            $return .= '<div class="msg error">'.
                sprintf($this->getLang('missing_dependency'), implode(', ', /*array_map(array($this->helper, 'make_extensionsearchlink'),*/ $missing_dependencies)).
                '</div>';
        }
        if($extension->isInWrongFolder()) {
            $return .= '<div class="msg error">'.
                sprintf($this->getLang('wrong_folder'), hsc($extension->getInstallName()), hsc($extension->getBase())).
                '</div>';
        }
        if(($securityissue = $extension->getSecurityIssue()) !== false) {
            $return .= '<div class="msg error">'.
                sprintf($this->getLang('security_issue'), hsc($securityissue )).
                '</div>';
        }
        if(($securitywarning = $extension->getSecurityWarning()) !== false) {
            $return .= '<div class="msg notify">'.
                sprintf($this->getLang('security_warning'), hsc($securitywarning)).
                '</div>';
        }
        if($extension->updateAvailable()) {
            $return .=  '<div class="msg notify">'.
                sprintf($this->getLang('update_available'), hsc($extension->getLastUpdate())).
                '</div>';
        }
        if($extension->hasDownloadURLChanged()) {
            $return .=  '<div class="msg notify">'.
                sprintf($this->getLang('url_change'), hsc($extension->getDownloadURL()), hsc($extension->getLastDownloadURL())).
                '</div>';
        }
        return $return;
    }

    /**
     * Create a link from the given URL
     *
     * Shortens the URL for display
     *
     * @param string $url
     *
     * @return string  HTML link
     */
    function shortlink($url){
        $link = parse_url($url);

        $base = $link['host'];
        if($link['port']) $base .= $base.':'.$link['port'];
        $long = $link['path'];
        if($link['query']) $long .= $link['query'];

        $name = shorten($base, $long, 55);

        return '<a href="'.hsc($url).'" class="urlextern">'.hsc($name).'</a>';
    }

    /**
     * Plugin/template details
     *
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The HTML code
     */
    function make_info(helper_plugin_extension_extension $extension) {
        $default = $this->getLang('unknown');
        $return = '<dl class="details">';

        if (!$extension->isBundled()) {
            $return .= '<dt>'.$this->getLang('downloadurl').'</dt>';
            $return .= '<dd>';
            $return .= ($extension->getDownloadURL() ? $this->shortlink($extension->getDownloadURL()) : $default);
            $return .= '</dd>';

            $return .= '<dt>'.$this->getLang('repository').'</dt>';
            $return .= '<dd>';
            $return .= ($extension->getSourcerepoURL() ? $this->shortlink($extension->getSourcerepoURL()) : $default);
            $return .= '</dd>';
        }

        if ($extension->isInstalled()) {
            if ($extension->getInstalledVersion()) {
                $return .= '<dt>'.$this->getLang('installed_version').'</dt>';
                $return .= '<dd>';
                $return .= hsc($extension->getInstalledVersion());
                $return .= '</dd>';
            } else {
                $return .= '<dt>'.$this->getLang('install_date').'</dt>';
                $return .= '<dd>';
                $return .= ($extension->getUpdateDate() ? hsc($extension->getUpdateDate()) : $this->getLang('unknown'));
                $return .= '</dd>';
            }
        }
        if (!$extension->isInstalled() || $extension->updateAvailable()) {
            $return .= '<dt>'.$this->getLang('available_version').'</dt>';
            $return .= '<dd>';
            $return .= ($extension->getLastUpdate() ? hsc($extension->getLastUpdate()) : $this->getLang('unknown'));
            $return .= '</dd>';
        }

        if($extension->getInstallDate()) {
            $return .= '<dt>'.$this->getLang('installed').'</dt>';
            $return .= '<dd>';
            $return .= hsc($extension->getInstallDate());
            $return .= '</dd>';
        }

        $return .= '<dt>'.$this->getLang('provides').'</dt>';
        $return .= '<dd>';
        $return .= ($extension->getTypes() ? hsc(implode(', ', $extension->getTypes())) : $default);
        $return .= '</dd>';

        if($extension->getCompatibleVersions()) {
            $return .= '<dt>'.$this->getLang('compatible').'</dt>';
            $return .= '<dd>';
            foreach ($extension->getCompatibleVersions() as $date => $version) {
                $return .= $version['label'].' ('.$date.'), ';
            }
            $return .= '</dd>';
        }
        if($extension->getDependencies()) {
            $return .= '<dt>'.$this->getLang('depends').'</dt>';
            $return .= '<dd>';
            $return .= $this->make_linklist($extension->getDependencies());
            $return .= '</dd>';
        }

        if($extension->getSimilarExtensions()) {
            $return .= '<dt>'.$this->getLang('similar').'</dt>';
            $return .= '<dd>';
            $return .= $this->make_linklist($extension->getSimilarExtensions());
            $return .= '</dd>';
        }

        if($extension->getConflicts()) {
            $return .= '<dt>'.$this->getLang('conflicts').'</dt>';
            $return .= '<dd>';
            $return .= $this->make_linklist($extension->getConflicts());
            $return .= '</dd>';
        }
        if ($extension->getDonationURL()) {
            $return .= '<a href="'.hsc($extension->getDonationURL()).'" class="donate" title="'.$this->getLang('donate').'"></a>';
        }
        $return .= '</dl>';
        return $return;
    }

    /**
     * Generate a list of links for extensions
     *
     * @param array $ext The extensions
     * @return string The HTML code
     */
    function make_linklist($ext) {
        $return = '';
        foreach ($ext as $link) {
            $return .= '<a href="'.$this->gui->tabURL('search', array('q'=>'ext:'.$link)).'">'.hsc($link).'</a> ';
        }
        return $return;
    }

    /**
     * Display the action buttons if they are possible
     *
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The HTML code
     */
    function make_actions(helper_plugin_extension_extension $extension) {
        $return = '';
        if (!$extension->isInstalled() && $extension->canModify() === true) {
            $return .= $this->make_action('install', $extension);
        } elseif ($extension->canModify() === true) {
            if (!$extension->isBundled()) {
                $return .= $this->make_action('uninstall', $extension);
                if ($extension->getDownloadURL()) {
                    if ($extension->updateAvailable()) {
                        $return .= $this->make_action('update', $extension);
                    } else {
                        $return .= $this->make_action('reinstall', $extension);
                    }
                }
            }
            if (!$extension->isProtected()) {
                if ($extension->isEnabled()) {
                    if(!$extension->isTemplate()){ // templates can't be disabled, only another can be enabled
                        $return .= $this->make_action('disable', $extension);
                    }
                } else {
                    $return .= $this->make_action('enable', $extension);
                }
            }
        }

        if (!$extension->isInstalled()) {
            $return .= ' <span class="version">'.$this->getLang('available_version').' ';
            $return .= ($extension->getLastUpdate() ? hsc($extension->getLastUpdate()) : $this->getLang('unknown')).'</span>';
        }

        return $return;
    }

    /**
     * Display an action button for an extension
     *
     * @param string                            $action    The action
     * @param helper_plugin_extension_extension $extension The extension
     * @return string The HTML code
     */
    function make_action($action, $extension) {
        $title = '';

        switch ($action) {
            case 'install':
            case 'reinstall':
                $title = 'title="'.$extension->getDownloadURL().'"';
                break;
        }

        $classes = 'button '.$action;
        $name    = 'fn['.$action.']['.hsc($extension->getID()).']';

        return '<input class="'.$classes.'" name="'.$name.'" type="submit" value="'.$this->getLang('btn_'.$action).'" '.$title.' />';
    }
}
