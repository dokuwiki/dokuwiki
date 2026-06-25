<?php

namespace dokuwiki\test\Form;

use dokuwiki\Form;

class TextareaElementTest extends \DokuWikiTest
{
    /**
     * Create a form with a textarea element and return the raw inner HTML of the textarea.
     */
    private function rawTextareaBody(string $value): string
    {
        $form = new Form\Form();
        $form->addTextarea('wikitext', 'label')->val($value);

        $this->assertSame(
            1,
            preg_match('#<textarea[^>]*>(.*?)</textarea>#s', $form->toHTML(), $m),
            'expected exactly one textarea'
        );
        return $m[1];
    }

    public function testStartTagIsFollowedByGuardNewline()
    {
        $this->assertStringStartsWith("\n", $this->rawTextareaBody('hello'));
    }

    public function testValueIsEmittedInFullAfterGuard()
    {
        // a value beginning with a newline must appear unaltered after the
        // guard: the start tag is followed by the guard newline and then the
        // form-encoded value (including its own leading newline)
        $value = "\n## heading\n";
        $this->assertSame("\n" . formText($value), $this->rawTextareaBody($value));
    }
}
