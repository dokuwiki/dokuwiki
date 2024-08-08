<?php

namespace dokuwiki\plugin\extension;

class GuiExtension extends Gui
{
    const THUMB_WIDTH = 120;
    const THUMB_HEIGHT = 70;


    protected Extension $extension;

    public function __construct(Extension $extension)
    {
        parent::__construct();
        $this->extension = $extension;
    }


    public function render()
    {

        $classes = $this->getClasses();

        $html = "<section class=\"$classes\">";

        $html .= '<div class="screenshot">';
        $html .= $this->thumbnail();
        $html .= '</div>';

        $html.= '<h2>';
        $html .= '<bdi>' . hsc($this->extension->getDisplayName()) . '</bdi>';
        if ($this->extension->isBundled()) {
            $html .= ' <span class="version">' . hsc('<' . $this->getLang('status_bundled') . '>') . '</span>';
        } elseif ($this->extension->getInstalledVersion()) {
            $html .= ' <span class="version">' . hsc($this->extension->getInstalledVersion()) . '</span>';
        }
        $html .= $this->popularity();
        $html .= '</h2>';

        $html .= '<div class="main">';
        $html .= '<h3>' . $this->author() . '</h3>';
        $html .= '<p>' . hsc($this->extension->getDescription()) . '</p>';
        $html .= '</div>';




        $html .= '<div class="details">';
        $html .= $this->notices();
        $html .= $this->mainLinks();
        $html .= $this->details();
        $html .= '</div>';

        // show the available version if there is one
        if ($this->extension->getDownloadURL() && $this->extension->getLastUpdate()) {
            $html .= ' <div class="version">' . $this->getLang('available_version') . ' ' .
                hsc($this->extension->getLastUpdate()) . '</div>';
        }

        $html .= '<div class="actions">';
        $html .= $this->actions();
        $html .= '</div>';


        $html .= '</section>';

        return $html;
    }

    // region sections

    /**
     * Get the link and image tag for the screenshot/thumbnail
     *
     * @return string The HTML code
     */
    protected function thumbnail()
    {
        $screen = $this->extension->getScreenshotURL();
        $thumb = $this->extension->getThumbnailURL();

        $link = [];
        $img = [
            'width' => self::THUMB_WIDTH,
            'height' => self::THUMB_HEIGHT,
            'alt' => '',
        ];

        if ($screen) {
            $link = [
                'href' => $screen,
                'target' => '_blank',
                'class' => 'extension_screenshot',
                'title' => sprintf($this->getLang('screenshot'), $this->extension->getDisplayName())
            ];

            $img['src'] = $thumb;
            $img['alt'] = $link['title'];
        } elseif ($this->extension->isTemplate()) {
            $img['src'] = DOKU_BASE . 'lib/plugins/extension/images/template.png';
        } else {
            $img['src'] = DOKU_BASE . 'lib/plugins/extension/images/plugin.png';
        }

        $html = '';
        if ($link) $html .= '<a ' . buildAttributes($link) . '>';
        $html .= '<img ' . buildAttributes($img) . ' />';
        if ($link) $html .= '</a>';

        return $html;

    }

    /**
     * The main information about the extension
     *
     * @return string
     */
    protected function info()
    {



        return $html;
    }

    /**
     * Display the available notices for the extension
     *
     * @return string
     */
    protected function notices()
    {
        $notices = Notice::list($this->extension);

        $html = '';
        foreach ($notices as $type => $messages) {
            foreach ($messages as $message) {
                $message = hsc($message);
                $message = preg_replace('/`([^`]+)`/', '<bdi>$1</bdi>', $message);
                $html .= '<div class="msg ' . $type . '">' . $message . '</div>';
            }
        }
        return $html;
    }

    /**
     * Generate the link bar HTML code
     *
     * @return string The HTML code
     */
    public function mainLinks()
    {
        $html = '<div class="linkbar">';


        $homepage = $this->extension->getURL();
        if ($homepage) {
            $params = $this->prepareLinkAttributes($homepage, 'homepage');
            $html .= ' <a ' . buildAttributes($params, true) . '>' . $this->getLang('homepage_link') . '</a>';
        }

        $bugtracker = $this->extension->getBugtrackerURL();
        if ($bugtracker) {
            $params = $this->prepareLinkAttributes($bugtracker, 'bugs');
            $html .= ' <a ' . buildAttributes($params, true) . '>' . $this->getLang('bugs_features') . '</a>';
        }

        if ($this->extension->getDonationURL()) {
            $params = $this->prepareLinkAttributes($this->extension->getDonationURL(), 'donate');
            $html .= ' <a ' . buildAttributes($params, true) . '>' . $this->getLang('donate_action') . '</a>';
        }


        $html .= '</div>';

        return $html;
    }

