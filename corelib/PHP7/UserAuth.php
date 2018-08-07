<?php
namespace OPENAPI40{
    require_once __DIR__ . '/internal/OPENAPI.internal.php';
    require_once __DIR__ . '/APPs.php';
    class UserAuth{
        protected $m_Username = '';
        protected $m_APPID = '';
        protected $m_AuthRow = array(
            'username' => '',
            'appid' => '',
            'authcontent' => '',
        );
        protected function updateRowInfo() : void{
            $mDataArray = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'userauth', array('username'=>$this->m_Username, 'appid'=>$this->m_APPID));
            if($mDataArray['count']<1){
                throw new Exception('Non-existence data');
                return;
            }
            $this->m_AuthRow = $mDataArray['result'][0];
        }
        protected function submitRowInfo() : bool{
            $mSubmitState = \BoostPHP\MySQL::updateRows(Internal::$MySQLiConn,'userauth',$this->m_AuthRow,array('username'=>$this->m_Username, 'appid'=>$this->m_APPID));
            return $mSubmitState;
        }
        public function __construct(string $Username, string $APPID){
            if(!self::checkExist($Username, $APPID)){
                throw new Exception('Non-existence data');
                return;
            }
            $this->m_Username = $Username;
            $this->m_APPID = $APPID;
            $this->updateRowInfo();
        }
        public function delete() : void{
            $myAPP = new APP($this->m_APPID);
            $myAPP->callUserDeletedURL($this->m_Username);
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn, 'userauth', $this->m_AuthRow, array('username'=>$this->m_Username,'appid'=>$this->m_APPID));
        }

        public function getUsername() : string{
            return $this->m_Username;
        }

        public function getAPPID() : string{
            return $this->m_APPID;
        }

        public function getAuthContent() : array{
            $AuthJSON = gzuncompress($this->m_AuthRow['authcontent']);
            $Auths = json_decode($AuthJSON,true);
            return $Auths;
        }

        public function setAuthContent(array $AuthContent) : void{
            foreach($AuthContent as $SingleAuthKey => &$SingleAuth){
                $CanFind = false;
                foreach($GLOBALS['OPENAPISettings']['Fieldnames']['UserAuth'] as $AuthField){
                    if($AuthField === $SingleAuthKey){
                        $CanFind = true;
                        break;
                    }
                }
                if(!$CanFind){
                    unset($SingleAuth);
                }
            }
            $AuthJSON = json_encode($AuthContent);
            $this->m_AuthRow['authcontent'] = gzcompress($AuthJSON,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
        }

        public function updateAuthContent(array $AuthContent) : void{
            $OldAuthContent = $this->getAuthContent();
            foreach($AuthContent as $SingleAuthKey => $SingleAuth){
                $OldAuthContent[$SingleAuthKey] = $SingleAuth;
            }
            $this->setAuthContent($OldAuthContent);
        }

        public function getAuthItem(string $itemName) : bool{
            $Auths = $this->getAuthContent();
            if(!empty($Auths[$itemName])){
                if($Auths[$itemName] === 'true'){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }

        public function setAuthItem(string $itemName, bool $Value) : void{
            $Auths = $this->getAuthContent();
            $Auths[$itemName] = $Value ? 'true' : 'false';
            $this->setAuthContent($Auths);
        }

        public static function getAllAuthsByUser(string $Username) : array{
            $dataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'userauth', array('username'=>$Username));
            if($dataRow['count'] < 1){
                return array();
            }else{
                $mRst = array();
                foreach($dataRow['result'] as $SingleResult){
                    $mRst[count($mRst)] = new UserAuth($Username,$SingleResult['appid']);
                }
                return $mRst;
            }
        }

        public static function getAllAuthsByAPPID(string $APPID) : array{
            $dataRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'userauth', array('appid'=>$APPID));
            if($dataRow['count'] < 1){
                return array();
            }else{
                $mRst = array();
                foreach($dataRow['result'] as $SingleResult){
                    $mRst[count($mRst)] = new UserAuth($APPID,$SingleResult['username']);
                }
                return $mRst;
            }
        }

        public static function checkExist(string $Username, string $APPID) : bool{
            $mDataCount = \BoostPHP\MySQL::checkExist(Internal::$MySQLiConn, 'userauth', array('username'=>$Username, 'appid'=>$APPID));
            if($mDataCount < 1){
                return false;
            }else{
                return true;
            }
        }

        public static function createAuthContent(string $Username, string $APPID, array $AuthContent = array()) : UserAuth{
            $userCount = \BoostPHP\MySQL::checkExist(Internal::$MySQLiConn,'users',array('username'=>$Username));
            if($userCount < 1){
                throw new Exception('Non-existence user');
                return null;
            }
            $APPCount = \BoostPHP\MySQL::checkExist(Internal::$MySQLiConn, 'apps', array('apppid'=>$APPID));
            if($APPCount < 1){
                throw new Exception('Non-existence user');
                return null;
            }
            if(self::checkExist($Username,$APPID)){
                throw new Exception('Existence user');
                return null;
            }
            foreach($GLOBALS['OPENAPISettings']['UserAuth']['defaultValues'] as $defaultKey => &$defaultVal){
                if(empty($AuthContent[$defaultKey])){
                    $AuthContent[$defaultKey] = $defaultVal;
                }
            }
            foreach($AuthContent as $SingleAuthKey => &$SingleAuth){
                $CanFind = false;
                foreach($GLOBALS['OPENAPISettings']['Fieldnames']['UserAuth'] as &$AuthField){
                    if($AuthField === $SingleAuthKey){
                        $CanFind = true;
                        break;
                    }
                }
                if(!$CanFind){
                    unset($SingleAuth);
                }
            }
            $insertArray = array(
                'username' => $Username,
                'appid' => $APPID,
                'authcontent' => gzcompress(json_encode($AuthContent),$GLOBALS['OPENAPISettings']['CompressIntensity'])
            );
            \BoostPHP\MySQL::insertRow(Internal::$MySQLiConn, 'userauth', $insertArray);
            return new UserAuth($Username,$APPID);
        }
    }
}