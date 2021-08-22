<?php


class template_tpl_classes_test extends DokuWikiTest {

    function hasClass($class, $classes) {
        /*
         * with phpunit for php 7.3, I can't use regexp. So, instead of using
         * $this->assertMatchesRegularExpression('/\bSOME-STRING\b/', $classes_str);
         * I have to use
         * $this->assertTrue(in_array('SOME-STRING', explode(' ', $classes_str)));
         * Which I simplified a bit as
         * $this->assertTrue($this->hasClass('SOME-STRING', $classes_str));
         * The error messages in case of failure are not very informative.
         */
        return in_array($class, explode(' ', $classes));
    }
    function test_levels() {
        global $ID;
        
        foreach( [':a', 'a:b', ':a:b:c', 'a:b:c:d', ':a:b:c:d:e'] as $level => $ID ) {
            $classes=tpl_classes();
            $this->assertTrue($this->hasClass("lv_$level", $classes));
            // negative tests
            $this->assertFalse($this->hasClass('lv_', $classes));
            $this->assertFalse($this->hasClass('lv_'.($level - 1), $classes));
            $this->assertFalse($this->hasClass('lv_'.($level + 1), $classes));
        }
    }

    function test_pg() {
        global $ID;
        
        $ID=':a';
        $classes=tpl_classes();
        $this->assertTrue($this->hasClass('pg_a', $classes));
        // negative tests
        $this->assertFalse($this->hasClass('pg_', $classes));

        $ID='a:b';
        $classes=tpl_classes();
        $this->assertTrue($this->hasClass('pg_b', $classes));
        $this->assertTrue($this->hasClass('pg_a_b', $classes));
        // negative tests
        $this->assertFalse($this->hasClass('pg_a', $classes));
        $this->assertFalse($this->hasClass('pg_', $classes));

        $ID=':a:b:c:d:e';
        $classes=tpl_classes();
        $this->assertTrue($this->hasClass('pg_e', $classes));
        $this->assertTrue($this->hasClass('pg_a_b_c_d_e', $classes));
        // negative tests
        $this->assertFalse($this->hasClass('pg_a', $classes));
        $this->assertFalse($this->hasClass('pg_a_b', $classes));
        $this->assertFalse($this->hasClass('pg_a_b_c', $classes));
        $this->assertFalse($this->hasClass('pg_a_b_c_d', $classes));
        $this->assertFalse($this->hasClass('pg_', $classes));
    }

    function test_ns() {
        global $ID;
        
        $ID=':a';
        $classes=tpl_classes();
        $this->assertTrue($this->hasClass('ns__', $classes));
        // negative tests
        $this->assertFalse($this->hasClass('ns__a', $classes));
        $this->assertFalse($this->hasClass('ns_a_', $classes));

        $ID='a:b';
        $classes=tpl_classes();
        $this->assertTrue($this->hasClass('ns__a', $classes));
        $this->assertTrue($this->hasClass('ns_a_', $classes));
        // negative tests
        $this->assertFalse($this->hasClass('ns__a_b', $classes));
        $this->assertFalse($this->hasClass('ns__b_', $classes));
        $this->assertFalse($this->hasClass('ns__a_b_', $classes));

        $ID=':a:b:c:d:e';
        $classes=tpl_classes();
        $this->assertTrue($this->hasClass('ns__a', $classes));
        $this->assertTrue($this->hasClass('ns__a_b', $classes));
        $this->assertTrue($this->hasClass('ns__a_b_c', $classes));
        $this->assertTrue($this->hasClass('ns__a_b_c_d', $classes));
        $this->assertTrue($this->hasClass('ns__a_b_c_d', $classes));
        $this->assertTrue($this->hasClass('ns_d_', $classes));
        // negative tests
        $this->assertFalse($this->hasClass('ns_d', $classes));
        $this->assertFalse($this->hasClass('ns__e_', $classes));
        $this->assertFalse($this->hasClass('ns__a_b_c_d_e', $classes));
        $this->assertFalse($this->hasClass('ns__a_b_c_d_e_', $classes));
    }

    function test_underscore() {
        global $ID;
        
        $ID=':a_b';
        $classes=tpl_classes();
        $this->assertTrue($this->hasClass('pg_a__b', $classes));
        // negative tests
        $this->assertFalse($this->hasClass('pg_', $classes));
        $this->assertFalse($this->hasClass('pg_a_b', $classes));
        $this->assertFalse($this->hasClass('pg_b', $classes));
        $this->assertFalse($this->hasClass('pg__b', $classes));
        $this->assertFalse($this->hasClass('pg___b', $classes));

        $ID='a:b_c';
        $classes=tpl_classes();
        $this->assertTrue($this->hasClass('pg_b__c', $classes));
        $this->assertTrue($this->hasClass('pg_a_b__c', $classes));
        // negative tests
        $this->assertFalse($this->hasClass('pg_', $classes));
        $this->assertFalse($this->hasClass('pg_a_b_c', $classes));
        $this->assertFalse($this->hasClass('pg_b_c', $classes));
        $this->assertFalse($this->hasClass('pg__b_c', $classes));
        $this->assertFalse($this->hasClass('pg___b_c', $classes));

        $ID=':a_b:c';
        $classes=tpl_classes();
        $this->assertTrue($this->hasClass('pg_a__b_c', $classes));
        $this->assertTrue($this->hasClass('pg_c', $classes));
        // negative tests
        $this->assertFalse($this->hasClass('pg_', $classes));
        $this->assertFalse($this->hasClass('pg_a_b_c', $classes));
        $this->assertFalse($this->hasClass('pg__c', $classes));
        $this->assertFalse($this->hasClass('pg___c', $classes));

        $ID=':a_b:c_d';
        $classes=tpl_classes();
        $this->assertTrue($this->hasClass('pg_a__b_c__d', $classes));
        $this->assertTrue($this->hasClass('pg_c__d', $classes));
        // negative tests
        $this->assertFalse($this->hasClass('pg_', $classes));
        $this->assertFalse($this->hasClass('pg_a_b_c_d', $classes));
        $this->assertFalse($this->hasClass('pg_c_d', $classes));
        $this->assertFalse($this->hasClass('pg__c_d', $classes));
        $this->assertFalse($this->hasClass('pg___c_d', $classes));
    }
}
