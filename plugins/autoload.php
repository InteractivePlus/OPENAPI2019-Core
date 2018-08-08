<?php
namespace OPENAPI40\PluginAutoload{
    require_once __DIR__ . '/autoload_config.php';
    require_once __DIR__ . '/pluginInstallAutoLoad.php'; //自动安装最新添加的Plugin
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
        $SourceBaseDir = "/PHP5/";
    }else if(PHP_MAJOR_VERSION == 7 && PHP_MINOR_VERSION == 0){
        $SourceBaseDir = "/PHP5/";
    }else{ //PHP_MAJOR_VERSION >= 7 && PHP_MINOR_VERSION != 0
        $SourceBaseDir = "/PHP7/";
    }
    foreach (Internal::$RequiredPlugins as $SourcePlug){
        $SourcePath = __DIR__ . '/' . $SourcePlug . $SourceBaseDir . $SourcePlug . '.php'; 
        require_once($SourcePath);
    }
}