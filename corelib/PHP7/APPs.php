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
            deleteFromUser($this->getOwnerUsername());
            $MangageUsers = $this->getManageUsers();
            foreach($MangageUsers as $SingleManager){
                deleteFromUser($SingleManager);
            }   
            $PendingUsers = $this->getPendingUsers();
            foreach($PendingUsers as $SinglePending){
                deleteFromUser($SinglePending);
            }
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'apps',array('appid'=>$this->m_APPID));
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'userauth',array('appid'=>$this->m_APPID));
        }
        protected function addToUser($Username) : void{
            $ownerRow = \BoostPHP\MYSQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('username'=>$Username));
            if($ownerRow['count'] < 1){
                return;
            }
            $dataRow = &$ownerRow['result'][0];
            $ownerRelatedAPPJSON = gzuncompress($dataRow['relatedapps']);
            $ownerRelatedAPPs = json_decode($ownerRelatedAPPJSON,true);
            $ownerRelatedAPPs[count($ownerRelatedAPPs)] = $this->m_APPID;
            $ownerRelatedAPPJSON = json_encode($ownerRelatedAPPs);
            $dataRow['relatedapps'] = gzcompress($ownerRelatedAPPJSON,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            \BoostPHP\MySQL::updateRows(Internal::$MySQLiConn, 'users', array('relatedapps'=>$dataRow['relatedapps']), array('username'=>$Username));
        }

        protected function deleteFromUser($Username) : void{
            $ownerRow = \BoostPHP\MYSQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('username'=>$Username));
            if($ownerRow['count'] < 1){
                return;
            }
            $dataRow = &$ownerRow['result'][0];
            $ownerRelatedAPPJSON = gzuncompress($dataRow['relatedapps']);
            $ownerRelatedAPPs = json_decode($ownerRelatedAPPJSON,true);
            foreach($ownerRelatedAPPs as &$SingleRelatedApps){
                if($SingleRelatedApps === $this->m_APPID){
                    unset($SingleRelatedApps);
                }
            }
            $ownerRelatedAPPJSON = json_encode($ownerRelatedAPPs);
            $dataRow['relatedapps'] = gzcompress($ownerRelatedAPPJSON,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            \BoostPHP\MySQL::updateRows(Internal::$MySQLiConn, 'users', array('relatedapps'=>$dataRow['relatedapps']), array('username'=>$Username));
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
        public function getPermissions() : array{
            return json_decode(gzuncompress($this->m_APPRow['apppermission']),true);
        }
        public function setPermissions(array $newPermission) : void{
            foreach($newPermission as $SinglePermissionKey => &$SinglePermission){
                $CanFind = false;
                foreach($GLOBALS['OPENAPISettings']['Fieldnames']['APPPermission'] as $PermField){
                    if($PermField === $SinglePermissionKey){
                        $CanFind = true;
                        break;
                    }
                }
                if(!$CanFind){
                    unset($SinglePermission);
                }
            }
            $this->m_APPID['apppermission'] = gzcompress(json_encode($newPermission),$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
        }
        public function updatePermissions(array $newPermission) : void{
            $OldPermission = $this->getPermissions();
            foreach($newPermission as $SinglePermissionKey => $SinglePermission){
                $OldPermission[$SinglePermissionKey] = $SinglePermission;
            }
            $this->setPermissions($OldPermission);
        }
        public function getPermission(string $permissionItem) : bool{
            $Permissions = $this->getPermissions();
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
            $Permissions = $this->getPermissions();
            $Permissions[$permissionItem] = ($newValue ? 'true' : 'false');
            $this->setPermissions($Permissions);
        }

        public function getOwnerUsername() : string{
            return $this->m_APPRow['adminuser'];
        }

        public function setOwnerUsername(string $newOwner) : void{
            $this->deleteFromUser($this->getOwnerUsername);
            $this->addToUser($newOwner);
            $this->m_APPRow['adminuser'] = $newOwner;
            $this->submitRowInfo();
        }

        public function getManageUsers() : array{
            $ManageUsersJSON = gzuncompress($this->m_APPRow['manageusers']);
            $ManageUsers = json_decode($ManageUsersJSON,true);
            return $ManageUsers;
        }

        protected function setManageUsers(array $newManageList) : void{
            $ManageUsersJSON = json_encode($newManageList);
            $this->m_APPRow['manageusers'] = gzcompress($ManageUsersJSON,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
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
            $this->addToUser($Username);
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
            $this->deleteFromUser($Username);
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

        protected function setPendingUsers(array $newPendingUserList) : void{
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
            $this->addToUser($Username);
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
            $this->deleteFromUser($Username);
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

        public function sendThirdPartyEmail(string $toAddr, string $mailTitle, string $mailBody, string $Language) : bool{
            if($Language !== 'cn' && $Language !== 'en'){
                $Language = 'x-default';
            }

            $EmailTemplate = $GLOBALS['OPENAPISettings']['Email']['ThirdPartyMail'][$Language];
            $EmailTemplate['body'] = \str_replace('`appDisplayName`',$this->getAPPDisplayName(),$EmailTemplate['body']);
            $EmailTemplate['body'] = \str_replace('`appID`', $this->getAPPID(), $EmailTemplate['body']);
            $EmailTemplate['body'] = \str_replace('`thirdPartyMailBody`', $mailBody, $EmailTemplate['body']);
            $EmailTemplate['body'] = $GLOBALS['OPENAPISettings']['Email']['SharedTop'][$Language] . $EmailTemplate['body'] . $GLOBALS['OPENAPISettings']['Email']['SharedBottom'][$Language];
            $EmailTemplate['title'] = \str_replace('`appDisplayName`',$this->getAPPDisplayName(),$EmailTemplate['title']);
            $EmailTemplate['title'] = \str_replace('`appID`', $this->getAPPID(), $EmailTemplate['title']);
            $EmailTemplate['title'] = \str_replace('`thirdPartyMailTitle`',$mailTitle,$EmailTemplate['title']);
            return \BoostPHP\Mail::sendMail(
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPPort'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPHost'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPUser'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPPassword'],
                $toAddr,
                $EmailTemplate['title'],
                $EmailTemplate['body'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPSenderAddress'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPSenderName'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPSecureConnection']
            );
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
            if($APPIP !== $mTokenRow['tokenip'] && !empty($mTokenRow['tokenip'])){
                if(!$GLOBALS['OPENAPISettings']['APPTokenAvailableAfterIPChange'])
                    return false;
            }
            if($GLOBALS['OPENAPISettings']['RenewAPPTokenWhenChecking']){
                $this->renewRelatedToken($Username);
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

        public function renewRelatedToken(string $Username) : void{
            \BoostPHP\MySQL::updateRows(Internal::$MySQLiConn, 'apptokens', array('starttime'=>time()), array('relatedapp'=>$this->m_APPID, 'relateduser'=>$Username));
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
            $UserDataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('username'=>$Username));
            if($UserDataRow['count'] < 1){
                throw new Exception("Non-existence user");
                return array();
            }
            $SingleUserRow = &$UserDataRow['result'][0];
            $UserRelatedAPPs = json_decode(gzuncompress($SingleUserRow['relatedapps']),true);
            $Apps = array();
            foreach($UserRelatedAPPs as &$SingleRelatedAPP){
                $Apps[count($Apps)] = new APP($SingleRelatedAPP);
            }
            return $Apps;
        }

        public static function getAPPsBySearching(string $APPID = '') : array{
            $mDataArray = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'apps');
            if($mDataArray['count']<1){
                return array();
            }
            $SearchRst = array();
            foreach($mDataArray['result'] as &$SingleRow){
                if(empty($APPID) || strpos($SingleRow['appid'],$APPID) !== false){
                    $SearchRst[count($SearchRst)] = new APP($SingleRow['appid']);
                }
            }
            return $SearchRst;
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
            if(self::checkExist($DisplayName)){
                throw new Exception("Existence displayname");
                return null;
            }
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
            $myAPP = new APP($APPID);
            $myAPP->addToUser($adminUser);
            return $myAPP;
        }
    }
}