<?php
require_once __DIR__ . '../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$GroupName = $_POST['groupName'];
$NewGroupName = $_POST['newGroupName'];

if(empty($NewGroupName)){
    $NewGroupName = 'normalUsers';
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

if(!OPENAPI40\UserGroup::checkExist($NewGroupName)){
    generalReturn(true,4,$Language);
}

if(!OPENAPI40\UserGroup::checkExist($GroupName)){
    generalReturn(true,4,$Language);
}
$userArray = OPENAPI40\User::getUsersByGroup($GroupName);
foreach($userArray as &$singleUser){
    $singleUser->setUserGroup($NewGroupName);
}
$myUserGroup = new OPENAPI40\UserGroup($GroupName);
$myUserGroup.delete();
generalReturn(false,0,$Language);