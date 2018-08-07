<?php
require_once __DIR__ . '../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$Username = $_POST['authingUserName'];
if(!OPENAPI40\User::checkExist($manageUsername) || !OPENAPI\User::checkExist($Username)){
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
$userAuths = OPENAPI40\UserAuth::getAllAuthsByUser($Username);
$overallArray = array();
foreach($userAuths as $singleAuth){
    $overallArray[] = array('appid' => $singleAuth->getAPPID());
    $singleAuthContent = $singleAuth->getAuthContent();
    foreach($singleAuthContent as $everyAuthKey => $everyAuthContent){
        $overallArray[count($overallArray)-1][$everyAuthKey] = $everyAuthContent;
    }
}
generalReturn(false,0,$Language,array('authContent'=>$overallArray));