<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$Username = $_POST['changingUserName'];
$Password = $_POST['newPassword'];

if(empty($manageUsername) || empty($Token) || empty($Username) || empty($Password)){
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
}else{ //not admin, return permission error(needs vericode)
    generalReturn(true,8,$Language);
}

if(!OPENAPI40\FormatVerify::checkPassword($Password)){
    generalReturn(true,7,$Language);
}

$myUser = new OPENAPI40\User($Username);
$myUser->setPassword($Password);
$myUser->deleteRelatedToken();
generalReturn(false,0,$Language);
