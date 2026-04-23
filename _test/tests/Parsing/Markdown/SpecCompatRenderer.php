<?php

namespace dokuwiki\test\Parsing\Markdown;

use Doku_Renderer_xhtml;

/**
 * XHTML renderer tuned to emit the minimal HTML shape GFM's spec.txt uses.
 *
 * DokuWiki's production XHTML renderer wraps internal media in details
 * links pointing at `/lib/exe/fetch.php?media=...` / `/lib/exe/detail.php?media=...`,
 * rewrites internal link hrefs to `/doku.php?id=...`, and adds wiki-specific
 * classes and attributes. All of this is correct for live wiki pages but
 * diverges byte-for-byte from GFM's bare `<img src="...">` and
 * `<a href="...">...</a>`.
 *
 * This renderer is used only by {@see GfmSpecTest} so the spec roundtrip
 * can compare against byte-level spec HTML. Production rendering is
 * unchanged. Methods not overridden here fall through to the XHTML
 * renderer (paragraphs, emphasis, code spans, lists, etc.) — those render
 * the same shape the spec expects.
 *
 * Note: title attributes on links/images are discarded at handle time
 * (no DW instruction slot), so spec examples that expect `title="..."`
 * still don't pass and stay in `skip.php`.
 */
class SpecCompatRenderer extends Doku_Renderer_xhtml
{

    public function internalmedia(
        $src,
        $title = null,
        $align = null,
        $width = null,
        $height = null,
        $cache = null,
        $linking = null,
        $return = false
    ) {
        $this->doc .= $this->specImg($src, $title, $width, $height);
    }

    public function externalmedia(
        $src,
        $title = null,
        $align = null,
        $width = null,
        $height = null,
        $cache = null,
        $linking = null,
        $return = false
    ) {
        $this->doc .= $this->specImg($src, $title, $width, $height);
    }

    public function internallink($id, $name = null, $search = null, $returnonly = false, $linktype = 'content')
    {
        $this->doc .= $this->specLink($id, $name);
    }

    public function externallink($url, $name = null, $returnonly = false)
    {
        $this->doc .= $this->specLink($url, $name);
    }

    public function interwikilink($match, $name, $wikiName, $wikiUri, $returnonly = false)
    {
        // Spec has no interwiki expectations; emit the raw `wp>Page` form as
        // href so the mode is still visible but obviously non-standard.
        $this->doc .= $this->specLink($match, $name);
    }

    public function emaillink($address, $name = null, $returnonly = false)
    {
        $this->doc .= $this->specLink('mailto:' . $address, $name ?? $address);
    }

    public function locallink($hash, $name = null, $returnonly = false)
    {
        $this->doc .= $this->specLink('#' . $hash, $name ?? $hash);
    }

    public function windowssharelink($url, $name = null, $returnonly = false)
    {
        $this->doc .= $this->specLink($url, $name);
    }

    public function code($text, $language = null, $filename = null, $options = null)
    {
        $this->doc .= $this->specCode($text, $language);
    }

    public function file($text, $language = null, $filename = null, $options = null)
    {
        $this->doc .= $this->specCode($text, $language);
    }

    public function preformatted($text)
    {
        // The Preformatted CallWriter rewriter collapses start/content/
        // newline/end into one `preformatted` call. GFM expects the body
        // to end with a newline (spec example 104); DW's internal text
        // loses it to `trim()`, so we re-append here.
        $this->doc .= $this->specCode($text . "\n", null);
    }

    /**
     * GFM shape: <pre><code class="language-xxx">...</code></pre>. The
     * production DW renderer emits <pre class="code"> with no inner
     * <code>, which diverges byte-for-byte.
     */
    private function specCode($text, $language): string
    {
        $classAttr = '';
        if ($language !== null && $language !== '') {
            $classAttr = ' class="language-' . hsc((string) $language) . '"';
        }
        return '<pre><code' . $classAttr . '>' . hsc((string) $text) . '</code></pre>';
    }

    private function specImg($src, $alt, $width, $height): string
    {
        $out = '<img src="' . hsc((string) $src) . '"';
        $out .= ' alt="' . hsc((string) $alt) . '"';
        if ($width !== null)  $out .= ' width="' . (int) $width . '"';
        if ($height !== null) $out .= ' height="' . (int) $height . '"';
        $out .= ' />';
        return $out;
    }

    /**
     * Emit a bare <a href="...">label</a>. If the label is a media
     * descriptor array (the shape Media::parseMedia() returns, passed by
     * Internallink / GfmLink when the label is `{{img}}` / `![alt](img)`),
     * render the <img> inside the <a>.
     */
    private function specLink($href, $label): string
    {
        if (is_array($label) && isset($label['type'])) {
            $img = $this->specImg(
                $label['src'],
                $label['title'],
                $label['width'] ?? null,
                $label['height'] ?? null
            );
            return '<a href="' . hsc((string) $href) . '">' . $img . '</a>';
        }
        $text = ($label === null || $label === '') ? $href : $label;
        return '<a href="' . hsc((string) $href) . '">' . hsc((string) $text) . '</a>';
    }
}
