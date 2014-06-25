<?php

require_once 'mockups/AdminDAOMockup.php';
require_once 'mockups/RaffleDAOMockup.php';
require_once 'mockups/UserMockup.php';
require_once '../Raffle.php';

class RaffleTest extends PHPUnit_Framework_TestCase {
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
    private $userIdMockup = 'regularUserIdMockup';
    //private $userTokenMockup = '{"access_token":"mockupRegularUserToken","token_type":"Bearer","expires_in":3600,"id_token":"mockupRegularUserIdToken","created":"1403218562"}';
    private $rafflesMockupColumns = array (
        'raffleid',
        'raffledescription',
        'creatorid',
        'created',
        'privacy',
        'status',
    );
    private $participantsMockupColumns = array (
        'raffleid',
        'participantid',
        'comment',
        'joined',
    );
    private $winnersMockupColumns = array (
        'raffleid',
        'winnerid',
        'raffled',
    );
    
    private function createTablesMockup($tableIds,$rafflesRows,$participantsRows,$winnersRows){
        $tablesMockup = array();

        $rafflesMockup = new stdClass();
        $participantsMockup = new stdClass();
        $winnersMockup = new stdClass();

        $rafflesMockup->rows = $rafflesRows;
        $rafflesMockup->columns = $this->rafflesMockupColumns;

        $participantsMockup->rows = $participantsRows;
        $participantsMockup->columns = $this->participantsMockupColumns;

        $winnersMockup->rows = $winnersRows;
        $winnersMockup->columns = $this->winnersMockupColumns;

        $tablesMockup[$tableIds['raffles']] = $rafflesMockup;
        $tablesMockup[$tableIds['participants']] = $participantsMockup;
        $tablesMockup[$tableIds['winners']] = $winnersMockup;
        
        return $tablesMockup;
        
    }

    public function testCreate(){
        $emptyTablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(),
            array(),
            array()
        );

        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $emptyTablesMockup;
        $raffleDAO->tablesMockup = $emptyTablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $result = $raffle->create('Raffle description.');
        
