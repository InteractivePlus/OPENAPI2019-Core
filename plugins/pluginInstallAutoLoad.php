<?php
namespace OPENAPI40\PluginInstall{
    require_once __DIR__ . '/autoload_config.php';
    if(!is_dir(__DIR__ . '/_pluginInstallLocks/')){
        mkdir(__DIR__ . '/_pluginInstallLocks/');
    }
    foreach (\OPENAPI40\PluginAutoload\Internal::$RequiredPlugins as $SourcePlug){
        $SourcePath = __DIR__ . '/' . $SourcePlug . '/install.php';
        if(file_exists($SourcePath)){
            if(!file_exists(__DIR__ . '/_pluginInstallLocks/' . $SourcePlug . '.lock')){
                require_once($SourcePath);
                file_put_contents(__DIR__ . '/_pluginInstallLocks/' . $SourcePlug . '.lock','Locked');
            }
        }
    }
}