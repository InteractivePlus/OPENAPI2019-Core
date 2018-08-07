<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$Username = $_POST['authingUserName'];
$APPID = $_POST['authingAPPID'];
$Permissions = $_POST['permissions'];
if(!OPENAPI40\User::checkExist($manageUsername) || !OPENAPI40\User::checkExist($Username)){
    generalReturn(true,2,$Language);
}
$manageUser = new OPENAPI40\User($manageUsername);
if(!$manageUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}
if($manageUsername !== $Username){
    if(!$manageUser->checkHasPermission('EditUsers')){
        generalReturn(true,8,$Language);
    }
}

$AuthRecordExist = OPENAPI40\UserAuth::checkExist($Username,$APPID);
if(!$AuthRecordExist && !empty($Permissions)){
    $myAuth = OPENAPI40\UserAuth::createAuthContent($Username,$APPID);
}else if(!$AuthRecordExist && empty($Permissions)){
    generalReturn(false,0,$Language);
}else{
    $myAuth = new OPENAPI40\UserAuth($Username,$APPID);
}

if(empty($Permissions)){
    $myAuth->delete();
}else{
    $myAuth->setAuthContent(json_decode($Permissions,true));
}

generalReturn(false,0,$Language);