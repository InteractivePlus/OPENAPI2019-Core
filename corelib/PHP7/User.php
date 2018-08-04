<?php
namespace OPENAPI40{
    require_once __DIR__ . '/internal/OPENAPI.internal.php';
    class User{
        protected $m_Username;
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
            }
            $mUserRow = $mDataArray['result'][0];
        }
        protected function submitRowInfo() : bool{
            $mSubmitState = \BoostPHP\MySQL::updateRows(Internal::$MySQLiConn,'users',$this->m_UserRow,array('username'=>$this->m_Username));
            return $mSubmitState;
        }
        public function __construct(string $Username){
            if(!self::checkExist($Username)){
                throw new Exception('Non-existence user');
            }
            $this->m_Username = $Username;
            $this->updateRowInfo();
        }
        public function delete() : void{
            //函数施工未完成, 需要将用户从所有APP的manageusers和pendingusers中删除.
            //另外需要回调所有用户授权中userauth的APP.
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'users',array('username' => $Username));
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'users',array('relateduser'=>$Username));
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
            if(md5(\BoostPHP\Encryption\SHA::SHA256Encode($PasswordRaw,$OPENAPISettings['Salt'])) === $this->m_UserRow['password']){
                return true;
            }else{
                return false;
            }
        }

        public function setPassword(string $newPassword) : void{
            $this->m_UserRow['password'] = md5(\BoostPHP\BoostPHP\Encryption\SHA::SHA256Encode($newPassword,$OPENAPISettings['Salt']));
            $this->submitRowInfo();
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
            $this->m_UserRow['settings'] = gzcompress($newSettings,$OPENAPISettings['CompressIntensity']);
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
            $this->m_UserRow['thirdauth'] = gzcompress($newThirdAuth,$OPENAPISettings['CompressIntensity']);
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
            $this->m_UserRow['userpermission'] = gzcompress($newPermissionJSON,$OPENAPISettings['CompressIntensity']);
            $this->submitRowInfo();
        }

        public function getPermission(string $permissionType) : bool{
            $PermissionJSON = $this->getPermissionJSON();
            $Permissions = json_decode($PermissionJSON,true);
            unset($PermissionJSON);
            if(!empty($Permissions[$permissionType])){
                if($Permissions[$permissionType] === "true"){
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
            $Permissions[$permissionType] = $isPermissionAllowed ? "true" : "false";
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

        public function sendEmailVerifyCode(string $Language = 'x-default') : void{
            //replace '`clientName`' and '`verifyLink`' in templates.
            if($Language !== 'cn' && $Language !== 'en'){
                $Language = 'x-default';
            }
            $VerifyURL = $OPENAPISettings['BlueAirLive']['BaseURL'][$Language] . $OPENAPISettings['BlueAirLive']['Pages']['VerifyEmail'] . $this->m_UserRow['emailverifycode'];
            $EmailTemplate = $OPENAPISettings['Email']['VerifyTemplate'][$Language];
            $EmailTemplate['body'] = \str_replace($this->getDisplayName(),'`clientName`',$EmailTemplate['body']);
            $EmailTemplate['body'] = \str_replace($VerifyURL, '`verifyLink`',$EmailTemplate['body']);
            $EmailTemplate['body'] = $OPENAPISettings['Email']['SharedTop'][$Language] . $EmailTemplate['body'] . $OPENAPISettings['Email']['SharedBottom'][$Language];
            \BoostPHP\Mail::sendMail(
                $OPENAPISettings['Email']['Account']['SMTPPort'],
                $OPENAPISettings['Email']['Account']['SMTPHost'],
                $OPENAPISettings['Email']['Account']['SMTPUser'],
                $OPENAPISettings['Email']['Account']['SMTPPassword'],
                $this->getEmail(),
                $EmailTemplate['title'],
                $EmailTemplate['body'],
                $OPENAPISettings['Email']['Account']['SMTPSenderAddress'],
                $OPENAPISettings['Email']['Account']['SMTPSenderName'],
                $OPENAPISettings['Email']['Account']['SMTPSecureConnection']
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
        public static function getUserByEmail(string $Email) : User{
            $EmailDataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('email'=>$Email));
            if($EmailDataRow['count'] < 1){
                throw new Exception("Non-existence user");
                return null;
            }
            $RelatedUsername = $EmailDataRow['result'][0]['username'];
            return new User($RelatedUsername);
        }
    }
}