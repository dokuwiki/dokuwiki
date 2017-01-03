<?php

namespace dokuwiki\plugin\struct\test;

use dokuwiki\plugin\struct\test\mock\QueryBuilder;
use dokuwiki\plugin\struct\types\Text;

/**
 * Testing the Text Type
 *
 * @group plugin_struct
 * @group plugins
 */
class Type_Text_struct_test extends StructTest {

    public function data() {
        return array(
            // simple
            array(
                '', // prefix
                '', // postfix
                '=', // comp
                'val', // value
                '(T.col = ?)', // expect sql
                array('val'), // expect opts
            ),
            array(
                'before', // prefix
                '', // postfix
                '=', // comp
                'val', // value
                '(? || T.col = ?)', // expect sql
                array('before', 'val'), // expect opts
            ),
            array(
                '', // prefix
                'after', // postfix
                '=', // comp
                'val', // value
                '(T.col || ? = ?)', // expect sql
                array('after', 'val'), // expect opts
            ),
            array(
                'before', // prefix
                'after', // postfix
                '=', // comp
                'val', // value
                '(? || T.col || ? = ?)', // expect sql
                array('before', 'after', 'val'), // expect opts
            ),
            // LIKE
            array(
                '', // prefix
                '', // postfix
                'LIKE', // comp
                '%val%', // value
                '(T.col LIKE ?)', // expect sql
                array('%val%'), // expect opts
            ),
            array(
                'before', // prefix
                '', // postfix
                'LIKE', // comp
                '%val%', // value
                '(? || T.col LIKE ?)', // expect sql
                array('before','%val%'), // expect opts
            ),
            array(
                '', // prefix
                'after', // postfix
                'LIKE', // comp
                '%val%', // value
                '(T.col || ? LIKE ?)', // expect sql
                array('after','%val%'), // expect opts
            ),
            array(
                'before', // prefix
                'after', // postfix
                'LIKE', // comp
                '%val%', // value
                '(? || T.col || ? LIKE ?)', // expect sql
                array('before','after','%val%'), // expect opts
            ),
            // NOT LIKE
            array(
                '', // prefix
                '', // postfix
                'NOT LIKE', // comp
                '%val%', // value
                '(T.col NOT LIKE ?)', // expect sql
                array('%val%'), // expect opts
            ),
            array(
                'before', // prefix
                '', // postfix
                'NOT LIKE', // comp
                '%val%', // value
                '(? || T.col NOT LIKE ?)', // expect sql
                array('before','%val%'), // expect opts
            ),
            array(
                '', // prefix
                'after', // postfix
                'NOT LIKE', // comp
                '%val%', // value
                '(T.col || ? NOT LIKE ?)', // expect sql
                array('after','%val%'), // expect opts
            ),
            array(
                'before', // prefix
                'after', // postfix
                'NOT LIKE', // comp
                '%val%', // value
                '(? || T.col || ? NOT LIKE ?)', // expect sql
                array('before','after','%val%'), // expect opts
            ),

            // complex multi-value
            array(
                'before', // prefix
                'after', // postfix
                'NOT LIKE', // comp
                array('%val1%', '%val2%'), // multiple values
                '((? || T.col || ? NOT LIKE ? OR ? || T.col || ? NOT LIKE ?))', // expect sql
                array('before','after','%val1%', 'before','after','%val2%',), // expect opts
            ),
        );

    }

    /**
     * @dataProvider data
     */
    public function test_filter($prefix, $postfix, $comp, $val, $e_sql, $e_opt) {
        $QB = new QueryBuilder();

        $text = new Text(array('prefix' => $prefix, 'postfix' => $postfix));
        $text->filter($QB->filters(), 'T', 'col', $comp, $val, 'AND');

        list($sql, $opt) = $QB->getWhereSQL();
        $this->assertEquals($this->cleanWS($e_sql), $this->cleanWS($sql));
        $this->assertEquals($e_opt, $opt);
    }
}
