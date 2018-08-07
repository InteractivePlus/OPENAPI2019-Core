<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$APPID = $_POST['appID'];
$newPermission = $_POST['newPermission'];

if(empty($APPID) || empty($newPermission)){
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
if(!$myAPP->getOwnerUsername() === $manageUsername && !$myAPP->isManageUser($manageUsername)){
    if(!$manageUser->checkHasPermission('ModifyAPPIDs')){
        generalReturn(true,8,$Language);
    }
}
$myAPP->updatePermissions(json_decode($newPermission,true));
generalReturn(true,4,$Language);