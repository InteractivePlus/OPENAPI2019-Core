<?php
require_once __DIR__ . '/../sharedRequirements.php';
$Username = $_POST['userName'];
$appToken = $_POST['appToken'];
$APPID = $_POST['appID'];
$APPPass = $_POST['appPass'];

if(empty($Username) || empty($appToken) || empty($APPID) || empty($APPPass)){
    generalReturn(true,7,$Language);
}
if(!OPENAPI40\User::checkExist($Username)){
    generalReturn(true,2,$Language);
}
if(!OPENAPI40\APP::checkExist($APPID)){
    generalReturn(true,4,$Language);
}
$myAPP = new OPENAPI40\APP($APPID);
if(!$myAPP->checkPassword($APPPass)){
    generalReturn(true,1,$Language);
}
if(!$myAPP->checkAPPToken($IP,$appToken,$Username)){
    generalReturn(true,1,$Language);
}
$myUserAuth = new OPENAPI40\UserAuth($Username,$APPID);
if(!($myAPP->getPermission('accessInfo') && $myUserAuth->getAuthItem('accessInfo'))){
    generalReturn(true,8,$Language);
}
$myUser = new OPENAPI40\User($Username);
$userInfo = array(
    'userDisplayName' => $myUser->getDisplayName()
);
generalReturn(false,0,$Language,array('userInfo'=>$userInfo));
