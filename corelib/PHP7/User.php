<?php
namespace OPENAPI40{
    require_once __DIR__ . '/internal/OPENAPI.internal.php';
    require_once __DIR__ . '/APPs.php';
    require_once __DIR__ . '/UserAuth.php';
    require_once __DIR__ . '/UserGroup.php';

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
            'relatedapps'=>'',
            'regtime'=>0
        );
        protected function updateRowInfo() : void{
            $mDataArray = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('username'=>$this->m_Username));
            if($mDataArray['count']<1){
                throw new Exception('Non-existence user');
                return;
            }
            $this->m_UserRow = $mDataArray['result'][0];
            unset($this->m_UserRow['relatedapps']); //防止在APP类更改relatedapps后User submit会导致更改不生效的问题, 反正User不会操作relatedapps, 干脆unset掉
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
            if($GLOBALS['OPENAPISettings']['RenewTokenWhenChecking']){
                $this->renewRelatedToken();
            }
            return true;
        }

        public function checkVeriCode(string $Code, int $Action) : bool{
            $VeriCodeList = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn,'verificationcodes',array('username'=>$this->m_Username, 'actiontype'=>$Action, 'vericode'=>$Code));
            if($VeriCodeList['count'] < 1){
                return false;
            }
            $mCodeRow = $VeriCodeList['result'][0];
            if(time() - $mCodeRow['issuetime'] > $GLOBALS['OPENAPISettings']['VeriCodeAvailableDuration']){
                $this->deleteRelatedVeriCode();
                return false;
            }
            if($mCodeRow['actiontype'] != $Action){
                return false;
            }
            return true;
        }

        public static function checkActionNeedToken(int $Action) : bool{
            return $GLOBALS['OPENAPISettings']['VeriCode']['ActionTypes'][$Action]['needToken'];
        }

        public function autoAssignNewToken(string $UserIP) : string{
            $newToken = self::generateToken($this->m_Username);
            $this->assignNewToken($UserIP,$newToken);
            return $newToken;
        }

        public function assignNewToken(string $UserIP, string $newToken) : void{
            $this->deleteRelatedToken();
            $insertData = array(
                'token'=>$newToken,
                'starttime'=>time(),
                'relateduser'=>$this->m_Username,
                'tokenip'=>$UserIP
            );
            \BoostPHP\MySQL::insertRow(Internal::$MySQLiConn,'tokens',$insertData);
        }

        public function autoAssignNewVeriCode(int $ActionType) : string{
            $newVeriCode = self::generateVeriCode($this->m_Username);
            $this->assignNewVeriCode($ActionType, $newVeriCode);
            return $newVeriCode;
        }

        public function assignNewVeriCode(int $ActionType, string $newVeriCode) : void{
            $this->deleteRelatedVeriCode();
            $insertData = array(
                'actiontype' => $ActionType,
                'vericode' => $newVeriCode,
                'issuetime' => time(),
                'username' => $this->m_Username
            );
            \BoostPHP\MySQL::insertRow(Internal::$MySQLiConn,'verificationcodes', $insertData);
        }

        public function deleteRelatedToken() : void{
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'tokens',array('relateduser'=>$this->m_Username));
        }

        public function renewRelatedToken() : void{
            \BoostPHP\MySQL::updateRows(Internal::$MySQLiConn,'token',array('starttime'=>time()), array('relateduser'=>$this->m_Username));
        }

        public function deleteRelatedVeriCode() : void{
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'verificationcodes',array('username'=>$this->m_Username));
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

        public function getSettings() : array{
            return json_decode(gzuncompress($this->m_UserRow['settings']),true);
        }

        public function setSettings(array $newSettings) : void{
            foreach($newSettings as $SingleSettingKey => &$SingleSetting){
                $CanFind = false;
                foreach($GLOBALS['OPENAPISettings']['Fieldnames']['Settings'] as $SettingField){
                    if($SettingField === $SingleSettingKey){
                        $CanFind = true;
                        break;
                    }
                }
                if(!$CanFind){
                    unset($SingleSetting);
                }
            }
            $this->m_UserRow['settings'] = gzcompress(json_encode($newSettings),$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
        }

        public function getSetting(string $settingItem){
            $Settings = $this->getSettings();
            if(!empty($Settings[$settingItem])){
                return $Settings[$settingItem];
            }else{
                return null;
            }
        }

        public function setSetting(string $settingItem, $value) : void{
            $Settings = $this->getSettings();
            $Settings[$settingItem] = $value;
            $this->setSettingsJSON($Settings);
        }

        public function getThirdAuths() : array{
            return json_decode(gzuncompress($this->m_UserRow['thirdauth']),true);
        }

        public function setThirdAuths(array $newThirdAuth) : void{
            $this->m_UserRow['thirdauth'] = gzcompress(json_encode($newThirdAuth),$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
        }

        public function getThirdAuth(string $authName) : array{
            $ThirdAuths = $this->getThirdAuths();
            if(!empty($ThirdAuths[$authName])){
                return $ThirdAuths[$authName];
            }else{
                return array();
            }
        }

        public function setThirdAuth(string $authName, array $authValue) : void{
            $ThirdAuths = $this->getThirdAuths();
            $ThirdAuths[$authName] = $authValue;
            $this->setThirdAuths($ThirdAuths);
        }

        public function deleteThirdAuth(string $authName) : void{
            $ThirdAuths = $this->getThirdAuths();
            unset($ThirdAuths[$authName]);
            $this->setThirdAuths($ThirdAuths);
        }

        public function checkHasPermission(string $permissionType) : bool{
            $UserRelatedGroup = UserGroup::getGroupByUser($this->m_Username);
            return ($UserRelatedGroup->getPermission($permissionType) || $this->getPermission($permissionType));
        }

        public function getPermissions() : array{
            return json_decode(gzuncompress($this->m_UserRow['userpermission']),true);
        }

        public function setPermissions(array $newPermission) : void{
            foreach($newPermission as $SinglePermissionKey => &$SinglePermission){
                $CanFind = false;
                foreach($GLOBALS['OPENAPISettings']['Fieldnames']['Permission'] as $PermField){
                    if($PermField === $SinglePermissionKey){
                        $CanFind = true;
                        break;
                    }
                }
                if(!$CanFind){
                    unset($SinglePermission);
                }
            }
            $this->m_UserRow['userpermission'] = gzcompress(json_encode($newPermissionJSON),$GLOBALS['OPENAPISettings']['CompressIntensity']);
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

        public function sendSecurityVerifyCode(string $Language = 'x-default', string $veriCode, int $ActionType) : void{
             //replace '`clientName`' and '`actionName`', '`veriCode`' in templates.
             if($Language !== 'cn' && $Language !== 'en'){
                $Language = 'x-default';
            }
            $actionName = $GLOBALS['OPENAPISettings']['VeriCode']['ActionTypes'][$ActionType][$Language];

            $EmailTemplate = $GLOBALS['OPENAPISettings']['Email']['VeriCodeTemplate'][$Language];
            $EmailTemplate['body'] = \str_replace($this->getDisplayName(),'`clientName`',$EmailTemplate['body']);
            $EmailTemplate['body'] = \str_replace($actionName, '`actionName`',$EmailTemplate['body']);
            $EmailTemplate['body'] = \str_replace($veriCode, '`veriCode`', $EmailTemplate['body']);
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

        public static function getUsersByNickName(string $NickName) : User{
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

        public static function getUsersBySearching(string $SearchUsername = '') : array{
            $UserDataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users');
            if($UserDataRow['count'] < 1){
                return array();
            }
            $UserRst = array();
            foreach($UserDataRow['result'] as &$SingleData){
                if(empty($SearchUsername)||strpos($SingleData['username'],$SearchUsername) !== false){
                    $UserRst[count($UserRst)] = new User($SingleData['username']);
                }
            }
            return $UserRst;
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

        public static function generateVeriCode(string $Username) : string{
            return md5($Username . rand(0,10000) . time() . $GLOBALS['OPENAPISettings']['Salt']);
        }

        public static function registerUser(string $Username, string $Password, string $Email, string $NickName, array $Settings = array()) : User{
            $OverSettings = $GLOBALS['OPENAPISettings']['User']['defaultValues']['settings'];
            if(!empty($Settings)){
                foreach($OverSettings as $SingleSettingKey => &$SingleSettingVal){
                    if(!empty($Settings[$SingleSettingKey])){
                        $SingleSettingVal = $Settings[$SingleSettingKey];
                    }
                }
            }

            if(self::checkExist($Username)){
                throw new Exception('Existence user');
                return null;
            }
            if(self::checkEmailExist($Email)){
                throw new Exception('Existence email');
                return null;
            }
            if(self::checkNickNameExist($NickName)){
                throw new Exception('Existence displayname');
                return null;
            }

            if(empty($NickName))
                $NickName = $Username;
            
            $NewUserRow = array(
                'username' => $Username,
                'userdisplayname' => $NickName,
                'password' => self::encryptPassword($Password,$GLOBALS['OPENAPISettings']['Salt']),
                'email' => $Email,
                'settings' => gzcompress(json_encode($OverSettings),$GLOABLS['OPENAPISettings']['CompressIntensity']),
                'thirdauth' => $GLOBALS['OPENAPISettings']['User']['defaultValues']['thirdauth'],
                'emailverified' => false,
                'emailverifycode' => self::generateVerifyCode($Username),
                'userpermission' => $GLOBALS['OPENAPISettings']['User']['defaultValues']['userpermission'],
                'usergroup' => $GLOBALS['OPENAPISettings']['User']['defaultValues']['usergroup'],
                'regtime'=> time(),
                'relatedapps' => $GLOBALS['OPENAPISettings']['User']['defaultValues']['relatedapps']
            );
            \BoostPHP\MySQL::insertRow(Internal::$MySQLiConn,'users',$NewUserRow);
            return new User($Username);
        }
    }
}