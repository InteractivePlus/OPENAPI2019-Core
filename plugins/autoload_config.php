<?
namespace OPENAPI40\PluginAutoload{
    class Internal{
        public static $RequiredPlugins = array(
            
        );
	}
	
	$dh = opendir(__DIR__);
	if($dh){
		while($file = readdir($dh) !== false){
			if($file != "." && $file != ".."){
				$filePath = __DIR__ . '/' . $file;
				if(is_dir($filePath) && strpos($file,'_') !== 0){
					Internal::$RequiredPlugins[] = $file;
				}
			}
		}
		closedir($dh);
	}
}