<?php
namespace OPENAPI40{
    require_once __DIR__ . '/internal/OPENAPI.internal.php';
    require_once __DIR__ . '/APPs.php';
    require_once __DIR__ . '/UserAuth.php';
    class User{
        protected $m_Username = '';
        protected $m_UserRow = array(
            'username' => '',
            'userdisplayname' => '',
            'password' => '',
            'email' => '',
            'settings' => '',
            'thirdauth' => '',
            'emailverified'=>false,
            'emailverifycode'=>'',
            'userpermission'=>'',
            'usergroup'=>'',
            'regtime'=>0
        );
        protected function updateRowInfo() : void{
            $mDataArray = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('username'=>$this->m_Username));
            if($mDataArray['count']<1){
                throw new Exception('Non-existence user');
                return;
            }
            $this->m_UserRow = $mDataArray['result'][0];
        }
        protected function submitRowInfo() : bool{
            $mSubmitState = \BoostPHP\MySQL::updateRows(Internal::$MySQLiConn,'users',$this->m_UserRow,array('username'=>$this->m_Username));
            return $mSubmitState;
        }
        public function __construct(string $Username){
            if(!self::checkExist($Username)){
                throw new Exception('Non-existence user');
                return;
            }
            $this->m_Username = $Username;
            $this->updateRowInfo();
        }
        public function delete() : bool{
            $UserOwnedAPPs = APP::getAPPsIfOwner($this->m_Username);
            if(!empty($UserOwnedAPPs)){
                return false;
            }

            $UserManagedAPPs = APP::getAPPsOfUser($this->m_Username);
            foreach($UserManagedAPPs as &$SingleManagedAPPs){
                $SingleManagedAPPs->deleteFromBothList($this->m_Username);
            }
            $UserAuthedAPPs = UserAuth::getAllAuthsByUser($this->m_Username);
            foreach($UserAuthedAPPs as &$SingleAuthedAPPs){
                $SingleAuthedAPPs->delete();
            }

            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'users',array('username' => $Username));
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'tokens',array('relateduser'=>$Username));
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'apptokens', array('relateduser'=>$Username));
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'verificationcodes',array('username'=>$Username));
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'userauth',array('username'=>$Username));
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'apps',array('adminuser'=>$Username));
        }

        public function getUsername() : string{
            return $this->m_Username;
        }

        public function setUsername(string $newUsername){
            $this->m_UserRow['username'] = $newUsername;
            $this->submitRowInfo();
            $this->m_Username = $newUsername;
        }

        public function checkPassword(string $PasswordRaw) : bool{
            if(self::encryptPassword($PasswordRaw) === $this->m_UserRow['password']){
                return true;
            }else{
                return false;
            }
        }

        public function setPassword(string $newPassword) : void{
            $this->m_UserRow['password'] = md5(\BoostPHP\BoostPHP\Encryption\SHA::SHA256Encode($newPassword,$GLOBALS['OPENAPISettings']['Salt']));
            $this->submitRowInfo();
        }

        public function checkToken(string $Token, string $UserIP) : bool{
            $TokenList = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn,'tokens',array('relateduser'=>$this->m_Username));
            if($TokenList['count'] < 1){
                return false;
            }
            $mTokenRow = $TokenList['result'][0];
            if(time() - $mTokenRow['starttime'] > $GLOBALS['OPENAPISettings']['TokenAvailableDuration']){
                $this->deleteRelatedToken();
                return false;
            }
            if($UserIP !== $mTokenRow['tokenip']){
                if(!$GLOBALS['OPENAPISettings']['TokenAvailableAfterIPChange'])
                    return false;
            }
            return true;
        }

        public function autoAssignNewToken(string $UserIP) : string{
            $newToken = self::generateToken($this->m_Username);
            $this->assignNewToken($UserIP,$newToken);
            return $newToken;
        }

        public function assignNewToken(string $UserIP, string $newToken) : void{
            $this->deleteRelatedToken();
            \BoostPHP\MySQL::insertRow(Internal::$MySQLiConn,'tokens',array('token'=>$newToken,'starttime'=>time(),'relateduser'=>$this->m_Username,'tokenip'=>$UserIP));
            return;
        }

        public function deleteRelatedToken() : void{
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'tokens',array('relateduser'=>$this->m_Username));
        }

        public function getDisplayName() : string{
            return $this->m_UserRow['userdisplayname'];
        }

        public function setDisplayName(string $newDisplayName) : void{
            $this->m_UserRow['userdisplayname'] = $newDisplayName;
            $this->submitRowInfo();
        }

        public function getEmail() : string{
            return $this->m_UserRow['email'];
        }

        public function setEmail(string $newMail) : void{
            $this->m_UserRow['email'] = $newMail;
            $this->submitRowInfo();
            return;
        }

        public function getSettingsJSON() : string{
            return gzuncompress($this->m_UserRow['settings']);
        }

        public function setSettingsJSON(string $newSettings) : void{
            $this->m_UserRow['settings'] = gzcompress($newSettings,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
        }

        public function getSetting(string $settingItem){
            $SettingJSON = $this->getSettingsJSON();
            unset($SettingJSON);
            $Settings = json_decode($SettingJSON,true);
            if(!empty($Settings[$settingItem])){
                return $Settings[$settingItem];
            }else{
                return null;
            }
        }

        public function setSetting(string $settingItem, $value) : void{
            $SettingJSON = $this->getSettingsJSON();
            unset($SettingJSON);
            $Settings = json_decode($SettingJSON,true);
            $Settings[$settingItem] = $value;
            $SettingJSON = json_encode($Settings);
            $this->setSettingsJSON($SettingJSON);
        }

        public function getThirdAuthJSON() : string{
            return gzuncompress($this->m_UserRow['thirdauth']);
        }

        public function setThirdAuthJSON(string $newThirdAuth) : void{
            $this->m_UserRow['thirdauth'] = gzcompress($newThirdAuth,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
        }

        public function getThirdAuth(string $authName) : array{
            $ThirdAuthJSON = $this->getThirdAuthJSON();
            $ThirdAuths = json_decode($ThirdAuthJSON,true);
            unset($ThirdAuthJSON);
            if(!empty($ThirdAuths[$authName])){
                return $ThirdAuths[$authName];
            }else{
                return array();
            }
        }

        public function setThirdAuth(string $authName, array $authValue) : void{
            $ThirdAuthJSON = $this->getThirdAuthJSON();
            $ThirdAuths = json_decode($ThirdAuthJSON,true);
            unset($ThirdAuthJSON);
            $ThirdAuths[$authName] = $authValue;
            $ThirdAuthJSON = json_encode($ThirdAuths);
            $this->setThirdAuthJSON($ThirdAuthJSON);
        }

        public function deleteThirdAuth(string $authName) : void{
            $ThirdAuthJSON = $this->getThirdAuthJSON();
            $ThirdAuths = json_decode($ThirdAuthJSON,true);
            unset($ThirdAuthJSON);
            unset($ThirdAuths[$authName]);
            $ThirdAuthJSON = json_encode($ThirdAuths);
            $this->setThirdAuthJSON($ThirdAuthJSON);
        }

        public function getPermissionJSON() : string{
            return gzuncompress($this->m_UserRow['userpermission']);
        }

        public function setPermissionJSON(string $newPermissionJSON) : void{
            $this->m_UserRow['userpermission'] = gzcompress($newPermissionJSON,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
        }

        public function getPermission(string $permissionType) : bool{
            $PermissionJSON = $this->getPermissionJSON();
            $Permissions = json_decode($PermissionJSON,true);
            unset($PermissionJSON);
            if(!empty($Permissions[$permissionType])){
                if($Permissions[$permissionType] === 'true'){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }

        public function setPermission(string $permissionType, bool $isPermissionAllowed) : void{
            $PermissionJSON = $this->getPermissionJSON();
            $Permissions = json_decode($PermissionJSON,true);
            unset($PermissionJSON);
            $Permissions[$permissionType] = $isPermissionAllowed ? 'true' : 'false';
            $PermissionJSON = json_encode($Permissions);
            $this->setPermissionJSON($PermissionJSON);
        }

        public function getUserGroup() : string{
            return $this->m_UserRow['usergroup'];
        }

        public function setUserGroup(string $newGroup) : void{
            $this->m_UserRow['usergroup'] = $newGroup;
            $this->submitRowInfo();
        }

        public function isMailVerified() : bool{
            return ($this->m_UserRow['emailverified'] == 1 ? true : false);
        }

        public function setEmailVerifyStatus(bool $isVerified) : void{
            $this->m_UserRow['emailverified'] = $isVerified ? 1 : 0;
            $this->submitRowInfo();
        }

        public function checkEmailVerifyCode(string $VerifyCode) : bool{
            if($this->m_UserRow['emailverifycode'] === $VerifyCode){
                return true;
            }else{
                return false;
            }
        }

        public function setEmailVerifyCode(string $newVerifyCode) : void{
            $this->m_UserRow['emailverifycode'] = $newVerifyCode;
            $this->submitRowInfo();
        }

        public function sendEmailVerifyCode(string $Language = 'x-default') : void{
            //replace '`clientName`' and '`verifyLink`' in templates.
            if($Language !== 'cn' && $Language !== 'en'){
                $Language = 'x-default';
            }
            $VerifyURL = $GLOBALS['OPENAPISettings']['BlueAirLive']['BaseURL'][$Language] . $GLOBALS['OPENAPISettings']['BlueAirLive']['Pages']['VerifyEmail'] . $this->m_UserRow['emailverifycode'];
            $EmailTemplate = $GLOBALS['OPENAPISettings']['Email']['VerifyTemplate'][$Language];
            $EmailTemplate['body'] = \str_replace($this->getDisplayName(),'`clientName`',$EmailTemplate['body']);
            $EmailTemplate['body'] = \str_replace($VerifyURL, '`verifyLink`',$EmailTemplate['body']);
            $EmailTemplate['body'] = $GLOBALS['OPENAPISettings']['Email']['SharedTop'][$Language] . $EmailTemplate['body'] . $GLOBALS['OPENAPISettings']['Email']['SharedBottom'][$Language];
            \BoostPHP\Mail::sendMail(
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPPort'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPHost'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPUser'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPPassword'],
                $this->getEmail(),
                $EmailTemplate['title'],
                $EmailTemplate['body'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPSenderAddress'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPSenderName'],
                $GLOBALS['OPENAPISettings']['Email']['Account']['SMTPSecureConnection']
            );
        }


        public static function checkExist(string $Username) : bool{
            $UserRowCount = \BoostPHP\MySQL::checkExist(Internal::$MySQLiConn, 'users', array('username'=>$Username));
            if($UserRowCount > 0){
                return true;
            }else{
                return false;
            }
        }

        public static function checkNickNameExist(string $NickName) : bool{
            $NickNameDataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('userdisplayname'=>$NickName));
            if($NickNameDataRow['count'] < 1){
                return false;
            }else{
                return true;
            }
        }

        public static function checkEmailExist(string $Email) : bool{
            $EmailDataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('email'=>$Email));
            if($EmailDataRow['count'] < 1){
                return false;
            }else{
                return true;
            }
        }

        public static function getUserByNickName(string $NickName) : User{
            $NickNameDataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('userdisplayname'=>$NickName));
            if($NickNameDataRow['count'] < 1){
                throw new Exception('Non-existence user');
                return null;
            }
            $RelatedUsername = $NickNameDataRow['result'][0]['username'];
            return new User($RelatedUsername);
        }

        public static function getUserByEmail(string $Email) : User{
            $EmailDataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('email'=>$Email));
            if($EmailDataRow['count'] < 1){
                throw new Exception('Non-existence user');
                return null;
            }
            $RelatedUsername = $EmailDataRow['result'][0]['username'];
            return new User($RelatedUsername);
        }

        public static function generateVerifyCode(string $Username) : string{
            return md5(\BoostPHP\Encryption\SHA::SHA256Encode($Username . time(),$GLOBALS['OPENAPISettings']['Salt']));
        }

        protected static function encryptPassword(string $Password) : string{
            return md5(\BoostPHP\Encryption\SHA::SHA256Encode($Password,$GLOBALS['OPENAPISettings']['Salt']));
        }

        public static function generateToken(string $Username) : string{
            return md5(\BoostPHP\BoostPHP\Encryption\SHA::SHA256Encode($Username . rand(0,10000) . time(),$GLOBALS['OPENAPISettings']['Salt']));
        }

        public static function registerUser(string $Username, string $Password, string $Email, string $NickName = '') : User{
            if(self::checkExist($Username) || self::checkEmailExist($Email)){
                throw new Exception('Existence user');
                return null;
            }
            
            if(empty($NickName))
                $NickName = $Username;
            
            $NewUserRow = array(
                'username' => $Username,
                'userdisplayname' => $NickName,
                'password' => self::encryptPassword($Password,$GLOBALS['OPENAPISettings']['Salt']),
                'email' => $Email,
                'settings' => ['User']['defaultValues']['settings'],
                'thirdauth' => ['User']['defaultValues']['thirdauth'],
                'emailverified' => false,
                'emailverifycode' => self::generateVerifyCode($Username),
                'userpermission' => ['User']['defaultValues']['userpermission'],
                'usergroup' => ['User']['defaultValues']['usergroup'],
                'regtime'=> time()
            );
            \BoostPHP\MySQL::insertRow(Internal::$MySQLiConn,'users',$NewUserRow);
            return new User($Username);
        }
    }
}