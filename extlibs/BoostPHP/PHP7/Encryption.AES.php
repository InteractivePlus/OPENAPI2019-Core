<?php
namespace BoostPHP\Encryption{
    require_once __DIR__ . '/internal/BoostPHP.internal.php';
    class AES{
        public static $isOpenSSL = false;
        public static $eachEncryptionOpenSSL = array("ECB"=>false,"CFB8"=>false,"CBC"=>false,"OFB"=>false);
        public static function encryptECB(string $textToEncode, string $password, int $bit = 256) : string{
            if($bit != 128 && $bit != 192 && $bit != 256){
                throw new Exception("Encryption bit can only be 128, 192 or 256!");
            }
            
            if(AES::$isOpenSSL && AES::$eachEncryptionOpenSSL["ECB"]){
                return openssl_encrypt($textToEncode,"AES-" . $bit . "-ECB",$password);
            }else{
                $EncryptionBit = MCRYPT_RIJNDAEL_256;
                if($bit == 128){
                    $EncryptionBit = MCRYPT_RIJNDAEL_128;
                }else if($bit == 192){
                    $EncryptionBit = MCRYPT_RIJNDAEL_192;
                }
                $EncryptionMode = MCRYPT_MODE_ECB;
                return mcrypt_encrypt($EncryptionBit,$password,$textToEncode,$EncryptionMode);
            }
        }

        public static function decryptECB(string $textToDecode, string $password, int $bit = 256) : string{
            if($bit != 128 && $bit != 192 && $bit != 256){
                throw new Exception("Encryption bit can only be 128, 192 or 256!");
            }
            if(AES::$isOpenSSL && AES::$eachEncryptionOpenSSL["ECB"]){
                return openssl_decrypt($textToDecode,"AES-" . $bit . "-ECB",$password);
            }else{
                $EncryptionBit = MCRYPT_RIJNDAEL_256;
                if($bit == 128){
                    $EncryptionBit = MCRYPT_RIJNDAEL_128;
                }else if($bit == 192){
                    $EncryptionBit = MCRYPT_RIJNDAEL_192;
                }
                $EncryptionMode = MCRYPT_MODE_ECB;
                return mcrypt_decrypt($EncryptionBit,$password,$textToEncode,$EncryptionMode);
            }
        }

        public static function encryptCFB8(string $textToEncode, string $password, int $bit = 256) : string{
            if($bit != 128 && $bit != 192 && $bit != 256){
                throw new Exception("Encryption bit can only be 128, 192 or 256!");
            }

            if(AES::$isOpenSSL && AES::$eachEncryptionOpenSSL["CFB8"]){
                $iv = openssl_random_random_pseudo_bytes(openssl_cipher_iv_length("AES-" . $bit . "-CFB8"));
                return openssl_encrypt($textToEncode,"AES-" . $bit . "-CFB8",$password,0,$iv);
            }else{
                $EncryptionBit = MCRYPT_RIJNDAEL_256;
                if($bit == 128){
                    $EncryptionBit = MCRYPT_RIJNDAEL_128;
                }else if($bit == 192){
                    $EncryptionBit = MCRYPT_RIJNDAEL_192;
                }
                $EncryptionMode = MCRYPT_MODE_CFB;
                $EncryptionIVSize = mcrypt_get_iv_size($EncryptionBit,$EncryptionMode);
                $EncryptionIV = mcrypt_create_iv($EncryptionIVSize,MCRYPT_RAND);
                return mcrypt_encrypt($EncryptionBit,$password,$textToEncode,$EncryptionMode,$EncryptionIV);
            }
        }

