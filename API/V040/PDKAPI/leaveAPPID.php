<?php
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$APPID = $_POST['appID'];
$Username = $_POST['deletingUser'];

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
$UserInAPP = $myAPP->isUserInAPP($Username);
if($manageUsername !== $myAPP->getOwnerUsername() && !($manageUser === $Username && $UserInAPP !== "false")){
    if(!$manageUser->checkHasPermission('ModifyAPPIDs')){
        generalReturn(true,8,$Language);
    }
}
switch($UserInAPP){
    case "ManageUser":
        $myAPP->deleteManageUser($Username);
        break;
    case "PendingUser":
        $myAPP->deletePendingUser($Username);
        break;
    case "Owner":
        generalReturn(true, 8, $Language);
        break;
    default:
        generalReturn(true,500,$Language);
}
generalReturn(false,0,$Language);