<?php
require_once __DIR__ . '/installRequirements.php';
if(file_exists(__DIR__ . '/install.lock')){
    generalReturn(true,"install.lock已被锁定, 请删除/install/install.lock后重新安装");
}
$RepalceValues = array(
    'MySQLHost' => $_POST['MySQLHost'],
    'MySQLPort' => $_POST['MySQLPort'],
    'MySQLUsername' => $_POST['MySQLUsername'],
    'MySQLPassword' => $_POST['MySQLPassword'],
    'MySQLDatabase' => $_POST['MySQLDatabase'],
    
    'SMTPPort' => $_POST['SMTPPort'],
    'SMTPHost' => $_POST['SMTPHost'],
    'SMTPUser' => $_POST['SMTPUser'],
    'SMTPPassword' => $_POST['SMTPPassword'],
    'SMTPSenderAddress' => $_POST['SMTPSenderAddress'],
    'SMTPSenderName' => $_POST['SMTPSenderName'],
    'SMTPSecureConnection' => $_POST['SMTPSecureConnection'],

    'EncryptionSalt' => $POST['EncryptionSalt']
);


$settingFile = file_get_contents(__DIR__ . '/../settings.php-template');
foreach($ReplaceValues as $SingleUserDefField => $SingleUserDefVal){
    $settingFile = str_replace('`' . $SingleUserDefField . '`',$SingleUserDefVal,$settingFile);
}
file_put_contents(__DIR__ . '/../settings.php',$settingFile);
require_once __DIR__ . '../corelib/autoload.php';
$initState = OPENAPI40\Internal::InitializeOPENAPI();
if(!$initState){
    generalReturn(true,'连接数据库失败!');
}
\BoostPHP\MySQL::querySQL(
    OPENAPI40\Internal::$MySQLiConn,
    'DROP TABLE users;
    DROP TABLE usergroups;
    DROP TABLE tokens;
    DROP TABLE apptokens;
    DROP TABLE verificationcodes;
    DROP TABLE log;
    DROP TABLE userauth;
    DROP TABLE apps'
);
\BoostPHP\MySQL::querySQL(
    OPENAPI40\Internal::$MySQLiConn,
    'CREATE TABLE `users`(
        `username` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . '),
        `userdisplayname` VARCHAR(' . $OPENAPISettings['User']['DisplayNameLength']['max'] . '),
        `password` CHAR(32),
        `email` VARCHAR(50),
        `settings` TEXT,
        `thirdauth` TEXT,
        `emailverified` TINYINT(1),
        `emailverifycode` CHAR(32),
        `userpermission` TEXT,
        `usergroup` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . '),
        `regtime` INT,
        `relatedapps` TEXT
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
);
\BoostPHP\MySQL::querySQL(
    OPENAPI40\Internal::$MySQLiConn,
    'CREATE TABLE `usergroups`(
        `groupname` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . '),
        `groupdisplayname` VARCHAR(' . $OPENAPISettings['User']['DisplayNameLength']['max'] . '),
        `grouppermission` TEXT
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
);

\BoostPHP\MySQL::querySQL(
    OPENAPI40\Internal::$MySQLiConn,
    'CREATE TABLE `tokens`(
        `token` CHAR(32),
        `starttime` INT,
        `relateduser` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . '),
        `tokenip` VARCHAR(40)
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
);

\BoostPHP\MySQL::querySQL(
    OPENAPI40\Internal::$MySQLiConn,
    'CREATE TABLE `apptokens`(
        `token` CHAR(32),
        `starttime` INT,
        `relateduser` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . '),
        `relatedapp` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . '),
        `tokenip` VARCHAR(40)
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
);

\BoostPHP\MySQL::querySQL(
    OPENAPI40\Internal::$MySQLiConn,
    'CREATE TABLE `verificationcodes`(
        `actiontype` INT,
        `vericode` CHAR(32),
        `issuetime` INT,
        `username` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . ')
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
);

\BoostPHP\MySQL::querySQL(
    OPENAPI40\Internal::$MySQLiConn,
    'CREATE TABLE `log`(
        `logtime` INT,
        `logcontent` TEXT,
        `loglevel` INT
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
);

\BoostPHP\MySQL::querySQL(
    OPENAPI40\Internal::$MySQLiConn,
    'CREATE TABLE `userauth`(
        `username` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . '),
        `authcontent` TEXT,
        `appid` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . ')
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
);

\BoostPHP\MySQL::querySQL(
    OPENAPI40\Internal::$MySQLiConn,
    'CREATE TABLE `apps`(
        `appid` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . '),
        `appdisplayname` VARCHAR(' . $OPENAPISettings['User']['DisplayNameLength']['max'] . ')
        `apppass` CHAR(32),
        `apppermission` TEXT,
        `adminuser` VARCHAR(' . $OPENAPISettings['User']['UsernameLength']['max'] . '),
        `manageusers` TEXT,
        `pendingusers` TEXT,
        `appjumpbackpage` TINYTEXT,
        `userdeletedcallback` TINYTEXT
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;'
);

$normalGroup = OPENAPI40\UserGroup::createGroup("normalUsers","Normal_Users");
file_put_contents(__DIR__ . '/install.lock','OPENAPI-Locked');
OPENAPI40\Internal::DestroyOPENAPI();
generalReturn(false,"No error","cn");