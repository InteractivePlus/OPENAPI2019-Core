<?php
require_once __DIR__ . '../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$Username = $_POST['changingUserName'];
$NewMail = $_POST['newMail'];
$VeriCode = $_POST['veriCode'];

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
}else{
    if(!$manageUser->checkVeriCode($VeriCode,2)){
        generalReturn(true,1,$Language);
    }
}

if(!OPENAPI40\FormatVerify::CheckEmailAddr($NewMail)){
    generalReturn(true,7,$Language);
}

$myUser = new OPENAPI40\User($Username);
$myUser->setEmail($NewMail);
if($manageUsername === $Username){
    $myUser->deleteRelatedVeriCode();
}
generalReturn(false,0,$Language);