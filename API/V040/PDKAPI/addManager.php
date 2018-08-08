<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$APPID = $_POST['appID'];
$Username = $_POST['newManager'];

if(empty($manageUsername) || empty($Token) || empty($APPID) || empty($Username)){
    generalReturn(true,7,$Language);
}
if(!OPENAPI40\User::checkExist($Username) || !OPENAPI40\User::checkExist($manageUsername)){
    generalReturn(true,2,$Language);
}
$manageUser = new OPENAPI40\User($manageUsername);
if(!$manageUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}
if(!OPENAPI40\APP::checkExist($APPID)){
    generalReturn(true,4,$Language);
}
$myAPP = new OPENAPI40\APP($APPID);
if(!$myAPP->getOwnerUsername() === $Username){
    if(!$manageUser->checkHasPermission('ModifyAPPIDs')){
        generalReturn(true,8,$Language);
    }
}
if($myAPP->isUserInAPP($Username) !== "false"){
    generalReturn(true,3,$Language);
}
$myAPP->addPendingUser($Username);
generalReturn(false,0,$Language);