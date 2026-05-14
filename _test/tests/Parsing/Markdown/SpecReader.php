<?php

namespace dokuwiki\test\Parsing\Markdown;

/**
 * Parses cmark-gfm's `test/spec.txt` fenced-example format.
 *
 * Each example is a block like:
 *
 *     ```````````````` example [optional label]
 *     markdown input
 *     .
 *     expected html output
 *     ````````````````
 *
 * Fences are 10+ backticks; the opening line includes the word `example`,
 * optionally followed by a whitespace-separated label (used by the GFM
 * extensions, e.g. `example table`, `example disallowed_raw_html`).
 * A single `.` on its own line separates markdown from HTML.
 *
 * Examples are numbered sequentially from 1 in document order — the same
 * numbers shown in the rendered spec ("Example 42").
 *
 * The most recent `## Heading` line is carried as section context for each
 * example, to make test names informative.
 */
class SpecReader
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Yield one record per example found in the spec file.
     *
     * @return iterable<array{
     *     number: int,
     *     section: string,
     *     extension: ?string,
     *     markdown: string,
     *     html: string
     * }>
     */
    public function examples(): iterable
    {
        if (!is_file($this->path)) {
            throw new \RuntimeException("spec file not found: {$this->path}");
        }
        $lines = file($this->path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new \RuntimeException("cannot read spec file: {$this->path}");
        }

        $section    = '';
        $number     = 0;
        $state      = 'body';     // body | md | html
        $fenceLen   = 0;
        $extension  = null;
        $mdLines    = [];
        $htmlLines  = [];

        foreach ($lines as $raw) {
            if ($state === 'body') {
                if (preg_match('/^#{1,6}\s+(.*?)\s*#*\s*$/', $raw, $m)) {
                    $section = $m[1];
                    continue;
                }
                if (preg_match('/^(`{10,})\s+example(?:\s+(\S.*?))?\s*$/', $raw, $m)) {
                    $number++;
                    $fenceLen  = strlen($m[1]);
                    $extension = isset($m[2]) && $m[2] !== '' ? $m[2] : null;
                    $state     = 'md';
                    $mdLines   = [];
                    $htmlLines = [];
                }
                continue;
            }

            // Close-fence check: same char, same length, line is exactly the fence
            if (preg_match('/^(`{' . $fenceLen . ',})\s*$/', $raw, $m)
                && strlen($m[1]) === $fenceLen
            ) {
                yield [
                    'number'    => $number,
                    'section'   => $section,
                    'extension' => $extension,
                    'markdown'  => implode("\n", $mdLines),
                    'html'      => implode("\n", $htmlLines),
                ];
                $state = 'body';
                continue;
            }

            if ($state === 'md') {
                if ($raw === '.') {
                    $state = 'html';
                    continue;
                }
                $mdLines[] = $raw;
                continue;
            }

            // state === 'html'
            $htmlLines[] = $raw;
        }

        if ($state !== 'body') {
            throw new \RuntimeException(
                "spec file ended mid-example (#$number); opening fence of length $fenceLen was not closed"
            );
        }
    }
}
