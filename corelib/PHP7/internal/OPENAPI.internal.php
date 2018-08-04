<?php
namespace OPENAPI40{
    require_once __DIR__ . '/../../../extlibs/BoostPHP/autoload.php';
    require_once __DIR__ . '/../../../settings.php';
    class Internal{
        public static $MySQLiConn;
        public static function InitializeOPENAPI() : bool{
            global $OPENAPISettings;
            self::$MySQLiConn = \BoostPHP\MySQL::connectDB($OPENAPISettings['MySQL']['Username'],$OPENAPISettings['MySQL']['Password'],$OPENAPISettings['MySQL']['Database'],$OPENAPISettings['MySQL']['Host'],$OPENAPISettings['MySQL']['Port']);
            if(!self::$MySQLiConn){
                return false;
            }
            return true;
        }
        public static function DestroyOPENAPI() : void{
            \BoostPHP\MySQL::closeConn(Internal::$MySQLiConn);
        }
    }

}