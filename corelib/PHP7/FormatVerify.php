<?php
namespace OPENAPI40{
    require_once __DIR__ . '/internal/OPENAPI.internal.php';
    class FormatVerify{
        protected static function CheckLengthBetween(string $C_cahr, int $minLength, int $maxLength) : bool{    
            $C_cahr = trim($C_cahr);    
            if (strlen($C_cahr) < $minLength) return false;    
            if (strlen($C_cahr) > $maxLength) return false;    
            return true;    
        }
        protected static function CheckUserValid(string $C_user, int $min_CharNum = 0, int $max_CharNum = -1) : bool{
            if($min_CharNum > 0 || $max_CharNum != -1){
                if($max_CharNum == -1){$max_CharNum = strlen($C_user);}
                if (!self::CheckLengthBetween($C_user, $min_CharNum, $max_CharNum)) return false; //宽度检验
            }
            return preg_match("/^[a-zA-Z0-9]*$/", $C_user) ? true : false; //特殊字符检验
        }
        public static function CheckUserName(string $Username) : bool{
            return self::CheckUserValid($Username,$GLOBALS['OPENAPISettings']['User']['UsernameLength']['min'],$GLOBALS['OPENAPISettings']['User']['UsernameLength']['max']);
        }
        public static function CheckEmailAddr(string $C_mailaddr) : bool{
            return preg_match("/^[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*@[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*$/", $C_mailaddr) ? true : false;
        }
        protected static function checkPasswordValid(string $C_Password, int $min_CharNum = 0, int $max_CharNum = -1) : bool{
            if($min_CharNum > 0 || $max_CharNum != -1){
                if($max_CharNum == -1){$max_CharNum = strlen($C_Password);}
                if (!self::CheckLengthBetween($C_Password, $min_CharNum, $max_CharNum)) return false; //宽度检验
            }
            return preg_match("/^[a-zA-Z0-9\.]*$/", $C_Password) ? true : false; //特殊字符校验
        }
        public static function checkPassword(string $Password) : bool{
            return self::checkPasswordValid($Password,$GLOBALS['OPENAPISettings']['User']['PasswordLength']['min'],$GLOBALS['OPENAPISettings']['User']['PasswordLength']['max']);
        }
    }
}