        public static function decryptCFB8(string $textToDecode, string $password, int $bit = 256) : string{
            if($bit != 128 && $bit != 192 && $bit != 256){
                throw new Exception("Encryption bit can only be 128, 192 or 256!");
            }
            
            if(AES::$isOpenSSL && AES::$eachEncryptionOpenSSL["CFB8"]){
                $iv = openssl_random_random_pseudo_bytes(openssl_cipher_iv_length("AES-" . $bit . "-CFB8"));
                return openssl_decrypt($textToDecode,"AES-" . $bit . "-CFB8",$password,0,$iv);
            }else{
                $EncryptionBit = MCRYPT_RIJNDAEL_256;
                if($bit == 128){
                    $EncryptionBit = MCRYPT_RIJNDAEL_128;
                }else if($bit == 192){
                    $EncryptionBit = MCRYPT_RIJNDAEL_192;
                }
                $EncryptionMode = MCRYPT_MODE_CFB;
                $EncryptionIVSize = mcrypt_get_iv_size($EncryptionBit,$EncryptionMode);
                $EncryptionIV = mcrypt_create_iv($EncryptionIVSize,MCRYPT_RAND);
                return mcrypt_decrypt($EncryptionBit,$password,$textToEncode,$EncryptionMode,$EncryptionIV);
            }
        }

        public static function encryptCBC(string $textToEncode, string $password, int $bit = 256) : string{
            if($bit != 128 && $bit != 192 && $bit != 256){
                throw new Exception("Encryption bit can only be 128, 192 or 256!");
            }

            if(AES::$isOpenSSL && AES::$eachEncryptionOpenSSL["CBC"]){
                $iv = openssl_random_random_pseudo_bytes(openssl_cipher_iv_length("AES-" . $bit . "-CBC"));
                return openssl_encrypt($textToEncode,"AES-" . $bit . "-CBC",$password,0,$iv);
            }else{
                $EncryptionBit = MCRYPT_RIJNDAEL_256;
                if($bit == 128){
                    $EncryptionBit = MCRYPT_RIJNDAEL_128;
                }else if($bit == 192){
                    $EncryptionBit = MCRYPT_RIJNDAEL_192;
                }
                $EncryptionMode = MCRYPT_MODE_CBC;
                $EncryptionIVSize = mcrypt_get_iv_size($EncryptionBit,$EncryptionMode);
                $EncryptionIV = mcrypt_create_iv($EncryptionIVSize,MCRYPT_RAND);
                return mcrypt_encrypt($EncryptionBit,$password,$textToEncode,$EncryptionMode,$EncryptionIV);
            }
        }

        public static function decryptCBC(string $textToDecode, string $password, int $bit = 256) : string{
            if($bit != 128 && $bit != 192 && $bit != 256){
                throw new Exception("Encryption bit can only be 128, 192 or 256!");
            }
            
            if(AES::$isOpenSSL && AES::$eachEncryptionOpenSSL["CBC"]){
                $iv = openssl_random_random_pseudo_bytes(openssl_cipher_iv_length("AES-" . $bit . "-CBC"));
                return openssl_decrypt($textToDecode,"AES-" . $bit . "-CBC",$password,0,$iv);
            }else{
                $EncryptionBit = MCRYPT_RIJNDAEL_256;
                if($bit == 128){
                    $EncryptionBit = MCRYPT_RIJNDAEL_128;
                }else if($bit == 192){
                    $EncryptionBit = MCRYPT_RIJNDAEL_192;
                }
                $EncryptionMode = MCRYPT_MODE_CBC;
                $EncryptionIVSize = mcrypt_get_iv_size($EncryptionBit,$EncryptionMode);
                $EncryptionIV = mcrypt_create_iv($EncryptionIVSize,MCRYPT_RAND);
                return mcrypt_decrypt($EncryptionBit,$password,$textToEncode,$EncryptionMode,$EncryptionIV);
            }
        }

