<?php 
session_start();
set_include_path(
    "../../vendor/google/apiclient/src/" . PATH_SEPARATOR . get_include_path()
);
use synapp\info\tools\uuid\uuid;
require_once '../config/testConfig.php';
require_once '../../vendor/elcodedocle/uuid/uuid.php';
require_once '../../vendor/google/apiclient/src/Google/Client.php';
require_once '../../vendor/google/apiclient/src/Google/Service/Fusiontables.php';
require_once '../../vendor/google/apiclient/src/Google/Service/Plus.php';
    
$action= isset($_REQUEST['logout'])?
    'logout':
    (isset($_REQUEST['action'])?
        $_REQUEST['action']:'login');
$scopes = array(
    'https://www.googleapis.com/auth/plus.login', 
    'https://www.googleapis.com/auth/fusiontables'
);
$debug=true;

$client = new Google_Client();
$client->setClientId($config['client_id']);
$client->setClientSecret($config['client_secret']);
$client->setRedirectUri($config['redirect_uri']);
$client->setApprovalPrompt('force');
$client->setAccessType('offline');
$client->setScopes($scopes);

if ($action === 'logout') {
    unset($_SESSION['access_token']);
}

/************************************************
If we have a code back from the OAuth 2.0 flow,
we need to exchange that with the authenticate()
function. We store the resultant access token
bundle in the session, and redirect to ourself.
 ************************************************/
if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    //$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    //header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

/************************************************
If we have an access token, we can make
requests, else we generate an authentication URL.
 ************************************************/
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
    if ($client->isAccessTokenExpired()){
        $tokenArray = json_decode($_SESSION['access_token'],true);
        $client->refreshToken($tokenArray['refresh_token']);
    }
} else {
    $authUrl = $client->createAuthUrl();
}

/************************************************
If we're signed in we can go ahead and retrieve
the ID token, which is part of the bundle of
data that is exchange in the authenticate step
- we only need to do a network call if we have
to retrieve the Google certificate to verify it,
and that can be cached.
 ************************************************/
