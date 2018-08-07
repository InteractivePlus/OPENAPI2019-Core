<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$APPID = $_POST['appID'];
$Username = $_POST['searchUser'];

if(!OPENAPI40\User::checkExist($manageUsername)){
    generalReturn(true,2,$Language);
}
$manageUser = new OPENAPI40\User($manageUsername);
if(!$manageUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}

if($Username !== $manageUsername){
    if(!$manageUser->checkHasPermission('ModifyAPPIDs')){
        generalReturn(true,8,$Language);
    }
}

$mRelatedAPPs = array();
if($Username === $manageUsername){
    $myAPPS = OPENAPI40\APP::getAPPsOfUser($Username);
}else{
    $myAPPS = OPENAPI40\APP::getAPPsBySearching($APPID);
}
foreach($myAPPS as &$eachAPP){
    $mRelatedAPPs[] = array(
        'appID' => $eachAPP->getAPPID(),
        'appDisplayName' => $eachAPP->getAPPDisplayName(),
        'appOwner' => $eachAPP->getOwnerUsername(),
        'loginCallBackURL' => $eachAPP->getAPPJumpBackPageURL(),
        'deleteCallBackURL' => $eachAPP->getUserDeletedCallBackURL()
    );
}
generalReturn(false,0,$Language,array('appIDs'=>$mRelatedAPPs));
