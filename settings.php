<?php
$OPENAPISettings['MySQL'] = array(
    'Host' => '127.0.0.1',
    'Port' => 3306,
    'Username' => 'root',
    'Password' => 'root',
    'Database' => 'OEPNAPI'
);

$OPENAPISettings['Error']['ErrorCodes'] = array(
    '0' => 'No error',
    '1' => 'Credential is not valid',
    '2' => 'Non-existence user',
    '3' => 'Existence user',
    '4' => 'Format Error',
    '5' => 'Permission Error',
    '6' => 'Too frequent operation',
    '500' => 'Internal Error'
);

$OPENAPISettings['Salt'] = 'XSYDNB';

$OPENAPISettings['RenewTokenWhenChecking'] = true;
$OPENAPISettings['RenewAPPTokenWhenChecking'] = true;
$OPENAPISettings['TokenAvailableDuration'] = 3600*24*7;
$OPENAPISettings['APPTokenAvailableDuration'] = 3600*24*2;

$OPENAPISettings['TokenAvailableAfterIPChange'] = true;
$OPENAPISettings['APPTokenAvailableAfterIPChange'] = true;

$OPENAPISettings['CompressIntensity'] = 9; //0 to 9

$OPENAPISettings['DefaultLanguage'] = 'en';

$OPENAPISettings['User']['defaultValues'] = array(
    'settings' => gzcompress('{"subscribeToMail":"true"}',$OPENAPISettings['CompressIntensity']),
    'thirdauth' => gzcompress('{}',$OPENAPISettings['CompressIntensity']),
    'userpermission' => gzcompress('{"EditUsers": "false", "ViewLogs": "false", "ManageUserGroups": "false", "ChangeUserPermissions": "false"}',$OPENAPISettings['CompressIntensity']),
    'usergroup' => 'normalUsers'
);

$OPENAPISettings['BlueAirLive']['BaseURL']['cn'] = 'https://ucenter.xsyds.cn/cn/';
$OPENAPISettings['BlueAirLive']['BaseURL']['en'] = 'https://ucenter.xsyds.cn/en/';
$OPENAPISettings['BlueAirLive']['BaseURL']['x-default'] = &$OPENAPISettings['BlueAirLive']['BaseURL'][$OPENAPISettings['DefaultLanguage']];
$OPENAPISettings['BlueAirLive']['Pages']['VerifyEmail'] = 'emailVerification.php?VerifyCode=';

$OPENAPISettings['Email']['SharedTop']['cn'] = file_get_contents(__DIR__ . '/Templates/EmailTopBar/cn.html');
$OPENAPISettings['Email']['SharedTop']['en'] = file_get_contents(__DIR__ . '/Templates/EmailTopBar/en.html');
$OPENAPISettings['Email']['SharedTop']['x-default'] = &$OPENAPISettings['Email']['SharedTop'][$OPENAPISettings['DefaultLanguage']];

$OPENAPISettings['Email']['SharedBottom']['cn'] = file_get_contents(__DIR__ . '/Templates/EmailFootBar/cn.html');
$OPENAPISettings['Email']['SharedBottom']['en'] = file_get_contents(__DIR__ . '/Templates/EmailFootBar/en.html');
$OPENAPISettings['Email']['SharedBottom']['x-default'] = &$OPENAPISettings['Email']['SharedBottom'][$OPENAPISettings['DefaultLanguage']];

$OPENAPISettings['Email']['VerifyTemplate']['cn'] = array(
    'title' => '验证您的邮箱 - BlueAirLive',
    'body' => file_get_contents(__DIR__ . '/Templates/EmailVerification/cn.html')
);
$OPENAPISettings['Email']['VerifyTemplate']['en'] = array(
    'title' => 'Verify Your Email - BlueAirLive',
    'body' => file_get_contents(__DIR__ . '/Templates/EmailVerification/en.html')
);
$OPENAPISettings['Email']['VerifyTemplate']['x-default'] = &$OPENAPISettings['Email']['VerifyTemplate'][$OPENAPISettings['DefaultLanguage']];

$OPENAPISettings['Email']['Account'] = array(
    'SMTPPort' => 25,
    'SMTPHost' => 'smtp.xsyds.cn',
    'SMTPUser' => 'publicservice@xsyds.cn',
    'SMTPPassword' => 'XSYDNB',
    'SMTPSenderAddress' => 'publicservice@xsyds.cn',
    'SMTPSenderName' => 'BlueAirLive',
    'SMTPSecureConnection' => ''
);