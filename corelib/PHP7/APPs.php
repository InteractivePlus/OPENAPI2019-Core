<?php
namespace OPENAPI40{
    require_once __DIR__ . '/internal/OPENAPI.internal.php';
    class APP{
        protected $m_APPID = '';
        protected $m_APPRow = array(
            'appid' => '',
            'appdisplayname' => '',
            'apppass' => '',
            'apppermission' => '',
            'adminuser' => '',
            'manageusers' => '',
            'pendingusers' => '',
            'appjumpbackpage' => '',
            'userdeletedcallback' => '',
        );
        protected function updateRowInfo() : void{
            $mDataArray = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'apps', array('appid'=>$this->m_APPID));
            if($mDataArray['count']<1){
                throw new Exception('Non-existence user');
                return;
            }
            $this->m_APPRow = $mDataArray['result'][0];
        }
        protected function submitRowInfo() : bool{
            $mSubmitState = \BoostPHP\MySQL::updateRows(Internal::$MySQLiConn,'users',$this->m_APPRow,array('appid'=>$this->m_APPID));
            return $mSubmitState;
        }
        public function __construct(string $APPID){
            if(!self::checkExist($APPID)){
                throw new Exception('Non-existence user');
                return;
            }
            $this->m_APPID = $APPID;
            $this->updateRowInfo();
        }
        public function delete() : void{
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'apps',array('appid'=>$this->m_APPID));
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'userauth',array('appid'=>$this->m_APPID));
        }
        public function getAPPID() : string{
            return $this->m_APPID;
        }
        public function setAPPID(string $newAPPID) : void{
            $this->m_APPRow['appid'] = $newAPPID;
            $this->submitRowInfo();
            $this->m_APPID = $newAPPID;
        }
        public function getAPPDisplayName() : string{
            return $this->m_APPRow['appdisplayname'];
        }
        public function setAPPDisplayName(string $newDisplayName) : void{
            $this->m_APPRow['appdisplayname'] = $newDisplayName;
            $this->submitRowInfo();
        }
        public function checkPassword(string $Password) : bool{
            if(self::encryptAPPPass($Password) === $this->m_APPRow['apppass']){
                return true;
            }else{
                return false;
            }
        }
        public function setPassword(string $newPassword) : void{
            $this->m_APPRow['apppass'] = self::encryptAPPPass($newPassword);
            $this->submitRowInfo();
        }
        public function getPermissionJSON() : string{
            return gzuncompress($this->m_APPRow['apppermission']);
        }
        public function setPermissionJSON(string $newJSON) : void{
            $this->m_APPID['apppermission'] = gzcompress($newJSON,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
        }
        public function getPermission(string $permissionItem) : bool{
            $PermissionJSON = $this->getPermission();
            $Permissions = json_decode($PermissionJSON,true);
            unset($PermissionJSON);
            if(!empty($Permissions[$permissionItem])){
                if($Permissions[$permissionItem] === 'true'){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
        public function setPermission(string $permissionItem, bool $newValue) : void{
            $PermissionJSON = $this->getPermission();
            $Permissions = json_decode($PermissionJSON,true);
            unset($PermissionJSON);
            $Permissions[$permissionItem] = ($newValue ? 'true' : 'false');
            $PermissionJSON = json_encode($Permissions);
            $this->setPermissionJSON($PermissionJSON);
        }

        public function getOwnerUsername() : string{
            return $this->m_APPRow['adminuser'];
        }

        public function setOwnerUsername(string $newOwner) : void{
            $this->m_APPRow['adminuser'] = $newOwner;
        }

        public function getManageUsers() : array{
            $ManageUsersJSON = gzuncompress($this->m_APPRow['manageusers']);
            $ManageUsers = json_decode($ManageUsersJSON,true);
            return $ManageUsers;
        }

        public function setManageUsers(array $newManageList) : void{
            $ManageUsersJSON = json_encode($newManageList);
            $this->m_APPRow['manageusers'] = gzcompress($ManageUsersJSON,$GLOBALS['OPENAPISettings']['CompressIntensity']);
        }

        public function addManageUser(string $Username) : void{
            //Check if user exists first.
            $UserRowCount = \BoostPHP\MySQL::checkExist(Internal::$MySQLiConn, 'users', array('username'=>$Username));
            if($UserRowCount < 1){
                throw new Exception('Non-existence user');
                return;
            }
            $OriginalUser = $this->getManageUsers();
            $OriginalUser[count($OriginalUser)] = $Username;
            $this->setManageUsers($OriginalUser);
            return;
        }

        public function deleteManageUser(string $Username) : void{
            $OriginalUser = $this->getManageUsers();
            for($i = 0; $i < count($OriginalUser); $i++){
                $ManagerSingle = &$OriginalUser[$i];
                if($ManagerSingle === $Username){
                    unset($ManagerSingle);
                    break;
                }
            }
            $this->setManageUsers($OriginalUser);
        }

        public function isManageUser(string $Username) : bool{
            $ManageUsers = $this->getManageUsers();
            foreach($ManageUsers as $SingleManager){
                if($Username === $SingleManager){
                    return true;
                }
            }
            return false;
        }

        public function getPendingUsers() : array{
            $PendingUserJSON = gzuncompress($this->m_APPRow['pendingusers']);
            $PendingUsers = json_decode($PendingUserJSON,true);
            return $PendingUsers;
        }

        public function setPendingUsers(array $newPendingUserList) : void{
            $PendingUserJSON = json_encode($newPendingUserList);
            $this->m_APPRow['pendingusers'] = gzcompress($PendingUserJSON,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
        }

        public function addPendingUser(string $Username) : void{
            $UserRowCount = \BoostPHP\MySQL::checkExist(Internal::$MySQLiConn, 'users', array('username'=>$Username));
            if($UserRowCount < 1){
                throw new Exception('Non-existence user');
                return;
            }
            $OriginalUser = $this->getPendingUsers();
            $OriginalUser[count($OriginalUser)] = $Username;
            $this->setPendingUsers($OriginalUser);
            return;
        }

        public function deletePendingUser(string $Username) : void{
            $OriginalUser = $this->getPendingUsers();
            for($i = 0; $i < count($OriginalUser); $i++){
                $PendingSingle = &$OriginalUser[$i];
                if($PendingSingle === $Username){
                    unset($PendingSingle);
                    break;
                }
            }
            $this->setPendingUsers($OriginalUser);
        }

        public function isPendingUser(string $Username) : bool{
            $PendingUsers = $this->getPending();
            foreach($PendingUsers as $SinglePending){
                if($Username === $SinglePending){
                    return true;
                }
            }
            return false;
        }

        public function isUserInAPP(string $Username) : string{
            if($this->isManageUser($Username)){
                return "ManageUser";
            }else if($this->isPendingUser($Username)){
                return "PendingUser";
            }else if($this->getOwnerUsername() === $Username){
                return "Owner";
            }else{
                return "false";
            }
        }

        public function getAPPJumpBackPageURL() : string{
            return $this->m_APPRow['appjumpbackpage'];
        }

        public function setAPPJumpBackPageURL(string $newURL) : void{
            $this->m_APPRow['appjumpbackpage'] = $newURL;
            $this->submitRowInfo();
        }

        public function getUserDeletedCallBackURL() : string{
            return $this->m_APPRow['userdeletedcallback'];
        }

        public function setUserDeletedCallBackURL(string $newURL) : void{
            $this->m_APPRow['userdeletedcallback'] = $newURL;
            $this->submitRowInfo();
        }

        public function callUserDeletedURL($Username) : void{
            $callingURL = $this->getUserDeletedCallBackURL();
            $callingParam = array('deletedUser'=>$Username);
            \BoostPHP\GeneralUtility::postToAddr($callingURL,$callingParam);
        }

        public function deleteFromBothList(string $Username) : void{
            $this->deleteManageUser($Username);
            $this->deletePendingUser($Username);
        }

        public function checkAPPToken(string $APPIP,string $Token, string $Username) : bool{
            $TokenList = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn,'apptokens',array('relateduser'=>$Username, 'relatedapp'=>$this->m_APPID));
            if($TokenList['count'] < 1){
                return false;
            }
            $mTokenRow = $TokenList['result'][0];
            if(time() - $mTokenRow['starttime'] > $GLOBALS['OPENAPISettings']['APPTokenAvailableDuration']){
                $this->deleteRelatedToken($Username);
                return false;
            }
            if($APPIP !== $mTokenRow['tokenip']){
                if(!$GLOBALS['OPENAPISettings']['APPTokenAvailableAfterIPChange'])
                    return false;
            }
            return true;
        }

        public function autoAssignAPPToken(string $APPIP, string $Username) : string{
            $newToken = self::generateAPPToken($this->m_APPID);
            $this->assignAPPToken($APPIP,$Username,$newToken);
            return $newToken;
        }

        public function assignAPPToken(string $APPIP, string $Username, string $APPToken) : void{
            $this->deleteRelatedToken($Username);
            \BoostPHP\MySQL::insertRow(Internal::$MySQLiConn, 'apptokens', array('token'=>$APPToken, 'starttime'=>time(), 'relateduser'=>$Username, 'relatedapp'=>$this->m_APPID, 'tokenip'=>$APPIP));
        }

        public function deleteAllRelatedToken() : void{
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'apptokens',array('relatedapp'=>$this->m_APPID));
        }

        public function deleteRelatedToken(string $Username) : void{
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'apptokens',array('relatedapp'=>$this->m_APPID,'relateduser'=>$Username));
        }

        public static function checkExist(string $APPID) : bool{
            $APPRowCount = \BoostPHP\MySQL::checkExist(Internal::$MySQLiConn, 'apps', array('appid'=>$APPID));
            if($UserRowCount > 0){
                return true;
            }else{
                return false;
            }
        }
        public static function generateAPPToken(string $APPID) : string{
            return md5(\BoostPHP\Encryption\SHA::SHA256Encode($APPID . rand(0,10000) . time(),$GLOBALS['OPENAPISettings']['Salt']));
        }
        protected static function encryptAPPPass(string $PasswordRaw) : string{
            return md5(\BoostPHP\Encryption\SHA::SHA256Encode($PasswordRaw,$GLOBALS['OPENAPISettings']['Salt']));
        }
        public static function getAPPsIfOwner(string $Username) : array{
            $mDataArray = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'apps', array('adminuser'=>$Username));
            if($mDataArray['count']<1){
                return array();
            }else{
                $mRstArray = array();
                foreach($mDataArray['result'] as $SingleAPPRow){
                    $mRstArray[count($mRstArray)] = new APP($SingleAPPRow['appid']);
                }
                return $mRstArray;
            }
        }
        public static function getAPPsOfUser(string $Username) : array{
            $mDataArray = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'apps');
            if($mDataArray['count']<1){
                return array();
            }else{
                $mRstArray = array();
                foreach($mDataArray['result'] as $SingleAPPRow){
                    $tmpAPP = new APP($SingleAPPRow['appid']);
                    if($tmpAPP->isUserInAPP($Username) !== "false"){
                        $mRstArray[count($mRstArray)] = $tmpAPP;
                    }
                    unset($tmpAPP);
                }
            }
        }
        public static function checkDisplayNameExist(string $DisplayName) : bool{
            $NickNameDataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'apps', array('appdisplayname'=>$DisplayName));
            if($NickNameDataRow['count'] < 1){
                return false;
            }else{
                return true;
            }
        }
        public static function getAPPByDisplayName(string $DisplayName) : APP{
            $NickNameDataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'apps', array('appdisplayname'=>$DisplayName));
            if($NickNameDataRow['count'] < 1){
                throw new Exception('Non-existence user');
                return null;
            }
            $RelatedAPP = $NickNameDataRow['result'][0]['appid'];
            return new APP($RelatedAPP);
        }
        public static function registerAPP(string $APPID, string $APPPass, string $adminUser, string $DisplayName = '') : APP{
            if(self::checkExist($APPID)){
                throw new Exception("Existence user");
                return null;
            }
            if(empty($DisplayName))
                $DisplayName = $APPID;
            $NewAPPRow = array(
                'appid' => $APPID,
                'appdisplayname' => $DisplayName,
                'apppass' => self::encryptAPPPass($APPPass),
                'apppermission'=>$GLOBALS['OPENAPISettings']['APP']['defaultValues']['apppermission'],
                'adminuser' => $adminUser,
                'manageusers' => $GLOBALS['OPENAPISettings']['APP']['defaultValues']['manageusers'],
                'pendingusers' => $GLOBALS['OPENAPISettings']['APP']['defaultValues']['pendingusers'],
                'appjumpbackpage' => $GLOBALS['OPENAPISettings']['APP']['defaultValues']['appjumpbackpage'],
                'userdeletedcallback' => $GLOABLS['OPENAPISettings']['APP']['defaultValues']['userdeletedcallback']
            );
            \BoostPHP\MySQL::insertRow(Internal::$MySQLiConn,'apps',$NewAPPRow);
            return new APP($APPID);
        }
    }
}