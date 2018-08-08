<?php
require_once __DIR__ . '/../corelib/autoload.php';
require_once __DIR__ . '/../install/installRequirements.php';
if(file_exists(__DIR__ . '/../install/install.lock')){
    \generalReturn(true,'install.lock已被锁定, 请删除/install/install.lock后重新安装');
}