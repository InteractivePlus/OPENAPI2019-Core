<?php
require_once __DIR__ . '/../sharedRequirements.php';
$Username = $_POST['userName'];
$Token = $_POST['token'];
$Action = $_POST['action'];
if(empty($Username) || empty($Token) || empty($Action)){
    generalReturn(true,7,$Language);
}
$ActionID = inval($Action);
if(!OPENAPI40\User::checkActionExist($ActionID)){
    generalReturn(true,7,$Language);
}
$NeedToken = OPENAPI40\User::checkActionNeedToken($ActionID);
if(!OPENAPI40\User::checkExist($Username)){
    generalReturn(true,2,$Language);
}
$myUser = new OPENAPI40\User($Username);
if($NeedToken){
    if(!$myUser->checkToken($Token,$IP)){
        generalReturn(true,1,$Language);
    }
}
$newVeriCode = $myUser->autoAssignNewVeriCode($ActionID);
$myUser->sendSecurityVerifyCode($Language,$newVeriCode,$ActionID);
generalReturn(false,0,$Language);