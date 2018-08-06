<?php
$OPENAPISettings['MySQL'] = array(
    'Host' => '127.0.0.1',
    'Port' => 3306,
    'Username' => 'root',
    'Password' => 'root',
    'Database' => 'OEPNAPI'
);

$OPENAPISettings['Email']['Account'] = array(
    'SMTPPort' => 25,
    'SMTPHost' => 'smtp.xsyds.cn',
    'SMTPUser' => 'publicservice@xsyds.cn',
    'SMTPPassword' => 'XSYDNB',
    'SMTPSenderAddress' => 'publicservice@xsyds.cn',
    'SMTPSenderName' => 'BlueAirLive',
    'SMTPSecureConnection' => ''
);

$OPENAPISettings['Salt'] = 'XSYDNB';

$OPENAPISettings['RenewTokenWhenChecking'] = true;
$OPENAPISettings['RenewAPPTokenWhenChecking'] = true;
$OPENAPISettings['TokenAvailableDuration'] = 3600*24*7;
$OPENAPISettings['APPTokenAvailableDuration'] = 3600*24*2;
$OPENAPISettings['VeriCodeAvailableDuration'] = 3600*2;

$OPENAPISettings['TokenAvailableAfterIPChange'] = true;
$OPENAPISettings['APPTokenAvailableAfterIPChange'] = true;

$OPENAPISettings['CompressIntensity'] = 9; //0 to 9

$OPENAPISettings['DefaultLanguage'] = 'en';

$OPENAPISettings['User']['defaultValues'] = array(
    'settings' => gzcompress('{"subscribeToMail":"true"}',$OPENAPISettings['CompressIntensity']),
    'thirdauth' => gzcompress(
        '{

        }',
        $OPENAPISettings['CompressIntensity']
    ),
    'userpermission' => gzcompress(
        '{
            "EditUsers": "false", 
            "ViewLogs": "false", 
            "ManageUserGroups": "false", 
            "ChangeUserPermissions": "false"
        }',
        $OPENAPISettings['CompressIntensity']
    ),
    'usergroup' => 'normalUsers'
);

$OPENAPISettings['APP']['defaultValues'] = array(
    'apppermission' => gzcompress(
        '{
            "accessInfo": "true",
            "sendEmailToUsers": "false"
        }',
        $OPENAPISettings['CompressIntensity']
    ),
    'manageusers' => gzcompress(
        '[

        ]',
        $OPENAPISettings['CompressIntensity']
    ),
    'pendingusers' => gzcompress(
        '[
            
        ]',
        $OPENAPISettings['CompressIntensity']
    ),
    'appjumpbackpage' => '',
    'userdeletedcallback' => ''
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

$OPENAPISettings['Email']['VeriCodeTemplate']['cn'] = array(
    'title' => '重要操作验证码 - BlueAirLive',
    'body' => file_get_contents(__DIR__ . '/Templates/VeriCode/cn.html')
);
$OPENAPISettings['Email']['VeriCodeTemplate']['en'] = array(
    'title' => 'Important Action Verification Code - BlueAirLive',
    'body' => file_get_contents(__DIR__ . '/Templates/VeriCode/en.html')
);
$OPENAPISettings['Email']['VeriCodeTemplate']['x-default'] = &$OPENAPISettings['Email']['VeriCodeTemplate'][$OPENAPISettings['DefaultLanguage']];

$OPENAPISettings['Error']['ErrorCodes'] = array(
    '0' => array(
        'en' => 'No error',
        'cn' => '无错误'
    ),
    '1' => array(
        'en' => 'Credential is not valid',
        'cn' => '凭据错误'
    ),
    '2' => array(
        'en' => 'Non-existence user',
        'cn' => '用户不存在'
    ),
    '3' => array(
        'en' => 'Existence user',
        'cn' => '用户已存在'
    ),
    '4' => array(
        'en' => 'Non-existence data',
        'cn' => '数据不存在'
    ),
    '5' => array(
        'en' => 'Existence email',
        'cn' => '邮箱已存在'
    ),
    '6' => array(
        'en' => 'Existence displayname',
        'cn' => '展示名已存在'
    ),
    '7' => array(
        'en' => 'Format Error',
        'cn' => '格式不正确'
    ),
    '8' => array(
        'en' => 'Permission Error',
        'cn' => '权限错误'
    ),
    '9' => array(
        'en' => 'Too frequent operation',
        'cn' => '操作过于频繁'
    ),
    '500' => array(
        'en' => 'Internal Error',
        'cn' => '内部错误'
    )
);
foreach($OPENAPISettings['Error']['ErrorCodes'] as $SingleErrorKey => &$SingleErrorValue){
    $SingleErrorValue['x-default'] = &$SingleErrorValue[$OPENAPISettings['DefaultLanguage']];
}
unset($SingleError);

$OPENAPISettings['VeriCode']['ActionTypes'] = array(
    '1' => array(
        'needToken' => false,
        'cn' => '更改密码',
        'en' => 'Change Password'
    ),
    '2' => array(
        'needToken' => true,
        'cn' => '更改邮箱',
        'en' => 'Change Mail'
    ),
    '3' => array(
        'needToken' => true,
        'cn' => '删除账号',
        'en' => 'Delete Account'
    ),
    '4' => array(
        'needToken' => true,
        'cn' => '删除APPID',
        'en' => 'Delete APPID'
    )
);
foreach($OPENAPISettings['VeriCode']['ActionTypes'] as &$SingleType){
    $SingleType['x-default'] = &$SingleType[$OPENAPISettings['DefaultLanguage']];
}
unset($SingleType);