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
        /**
         * Send a Post Request
         * returns false on failure
         * @param string URL Posting To
         * @param array Data to POST(In array Key=>Value)
         * @param string Reference(Which website were you previously?)
         * @param array Cookies to POST with
         * @access public
         * @return array {'code'=>HTTPStat, 'content'=>Content}
         * code will be set to 400 if on error
         */
        public static function postToAddr(string $url, array $data = array(), string $ref = '', array $cookie = array()) : array{ 
            $curl = curl_init(); // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
            if(!empty($cookie)){
                curl_setopt($curl, CURLOPT_COOKIE, $cookie);
            }
            curl_setopt($curl, CURLOPT_REFERER, $ref);// 设置Referer
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
            $tmpInfo = curl_exec($curl); // 执行操作
            if (curl_errno($curl)) {
                $tmpRst['code'] = 400; //错误啦~
                return $tmpRst;
            }
            $tmpRst['code'] = curl_getinfo($curl,CURLINFO_HTTP_CODE); //我知道HTTPSTAT码哦～
            $tmpRst['content'] = $tmpInfo;
            curl_close($curl); // 关闭CURL会话
            return $tmpRst; // 返回数据
        }
        /**
         * Send a Get Request
         * Returns false on failure
         * @param string The URL You want to access
         * @param string The reference URL You want to show
         * @param array The cookies you want to set
         * @access public
         * @return array {'code'=>HTTP_Stat, 'content'=>Content}
         * code will be set to 400 if on error
         */
        public static function getFromAddr(string $url, string $ref = '', array $cookie = array()){
            $curl = curl_init(); // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
            if(!empty($cookie)){
                curl_setopt($curl, CURLOPT_COOKIE, $cookie);
            }
            curl_setopt($curl, CURLOPT_REFERER, $ref);// 设置Referer
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
            $tmpInfo = curl_exec($curl); // 执行操作
            if (curl_errno($curl)) {
                $tmpRst['code'] = 400; //错误啦~
                return $tmpRst;
            }
            $tmpRst['code'] = curl_getinfo($curl,CURLINFO_HTTP_CODE); //获取HTTP状态嘛
            $tmpRst['content'] = $tmpInfo;
            curl_close($curl); // 关闭CURL会话
            return $tmpRst; // 返回数据
        }
    }
}