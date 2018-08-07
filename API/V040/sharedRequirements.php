<?
require_once '../../corelib/autoload.php';
require_once '../../extlibs/BoostPHP/autoload.php';

$Language = $_POST['language'];
if(strtolower($Language) === 'zh-cn' || strtolower($Language) === 'zh'){
    $Language = 'cn';
}else if(strtolower($Language) === 'en'){
    $Language = 'en';
}else{
    $Language = 'x-default';
}

function returnJSON($Array) : void{
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