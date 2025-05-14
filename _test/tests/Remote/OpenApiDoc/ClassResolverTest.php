<?php

namespace dokuwiki\test\Remote\OpenApiDoc;

use dokuwiki\Remote\OpenApiDoc\ClassResolver;

class ClassResolverTest extends \DokuWikiTest
{


    public function testResolving()
    {
        $resolver = new ClassResolver();

        // resolve by use statement
        $this->assertEquals(ClassResolver::class, $resolver->resolve('ClassResolver', self::class));

        // resolve in same namespace
        $this->assertEquals(
            'dokuwiki\test\Remote\OpenApiDoc\Something\Else',
            $resolver->resolve('Something\Else', self::class)
        );

        // resolve fully qualified
        $this->assertEquals(
            'fully\Qualified\Class',
            $resolver->resolve('\fully\Qualified\Class', self::class)
        );
    }
}
