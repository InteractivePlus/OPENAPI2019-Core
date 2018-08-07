<?php
require_once __DIR__ . '/../sharedRequirements.php';
$Username = $_POST['userName'];
$veriCode = $_POST['veriCode'];
$Password = $_POST['newPassword'];
if(!OPENAPI40\User::checkExist($Username)){
    generalReturn(true,2,$Language);
}
$myUser = new OPENAPI40\User($Username);
if(!$myUser->checkVeriCode($veriCode,1)){
    generalReturn(true,1,$Language);
}
if(!OPENAPI40\FormatVerify::checkPassword($Password)){
    generalReturn(true,7,$Language);
}
$myUser->setPassword($Password);
$myUser->deleteRelatedToken();
$myUser->deleteRelatedVeriCode();
generalReturn(false,0,$Language);