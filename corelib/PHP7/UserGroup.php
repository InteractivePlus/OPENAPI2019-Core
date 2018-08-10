<?php
namespace OPENAPI40{
    require_once __DIR__ . '/internal/OPENAPI.internal.php';
    require_once __DIR__ . '/Logs.php';
    class UserGroup{
        protected $m_GroupName = '';
        protected $m_GroupRow = array(
            'groupname' => '',
            'groupdisplayname' => '',
            'grouppermission' => ''
        );

        protected function updateRowInfo() : void{
            $mDataArray = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'usergroups', array('groupname'=>$this->m_GroupName));
            if($mDataArray['count']<1){
                throw new \Exception('Non-existence user');
                return;
            }
            $this->m_GroupRow = $mDataArray['result'][0];
        }
        protected function submitRowInfo() : bool{
            $mSubmitState = \BoostPHP\MySQL::updateRows(Internal::$MySQLiConn,'usergroups',$this->m_GroupRow,array('groupname'=>$this->m_GroupName));
            return $mSubmitState;
        }
        public function __construct(string $GroupName){
            if(!self::checkExist($GroupName)){
                throw new \Exception('Non-existence user');
                return;
            }
            $this->m_GroupName = $GroupName;
            $this->updateRowInfo();
        }

        public function delete() : void{
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'usergroups',array('groupname'=>$this->m_GroupName));
        }

        public function getGroupName() : string{
            return $this->m_GroupName;
        }

        public function setGroupName(string $newGroupName) : void{
            Log::recordLogs(1,'GroupNameChanged(' . $this->getGroupName() . '):' . $this->getGroupName() . ' => ' . $newGroupName);
            $this->m_GroupRow['groupname'] = $newGroupName;
            $this->submitRowInfo();
            $this->m_GroupName = $newGroupName;
        }

        public function getDisplayName() : string{
            return $this->m_GroupRow['groupdisplayname'];
        }

        public function setDisplayName(string $newDisplayName) : void{
            Log::recordLogs(1,'GroupDisplayNameChanged(' . $this->getGroupName() . '):' . $this->getDisplayName() . ' => ' . $newDisplayName);
            $this->m_GroupRow['groupdisplayname'] = $newDisplayName;
            $this->submitRowInfo();
        }

        public function getPermissions() : array{
            $PermissionJSON = gzuncompress($this->m_GroupRow['grouppermission']);
            $Permissions = json_decode($PermissionJSON,true);
            return $Permissions;
        }

        public function setPermissions(array $newPermission) : void{
            Log::recordLogs(1,'GroupPermissionChanged(' . $this->getGroupName() . '):' . json_encode($this->getPermissions()) . ' => ' . json_encode($newPermission));
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
            $PermissionJSON = json_encode($newPermission);
            $this->m_GroupRow['grouppermission'] = gzcompress($PermissionJSON,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            $this->submitRowInfo();
        }

        public function updatePermissions(array $newPermission) : void{
            $OldPermissions = $this->getPermissions();
            foreach($newPermission as $SinglePermissionKey => $SinglePermission){
                $OldPermissions[$SinglePermissionKey] = $SinglePermission;
            }
            $this->setPermissions($OldPermissions);
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

        public function setPermission(string $permissionItem, bool $Value) : void{
            $canFind = false;
            foreach($GLOBALS['OPENAPISettings']['Fieldnames']['Permission'] as $SinglePermField){
                if($SinglePermField === $permissionItem){
                    $canFind = true;
                    break;
                }
            }
            if(!$canFind){
                return;
            }

            $Permissions = $this->getPermissions();
            $Permissions[$permissionItem] = $Value ? 'true' : 'false';
            $this->setPermissions($Permissions);
        }

        public static function checkExist(string $GroupName) : bool{
            $mDataCount = \BoostPHP\MySQL::checkExist(Internal::$MySQLiConn, 'usergroups', array('groupname'=>$GroupName));
            if($mDataCount < 1){
                return false;
            }else{
                return true;
            }
        }

        public static function checkDisplayNameExist(string $dispalyName) : bool{
            $mDataCount = \BoostPHP\MySQL::checkExist(Internal::$MySQLiConn, 'usergroups', array('groupdisplayname'=>$dispalyName));
            if($mDataCount < 1){
                return false;
            }else{
                return true;
            }
        }

        public static function getGroupByDisplayName(string $groupName) : bool{
            $mData = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn,'usergroups',array('groupdisplayname'=>$displayName));
            if($mData['count'] < 1){
                throw new \Exception('Non-existence user');
                return null;
            }
            $mGroupRow = $mData['result'][0];
            return new UserGroup($mGroupRow['groupname']);
        }

        public static function getGroupByUser(string $Username) : UserGroup{
            $mUserRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'users', array('username'=>$Username));
            if($mUserRow['count'] < 1){
                throw new \Exception('Non-existence user');
                return null;
            }else{
                return new UserGroup($mUserRow['result'][0]['usergroup']);
            }
        }

        public static function getGroupsBySearching(string $searchGroupName = '') : array{
            $mGroupRow = \BoostPHP\MySQL::selectIntoArray_FromRequirements(Internal::$MySQLiConn, 'usergroups');
            if($mGroupRow['count'] < 1){
                return array();
            }
            $groupRst = array();
            foreach($mGroupRow['result'] as &$SingleRow){
                if(empty($searchGroupName) || strpos($SingleRow['groupname'],$searchGroupName) !== false){
                    $groupRst[count($groupRst)] = new UserGroup($SingleRow['groupname']);
                }
            }
            return $groupRst;
        }
        public static function createGroup(string $groupID, string $displayName, array $Permission = array()) : UserGroup{
            if(self::checkExist($groupID)){
                throw new \Exception('Existence user');
                return null;
            }else if(self::checkExist($displayName)){
                throw new \Exception('Existence displayname');
                return null;
            }

            Log::recordLogs(1,'UserGroupCreated(' . $groupID . ')');

            $insertingArray = array(
                'groupname' => $groupID,
                'groupdisplayname' => $displayName,
                'grouppermission' => $GLOBALS['OPENAPISettings']['UserGroup']['defaultValues']['grouppermission']
            );
            \BoostPHP\MySQL::insertRow(Internal::$MySQLiConn,'usergroups',$insertingArray);
            $mGroup = new UserGroup($groupID);
            $mGroup->updatePermissions($Permission);
            return $mGroup;
        }
    }
}