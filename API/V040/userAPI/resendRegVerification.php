<?php
require_once __DIR__ . '/../sharedRequirements.php';
$Username = $_POST['userName'];
$Password = $_POST['password'];
if(empty($Username) || empty($Password)){
    generalReturn(true,7,$Language);
}
//Check user exist first.
if(!OPENAPI40\User::checkExist($Username)){
    generalReturn(true,2,$Language);
}
$myUser = new OPENAPI40\User($Username);
//Check if user has already verified his/her email
if($myUser->isMailVerified()){
    generalReturn(true,5,$Language);
}
$myUser->sendEmailVerifyCode($Language);
generalReturn(false,0,$Language);