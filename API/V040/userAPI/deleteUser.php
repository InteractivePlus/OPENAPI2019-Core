<?php
require_once __DIR__ . '/../sharedRequirements.php';
$mangaeUsername = $_POST['userName'];
$Token = $_POST['token'];
$Username = $_POST['deletingUserName'];
$VeriCode = $_POST['verificationCode'];

if(!OPENAPI40\User::checkExist($manageUsername) || !OPENAPI40\User::checkExist($Username)){
    generalReturn(true,2,$Language);
}
$manageUser = new OPENAPI40\User($manageUsername);
if(!$manageUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}
$myUser = new OPENAPI40\User($Username);
if($manageUsername !== $Username){
    if(!$manageUser->checkHasPermission('EditUsers')){
        generalReturn(true,8,$Language);
    }
}else{
    if(!$myUser->checkVeriCode($VeriCode,3)){
        generalReturn(true,1,$Language);
    }
}
$DeleteState = $myUser.delete();
if($DeleteState){
    generalReturn(false,0,$Language);
}else{
    generalReturn(true,8,$Language);
}