        $expectedResult = new stdClass();
        $expectedResult->columns = $emptyTablesMockup[
            $this->adminSettingsMockup['tableIds']['raffles']
        ]->columns;
        if (
            isset($result->rows) &&
            is_array($result->rows) &&
            count($result->rows) === 1 &&
            is_array($result->rows[0]) &&
            count($result->rows[0]) === count($expectedResult->columns)
        ){
            $expectedResult->rows = $result->rows;
        } else {
            $this->fail('Unexpected result.');
        }
        $this->assertEquals($expectedResult,$result);
    }
    public function testDelete(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $result = $raffle->delete('raffleUUIDV5Mockup');
        $this->assertEquals($result,true);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 401
     */
    public function testDeleteUnauthorized(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    'creatorIdMockup',
                    null,
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,'notCreatorIdOrAdminIdMockup',false);
        $raffle->delete('raffleUUIDV5Mockup');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testDeleteNonExistent(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->delete('nonExistentRaffleId');
    }
    public function testUpdateStatus(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $result = $raffle->updateStatus('raffleUUIDV5Mockup','closed');
        
        $expectedResult = new stdClass();
        $expectedResult->columns = array('affected_rows');
        $expectedResult->rows = array(array(1));
        
        $this->assertEquals($expectedResult,$result);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 401
     */
    public function testUpdateStatusUnauthorized(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    'creatorIdMockup',
                    null,
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,'notCreatorIdOrAdminIdMockup',false);
        $raffle->updateStatus('raffleUUIDV5Mockup','closed');
    }


    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testUpdateStatusNonExistent(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->updateStatus('nonExistentRaffleUUIDV5','closed');

    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 400
     */
    public function testUpdateStatusInvalid(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->updateStatus('raffleUUIDV5Mockup','invalidStatus');
    }
    public function testRaffle(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    'closed',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    'participantIdMockup',
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $result = $raffle->raffle('raffleUUIDV5Mockup');
        
        $expectedResult = new stdClass();
        
        $expectedResult->columns = $this->winnersMockupColumns;
        if(
            isset($result->rows)&&
            isset($result->rows[0])&&
            isset($result->rows[0][2])
        ){
            $expectedResult->rows = array(
                array(
                    'raffleUUIDV5Mockup',
                    'participantIdMockup',
                    $result->rows[0][2],
                )
            );
        } else {
            $this->fail("Unexpected result:".PHP_EOL.var_export($result,true).PHP_EOL);
        }

        $this->assertEquals($expectedResult,$result);
        
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 401
     */
    public function testRaffleUnauthorized(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    'creatorIdMockup',
                    null,
                    null,
                    'closed',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    'participantIdMockup',
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,'notCreatorIdOrAdminId',false);
        $raffle->raffle('raffleUUIDV5Mockup');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testRaffleNonExistent(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    'closed',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    'participantIdMockup',
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->raffle('nonExistentRaffleUUIDV5');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testRaffleNoParticipants(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    'closed',
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->raffle('raffleUUIDV5Mockup');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 400
     */
    public function testRaffleNotClosedButOpen(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    'open',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    'participantIdMockup',
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->raffle('raffleUUIDV5Mockup');

    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 400
     */
    public function testRaffleNotClosedButRaffled(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    'raffled',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    'participantIdMockup',
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->raffle('raffleUUIDV5Mockup');
    }
    public function testGetListAll(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $filterArray = array();
        $result = $raffle->getList($filterArray);

        $expectedResult = new stdClass();

        $expectedResult->columns = $this->rafflesMockupColumns;
        $expectedResult->rows = $tablesMockup[$this->adminSettingsMockup['tableIds']['raffles']]->rows;

        $this->assertEquals($expectedResult,$result);
    }
    public function testGetListMine(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    'creatorIdMockup',
                    null,
                    null,
                    null,
                ),
                array(
                    'anotherRaffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $filterArray = array(
            'creatorid' => array(
                'condition' => '=',
                'value' => $this->userIdMockup,
            ),
        );
        $result = $raffle->getList($filterArray);

        $expectedResult = new stdClass();

        $expectedResult->columns = $this->rafflesMockupColumns;
        $expectedResult->rows =
            array(
                array(
                    'anotherRaffleUUIDV5Mockup',
                    null,
                    $this->userIdMockup,
                    null,
                    null,
                    null,
                ),
            );

        $this->assertEquals($expectedResult,$result);
    }
    public function testGetListMe(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    null,
                ),
                array(
                    'anotherRaffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    null,
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    $this->userIdMockup,
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $filterArray = array(
            'participantid' => array(
                'condition' => '=',
                'value' => $this->userIdMockup,
            ),
        );
        $result = $raffle->getList($filterArray);

        $expectedResult = new stdClass();

        $expectedResult->columns = $this->rafflesMockupColumns;
        $expectedResult->rows = array(
            $tablesMockup[$this->adminSettingsMockup['tableIds']['raffles']]->rows[0]
        );

        $this->assertEquals($expectedResult,$result);
    }
    public function testGetListOpen(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'closed',
                ),
                array(
                    'anotherRaffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
                array(
                    'evenAnotherRaffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'raffled',
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $filterArray = array(
            'status'=>array(
                'condition' => '=',
                'value' => 'open',
            ),
        );
        $result = $raffle->getList($filterArray);

        $expectedResult = new stdClass();

        $expectedResult->columns = $this->rafflesMockupColumns;
        $expectedResult->rows =
            array(
                array(
                    'anotherRaffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            );

        $this->assertEquals($expectedResult,$result);
    }
    public function testGetParticipants(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    null,
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    'participantId',
                    null,
                    null,
                ),
                array(
                    'anotherRaffleUUIDV5Mockup',
                    'participantId',
                    null,
                    null,
                ),
                array(
                    'raffleUUIDV5Mockup',
                    'anotherParticipantId',
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        
        $result = $raffle->getParticipants('raffleUUIDV5Mockup');

        $expectedResult = new stdClass();

        $expectedResult->columns = $this->participantsMockupColumns;
        $expectedResult->rows = array(
            $tablesMockup[$this->adminSettingsMockup['tableIds']['participants']]->rows[0],
            $tablesMockup[$this->adminSettingsMockup['tableIds']['participants']]->rows[2],
        );

        $this->assertEquals($expectedResult,$result);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testGetParticipantsNonExistent(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    null,
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    'participantId',
                    null,
                    null,
                ),
                array(
                    'anotherRaffleUUIDV5Mockup',
                    'participantId',
                    null,
                    null,
                ),
                array(
                    'raffleUUIDV5Mockup',
                    'anotherParticipantId',
                    null,
                    null,
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);

        $raffle->getParticipants('nonExistentRaffleIdMockup');
    }
    public function testJoin(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $result = $raffle->join('raffleUUIDV5Mockup');

        $expectedResult = new stdClass();

        $expectedResult->columns = array('rowid');
        if(
            isset($result->rows )&&
            is_array($result->rows) &&
            count($result->rows) === 1 &&
            isset($result->rows[0]) &&
            is_array($result->rows[0]) &&
            count($result->rows[0]) === 1 &&
            isset($result->rows[0][0])
        ){
            $expectedResult->rows = $result->rows;
        } else {
            $this->fail("Unexpected result:".PHP_EOL.var_export($result,true).PHP_EOL);
        }

        $this->assertEquals($expectedResult,$result);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testJoinNonExistent(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->join('nonExistenRaffleUUIDV5Mockup');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 403
     */
    public function testJoinClosed(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'closed',
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->join('raffleUUIDV5Mockup');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 400
     */
    public function testJoinTwice(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    $this->userIdMockup,
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->join('raffleUUIDV5Mockup');
    }
    public function testLeave(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    $this->userIdMockup,
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $result = $raffle->leave('raffleUUIDV5Mockup');
        $this->assertEquals(true,$result);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testLeaveNonExistentRaffle(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    $this->userIdMockup,
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->leave('nonExistentRaffleUUIDV5Mockup');
    }
    
    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testLeaveNonExistentParticipant(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    'aDifferentUserIdMockup',
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->leave('raffleUUIDV5Mockup');
    }
    public function testCheck(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    $this->userIdMockup,
                    null,
                    null,
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    $this->userIdMockup,
                    null,
                ),
            )
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $result = $raffle->check('raffleUUIDV5Mockup');
        $this->assertEquals($result->rows,array(array('raffleUUIDV5Mockup',$this->userIdMockup,$result->rows[0][2])));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testCheckNonExistentRaffle(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    $this->userIdMockup,
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->check('nonExistingRaffleUUIDV5Mockup');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testCheckNonExistentParticipants(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            ),
            array(),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->check('raffleUUIDV5Mockup');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 404
     */
    public function testCheckNonExistentWinners(){
        $tablesMockup = $this->createTablesMockup(
            $this->adminSettingsMockup['tableIds'],
            array(
                array(
                    'raffleUUIDV5Mockup',
                    null,
                    null,
                    null,
                    null,
                    'open',
                ),
            ),
            array(
                array(
                    'raffleUUIDV5Mockup',
                    $this->userIdMockup,
                    null,
                    null,
                ),
            ),
            array()
        );
        $raffleDAO = new RaffleDAOMockup($this->adminSettingsMockup['tableIds']);
        $raffleDAO->tablesMockup = $tablesMockup;
        $raffle = new Raffle($raffleDAO,$this->userIdMockup,false);
        $raffle->check('raffleUUIDV5Mockup');
    }
    
}
 