        public static function encryptOFB(string $textToEncode, string $password, int $bit = 256) : string{
            if($bit != 128 && $bit != 192 && $bit != 256){
                throw new Exception("Encryption bit can only be 128, 192 or 256!");
            }

            if(AES::$isOpenSSL && AES::$eachEncryptionOpenSSL["OFB"]){
                $iv = openssl_random_random_pseudo_bytes(openssl_cipher_iv_length("AES-" . $bit . "-OFB"));
                return openssl_encrypt($textToEncode,"AES-" . $bit . "-OFB",$password,0,$iv);
            }else{
                $EncryptionBit = MCRYPT_RIJNDAEL_256;
                if($bit == 128){
                    $EncryptionBit = MCRYPT_RIJNDAEL_128;
                }else if($bit == 192){
                    $EncryptionBit = MCRYPT_RIJNDAEL_192;
                }
                $EncryptionMode = MCRYPT_MODE_OFB;
                $EncryptionIVSize = mcrypt_get_iv_size($EncryptionBit,$EncryptionMode);
                $EncryptionIV = mcrypt_create_iv($EncryptionIVSize,MCRYPT_RAND);
                return mcrypt_encrypt($EncryptionBit,$password,$textToEncode,$EncryptionMode,$EncryptionIV);
            }
        }

        public static function decryptOFB(string $textToDecode, string $password, int $bit = 256) : string{
            if($bit != 128 && $bit != 192 && $bit != 256){
                throw new Exception("Encryption bit can only be 128, 192 or 256!");
            }

            if(AES::$isOpenSSL && AES::$eachEncryptionOpenSSL["OFB"]){
                $iv = openssl_random_random_pseudo_bytes(openssl_cipher_iv_length("AES-" . $bit . "-OFB"));
                return openssl_decrypt($textToDecode,"AES-" . $bit . "-OFB",$password,0,$iv);
            }else{
                $EncryptionBit = MCRYPT_RIJNDAEL_256;
                if($bit == 128){
                    $EncryptionBit = MCRYPT_RIJNDAEL_128;
                }else if($bit == 192){
                    $EncryptionBit = MCRYPT_RIJNDAEL_192;
                }
                $EncryptionMode = MCRYPT_MODE_OFB;
                $EncryptionIVSize = mcrypt_get_iv_size($EncryptionBit,$EncryptionMode);
                $EncryptionIV = mcrypt_create_iv($EncryptionIVSize,MCRYPT_RAND);
                return mcrypt_decrypt($EncryptionBit,$password,$textToEncode,$EncryptionMode,$EncryptionIV);
            }
        }
    }
    //this code functions for the detection for OpenSSL functions, it executes when this file gets included.
    AES::$isOpenSSL = function_exists('openssl_open');
    $ciphers = array();
    if(AES::$isOpenSSL){
        $ciphers = openssl_get_cipher_methods();
        foreach($ciphers as $EncryptionMethod){
            if($EncryptionMethod === "AES-128-ECB" || $EncryptionMethod === "AES-192-ECB" || $EncryptionMethod === "AES-256-ECB"){
                AES::$eachEncryptionOpenSSL["ECB"] = true;
            }
            else if($EncryptionMethod === "AES-128-CFB8" || $EncryptionMethod === "AES-192-CFB8" || $EncryptionMethod === "AES-256-CFB8"){
                AES::$eachEncryptionOpenSSL["CFB8"] = true;
            }
            else if($EncryptionMethod === "AES-128-CBC" || $EncryptionMethod === "AES-192-CBC" || $EncryptionMethod === "AES-256-CBC"){
                AES::$eachEncryptionOpenSSL["CBC"] = true;
            }else if($EncryptionMethod === "AES-128-OFB" || $EncryptionMethod === "AES-192-OFB" || $EncryptionMethod === "AES-256-OFB"){
                AES::$eachEncryptionOpenSSL["OFB"] = true;
            }
            if(AES::$eachEncryptionOpenSSL["ECB"] && AES::$eachEncryptionOpenSSL["CFB8"] && AES::$eachEncryptionOpenSSL["CBC"] && AES::$eachEncryptionOpenSSL["OFB"]){
                break; //Finished reading, do not need rest of the stuff.
            }
        }
    }
}