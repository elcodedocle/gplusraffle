<?php

require_once '../vendor/google/apiclient/src/Google/Client.php';
require_once '../vendor/google/apiclient/src/Google/Service/Plus.php';
require_once '../User.php';

class UserTest extends PHPUnit_Framework_TestCase {
    private $testConfigFile = 'config/testConfig.php'; //All test require a valid config file name. Check out config/testConfig.php.dist
    private $token = '<YOUR-ACCESS-TOKEN-HERE>'; //JSON encoded. If it's not set, test requiring a token will be skipped.
    private $invalidToken = "Whatever."; //Invalid or expired token. Should trigger a Google_Auth_Exception
    
    private $expectedUserId = '<YOUR-USER-ID-HERE>'; //JSON encoded. If it's not set, test requiring a token will be skipped.
    
    private function getConfig($testConfigFile = null){
        if ($testConfigFile === null) { $testConfigFile = $this->testConfigFile; }

        $config = array();
        if (!file_exists($testConfigFile)) { 
            return false; 
        } else {
            //sets $config values
            include $testConfigFile;
        }
        if (
            !isset($config['client_id']) || !$config['client_id'] ||
            !isset($config['client_secret']) || !$config['client_secret'] ||
            !isset($config['redirect_uri']) || !$config['redirect_uri']
        ){
            return false;
        }
        return $config;
    }

    /**
     * @expectedException Google_Auth_Exception
     */
    public function testInvalidTokenAuthentication(){
        if (($config = $this->getConfig())===false){
            $this->markTestSkipped(
                "A valid config file must be provided for this test."
            );
            return;
        }
        $user = new User(
            $config['client_id'],
            $config['client_secret'],
            $config['redirect_uri'],
            $this->invalidToken
        );
        $out = $user->authenticate();
        $this->assertArrayHasKey('authUrl',$out);
        $this->assertEquals(false,isset($out['access_token']));
        
        $user->requestUserId(); // this triggers the exception
    }
    
    public function testNoTokenAuthentication(){
        if (($config = $this->getConfig())===false){
            $this->markTestSkipped(
                "A valid config file must be provided for this test."
            );
            return;
        }
        $user = new User(
            $config['client_id'],
            $config['client_secret'],
            $config['redirect_uri']
        );
        $out = $user->authenticate();
        $this->assertArrayHasKey('authUrl',$out);
        $this->assertEquals(false,isset($out['access_token']));
    }

    public function testTokenAuthentication(){
        if (($config = $this->getConfig())===false){
            $this->markTestSkipped(
                "A valid config file must be provided for this test."
            );
            return;
        }
        if (
            $this->token==='<YOUR-ACCESS-TOKEN-HERE>'
        ){
            $this->markTestSkipped(
                "A valid test token must be provided for this test."
            );
            return;
        }
        //sets $config values
        $user = new User(
            $config['client_id'],
            $config['client_secret'],
            $config['redirect_uri'],
            $this->token
        );
        $out = $user->authenticate();
        $this->assertArrayHasKey('access_token',$out);
        $this->assertEquals(false,isset($out['authUrl']));
    }

    public function testGetUserId(){
        if (
            $this->token === '<YOUR-ACCESS-TOKEN-HERE>'
        ){
            $this->markTestSkipped(
                "A valid test token must be provided for this test."
            );
            return;
        }
        if (
            $this->expectedUserId === '<YOUR-USER-ID-HERE>'
        ){
            $this->markTestSkipped(
                "An expected user id must be provided for this test."
            );
            return;
        }
        if (($config = $this->getConfig())===false){
            $this->markTestSkipped(
                "A valid config file must be provided for this test."
            );
            return;
        }
        $user = new User(
            $config['client_id'],
            $config['client_secret'],
            $config['redirect_uri'],
            $this->token
        );
        $me = $user->requestUserId();
        $this->assertEquals($this->expectedUserId,$me);
    }

}
 