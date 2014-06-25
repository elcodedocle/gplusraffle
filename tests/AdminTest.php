<?php

require_once 'mockups/AdminDAOMockup.php';
require_once '../Admin.php';


class AdminTest extends PHPUnit_Framework_TestCase {
    private $adminSettingsMockup = array (
        'adminId' => 'adminUserIdMockup',
        'adminToken' => '{"access_token":"mockupToken","token_type":"Bearer","expires_in":3600,"id_token":"mockupIdToken","refresh_token":"mockupRefreshToken","created":"1403218562"}',
        'tableIds' =>
            array (
                'raffles' => 'mockupRafflesId',
                'participants' => 'mockupParticipantsId',
                'winners' => 'mockupWinnersId',
            ),
    );
    public function testInstall(){
        $adminSettingsMockup = $this->adminSettingsMockup;
        $adminDAO = new AdminDAOMockup();
        $adminDAO->userIdMockup = $adminSettingsMockup['adminId'];
        $adminDAO->tableIdsMockup = $adminSettingsMockup['tableIds'];
        $admin = new Admin($adminDAO);
        $authResponse = $adminDAO->authenticate(
            null,
            null,
            $adminSettingsMockup['adminToken']
        );
        $this->assertEquals(false,isset($authResponse['authUrl']));
        $admin->install();
        $this->assertEquals($adminSettingsMockup['adminId'],$adminDAO->getAdminId());
        $this->assertEquals($adminSettingsMockup['adminToken'],$adminDAO->getAccessToken());
        $this->assertEquals($adminSettingsMockup['tableIds'],$adminDAO->getTableIds());
        $this->assertEquals($adminSettingsMockup,$adminDAO->settingsMockup);
    }
    public function testUninstall(){
        $adminSettingsMockup = $this->adminSettingsMockup;
        $adminDAO = new AdminDAOMockup();
        $adminDAO->settingsMockup = $adminSettingsMockup;
        $adminDAO->readAdminSettings();
        $adminDAO->userIdMockup = $adminSettingsMockup['adminId'];
        $admin = new Admin($adminDAO);
        $authResponse = $adminDAO->authenticate(
            null,
            null,
            $adminSettingsMockup['adminToken']
        );
        $this->assertEquals(false,isset($authResponse['authUrl']));
        $admin->uninstall();
        $this->assertEquals(false,isset($adminDAO->settingsMockup));
        $this->assertEquals(null,$adminDAO->getAdminId());
        $this->assertEquals(null,$adminDAO->getAccessToken());
        $this->assertEquals(null,$adminDAO->getTableIds());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Authorization error.
     * @expectedExceptionCode 401
     */
    public function testUnauthenticatedCantUninstall(){
        $adminSettingsMockup = $this->adminSettingsMockup;
        $adminDAO = new AdminDAOMockup();
        $adminDAO->settingsMockup = $adminSettingsMockup;
        $adminDAO->readAdminSettings();
        $adminDAO->userIdMockup = null;
        $admin = new Admin($adminDAO);
        $authResponse = $adminDAO->authenticate(
            null,
            null,
            null
        );
        $this->assertEquals(true,isset($authResponse['authUrl']));
        $admin->uninstall();
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Authorization error.
     * @expectedExceptionCode 401
     */
    public function testUnauthorizedCantUninstall(){
        $adminSettingsMockup = $this->adminSettingsMockup;
        $adminDAO = new AdminDAOMockup();
        $adminDAO->settingsMockup = $adminSettingsMockup;
        $adminDAO->readAdminSettings();
        $adminDAO->userIdMockup = "regularUserIdMockup";
        $admin = new Admin($adminDAO);
        $authResponse = $adminDAO->authenticate(
            null,
            null,
            '{"access_token":"mockupRegularUserToken","token_type":"Bearer","expires_in":3600,"id_token":"mockupRegularUserIdToken","created":"1403218562"}'
        );
        $this->assertEquals(false,isset($authResponse['authUrl']));
        $admin->uninstall();
    }
}

 