<?php
namespace BoostPHP{
    require_once __DIR__ . '/internal/BoostPHP.internal.php';
	
	class UploadFile{
		/**
		* Deal with the file that users uploaded
		* returns false on failure
		* @param string POSTED Value Name
		* @param string Where to put the upload file?
		* @param array Allowed extension(jpg,png), * means everyextent
		* @param int FileSize in BYTES to be allowed, 0 means to allow any
		* @access public
		* @return boolean true when succeed
		*/
		public static function dealUploadFile($UploadName, $PutTo, $AllowedExt = array("*"), $AllowedSize = 0){
			if(empty($_FILES[$UploadName])){return false;}
			$MyFile = $_FILES[$UploadName];
			if($MyFile['error']>0){return false;}
			$TempExtArr = explode(".",$MyFile['name']);
			$FileExension = end($TempExtArr);
			if($MyFile['size'] > $AllowedSize && $AllowedSize != 0){
				return false;
			}
			$FindedExt = false;
			foreach($AllowedExt as $TempAExt){
				if($TempAExt == $FileExension || $TempAExt == "*"){
					$FindedExt = true;
				}
			}
			if(!$FindedExt){return false;}
			if(file_exists($PutTo)){
				if(!unlink($PutTo)){return false;}
			}
			move_uploaded_file($MyFile['tmp_name'],$PutTo);
			return true;
		}
		
		/**
		* Get the original name of the uploaded file
		* returns false on failure
		* @param string POSTED Value Name
		* @access public
		* @return string the original name
		*/
		public static function getUploadFileOriginalName($UploadName){
			if(!empty($_FILE[$UploadName])){return $_FILE[$UploadName]['name'];}else{return "false";}
		}
		
		/**
		* Get the original extension of the uploaded file
		* returns false on failure
		* @param string POSTED Value Name
		* @access public
		* @return string the original extension
		*/
		public static function getUploadFileOriginalExt($UploadName){
			if(!empty($_FILE[$UploadName])){return end(explode(".",$_FILE[$UploadName]['name']));}else{return "false";}
		}
	}
}