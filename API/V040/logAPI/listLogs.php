<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$minimumLevel = $_POST['minimumLevel'];

if(empty($manageUsername) || empty($Token) || empty($minimumLevel)){
    generalReturn(true,7,$Language);
}
if($minimumLevel < 1 || minimumLevel > 5){
    generalReturn(true,7,$Language);
}
if(!OPENAPI40\User::checkExist($manageUsername)){
    generalReturn(true,2,$Language);
}
$manageUser = new OPENAPI40\User($manageUsername);
if(!$manageUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}
if(!$manageUser->checkHasPermission('ViewLogs')){
    generalReturn(true,8,$Language);
}
$LogsArray = OPENAPI40\Log::listAllLogs($minimumLevel);
$ReturnRst = array();
foreach($LogsArray as $SingleLog){
    $ReturnRst[] = array(
        'logLevel' => $SingleLog['loglevel'],
        'logContent' => $SingleLog['logcontent'],
        'logTime' => $SingleLog['logtime']
    );
}
generalReturn(false,0,$Language,array('logs'=>$ReturnRst));