<?php
require_once __DIR__ . '/../sharedRequirements.php';
$manageUsername = $_POST['userName'];
$Token = $_POST['token'];
$searchingUsername = $_POST['findingUser'];

if(empty($searchingUsername)){
    $searchingUsername = '';
}

if(empty($manageUsername) || empty($Token)){
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
$UserArray = array();
$selectedUser = OPENAPI40\User::getUsersBySearching($searchingUsername);
foreach($selectedUser as &$eachUser){
    $UserArray[] = array(
        'username' => $eachUser->getUsername(),
        'displayName' => $eachUser->getDisplayName(),
        'email' => $eachUser->getEmail(),
        'userGroup' => $eachUser->getUserGroup()
    );
}
generalReturn(false,0,$Language,array('users'=>$UserArray));