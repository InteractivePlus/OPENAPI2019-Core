<?php
require_once __DIR__ . '../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$GroupName = $_POST['groupName'];
$GroupDisplayName = $_POST['newGroupDisplayName'];
if(empty($GroupDisplayName)){
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
if(!OPENAPI40\UserGroup::checkExist($GroupName)){
    generalReturn(true,4,$Language);
}else if(OPENAPI40\UserGroup::checkDisplayNameExist($GroupDisplayName)){
    generalReturn(true,6,$Language);
}
$myUserGroup = new OPENAPI40\UserGroup($GroupName);
$myUserGroup->setDisplayName($GroupDisplayName);
generalReturn(false,0,$Language);