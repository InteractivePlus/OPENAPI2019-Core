<?php
require_once __DIR__ . '../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$searchingGroupName = $_POST['searchGroupName'];
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
$returnGroups = array();
$groupsGot = OPENAPI40\UserGroup::getGroupsBySearching($searchingGroupName);
foreach($groupsGot as &$singleGroup){
    $returnGroups[] = array(
        'groupName' => $singleGroup->getGroupName(),
        'groupDisplayName'=> $singleGroup->getDisplayName()
    );
}
generalReturn(false,0,$Language,array('groups'=>$returnGroups));