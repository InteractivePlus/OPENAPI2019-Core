<?php
namespace OPENAPI40\PluginInstall{
    require_once __DIR__ . '/autoload_config.php';
    foreach (\OPENAPI40\PluginAutoload\Internal::$RequiredPlugins as $SourcePlug){
        $SourcePath = __DIR__ . '/' . $SourcePlug . '/install.php';
        if(file_exists($SourcePath)){
            require_once($SourcePath);
        }
    }
}