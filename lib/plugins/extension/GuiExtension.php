<?php

namespace dokuwiki\plugin\extension;

class GuiExtension extends Gui
{
    public const THUMB_WIDTH = 120;
    public const THUMB_HEIGHT = 70;


    protected Extension $extension;

    public function __construct(Extension $extension)
    {
        parent::__construct();
        $this->extension = $extension;
    }


    public function render()
    {

        $classes = $this->getClasses();

        $html = "<section class=\"$classes\" data-ext=\"{$this->extension->getId()}\">";

        $html .= '<div class="screenshot">';
        $html .= $this->thumbnail();
        $html .= '<span class="id" title="' . hsc($this->extension->getBase()) . '">' .
            hsc($this->extension->getBase()) . '</span>';
        $html .= $this->popularity();
        $html .= '</div>';

        $html .= '<div class="main">';
        $html .= $this->main();
        $html .= '</div>';

        $html .= '<div class="notices">';
        $html .= $this->notices();
        $html .= '</div>';

        $html .= '<div class="details">';
        $html .= $this->details();
        $html .= '</div>';

        $html .= '<div class="actions">';
        // show the available update if there is one
        if ($this->extension->isUpdateAvailable()) {
            $html .= ' <div class="available">' . $this->getLang('available_version') . ' ' .
                '<span class="version">' . hsc($this->extension->getLastUpdate()) . '</span></div>';
        }

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
            'class' => 'shot',
            'loading' => 'lazy',
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
    protected function main()
    {
        $html = '';
        $html .= '<h2>';
        $html .= '<div>';
        $html .= sprintf($this->getLang('extensionby'), hsc($this->extension->getDisplayName()), $this->author());
        $html .= '</div>';

        $html .= '<div class="version">';
        if ($this->extension->isBundled()) {
            $html .= hsc('<' . $this->getLang('status_bundled') . '>');
        } elseif ($this->extension->getInstalledVersion()) {
            $html .= hsc($this->extension->getInstalledVersion());
        }
        $html .= '</div>';
        $html .= '</h2>';

        $html .= '<p>' . hsc($this->extension->getDescription()) . '</p>';
        $html .= $this->mainLinks();

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

        $html = '<ul>';
        foreach ($notices as $type => $messages) {
            foreach ($messages as $message) {
                $message = hsc($message);
                $message = nl2br($message);
                $message = preg_replace('/`([^`]+)`/', '<bdi>$1</bdi>', $message);
                $message = sprintf(
                    '<span class="icon">%s</span><span>%s</span>',
                    inlineSVG(Notice::icon($type)),
                    $message
                );
                $html .= '<li class="' . $type . '"><div class="li">' . $message . '</div></li>';
            }
        }
        $html .= '</ul>';
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
        $html .= '<summary>' . $this->getLang('details') . '</summary>';


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
                    $installDate = $this->extension->getManager()->getInstallDate();
                    $list['installed'] = $installDate ? dformat($installDate->getTimestamp()) : $default;

                    $updateDate = $this->extension->getManager()->getLastUpdate();
                    $list['install_date'] = $updateDate ? dformat($updateDate->getTimestamp()) : $default;
                }
            }
        }

        if (!$this->extension->isInstalled() || $this->extension->isUpdateAvailable()) {
            $list['available_version'] = $this->extension->getLastUpdate()
                ? hsc($this->extension->getLastUpdate())
                : $default;
        }


        if (!$this->extension->isBundled() && $this->extension->getCompatibleVersions()) {
            $list['compatible'] = implode(', ', array_map(
                static fn($date, $version) => '<bdi>' . $version['label'] . ' (' . $date . ')</bdi>',
                array_keys($this->extension->getCompatibleVersions()),
                array_values($this->extension->getCompatibleVersions())
            ));
        }

        $list['provides'] = implode(', ', array_map('hsc', $this->extension->getComponentTypes()));

        $tags = $this->extension->getTags();
        if ($tags) {
            $list['tags'] = implode(', ', array_map(function ($tag) {
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
            $html .= '<dt>' . rtrim($this->getLang($key), ':') . '</dt>';
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

        $names = explode(',', $this->extension->getAuthor());
        $names = array_map('trim', $names);
        if (count($names) > 2) {
            $names = array_slice($names, 0, 2);
            $names[] = '…';
        }
        $name = implode(', ', $names);

        $mailid = $this->extension->getEmailID();
        if ($mailid) {
            $url = $this->tabURL('search', ['q' => 'authorid:' . $mailid]);
            $html = '<a href="' . $url . '" class="author" title="' . $this->getLang('author_hint') . '" >' .
                '<img src="//www.gravatar.com/avatar/' . $mailid .
                '?s=60&amp;d=mm" width="20" height="20" alt="" /> ' .
                hsc($name) . '</a>';
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

        $popimg = '<img src="' . DOKU_BASE . 'lib/plugins/extension/images/fire.svg" alt="🔥" />';

        if ($popularity > 0.25) {
            $title = $this->getLang('popularity_high');
            $emoji = str_repeat($popimg, 3);
        } elseif ($popularity > 0.15) {
            $title = $this->getLang('popularity_medium');
            $emoji = str_repeat($popimg, 2);
        } elseif ($popularity > 0.05) {
            $title = $this->getLang('popularity_low');
            $emoji = str_repeat($popimg, 1);
        } else {
            return '';
        }
        $title .= ' (' . round($popularity * 100) . '%)';

        return '<span class="popularity" title="' . $title . '">' . $emoji . '</span>';
    }

    /**
     * Generate the action buttons
     *
     * @return string
     */
    protected function actions()
    {
        $html = '';
        $actions = [];

        // check permissions
        try {
            Installer::ensurePermissions($this->extension);
        } catch (\Exception $e) {
            return '';
        }

        // gather available actions
        if ($this->extension->isInstalled()) {
            if (!$this->extension->isProtected()) $actions[] = 'uninstall';
            if ($this->extension->getDownloadURL()) {
                $actions[] = $this->extension->isUpdateAvailable() ? 'update' : 'reinstall';
            }
            // no enable/disable for templates
            if (!$this->extension->isProtected() && !$this->extension->isTemplate()) {
                $actions[] = $this->extension->isEnabled() ? 'disable' : 'enable';
            }
        } elseif ($this->extension->getDownloadURL()) {
            $actions[] = 'install';
        }

        // output the buttons
        foreach ($actions as $action) {
            $attr = [
                'class' => 'button ' . $action,
                'type' => 'submit',
                'name' => 'fn[' . $action . '][' . $this->extension->getID() . ']',
            ];
            $html .= '<button ' . buildAttributes($attr) . '>' . $this->getLang('btn_' . $action) . '</button>';
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
     * @param string $fallback If URL is empty return this fallback (raw HTML)
     * @return string  HTML link
     */
    protected function shortlink($url, $class, $fallback = '')
    {
        if (!$url) return $fallback;

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
