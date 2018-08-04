<?php
namespace BoostPHP\Internal{
    require_once __DIR__ . '/internal/BoostPHP.settings.php';
    if (!defined('PHP_VERSION_ID')) {
        $version = explode('.', PHP_VERSION);
        define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
    }
    if (PHP_VERSION_ID < 50207) {
        define('PHP_MAJOR_VERSION',   $version[0]);
        define('PHP_MINOR_VERSION',   $version[1]);
        define('PHP_RELEASE_VERSION', $version[2]);
    }
    $SourceBaseDir = "";
    if(PHP_MAJOR_VERSION <= 5){
        $SourceBaseDir = __DIR__ . "/PHP5/";
    }else if(PHP_MAJOR_VERSION == 7 && PHP_MINOR_VERSION == 0){
        $SourceBaseDir = __DIR__ . "/PHP5/";
    }else{ //PHP_MAJOR_VERSION >= 7 && PHP_MINOR_VERSION != 0
        $SourceBaseDir = __DIR__ . "/PHP7/";
    }
    foreach (\BoostPHP\Settings::$boostPHPSourceList as $SourceFile){
        require_once($SourceBaseDir . $SourceFile);
    }
}