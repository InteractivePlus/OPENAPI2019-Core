<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$APPID = $_POST['appID'];
$newDisplayName = $_POST['newDisplayName'];

if(empty($APPID) || OPENAPI40\FormatVerify::checkDisplayName($newDisplayName)){
    generalReturn(true,7,$Language);
}
if(!OPENAPI40\User::checkExist($manageUsername)){
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
$UserInAPP = $myAPP->isUserInAPP($Username);
if($manageUsername !== $myAPP->getOwnerUsername() && !$myAPP->isManageUser($manageUsername)){
    if(!$manageUser->checkHasPermission('ModifyAPPIDs')){
        generalReturn(true,8,$Language);
    }
}
$myAPP->setAPPDisplayName($newDisplayName);
generalReturn(false,0,$Language);