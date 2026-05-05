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
    public function table_open($maxcols = null, $numrows = null, $pos = null, $classes = null)
    {
        // Production DW wraps `<table>` in `<div class="table"><table class="inline">`;
        // the spec expects bare `<table>`.
        $this->doc .= "<table>\n";
    }

    public function table_close($pos = null)
    {
        // Drop the matching `</div>` from the production wrapper.
        $this->doc .= "</table>";
    }

    public function tablerow_open($classes = null)
    {
        // Strip DW's `class="rowN"` row counter — spec rows have no class.
        $this->doc .= "<tr>\n";
    }

    public function tableheader_open($colspan = 1, $align = null, $rowspan = 1, $classes = null)
    {
        // Production DW emits alignment as `class="...align"`; the spec uses
        // an `align="..."` attribute. Drop the `class="colN"` counter too.
        $this->doc .= '<th' . $this->alignAttr($align) . '>';
    }

    public function tablecell_open($colspan = 1, $align = null, $rowspan = 1, $classes = null)
    {
        $this->doc .= '<td' . $this->alignAttr($align) . '>';
    }

    private function alignAttr(?string $align): string
    {
        if ($align === null) return '';
        return ' align="' . $align . '"';
    }

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

    public function linebreak()
    {
        // Production DW emits `<br/>` (no space); the spec expects the
        // XHTML-classic `<br />` (space before the slash).
        $this->doc .= '<br />' . DOKU_LF;
    }

    public function entity($entity)
    {
        // The Entity mode rewrites --, ---, ->, (c), ... and other prose
        // abbreviations into typographic glyphs via conf/entities.conf.
        // Correct for live wiki pages, diverges byte-for-byte from the
        // GFM spec corpus which expects those bytes preserved literally.
        // Emit the original match unchanged.
        $this->doc .= $this->_xmlEntities((string) $entity);
    }

    public function _xmlEntities($string)
    {
        // Production hsc() escapes both `"` and `'` (ENT_QUOTES) so cdata
        // is safe to splice into any HTML attribute as well as body text.
        // CommonMark / GFM spec output uses a narrower body-text policy:
        // `"` is escaped to `&quot;` (e.g. example #323) but `'` is left
        // literal (e.g. example #670). ENT_COMPAT matches that exactly.
        // Attribute values rendered by SpecCompatRenderer (href, src, alt)
        // still go through hsc() in specLink / specImg, which escapes both.
        return htmlspecialchars(
            (string) $string,
            ENT_COMPAT | ENT_SUBSTITUTE | ENT_HTML401,
            'UTF-8'
        );
    }

    public function quote_open()
    {
        // Production DW wraps blockquote content in `<div class="no">`;
        // the spec expects bare `<blockquote>...</blockquote>`.
        $this->doc .= "<blockquote>\n";
    }

    public function quote_close()
    {
        $this->doc .= "</blockquote>\n";
    }

    public function listu_open($classes = null)
    {
        $this->doc .= "<ul>\n";
    }

    public function listu_close()
    {
        $this->doc .= "</ul>\n";
    }

    public function listo_open($classes = null)
    {
        $this->doc .= "<ol>\n";
    }

    public function listo_open_start($start = 1)
    {
        $start = (int) $start;
        if ($start === 1) {
            $this->listo_open();
            return;
        }
        $this->doc .= '<ol start="' . $start . "\">\n";
    }

    public function listo_close()
    {
        $this->doc .= "</ol>\n";
    }

    public function listitem_open($level, $node = false)
    {
        $this->doc .= '<li>';
    }

    public function listitem_close()
    {
        $this->doc .= "</li>\n";
    }

    public function listcontent_open()
    {
        // GFM has no per-item content wrapper - tight items put text directly
        // inside <li>, loose items wrap it in <p>. The handler emits/strips
        // p_open / p_close to drive that distinction; the wrapper itself
        // produces no output here.
    }

    public function listcontent_close()
    {
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
        $href = $this->specEncodeUrl((string) $href);
        if (is_array($label) && isset($label['type'])) {
            $img = $this->specImg(
                $label['src'],
                $label['title'],
                $label['width'] ?? null,
                $label['height'] ?? null
            );
            return '<a href="' . hsc($href) . '">' . $img . '</a>';
        }
        $text = ($label === null || $label === '') ? $href : $label;
        return '<a href="' . hsc($href) . '">' . hsc((string) $text) . '</a>';
    }

    /**
     * Percent-encode characters not in CommonMark's URL-safe set,
     * preserving existing %XX sequences. Matches what cmark-gfm's
     * reference renderer does for the spec corpus: UTF-8 bytes and
     * non-URL-safe ASCII (e.g. `\`, space) become %XX; alphanumerics,
     * RFC 3986 unreserved/reserved, and `%` itself pass through.
     */
    private function specEncodeUrl(string $url): string
    {
        return preg_replace_callback(
            "/[^A-Za-z0-9\\-._~:\\/?#\\[\\]@!$&'()*+,;=%]/",
            static fn($m) => '%' . strtoupper(bin2hex($m[0])),
            $url
        );
    }
}
