<?php
namespace BoostPHP\Cache{
    require_once __DIR__ . '/internal/BoostPHP.internal.php';
    class AutoMode{
    
        public static $m_CacheInfos = array(); //it will be set into array(array("cachefullpath",needUpdate T/F), array(...), array(...))

        /**
         * Start automode caching block
         * @param int cacheAvailableDuration - how long should your cache file last?
         * @param string cacheFolder - where should we put your file(please put a / or \\ in the end)?
         * @param bool differentForPostData - should the post data differ the cache result?
         * @access public
         * @return bool do I still need to execute my code?
         */
        public static function cacheStart($cacheAvailableDuration, $cacheFolder = "", $differentForPostData = true){
            ob_start();
            $cacheFileName = "";
            if($differentForPostData){
                $cacheFileName = md5($_SERVER['REQUEST_URI']) . md5(file_get_contents("php://input")) . '.html';//php://input is the POST data
            }else{
                $cacheFileName = md5($_SERVER['REQUEST_URI']) . '.html';
            }
            $cacheFullPath = $cacheFolder . $cacheFileName;
            if(file_exists($cacheFullPath)){
                if(time()-filemtime($cacheFullPath) < $cacheAvailableDuration){
                    \BoostPHP\output(file_get_contents($cacheFullPath));
                    AutoMode::$m_CacheInfos[count(AutoMode::$m_CacheInfos)] = array($cacheFullPath,false);
                    return false;
                }
            }
            AutoMode::$m_CacheInfos[count(AutoMode::$m_CacheInfos)] = array($cacheFullPath, true);
            return true;
        }

        /**
         * End the cache Block
         * @access public
         * @return void
         */
        public static function cacheEnd(){
            $cacheCount = count(AutoMode::$m_CacheInfos);
            $blockInfo = AutoMode::$m_CacheInfos[$cacheCount-1];
            if($blockInfo[1]){
                $fp = fopen($blockInfo[0],'w');
                fwrite($fp,ob_get_contents());
                fclose($fp);
            }
            ob_end_flush();
            unset(AutoMode::$m_CacheInfos[$cacheCount-1]); //delete the cache block info
        }
    }
    class ManualMode{
        public static $m_CacheInfos = array(); //it will be set into array(array("cachefullpath",needUpdate T/F), array(...), array(...))
        
        /**
         * Start manualmode caching block
         * @param int cacheAvailableDuration - how long should your cache file last?
         * @param string cacheFullPath - where should we put your file?
         * @access public
         * @return bool do I still need to execute my code?
         */
        public static function cacheStart($cacheAvailableDuration, $cacheFullPath){
            ob_start();
            if(file_exists($cacheFullPath)){
                if(time()-filemtime($cacheFullPath) < $cacheAvailableDuration){
                    \BoostPHP\output(file_get_contents($cacheFullPath));
                    ManualMode::$m_CacheInfos[count(ManualMode::$m_CacheInfos)] = array($cacheFullPath,false);
                    return false;
                }
            }
            ManualMode::$m_CacheInfos[count(ManualMode::$m_CacheInfos)] = array($cacheFullPath, true);
            return true;
        }

        /**
         * End the cache Block
         * @access public
         * @return void
         */
        public static function cacheEnd(){
            $cacheCount = count(ManualMode::$m_CacheInfos);
            $blockInfo = ManualMode::$m_CacheInfos[$cacheCount-1];
            if($blockInfo[1]){
                $fp = fopen($blockInfo[0],'w');
                fwrite($fp,ob_get_contents());
                fclose($fp);
            }
            ob_end_flush();
            unset(ManualMode::$m_CacheInfos[$cacheCount-1]); //delete the cache block info
        }
    }   
}