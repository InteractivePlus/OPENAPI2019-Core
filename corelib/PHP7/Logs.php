<?php
namespace OPENAPI40{
    require_once __DIR__ . '/internal/OPENAPI.internal.php';
    class Log{
        public static function recordLogs(int $LogLevel, string $LogContent = '') : void{
            \BoostPHP\MySQL::inserRow(Internal::$MySQLiConn,'log',array('loglevel'=>$LogLevel,'logcontent'=>gzcompress($LogContent,$GLOBALS['OPENAPISettings']['CompressIntensity']),'logtime'=>time()));
        }
        public static function deleteLogs(int $LogLevel = -1, string $LogContent = '', int $LogTime = -1) : void{
            $SelectRequirement = array();
            if($LogLevel !== -1){
                $SelectRequirement['loglevel'] = $LogLevel;
            }
            if(!empty($LogContent)){
                $SelectRequirement['logcontent'] = gzcompress($LogContent,$GLOBALS['OPENAPISettings']['CompressIntensity']);
            }
            if($LogTime !== -1){
                $SelectRequirement['logtime'] = $LogTime;
            }
            \BoostPHP\MySQL::deleteRows(Internal::$MySQLiConn,'log',$SelectRequirement);
        }
        public static function deleteExpiredLogs() : void{
            if($GLOBALS['OPENAPISettings']['LightLogAvailableTime'] < 0 || $GLOBALS['OPENAPISettings']['LightLogLevel'] < 0){
                return;
            }
            $MySQLStatement = 'DELETE FROM log WHERE loglevel<='. $GLOBALS['OPENAPISettings']['LightLogLevel'] . ' AND logtime<=' . (time() - $GLOBALS['OPENAPISettings']['LightLogAvailableTime']);
            \BoostPHP\MySQL::querySQL(Internal::$MySQLiConn,$MySQLStatement);
        }
        public static function listAllLogs(int $minimumLogLevel = 0) : array{
            $MySQLStatement = 'SELECT * FROM log WHERE loglevel>=' . $minimumLogLevel;
            $DataRow = \BoostPHP\MySQL::selectIntoArray_FromStatement(Internal::$MySQLiConn,$MySQLStatement);
            if($DataRow['count']<1){
                return array();
            }
            $OverallRst = array();
            foreach($DataRow['result'] as $SingleResult){
                $OverallRst[] = array(
                    'loglevel' => $SingleResult['loglevel'],
                    'logcontent' => gzuncompress($SingleResult['logcontent']),
                    'logtime' => $SingleResult['logtime']
                );
            }
            return $OverallRst;
        }
    }
    Log::deleteExpiredLogs();
}