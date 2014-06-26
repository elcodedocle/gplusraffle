<?php
/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle 
 * management system
 * 
 * Routing/bootstrapping script.
 * 
 * This script handles main controller dependencies and bottom level exceptions.
 * 
 * @package gplusraffle
 * @copyright (c) Gael Abadin 2014
 * @license  MIT Expat
 * @version v0.1.1-beta
 * 
 */
date_default_timezone_set("GMT");

$startTime = microtime(true);
$debug = true;
ob_start();
session_start();
// The only action you can do without being signed in is signing in.
// (or sign out, which does nothing.)
if (
    // not signing in
    (
        !isset($_REQUEST['collection'])
        ||(
            $_REQUEST['collection']!=='user'
            &&$_REQUEST['collection']!=='admin'
        )
        ||(
            $_REQUEST['action']!=='login'
            &&$_REQUEST['action']!=='logout'
        )
    )
    // not signed in
    &&(!isset($_SESSION['access_token'])||$_SESSION['access_token']==='')
){
    ob_end_clean();
    header($_SERVER['SERVER_PROTOCOL'] . ' 401 Authorization Required');
    die("Error 401: Authorization Required");
}

set_include_path(
    "vendor/google/apiclient/src/" . PATH_SEPARATOR . get_include_path()
);

require_once 'vendor/google/apiclient/src/Google/Client.php';
require_once 'vendor/google/apiclient/src/Google/Service/Fusiontables.php';
require_once 'vendor/google/apiclient/src/Google/Service/Plus.php';
require_once 'config.php';
require_once 'MainController.php';
if (isset($_REQUEST['collection'])){
    if ($_REQUEST['collection']==='raffle'){
        require_once 'AdminDAO.php';
        require_once 'User.php';
        require_once 'vendor/elcodedocle/uuid/uuid.php';
        require_once 'RaffleDAO.php';
        require_once 'Raffle.php';
    } else if ($_REQUEST['collection']==='user'){
        require_once 'User.php';
    } else if ($_REQUEST['collection']==='admin'){
        require_once 'AdminDAO.php';
        require_once 'Admin.php';
    }
}

if (!is_string($config['client_id'])||strlen($config['client_id'])<2){
    ob_end_clean();
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error (invalid client_id)');
    die ('Bad config: client_id');
}
if (!is_string($config['client_secret'])||strlen($config['client_secret'])<2){
    ob_end_clean();
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error (invalid client_secret)');
    die ('Bad config: client_secret');
}
if (!is_string($config['redirect_uri'])||!filter_var($config['redirect_uri'],FILTER_VALIDATE_URL)){
    ob_end_clean();
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error (invalid redirect_uri)');
    die ('Bad config: redirect_uri');
}
if (!is_string($config['admin_redirect_uri'])||!filter_var($config['admin_redirect_uri'],FILTER_VALIDATE_URL)){
    ob_end_clean();
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error (invalid admin_redirect_uri)');
    die ('Bad config: admin_redirect_uri');
}

try {
    $main = new MainController(
        $_REQUEST,
        $config['client_id'],
        $config['client_secret'],
        $config['redirect_uri'],
        $config['admin_redirect_uri'],
        isset($_SESSION['access_token'])?$_SESSION['access_token']:null,
        $startTime,
        null,
        null,
        null,
        null,
        null,
        isset($_SESSION['webapp'])?$_SESSION['webapp']:false,
        $debug
    );
    $out = $main->process();
    // on token expiration/revocation/refreshing this should reset the token
    $_SESSION['access_token'] = $main->getAccessToken();
    if (isset($out['loginSuccess'])&&$out['loginSuccess']&&isset($_SESSION['webapp'])&&$_SESSION['webapp']){
        header('Location: webapp/index.php');
        exit;
    }
    if (isset($out['logoutSuccess'])&&$out['logoutSuccess']&&isset($_SESSION['webapp'])&&$_SESSION['webapp']){
        header('Location: main.php?collection=user&action=login');
        exit;
    }
    if (isset($out['html'])) {
        echo $out['html'];
    } else {
        echo json_encode($out);
    }
} catch (Exception $e){
    // on token expiration/revocation/refreshing this should reset the token
    $_SESSION['access_token'] = $main->getAccessToken();
    
    //handle critical errors
    if ($debug){
        error_log($e->getMessage());
        error_log($e->getTraceAsString());
    }
    ob_end_clean();
    //echo json_encode(array('error',$e->getMessage()));
    switch($e->getCode()){
        case 400:
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            die(json_encode(array('error'=>_("400 Bad Request"))));
        case 401:
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Authorization Required');
            die(json_encode(array('error'=>_("401 Authorization Required"))));
        case 403: 
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
            die(json_encode(array('error'=>_("403 Forbidden"))));
        case 404:
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            die(json_encode(array('error'=>_("No matching records found"))));
        case 405: 
            header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
            die(json_encode(array('error'=>_("405 Method Not Allowed"))));
        default:
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            die(json_encode(array('error'=>_("500 Internal Server Error"))));
    }
}
ob_end_flush();
