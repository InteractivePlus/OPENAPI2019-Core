<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$LogLevel = $_POST['logLevel'];
$LogTime = $_POST['logTime'];

if(empty($manageUsername) || empty($Token) || empty($LogLevel) || empty($LogTime)){
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
OPENAPI40\Log::deleteLogs($LogLevel,'',$LogLevel);
generalReturn(false,0,$Language);