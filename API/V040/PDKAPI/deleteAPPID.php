<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$APPID = $_POST['appID'];
$VeriCode = $_POST['veriCode'];

if(empty($APPID)){
    generalReturn(true,7,$Language);
}
if(!OPENAPI40\User::checkExist($manageUsername)){
    generalReturn(true,2,$Language);
}
$manageUser = new OPENAPI40\User($manageUsername);
if(!$manageUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}
if(!OPENAPI40\APP::checkExist($APPID)){
    generalReturn(true,4,$Language);
}
$myAPP = new OPENAPI40\APP($APPID);
$isOwner = $manageUsername === $myAPP->getOwnerUsername();
if(!$isOwner){
    if(!$manageUser->checkHasPermission('ModifyAPPIDs')){
        generalReturn(true,8,$Language);
    }
}else{
    if(!$manageUser->checkVeriCode($VeriCode,4)){
        generalReturn(true,1,$Language);
    }
}
if($isOwner){
    $manageUser->deleteRelatedVeriCode();
}
$myAPP.delete();
generalReturn(false,0,$Language);
