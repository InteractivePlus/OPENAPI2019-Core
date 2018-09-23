<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$Username = $_POST['changingUserName'];
$NewPermissions = $_POST['newPermission'];
if(empty($manageUsername) || empty($Token) || empty($Username) || empty($NewPermissions)){
    generalReturn(true,7,$Language);
}
if($Username === 'admin'){
    generalReturn(true,8,$Language);
}
if(!OPENAPI40\User::checkExist($manageUsername) || !OPENAPI40\User::checkExist($Username)){
    generalReturn(true,2,$Language);
}
$manageUser = new OPENAPI40\User($manageUsername);
if(!$manageUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}
if(!$manageUser->checkHasPermission('ChangeUserPermissions')){
    generalReturn(true,8,$Language);
}
$myUser = new OPENAPI40\User($Username);
$myUser->updatePermissions(json_decode($NewPermissions,true));
generalReturn(false,0,$Language);