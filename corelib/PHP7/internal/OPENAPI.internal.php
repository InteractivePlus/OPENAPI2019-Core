<?php
namespace OPENAPI40{
    require_once __DIR__ . '/../../../extlibs/BoostPHP/autoload.php';
    require_once __DIR__ . '/../../../settings.php';
    class Internal{
        public static $MySQLiConn;
        protected static $inited = false;
        public static function InitializeOPENAPI() : bool{
            if(self::$inited){
                return true;
            }

            self::$MySQLiConn = \BoostPHP\MySQL::connectDB($GLOBALS['OPENAPISettings']['MySQL']['Username'],$GLOBALS['OPENAPISettings']['MySQL']['Password'],$GLOBALS['OPENAPISettings']['MySQL']['Database'],$GLOBALS['OPENAPISettings']['MySQL']['Host'],$GLOBALS['OPENAPISettings']['MySQL']['Port']);
            if(!self::$MySQLiConn){
                return false;
            }
            return true;
        }
        public static function DestroyOPENAPI() : void{
            \BoostPHP\MySQL::closeConn(Internal::$MySQLiConn);
            self::$inited = false;
        }
    }

}