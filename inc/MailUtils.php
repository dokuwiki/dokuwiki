<?php

namespace dokuwiki;

use dokuwiki\Utf8\Conversion;

/**
 * Stateless email-address utilities: obfuscation, validation, and quoted-printable body encoding.
 */
class MailUtils
{
    /**
     * RFC 2822 atext characters (paras 3.4.1 & 3.2.4).
     *
     * NOTE: the unquoted '/' must remain unquoted to be usable as part of a
     * Lexer pattern; pick the surrounding pattern delimiters with care.
     */
    public const RFC2822_ATEXT = "0-9a-zA-Z!#$%&'*+/=?^_`{|}~-";

    /**
     * Pattern for use in email detection and validation.
     *
     * Uses non-capturing groups since the parser does not allow captures.
     *
     * The dot-separated groups are possessive: an atext run stops at the
     * next dot or the @, and a domain label always ends in a dot, so neither
     * group can over-consume and neither ever needs to backtrack. A plain
     * quantifier here is a ReDoS vector: a long `a.a.a.a…` local part or
     * `a.a.a.a…` domain makes the non-JIT PCRE engine retain one
     * backtracking frame per segment before the match ultimately fails.
     */
    public const PREG_PATTERN_VALID_EMAIL =
        '[' . self::RFC2822_ATEXT . ']+(?:\.[' . self::RFC2822_ATEXT . ']+)*+'
        . '@(?i:[0-9a-z][0-9a-z-]*\.)++(?i:[a-z]{2,63})';

    // region email-address obfuscation

    /**
     * Return an obfuscated email address suitable for HTML text content
     * (link labels, titles).
     *
     * The caller MUST pass a raw, unescaped string; the result is
     * HTML-text-safe. Any query string after the first '?' is preserved
     * verbatim and is never run through the [at]/[dot]/[dash] substitution,
     * so dots and dashes inside body/subject values stay intact.
     *
     * @param string $email raw email address, optionally followed by ?query
     * @return string HTML-text-safe representation
     */
    public static function obfuscate(string $email): string
    {
        global $conf;

        [$addr, $query] = sexplode('?', $email, 2);
        $out = self::obfuscateAddress($addr);
        // 'hex' output is already pure ASCII numeric entities → HTML-safe.
        // For 'none'/'visible' the address half still needs HTML escaping.
        if ($conf['mailguard'] !== 'hex') {
            $out = htmlspecialchars($out, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        if ($query !== null) {
            $out .= '?' . htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        return $out;
    }

    /**
     * Return an obfuscated email address suitable for use as a mailto: href
     * value (HTML attribute context).
     *
     * Like obfuscate() but for HTML attribute context. The caller MUST pass a
     * raw, unescaped string. The address half is obfuscated per the mailguard
     * setting; in 'visible' mode the address (with its [at]/[dot] spaces) is
     * percent-encoded so the URL is well-formed. The query string is
     * preserved verbatim with only HTML-attribute escaping applied, so mail
     * clients receive correct subject/body separators.
     *
     * @param string $email raw email address, optionally followed by ?query
     * @return string HTML-attribute-safe URL fragment (without 'mailto:' prefix)
     */
    public static function obfuscateUrl(string $email): string
    {
        global $conf;

        [$addr, $query] = sexplode('?', $email, 2);
        $addr = self::obfuscateAddress($addr);
        if ($conf['mailguard'] === 'visible') {
            $addr = rawurlencode($addr);
        }
        if ($conf['mailguard'] !== 'hex') {
            $addr = htmlspecialchars($addr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        if ($query !== null) {
            $addr .= '?' . htmlspecialchars($query, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        return $addr;
    }

    /**
     * Apply the configured mailguard mode to the address half of a mailto
     * target. Returns hex-mode output as numeric entities (HTML-safe);
     * visible/none modes return raw text that still needs HTML escaping.
     *
     * @param string $addr raw local@domain
     * @return string
     */
    protected static function obfuscateAddress(string $addr): string
    {
        global $conf;

        return match ($conf['mailguard']) {
            'visible' => strtr($addr, ['@' => ' [at] ', '.' => ' [dot] ', '-' => ' [dash] ']),
            'hex' => Conversion::toHtml($addr, true),
            default => $addr,
        };
    }

    // endregion
    // region outgoing-mail helpers

    /**
     * Check if a given mail address is valid.
     *
     * @param string $email the address to check
     * @return bool true if address is valid
     */
    public static function isValid(string $email): bool
    {
        return \EmailAddressValidator::checkEmailAddress($email, true);
    }

    /**
     * RFC 2045 quoted-printable encoding.
     *
     * @param string $sText
     * @param int $maxlen
     * @param bool $bEmulate_imap_8bit
     * @return string
     * @author umu <umuAThrz.tu-chemnitz.de>
     * @link   http://php.net/manual/en/function.imap-8bit.php#61216
     *
     */
    public static function quotedPrintableEncode(
        string $sText,
        int $maxlen = 74,
        bool $bEmulate_imap_8bit = true
    ): string {
        // split text into lines
        $aLines = preg_split("/(?:\r\n|\r|\n)/", $sText);
        $cnt = count($aLines);

        for ($i = 0; $i < $cnt; $i++) {
            $sLine =& $aLines[$i];
            if ($sLine === '') continue; // do nothing, if empty

            $sRegExp = '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';

            // imap_8bit encodes x09 everywhere, not only at lineends,
            // for EBCDIC safeness encode !"#$@[\]^`{|}~,
            // for complete safeness encode every character :)
            if ($bEmulate_imap_8bit)
                $sRegExp = '/[^\x20\x21-\x3C\x3E-\x7E]/';

            $sLine = preg_replace_callback(
                $sRegExp,
                static fn(array $matches): string => sprintf("=%02X", ord($matches[0])),
                $sLine
            );

            // encode x09,x20 at lineends
            $iLength = strlen($sLine);
            $iLastChar = ord($sLine[$iLength - 1]);

            // imap_8_bit does not encode x20 at the very end of a text,
            // here is, where I don't agree with imap_8_bit,
            // please correct me, if I'm wrong,
            // or comment next line for RFC2045 conformance, if you like
            if (!($bEmulate_imap_8bit && ($i == count($aLines) - 1))) {
                if (($iLastChar == 0x09) || ($iLastChar == 0x20)) {
                    $sLine[$iLength - 1] = '=';
                    $sLine .= ($iLastChar == 0x09) ? '09' : '20';
                }
            }

            // imap_8bit encodes x20 before chr(13), too
            // although IMHO not requested by RFC2045, why not do it safer :)
            // and why not encode any x20 around chr(10) or chr(13)
            if ($bEmulate_imap_8bit) {
                $sLine = str_replace(' =0D', '=20=0D', $sLine);
                //$sLine=str_replace(' =0A','=20=0A',$sLine);
                //$sLine=str_replace('=0D ','=0D=20',$sLine);
                //$sLine=str_replace('=0A ','=0A=20',$sLine);
            }

            // finally split into softlines no longer than $maxlen chars,
            // for even more safeness one could encode x09,x20
            // at the very first character of the line
            // and after soft linebreaks, as well,
            // but this wouldn't be caught by such an easy RegExp
            if ($maxlen) {
                preg_match_all('/.{1,' . ($maxlen - 2) . '}([^=]{0,2})?/', $sLine, $aMatch);
                $sLine = implode('=' . MAILHEADER_EOL, $aMatch[0]); // add soft crlf's
            }
        }

        // join lines into text
        return implode(MAILHEADER_EOL, $aLines);
    }

    // endregion
}
