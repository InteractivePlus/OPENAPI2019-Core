<?php
namespace OPENAPI40\PluginInstall{
    require_once __DIR__ . '/autoload_config.php';
    require_once __DIR__ . '/../install/installRequirements.php';
    if(file_exists(__DIR__ . '/../install/install.lock')){
        \generalReturn(true,'install.lock已被锁定, 请删除/install/install.lock后重新安装');
    }
    foreach (\OPENAPI40\PluginAutoload\Internal::$RequiredPlugins as $SourcePlug){
        $SourcePath = __DIR__ . '/' . $SourcePlug . '/uninstall.php';
        if(file_exists($SourcePath)){
            require_once($SourcePath);
        }
    }
}