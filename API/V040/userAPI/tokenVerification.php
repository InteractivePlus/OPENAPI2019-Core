<?php
require_once __DIR__ . '/../sharedRequirements.php';
$Username = $_POST['userName'];
$Token = $_POST['token'];
if(!OPENAPI40\User::checkExist($Username)){
    generalReturn(true,2,$Language);
}
$myUser = new OPENAPI40\User($Username);
if(!$myUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}
generalReturn(false,0,$Language);