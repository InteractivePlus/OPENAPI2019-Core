<?php
require_once __DIR__ . '/../sharedRequirements.php';
$Username = $_POST['userName'];
$Token = $_POST['token'];
$APPID = $_POST['appID'];
$APPIP = $_POST['appIP'];

if(empty($Username) || empty($Token) || empty($APPID)){
    generalReturn(true,7,$Language);
}
if(empty($APPIP)){
    $APPIP = '';
}
if(!OPENAPI40\User::checkExist($Username)){
    generalReturn(true,2,$Language);
}
if(!OPENAPI40\APP::checkExist($APPID)){
    generalReturn(true,4,$Language);
}
if(!OPENAPI40\UserAuth::checkExist($Username,$APPID)){
    generalReturn(true,4,$Language);
}
if(empty($APPID)){
    generalReturn(true,7,$Language);
}
$myUser = new OPENAPI40\User($Username);
if(!$myUser->checkToken($Token)){
    generalReturn(true,1,$Language);
}
$myAPP = new OPENAPI40\APP($APPID);
$newAPPToken = $myAPP->autoAssignAPPToken($APPIP,$Username);
generalReturn(false,0,$Language,array('appToken'=>$newAPPToken,'callBackURL'=>$myAPP->getAPPJumpBackPageURL()));