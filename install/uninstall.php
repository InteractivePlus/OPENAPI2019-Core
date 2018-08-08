<?php
require_once __DIR__ . '/installRequirements.php';
require_once __DIR__ . '/../corelib/autoload.php';

if(file_exists(__DIR__ . '/install.lock')){
    generalReturn(true,"install.lock已被锁定, 请删除/install/install.lock后重新安装");
}

$initState = OPENAPI40\Internal::InitializeOPENAPI();
if(!$initState){
    generalReturn(true,'连接数据库失败!');
}
require_once __DIR__ . '/../plugins/pluginUninstallAutoLoad.php'; //卸载所有插件
\BoostPHP\MySQL::querySQL(
    \OPENAPI40\Internal::$MySQLiConn,
    'DROP TABLE IF EXISTS 
        `users`,
        `usergroups`,
        `tokens`,
        `apptokens`,
        `verificationcodes`,
        `log`,
        `userauth`,
        `apps`
    ;'
);
OPENAPI40\Internal::DestroyOPENAPI();