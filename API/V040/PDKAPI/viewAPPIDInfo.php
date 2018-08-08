<?php
require_once __DIR__ . '/../sharedRequirements.php';
$Username = $_POST['userName'];
$Token = $_POST['token'];
$APPID = $_POST['appID'];

$showDetailedInfo = false;
if(empty($Username) || empty($Token) || empty($APPID)){
    generalReturn(true,7,$Language);
}
if(!OPENAPI40\User::checkExist($Username)){
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
if($myAPP->isUserInAPP($Username) === "false"){
    if(!$manageUser->checkHasPermission('ModifyAPPIDs')){
        $showDetailedInfo = false;
    }else{
        $showDetailedInfo = true;
    }
}else{
    $showDetailedInfo = true;
}
if($showDetailedInfo){
    $returnRst = array(
        'adminUser' => $myAPP->getOwnerUsername(),
        'appDisplayName' => $myAPP->getAPPDisplayName(),
        'appPermission' => $myAPP->getPermissions(),
        'manageUsers' => $myAPP->getManageUsers(),
        'pendingUsers' => $myAPP->getPendingUsers()
    );
}else{
    $returnRst = array(
        'appDisplayName' => $myAPP->getAPPDisplayName(),
        'appPermission' => $myAPP->getPermissions()
    );
}
generalReturn(false,0,$Language,array('appIDInfo'=>$returnRst));