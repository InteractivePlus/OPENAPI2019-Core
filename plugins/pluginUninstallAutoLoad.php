<?php
namespace OPENAPI40\PluginInstall{
    require_once __DIR__ . '/autoload_config.php';
    foreach (\OPENAPI40\PluginAutoload\Internal::$RequiredPlugins as $SourcePlug){
        require_once(__DIR__ . '/' . $SourcePlug . '/uninstall.php');
    }
}