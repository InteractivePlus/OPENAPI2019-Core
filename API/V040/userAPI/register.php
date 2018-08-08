<?php
require_once __DIR__ . '/../sharedRequirements.php';
$Username = $_POST['userName'];
$Password = $_POST['password'];
$Email = $_POST['email'];
$Settings = $_POST['settings'];
$DisplayName = $_POST['displayName'];

if(empty($Username) || empty($Password) || empty($Email) || empty($DisplayName)){
    generalReturn(true,7,$Language);
}
//Check if user, password and email fits with format requirements
if(!OPENAPI40\FormatVerify::checkUserName($Username) || !OPENAPI40\FormatVerify::checkPassword($Password) || !OPENAPI40\FormatVerify::checkEmailAddr($Email) || !OPENAPI40\FormatVerify::checkDisplayName($DisplayName)){
    generalReturn(true,7,$Language);
}
//Check if user, email and displayname exists.
if(OPENAPI40\User::checkExist($Username)){
    generalReturn(true,3,$Language);
}
if(OPENAPI40\User::checkNickNameExist($Username)){
    generalReturn(true,6,$Language);
}
if(OPENAPI40\User::checkEmailExist($Email)){
    generalReturn(true,5,$Language);
}
$myUser = OPENAPI40\User::registerUser($Username,$Password,$Email,$DisplayName,!empty($Settings) ? json_decode($Settings,true) : array());
$myUser->sendEmailVerifyCode($Language);
generalReturn(false,0,$Language);