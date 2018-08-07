<?php
require_once '../sharedRequirements.php';
$Username = $_POST['userName'];
$Password = $_POST['password'];
$Email = $_POST['email'];
$Settings = $_POST['settings'];
$DisplayName = $_POST['displayName'];
//Check if user, password and email fits with format requirements
if(!OPENAPI40\FormatVerify::CheckUserName($Username) || !OPENAPI40\FormatVerify::checkPassword($Password) || !OPENAPI40\FOrmatVerify::CheckEmailAddr($Email)){
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
$myUser = OPENAPI40\User::registerUser($Username,json_decode($Settings,true),$Password,$Email,$DisplayName);
generalReturn(false,0,$Language);