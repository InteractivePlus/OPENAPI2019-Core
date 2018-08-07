<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$GroupName = $_POST['groupName'];
$GroupDisplayName = $_POST['groupDisplayName'];
if(!OPENAPI40\FormatVerify::checkUserName($GroupName) || !OPENAPI40\FormatVerify::checkDisplayName($GroupDisplayName)){
    generalReturn(true,7,$Language);
}
if(!OPENAPI40\User::checkExist($manageUsername)){
    generalReturn(true,2,$Language);
}
$manageUser = new OPENAPI40\User($manageUsername);
if(!$manageUser->checkToken($Token,$IP)){
    generalReturn(true,1,$Language);
}
if(!$manageUser->checkHasPermission('ManageUserGroups')){
    generalReturn(true,8,$Language);
}
if(OPENAPI40\UserGroup::checkExist($GroupName)){
    generalReturn(true,3,$Language);
}else if(OPENAPI40\UserGroup::checkDisplayNameExist($GroupDisplayName)){
    generalReturn(true,6,$Language);
}
$myUserGroup = OPENAPI40\UserGroup::createGroup($GroupName,$GroupDisplayName);
generalReturn(false,0,$Language);