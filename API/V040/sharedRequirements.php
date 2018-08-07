<?
require_once __DIR__ . '../../extlibs/BoostPHP/autoload.php';
require_once __DIR__ . '../../corelib/autoload.php';

OPENAPI40\Internal::InitializeOPENAPI();

$Language = $_POST['language'];
if(strtolower($Language) === 'zh-cn' || strtolower($Language) === 'zh'){
    $Language = 'cn';
}else if(strtolower($Language) === 'en'){
    $Language = 'en';
}else{
    $Language = 'x-default';
}
$IP = \BoostPHP\GeneralUtility::getUserIP();

function returnJSON($Array) : void{
    OPENAPI40\Internal::DestroyOPENAPI();
    exit(json_encode($Array));
}
function generalReturn($isError, $ErrorCode, $Language = 'cn', $OtherParams = array()){
    $mArray = array();
    $mArray['succeed'] = $isError ? false : true;
    $mArray['errorInfo'] = array(
        'errCode' => $isError,
        'errDescription' => $GLOBALS['OPENAPISettings']['Error']['ErrorCodes'][$ErrorCode]['en']
    );
    foreach($OtherParams as $ParamKey => &$ParamValue){
        $mArray[$ParamKey] = &$ParamValue;
    }
    returnJSON($mArray);
    return;
}