if ($client->getAccessToken() && !$client->isAccessTokenExpired()) {
    $_SESSION['access_token'] = $client->getAccessToken();
    $token_data = '';//$client->verifyIdToken()->getAttributes();

    $tableSchema = array(
        'raffles' => array(
            'raffleid'=>'STRING',
            'raffledescription'=>'STRING',
            'creatorid'=>'STRING',
            'created'=>'DATETIME',
            'privacy'=>'STRING',
            'status'=>'STRING',
        ),
        'participants' => array(
            'raffleid'=>'STRING',
            'participantid'=>'STRING',
            'comment'=>'STRING',
            'joined'=>'DATETIME',
        ),
        'winners' => array(
            'raffleid'=>'STRING',
            'winnerid'=>'STRING',
            'raffled'=>'DATETIME',
        ),
    );
    
    $plusService = new Google_Service_Plus($client);
    $person = $plusService->people->get('me');

    $uuid =new uuid();
    $raffleids = array(
        uuid::v5(uuid::v4(), 'synapp\\info\\tools\\gplusraffle'),
        uuid::v5(uuid::v4(), 'synapp\\info\\tools\\gplusraffle'),
        uuid::v5(uuid::v4(), 'synapp\\info\\tools\\gplusraffle'),
        uuid::v5(uuid::v4(), 'synapp\\info\\tools\\gplusraffle'),
    );
    $insertQueries = array(
        array(
            'tableName'=>
                'raffles',
            'sql'=>
                " ('raffleid','raffledescription','creatorid','created','privacy','status') "
                ."VALUES ('$raffleids[0]', 'test raffle 1','$person->id','"
                .date("Y-m-d H:i:s")."','public','closed')"
        ),
        array(
            'tableName'=>'raffles',
            'sql'=>
                " ('raffleid','raffledescription','creatorid','created','privacy','status') "
                ."VALUES ('$raffleids[1]', 'test raffle 2','$person->id','"
                .date("Y-m-d H:i:s")."','public','closed')"
        ),
        array(
            'tableName'=>
                'raffles',
            'sql'=>
                " ('raffleid','raffledescription','creatorid','created','privacy','status') "
                ."VALUES ('$raffleids[2]', 'test raffle 3','$person->id','"
                .date("Y-m-d H:i:s")."','public','closed')"
        ),
        array(
            'tableName'=>
                'raffles',
            'sql'=>
                " ('raffleid','raffledescription','creatorid','created','privacy','status') "
                ."VALUES ('$raffleids[3]', 'test raffle 4','$person->id','"
                .date("Y-m-d H:i:s")."','public','closed')"
        ),
        array(
            'tableName'=>'participants',
            'sql'=>" ('raffleid','participantid','comment','joined') "
                ."VALUES ('$raffleids[0]', '$person->id', 'Comment 1','"
                .date("Y-m-d H:i:s")."')"
        ),
        array(
            'tableName'=>'participants',
            'sql'=>" ('raffleid','participantid','joined') "
                ."VALUES ('$raffleids[1]', '$person->id', 'Comment 2','"
                .date("Y-m-d H:i:s")."')"
        ),
        array(
            'tableName'=>'participants',
            'sql'=>" ('raffleid','participantid','joined') "
                ."VALUES ('$raffleids[2]', '$person->id', 'Comment 3','"
                .date("Y-m-d H:i:s")."')"
        ),
        array(
            'tableName'=>'participants',
            'sql'=>" ('raffleid','participantid','joined') "
                ."VALUES ('$raffleids[3]', '$person->id', 'Comment 4','"
                .date("Y-m-d H:i:s")."')"
        ),
        array(
            'tableName'=>'winners',
            'sql'=>" ('raffleid','winnerid','raffled') "
                ."VALUES ('$raffleids[0]', '$person->id','"
                .date("Y-m-d H:i:s")."')"
        ),
        array(
            'tableName'=>'winners',
            'sql'=>" ('raffleid','winnerid','raffled') "
                ."VALUES ('$raffleids[1]', '$person->id','"
                .date("Y-m-d H:i:s")."')"
        ),
        array(
            'tableName'=>'winners',
            'sql'=>" ('raffleid','winnerid','raffled') "
                ."VALUES ('$raffleids[2]', '$person->id','"
                .date("Y-m-d H:i:s")."')"
        ),
        array(
            'tableName'=>'winners',
            'sql'=>" ('raffleid','winnerid','raffled') "
                ."VALUES ('$raffleids[3]', '$person->id','"
                .date("Y-m-d H:i:s")."')"
        ),
    );
    $selectQueries = array(
        array(
            'tableName'=>'raffles','sql'=>""
        ),
        array(
            'tableName'=>'participants','sql'=>""
        ),
        array(
            'tableName'=>'winners','sql'=>""
        ),
        array(
            'tableName'=>'raffles','sql'=>" WHERE raffleid='$raffleids[0]'"
        ),
        array(
            'tableName'=>'raffles','sql'=>" WHERE raffleid!='$raffleids[0]'"
        ),
    );

    $fusionTablesService = new Google_Service_Fusiontables($client);
    $errors = array();
    $tableids = array();
    foreach ($tableSchema as $tableName=>$columns){
        $tableColumns = array();
        foreach ($columns as $columnName=>$columnType){
            $column = new Google_Service_Fusiontables_Column();
            $column->setName($columnName);
            $column->setType($columnType);
            $tableColumns[]=$column;
        }
        $table = new Google_Service_Fusiontables_Table();
        $table->setName($tableName);
        $table->setColumns($tableColumns);
        $table->setIsExportable('true');
        // create fusion tables, saving their ids
        try {
            $response = $fusionTablesService->table->insert($table);
            $tableids[$tableName] = $response->tableId;
        } catch (Google_Service_Exception $e) {
            $errors[] = $e->getErrors();
        }
        //error_log($e->getMessage());
    }
    $queryResults=array();
    try {
        // insert into tables
        foreach ($insertQueries as $query){
                $fusionTablesService->query->sql(
                    "INSERT INTO ".$tableids[$query['tableName']].$query['sql']
                );
        }
        foreach ($selectQueries as $query){
            $queryResults[]=$fusionTablesService->query->sql(
                "SELECT * FROM ".$tableids[$query['tableName']].$query['sql']
            );
        }
        $queryResults[]=$fusionTablesService->query->sql(
            "SELECT * FROM {$tableids['raffles']} JOIN {$tableids['participants']} USING (raffleid) WHERE participantid = '{$person->id}'"
        );
    } catch (Google_Service_Exception $e) {
        $errors[] = $e->getErrors();
    }
}


?><!doctype html>
<html>
<head>
    <title>google plus raffle app user login</title>
    <link 
        href='../../vendor/google/apiclient/examples/styles/style.css' 
        rel='stylesheet' 
        type='text/css' 
    />
</head>
<body>
<div class="box">
    <div class="request">
        <?php if (isset($authUrl)): ?>
            <a class='login' href='<?php echo $authUrl; ?>'>Connect Me!</a>
        <?php else: ?>
            <a class='logout' href='?logout'>Logout</a>
        <?php endif ?>
    </div>

    <?php if (isset($token_data)&&$debug): ?>
        <div class="data">
            <pre>
                <code><?php 
                    var_export($token_data); 
                    var_export($queryResults); 
                    var_export($errors);
                    ?>
                </code>
            </pre>
        </div>
    <?php endif ?>
</div>
</body>
</html>