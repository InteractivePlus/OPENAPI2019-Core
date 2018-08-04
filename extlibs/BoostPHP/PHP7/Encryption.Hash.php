<?php
namespace BoostPHP\Encryption{
    require_once __DIR__ . '/internal/BoostPHP.internal.php';
    class SHA{
        /**
        * Encode the string using SHA256 Encoding Method(With Salt)
        * @param string $Text String to be encode with
        * @param string $Salt Optional, The Salt to be add together
        * @access public
        * @return string The encoded string
        */
        public static function SHA256Encode(string $Text,string $Salt = "") : string{
            if(!empty($Salt)){$Salt=md5($Salt);}
            return hash("sha256",$Text . $Salt);
        }
        
        /**
        * Encode the string using SHA512 Encoding Method(With Salt)
        * @param string $Text String to be encode with
        * @param string $Salt Optional, The Salt to be add together
        * @access public
        * @return string The encoded string
        */
        public static function SHA512Encode(string $Text, string $Salt = "") : string{
            if(!empty($Salt)){$Salt=md5($Salt);}
            return hash("sha512",$Text . $Salt);
        }
        
        /**
        * Encode the string using SHA1 Encoding Method(With Salt)
        * @param string $Text String to be encode with
        * @param string $Salt Optional, The Salt to be add together
        * @access public
        * @return string The encoded string
        */
        public static function SHA1Encode(string $Text, string $Salt = "") : string{
            if(!empty($Salt)){$Salt=md5($Salt);}
            return hash("sha1",$Text . $Salt);
        }
    }
}