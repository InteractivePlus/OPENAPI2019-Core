<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$Username = $_POST['changingUserName'];
$newDisplayName = $_POST['newDisplayName'];

if(!OPENAPI40\FormatVerify::checkDisplayName($newDisplayName)){
    generalReturn(true,7,$Language);
}

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
if(OPENAPI40\User::checkNickNameExist($newDisplayName)){
    generalReturn(true,6,$Language);
}
$myUser = new OPENAPI40\User($Username);
$myUser->setDisplayName($newDisplayName);
generalReturn(false,0,$Language);