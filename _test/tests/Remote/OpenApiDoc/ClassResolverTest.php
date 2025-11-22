<?php

namespace easywiki\test\Remote\OpenApiDoc;

use easywiki\Remote\OpenApiDoc\ClassResolver;

class ClassResolverTest extends \EasyWikiTest
{


    public function testResolving()
    {
        $resolver = new ClassResolver();

        // resolve by use statement
        $this->assertEquals(ClassResolver::class, $resolver->resolve('ClassResolver', self::class));

        // resolve in same namespace
        $this->assertEquals(
            'easywiki\test\Remote\OpenApiDoc\Something\Else',
            $resolver->resolve('Something\Else', self::class)
        );

        // resolve fully qualified
        $this->assertEquals(
            'fully\Qualified\Class',
            $resolver->resolve('\fully\Qualified\Class', self::class)
        );
    }
}
