<?php
namespace BoostPHP{
    require_once __DIR__ . "/internal/BoostPHP.internal.php";
    require_once __DIR__ . "/../utilities/phpMailer/class.phpmailer.php";
	
	class Mail{
		/**
		 * Send a mail using SMTP
		 * returns false on failure
		 * @param int Port for SMTP
		 * @param string Host for the SMTP
		 * @param string Username for the SMTP
		 * @param string Password for the SMTP
		 * @param string Who are u sending to, can be multi users, split them by adding semi-colun(;)
		 * @param string Your email subject
		 * @param string Your email body(Supports HTML5)
		 * @param string Your Sender Address(Usually same with the Username)
		 * @param string Your Sender Name(E.g. BlueAirTechGroup)
		 * @param string ssl / tls / empty for nonsecure
		 * @access public
		 * @return bool Successful?
		 */
		public static function sendMail(int $SMTPPort = 25,string $SMTPHost,string $SMTPUsername,string $SMTPPassword,string $To,string $Subject,string $Body,string $Sender,string $SenderName, string $SecureConnection = 'ssl') : bool{
			$MySD=new \PHPMailer;
			$MySD->IsSMTP();
			$MySD->isHTML(true);
			$MySD->CharSet = 'utf-8';
			$MySD->SMTPAuth = true;
			$MySD->Port = $SMTPPort;
			$MySD->Host = $SMTPHost;
			$MySD->Username = $SMTPUsername;
			$MySD->Password = $SMTPPassword;
			$MySD->From = $Sender;
			$MySD->FromName = $SenderName;
			if(!empty($SecureConnection)){
				if($SecureConnection == 'tls'){
					$MySD->SMTPSecure = 'tls';
				}else{
					$MySD->SMTPSecure = 'ssl';
				}
			}
			
			if(strpos($To,";")!==false){
				//如果分为好几个收件人
				$MyRSVA=explode(";",$To);
				foreach($MyRSVA as $Addr){
					if(!empty($Addr)){
						$MySD->addAddress($Addr);
					}
				}
			}else{
				if($MySD->addAddress($To)==false){
					return false;
				}
			}
			$MySD->Subject = $Subject;
			$MySD->Body=$Body;
			$MySD->send();
			return true;
		}
	}
}