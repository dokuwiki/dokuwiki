<?php

namespace dokuwiki\test\Parsing\Markdown;

class SpecReaderTest extends \DokuWikiTest
{
    /** Where to write a throwaway fixture. TMP_DIR is created by the test
     *  bootstrap and removed by its shutdown handler — no per-test cleanup
     *  needed. */
    private const FIXTURE_PATH = TMP_DIR . '/spec-reader-test.txt';

    private function writeFixture(string $content): SpecReader
    {
        file_put_contents(self::FIXTURE_PATH, $content);
        return new SpecReader(self::FIXTURE_PATH);
    }

    public function testSingleOrdinaryExample()
    {
        $reader = $this->writeFixture(<<<SPEC
## Simple Headings

`````````` example
# foo
.
<h1>foo</h1>
``````````

trailing prose
SPEC);

        $examples = iterator_to_array($reader->examples(), false);
        $this->assertCount(1, $examples);
        $this->assertSame(1, $examples[0]['number']);
        $this->assertSame('Simple Headings', $examples[0]['section']);
        $this->assertNull($examples[0]['extension']);
        $this->assertSame("# foo", $examples[0]['markdown']);
        $this->assertSame("<h1>foo</h1>", $examples[0]['html']);
    }

    public function testSectionTracking()
    {
        $reader = $this->writeFixture(<<<SPEC
## Section One

`````````` example
a
.
<p>a</p>
``````````

## Section Two

`````````` example
b
.
<p>b</p>
``````````
SPEC);

        $examples = iterator_to_array($reader->examples(), false);
        $this->assertCount(2, $examples);
        $this->assertSame(1, $examples[0]['number']);
        $this->assertSame('Section One', $examples[0]['section']);
        $this->assertSame(2, $examples[1]['number']);
        $this->assertSame('Section Two', $examples[1]['section']);
    }

    public function testExampleWithExtensionLabel()
    {
        $reader = $this->writeFixture(<<<SPEC
## Tables

`````````` example table
| a | b |
| - | - |
| 1 | 2 |
.
<table>…</table>
``````````
SPEC);

        $examples = iterator_to_array($reader->examples(), false);
        $this->assertCount(1, $examples);
        $this->assertSame('table', $examples[0]['extension']);
        $this->assertStringContainsString('| a | b |', $examples[0]['markdown']);
    }

    public function testMultilineMarkdownAndHtml()
    {
        $reader = $this->writeFixture(<<<SPEC
## Multiline

`````````` example
line one
line two

line four
.
<p>line one
line two</p>
<p>line four</p>
``````````
SPEC);

        $examples = iterator_to_array($reader->examples(), false);
        $this->assertSame(
            "line one\nline two\n\nline four",
            $examples[0]['markdown']
        );
        $this->assertSame(
            "<p>line one\nline two</p>\n<p>line four</p>",
            $examples[0]['html']
        );
    }

    public function testBackticksInsideExampleAreNotConfusedForFence()
    {
        // Opening fence is 14 backticks; a shorter run inside must not close.
        $reader = $this->writeFixture(<<<SPEC
## Code

`````````````` example
here is `code` with backticks
.
<p>here is <code>code</code> with backticks</p>
``````````````
SPEC);

        $examples = iterator_to_array($reader->examples(), false);
        $this->assertCount(1, $examples);
        $this->assertStringContainsString('`code`', $examples[0]['markdown']);
    }

    public function testNumbersAreSequential()
    {
        $body = '';
        for ($i = 1; $i <= 5; $i++) {
            $body .= "`````````` example\nmd$i\n.\nhtml$i\n``````````\n\n";
        }
        $reader = $this->writeFixture($body);
        $examples = iterator_to_array($reader->examples(), false);
        $this->assertCount(5, $examples);
        foreach ($examples as $i => $ex) {
            $this->assertSame($i + 1, $ex['number']);
        }
    }

    public function testUnclosedFenceThrows()
    {
        $reader = $this->writeFixture(<<<SPEC
`````````` example
no closer ever arrives
.
<p>x</p>
SPEC);
        $this->expectException(\RuntimeException::class);
        iterator_to_array($reader->examples(), false);
    }

    public function testMissingFileThrows()
    {
        $reader = new SpecReader('/nonexistent/path/spec.txt');
        $this->expectException(\RuntimeException::class);
        iterator_to_array($reader->examples(), false);
    }
}
