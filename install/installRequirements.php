<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once __DIR__ . '/../extlibs/BoostPHP/autoload.php';

function returnJSON($Array){
    exit(json_encode($Array));
}
function generalReturn($isError, $Error, $Language = 'cn', $OtherParams = array()){
    $mArray = array();
    $mArray['succeed'] = $isError ? false : true;
    $mArray['errorInfo'] = array(
        'errDescription' => $Error
    );
    foreach($OtherParams as $ParamKey => &$ParamValue){
        $mArray[$ParamKey] = &$ParamValue;
    }
    returnJSON($mArray);
    return;
}

$IP = \BoostPHP\GeneralUtility::getUserIP();