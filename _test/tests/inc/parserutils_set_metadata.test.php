<?php
/**
 * Unit Test for inc/common.php - pageinfo()
 *
 * @author Christopher Smith <chris@jalakai.co.uk>
 */
class parserutils_set_metadata_test extends DokuWikiTest {
    // the id used for this test case
    private $id;

    function helper_prepare_users($id = 1){
        global $INFO, $USERINFO;
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';
        if($id == 2){
            $USERINFO = array(
               'pass' => '179ad45c6ce2cb97cf1029e212046e81',
               'name' => 'Tester2',
               'mail' => 'tester2@example.com',
               'grps' => array ('user'),
            );
            $INFO['userinfo'] = $USERINFO;
            $_SERVER['REMOTE_USER'] = 'tester2';
        }else{
            global $USERINFO, $INFO;
            $USERINFO = array(
               'pass' => '179ad45c6ce2cb97cf1029e212046e81',
               'name' => 'Tester1',
               'mail' => 'tester1@example.com',
               'grps' => array ('admin','user'),
            );
            $INFO['userinfo'] = $USERINFO;
            $_SERVER['REMOTE_USER'] = '1';
        }
    }
    /**
     *  test array merge, including contributors with numeric keys and array data overwritting
     */
    function test_array_replace(){
        // prepare user
        $this->helper_prepare_users(1);

        // prepare page
        $this->id = 'test:set_metadata_array_replace';
        saveWikiText($this->id, 'Test', 'Test data setup');
        $meta = p_get_metadata($this->id);

        $this->assertEquals('1', $meta['user'], 'Initial page has wrong user ID');
        // $this->assertEquals(empty($meta['contributor']), true, 'Initial page should have no contributors');

        // first revision with numeric user
        saveWikiText($this->id, 'Test1', 'Test first edit');
        $meta = p_get_metadata($this->id);

        $last_edit_date = $meta['date']['modified'];
        $this->assertEquals(array('1'=>'Tester1'), $meta['contributor'], 'First edit contributors error');

        // second revision with alphabetic user
        sleep(1); // To generate a different timestamp
        $this->helper_prepare_users(2);
        saveWikiText($this->id, 'Test2', 'Test second edit');
        $meta = p_get_metadata($this->id);

        $this->assertNotEquals($last_edit_date, $meta['date']['modified'], 'First edit date merge error');
        $this->assertEquals(array('tester2'=>'Tester2', '1'=>'Tester1'), $meta['contributor'], 'Second edit contributors error');

        // third revision with the first user
        $this->helper_prepare_users(1);
        saveWikiText($this->id, 'Test3', 'Test third edit');
        $meta = p_get_metadata($this->id);

        $this->assertEquals(array('tester2'=>'Tester2', '1'=>'Tester1'), $meta['contributor'], 'Third edit contributors error');
    }
}

//Setup VIM: ex: et ts=4 :
