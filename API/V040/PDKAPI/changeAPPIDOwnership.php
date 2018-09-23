<?php
require_once __DIR__ . '/../sharedRequirements.php';
$Username = $_POST['userName'];
$Token = $_POST['token'];
$APPID = $_POST['appID'];
$NewOwner = $_POST['newOwner'];

if(empty($Username) || empty($Token) || empty($APPID) || empty($NewOwner)){
    generalReturn(true,7,$Language);
}
if(!OPENAPI40\User::checkExist($Username) || !OPENAPI40\User::checkExist($NewOwner)){
    generalReturn(true,2,$Language);
}
$manageUser = new OPENAPI40\User($Username);
if(!$manageUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}
if(!OPENAPI40\APP::checkExist($APPID)){
    generalReturn(true,4,$Language);
}
$myAPP = new OPENAPI40\APP($APPID);
if(!$myAPP->getOwnerUsername() === $Username){
    if(!$manageUser->checkHasPermission('ModifyAPPIDs')){
        generalReturn(false,8,$Language);
    }
}
$UserInAPP = $myAPP->isUserInAPP($NewOwner);
if($UserInAPP === "PendingUser" || $UserInAPP === "false"){ //UserInAPP !== ManageUser && UserInAPP !== PendingUser
    generalReturn(true,8,$Language);
}
$myAPP->deleteFromBothList($NewOwner);
$OldOwner = $myAPP -> getOwnerUsername();
$myAPP->setOwnerUsername($NewOwner);
$myAPP->addManageUser($OldOwner);


generalReturn(false,0,$Language);