    /**
     * Create the details section
     *
     * @return string
     */
    protected function details()
    {
        $html = '<details>';
        $html .= '<summary>' . 'FIXME label' . '</summary>';


        $default = $this->getLang('unknown');
        $list = [];

        if (!$this->extension->isBundled()) {
            $list['downloadurl'] = $this->shortlink($this->extension->getDownloadURL(), 'download', $default);
            $list['repository'] = $this->shortlink($this->extension->getSourcerepoURL(), 'repo', $default);
        }

        if ($this->extension->isInstalled()) {
            if ($this->extension->isBundled()) {
                $list['installed_version'] = $this->getLang('status_bundled');
            } else {
                if ($this->extension->getInstalledVersion()) {
                    $list['installed_version'] = hsc($this->extension->getInstalledVersion());
                }
                if (!$this->extension->isBundled()) {
                    $updateDate = $this->extension->getManager()->getLastUpdate();
                    $list['install_date'] = $updateDate ? hsc($updateDate) : $default;
                }
            }
        }

        if (!$this->extension->isInstalled() || $this->extension->isUpdateAvailable()) {
            $list['available_version'] = $this->extension->getLastUpdate()
                ? hsc($this->extension->getLastUpdate())
                : $default;
        }


        if (!$this->extension->isBundled() && $this->extension->getCompatibleVersions()) {
            $list['compatible'] = join(', ', array_map(
                function ($date, $version) {
                    return '<bdi>' . $version['label'] . ' (' . $date . ')</bdi>';
                },
                array_keys($this->extension->getCompatibleVersions()),
                array_values($this->extension->getCompatibleVersions())
            ));
        }

        $tags = $this->extension->getTags();
        if ($tags) {
            $list['tags'] = join(', ', array_map(function ($tag) {
                $url = $this->tabURL('search', ['q' => 'tag:' . $tag]);
                return '<bdi><a href="' . $url . '">' . hsc($tag) . '</a></bdi>';
            }, $tags));
        }

        if ($this->extension->getDependencyList()) {
            $list['depends'] = $this->linkExtensions($this->extension->getDependencyList());
        }

        if ($this->extension->getSimilarList()) {
            $list['similar'] = $this->linkExtensions($this->extension->getSimilarList());
        }

        if ($this->extension->getConflictList()) {
            $list['conflicts'] = $this->linkExtensions($this->extension->getConflictList());
        }

        $html .= '<dl>';
        foreach ($list as $key => $value) {
            $html .= '<dt>' . $this->getLang($key) . '</dt>';
            $html .= '<dd>' . $value . '</dd>';
        }
        $html .= '</dl>';

        $html .= '</details>';
        return $html;
    }

    /**
     * Generate a link to the author of the extension
     *
     * @return string The HTML code of the link
     */
    protected function author()
    {
        if (!$this->extension->getAuthor()) {
            return '<em class="author">' . $this->getLang('unknown_author') . '</em>';
        }

        $mailid = $this->extension->getEmailID();
        if ($mailid) {
            $url = $this->tabURL('search', ['q' => 'authorid:' . $mailid]);
            $html = '<a href="' . $url . '" class="author" title="' . $this->getLang('author_hint') . '" >' .
                '<img src="//www.gravatar.com/avatar/' . $mailid .
                '?s=60&amp;d=mm" width="20" height="20" alt="" /> ' .
                hsc($this->extension->getAuthor()) . '</a>';
        } else {
            $html = '<span class="author">' . hsc($this->extension->getAuthor()) . '</span>';
        }
        return '<bdi>' . $html . '</bdi>';
    }

    /**
     * The popularity bar
     *
     * @return string
     */
    protected function popularity()
    {
        $popularity = $this->extension->getPopularity();
        if (!$popularity) return '';
        if ($this->extension->isBundled()) return '';

        $popularityText = sprintf($this->getLang('popularity'), round($popularity * 100, 2));
        return '<div class="popularity" title="' . $popularityText . '">' .
            '<div style="width: ' . ($popularity * 100) . '%;">' .
            '<span class="a11y">' . $popularityText . '</span>' .
            '</div></div>';

    }

