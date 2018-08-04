<?php
namespace BoostPHP{
    require_once __DIR__ . '/internal/BoostPHP.internal.php';
    class GeneralUtility{
        /**
         * Get the IP of the visitor
         * @param bool detectCDN If you want to automatic detect your visitors' IP behind a CDN, might cause security issue.
         * @access public
         * @return string The IP address of the visitor
         */
        public static function getUserIP(bool $detectCDN = true) : string{
            if($detectCDN){
                return empty($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["REMOTE_ADDR"] : $_SERVER["HTTP_X_FORWARDED_FOR"];
            }else{
                return $_SERVER["REMOTE_ADDR"];
            }
        }

        /**
         * Jump to a page using header writing, Javascript and HTML tags
         * Note that PHP will keep executing, if you want to exit, add exit(0) after.
         * @param string URL you want to redirect your user to
         * @access public
         * @return void
         */
        public static function redirectPageURL(string $URL) : void{
            header("Location: " . $URL);
            \BoostPHP\output('<script>document.location="' . $URL . '";window.location="' . $URL . '";location.href="' . $URL . '";</script>');
            \BoostPHP\output('<noscript><meta http-equiv="refresh" content="0;URL=\'' . $URL . '\'" /></nocript>');
        }
    }
}