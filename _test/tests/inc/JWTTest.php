<?php

namespace dokuwiki\test;

use dokuwiki\JWT;

class JWTTest extends \DokuWikiTest
{


    public function testCreation()
    {
        // no token file yet
        $file = JWT::getStorageFile('test');
        $this->assertFileNotExists($file);

        // initialize a new token
        $jwt = JWT::fromUser('test');
        $this->assertFileExists($file);
        $this->assertEquals('test', $jwt->getUser());
        $token = $jwt->getToken();
        $issued = $jwt->getIssued();

        // validate the token
        $jwt = JWT::validate($token);
        $this->assertEquals('test', $jwt->getUser());
        $this->assertEquals($issued, $jwt->getIssued());


        // next access should get the same token
        $jwt = JWT::fromUser('test');
        $this->assertEquals($token, $jwt->getToken());
        $this->assertEquals($issued, $jwt->getIssued());

        // saving should create a new token
        sleep(1); // make sure we have a new timestamp
        $jwt->save();
        $this->assertNotEquals($token, $jwt->getToken());
        $this->assertNotEquals($issued, $jwt->getIssued());
    }

    public function testValidationFail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid JWT signature');
        JWT::validate('invalid');
    }

    public function testLoadFail()
    {
        $jwt = JWT::fromUser('test');
        $token = $jwt->getToken();
        $file = JWT::getStorageFile('test');
        unlink($file);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('JWT not found, maybe it expired?');
        JWT::validate($token);
    }

    public function testLoadExpireFail()
    {
        $jwt = JWT::fromUser('test');
        $token = $jwt->getToken();
        sleep(1); // make sure we have a new timestamp
        $jwt->save();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('JWT invalid, maybe it expired?');
        JWT::validate($token);
    }

    public function testLogin()
    {
        $_SERVER['HTTP_AUTHORIZATION'] =  'Bearer ' . JWT::fromUser('testuser')->getToken();

        $this->assertArrayNotHasKey('REMOTE_USER', $_SERVER);
        auth_tokenlogin();
        $this->assertEquals('testuser', $_SERVER['REMOTE_USER']);
        unset($_SERVER['HTTP_AUTHORIZATION']);
    }
}
