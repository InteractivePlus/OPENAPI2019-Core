<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$Username = $_POST['viewingUserName'];
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
$myUser = new OPENAPI40\User($Username);
$myAPPs = OPENAPI40\APP::getAPPsOfUser($Username);
$appArray = array();
foreach($myAPPs as &$singleAPP){
    $appArray[] = $singleAPP->getAPPID();
}
$userInfoArray = array(
    'userDisplayName' => $myUser->getDisplayName(),
    'email' => $myUser->getEmail(),
    'settings' => json_encode($myUser->getSettings()),
    'thirdAuth' => json_encode($myUser->getThirdAuths()),
    'userGroup' => json_encode($myUser->getUserGroup()),
    'relatedApps' => $appArray
);
generalReturn(false,0,$Language,array('userInfo'=>$userInfoArray));