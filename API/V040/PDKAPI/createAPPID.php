<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$Username = $_POST['creatingUserName'];
$APPID = $_POST['appID'];
$APPPass = $_POST['appPass'];
$APPDisplayName = $_POST['appDisplayName'];
if(empty($APPID) || empty($APPPass) || empty($APPDisplayName)){
    generalReturn(true,7,$Language);
}else if(!OPENAPI40\FormatVerify::checkUserName($APPID) || !OPENAPI40\FormatVerify::checkPassword($APPPass) || !OPENAPI40\FormatVerify::checkDisplayName($APPDisplayName)){
    generalReturn(true,7,$Language);
}
if(!OPENAPI40\User::checkExist($manageUsername) || !OPENAPI40\User::checkExist($Username)){
    generalReturn(true,2,$Language);
}
$manageUser = new OPENAPI40\User($manageUsername);
if(!$manageUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}
if($manageUsername !== $Username){
    if(!$manageUser->checkHasPermission('ModifyAPPIDs')){
        generalReturn(true,8,$Language);
    }
}
if(OPENAPI40\APP::checkExist($APPID)){
    generalReturn(true,3,$Language);
}
if(OPENAPI40\APP::checkDisplayNameExist($APPDisplayName)){
    generalReturn(true,6,$Language);
}
$myAPP = OPENAPI40\APP::registerAPP($APPID,$APPPass,$Username,$APPDisplayName);
generalReturn(false,0,$Language);