    protected function actions()
    {
        global $conf;

        $html = '';
        $actions = [];
        $errors = [];

        // gather available actions and possible errors to show
        try {
            Installer::ensurePermissions($this->extension);

            if ($this->extension->isInstalled()) {

                if (!$this->extension->isProtected()) $actions[] = 'uninstall';
                if ($this->extension->getDownloadURL()) {
                    $actions[] = $this->extension->isUpdateAvailable() ? 'update' : 'reinstall';

                    if ($this->extension->isGitControlled()) {
                        $errors[] = $this->getLang('git');
                    }
                }

                if (!$this->extension->isProtected() && !$this->extension->isTemplate()) { // no enable/disable for templates
                    $actions[] = $this->extension->isEnabled() ? 'disable' : 'enable';

                    if (
                        $this->extension->isEnabled() &&
                        in_array('Auth', $this->extension->getComponentTypes()) &&
                        $conf['authtype'] != $this->extension->getID()
                    ) {
                        $errors[] = $this->getLang('auth');
                    }
                }
            } else {
                if ($this->extension->getDownloadURL()) {
                    $actions[] = 'install';
                }
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        foreach ($actions as $action) {
            $html .= '<button name="fn[' . $action . '][' . $this->extension->getID() . ']" class="button" type="submit">' .
                $this->getLang('btn_' . $action) . '</button>';
        }

        foreach ($errors as $error) {
            $html .= '<div class="msg error">' . hsc($error) . '</div>';
        }

        return $html;
    }


    // endregion
    // region utility functions

    /**
     * Create the classes representing the state of the extension
     *
     * @return string
     */
    protected function getClasses()
    {
        $classes = ['extension', $this->extension->getType()];
        if ($this->extension->isInstalled()) $classes[] = 'installed';
        if ($this->extension->isUpdateAvailable()) $classes[] = 'update';
        $classes[] = $this->extension->isEnabled() ? 'enabled' : 'disabled';
        return implode(' ', $classes);
    }

    /**
     * Create an attributes array for a link
     *
     * Handles interwiki links to dokuwiki.org
     *
     * @param string $url The URL to link to
     * @param string $class Additional classes to add
     * @return array
     */
    protected function prepareLinkAttributes($url, $class)
    {
        global $conf;

        $attributes = [
            'href' => $url,
            'class' => 'urlextern',
            'target' => $conf['target']['extern'],
            'rel' => 'noopener',
            'title' => $url,
        ];

        if ($conf['relnofollow']) {
            $attributes['rel'] .= ' ugc nofollow';
        }

        if (preg_match('/^https?:\/\/(www\.)?dokuwiki\.org\//i', $url)) {
            $attributes['class'] = 'interwiki iw_doku';
            $attributes['target'] = $conf['target']['interwiki'];
            $attributes['rel'] = '';
        }

        $attributes['class'] .= ' ' . $class;
        return $attributes;
    }

    /**
     * Create a link from the given URL
     *
     * Shortens the URL for display
     *
     * @param string $url
     * @param string $class Additional classes to add
     * @param string $fallback If URL is empty return this fallback
     * @return string  HTML link
     */
    protected function shortlink($url, $class, $fallback = '')
    {
        if (!$url) return hsc($fallback);

        $link = parse_url($url);
        $base = $link['host'];
        if (!empty($link['port'])) $base .= $base . ':' . $link['port'];
        $long = $link['path'];
        if (!empty($link['query'])) $long .= $link['query'];

        $name = shorten($base, $long, 55);

        $params = $this->prepareLinkAttributes($url, $class);
        $html = '<a ' . buildAttributes($params, true) . '>' . hsc($name) . '</a>';
        return $html;
    }

    /**
     * Generate a list of links for extensions
     *
     * Links to the search tab with the extension name
     *
     * @param array $extensions The extension names
     * @return string The HTML code
     */
    public function linkExtensions($extensions)
    {
        $html = '';
        foreach ($extensions as $link) {
            $html .= '<bdi><a href="' .
                $this->tabURL('search', ['q' => 'ext:' . $link]) . '">' .
                hsc($link) . '</a></bdi>, ';
        }
        return rtrim($html, ', ');
    }

    // endregion
}
