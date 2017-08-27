<?php

/**
 * @group integration
 */
class EditAndSaveTest extends DokuWikiTest {

    /**
     * Execute the following requests:
     * - Section edit a page (headline 2, first occurrence)
     * - Save a page
     * - Redirect
     * Check if the header id is transmitted and if the final redirect
     * points to the correct header.
     */
    function testEditSaveRedirect_Headline2_A() {
        $request = new TestRequest();

        $input = array(
            'id'     => 'wiki:editandsavetest'
        );

        // Show page
        $response = $request->post($input);
        $content = $response->getContent();
        $this->assertTrue(!empty($content));

        // If the test page has got the right content for our test it should have
        // two headlines with the title "Headline2"
        preg_match_all('#<h1[^>]*>Headline2</h1[^>]*>#', $content, $matches, PREG_SET_ORDER);
        $this->assertEquals(2, count($matches));

        // Get the header ids
        $result = preg_match('/id="(.*)"/', $matches [0][0], $idA);
        $this->assertEquals(1, $result);
        $result = preg_match('/id="(.*)"/', $matches [1][0], $idB);
        $this->assertEquals(1, $result);
        $this->assertTrue($idA != $idB);

        // Search the section edit form/button for the second id
        $pattern  = '/<form class="button btn_secedit".*>.*';
        $pattern .= '<input type="hidden" name="hid" value="';
        $pattern .= $idA[1];
        $pattern .= '" \/>.*<\/form>/';
        $result = preg_match($pattern, $content, $formA);
        $this->assertEquals(1, $result);

        // Extract all inputs from the form
        $result = preg_match_all('/<input type="hidden" name="([^"]*)" value="([^"]*)" \/>/', $formA[0], $matches, PREG_SET_ORDER);
        $input = array();
        foreach ($matches as $match) {
            $input[$match[1]] = $match[2];
        }
        $this->assertEquals($input['hid'], $idA[1]);

        // Post the input fields (= do a section edit)
        $response = $request->post($input, '/doku.php');
        $content = $response->getContent();

        // Our header id should have been sent back to us in the edit
        // form as an hidden input field
        $content = str_replace("\n", " ", $content);
        $pattern  = '/<form id="dw__editform"[^>]*>.*';
        $pattern .= '<input type="hidden" name="hid" value="';
        $pattern .= $idA[1];
        $pattern .= '" \/>.*<\/form>/';
        $result = preg_match($pattern, $content, $editForm);
        $this->assertEquals(1, $result);

        // Extract all inputs from the edit form
        $result = preg_match_all('/<input type="hidden" name="([^"]*)" value="([^"]*)" \/>/', $editForm[0], $matches, PREG_SET_ORDER);
        $input = array();
        foreach ($matches as $match) {
            $input[$match[1]] = $match[2];
        }
        $this->assertEquals($input['hid'], $idA[1]);
        $input['do'] = 'save';

        // Post the input fields (= save page)
        $response = $request->post($input, '/doku.php');

        // The response should carry a notification that a redirect
        // was executed to our header ID
        $found = null;
        $notifications = $response->getNotifications();
        foreach ($notifications as $notification) {
            if ($notification['name'] == 'send_redirect') {
                $found = &$notification;
            }
        }
        $this->assertTrue($found !== null);
        $hash = strpos($found['url'], '#');
        $headerID = substr($found['url'], $hash);
        $this->assertEquals($headerID, '#'.$idA[1]);
    }

    /**
     * Execute the following requests:
     * - Section edit a page (headline 2, second occurrence)
     * - Save a page
     * - Redirect
     * Check if the header id is transmitted and if the final redirect
     * points to the correct header.
     */
    function testEditSaveRedirect_Headline2_B() {
        $request = new TestRequest();

        $input = array(
            'id'     => 'wiki:editandsavetest'
        );

        // Show page
        $response = $request->post($input);
        $content = $response->getContent();
        $this->assertTrue(!empty($content));

        // If the test page has got the right content for our test it should have
        // two headlines with the title "Headline2"
        preg_match_all('#<h1[^>]*>Headline2</h1[^>]*>#', $content, $matches, PREG_SET_ORDER);
        $this->assertEquals(2, count($matches));

        // Get the header ids
        $result = preg_match('/id="(.*)"/', $matches [0][0], $idA);
        $this->assertEquals(1, $result);
        $result = preg_match('/id="(.*)"/', $matches [1][0], $idB);
        $this->assertEquals(1, $result);
        $this->assertTrue($idA != $idB);

        // Search the section edit form/button for the second id
        $pattern  = '/<form class="button btn_secedit".*>.*';
        $pattern .= '<input type="hidden" name="hid" value="';
        $pattern .= $idB[1];
        $pattern .= '" \/>.*<\/form>/';
        $result = preg_match($pattern, $content, $formB);
        $this->assertEquals(1, $result);

        // Extract all inputs from the form
        $result = preg_match_all('/<input type="hidden" name="([^"]*)" value="([^"]*)" \/>/', $formB[0], $matches, PREG_SET_ORDER);
        $input = array();
        foreach ($matches as $match) {
            $input[$match[1]] = $match[2];
        }
        $this->assertEquals($input['hid'], $idB[1]);

        // Post the input fields (= do a section edit)
        $response = $request->post($input, '/doku.php');
        $content = $response->getContent();

        // Our header id should have been sent back to us in the edit
        // form as an hidden input field
        $content = str_replace("\n", " ", $content);
        $pattern  = '/<form id="dw__editform"[^>]*>.*';
        $pattern .= '<input type="hidden" name="hid" value="';
        $pattern .= $idB[1];
        $pattern .= '" \/>.*<\/form>/';
        $result = preg_match($pattern, $content, $editForm);
        $this->assertEquals(1, $result);

        // Extract all inputs from the edit form
        $result = preg_match_all('/<input type="hidden" name="([^"]*)" value="([^"]*)" \/>/', $editForm[0], $matches, PREG_SET_ORDER);
        $input = array();
        foreach ($matches as $match) {
            $input[$match[1]] = $match[2];
        }
        $this->assertEquals($input['hid'], $idB[1]);
        $input['do'] = 'save';

        // Post the input fields (= save page)
        $response = $request->post($input, '/doku.php');

        // The response should carry a notification that a redirect
        // was executed to our header ID
        $found = null;
        $notifications = $response->getNotifications();
        foreach ($notifications as $notification) {
            if ($notification['name'] == 'send_redirect') {
                $found = &$notification;
            }
        }
        $this->assertTrue($found !== null);
        $hash = strpos($found['url'], '#');
        $headerID = substr($found['url'], $hash);
        $this->assertEquals($headerID, '#'.$idB[1]);
    }
}
