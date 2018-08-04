<?php
namespace BoostPHP{
    require_once __DIR__ . '/internal/BoostPHP.internal.php';
    require_once __DIR__ . '/FastCompare.php';
    class International{
        /**
         * Custom Compare Sorting Function for getSupportedLanguage,
         * Do not use this function!
         */
        public static function customLanguageArrayCompare(array $a, array $b) : int{
            if($a['Weight'] == $b['Weight'])
                return 0;
            return ($a['Weight'] < $b['Weight']) ? -1 : 1;
        }
        /**
         * Get supported language from browser sent Accept-Language Header
         * @param boolean $toLower Set it to true if you want all of the english characters to be in lower case
         * @access public
         * @return array - Supported Languages
         * @returnKey Language[string] - Language Indication
         * @returnKey Weight[float] - Language Q Weight
         */
        public static function getSupportedLanguage(bool $toLower = true) : array{
            $acceptLangHeader = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            //正常格式: Accept-Language: zh-CN,zh;q=0.5, PHP会自动去掉Accept-Language: 头, q是权重的意思, 就是多希望用最前面的语言, 若Q为0则说明不支持的语言
            $acceptLangArray = explode(",",$acceptLangHeader);
            unset($acceptLangHeader);
            $LanguageFinalArray = array();
            for($i = 0; $i < count($acceptLangArray); $i++){
                $eachArray = $acceptLangArray[$i];
                $tmpArray = explode(";",$eachArray);
                if($toLower){
                    $LanguageFinalArray[$i]["Language"] = strtolower($tmpArray[0]);
                }else{
                    $LanguageFinalArray[$i]["Language"] = $tmpArray[0];
                }
                if(count($tmpArray) > 1){
                    $LanguageFinalArray[$i]["Weight"] = floatval($tmpArray[1]);
                }else{
                    $LanguageFinalArray[$i]["Weight"] = 0.8;
                }
            }
            unset($acceptLangArray);
            \BoostPHP\FastCompare::customCompareSort($LanguageFinalArray,"customLanguageArrayCompare");
            return $LanguageFinalArray;
        }
    }
}