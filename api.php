<?php
	set_time_limit(300);
	ini_set('display_errors', 1);
	error_reporting(E_ALL);

	class GibddParser{
		protected $SGibdd,$SGibddHurl;
		protected $CAPTCHA_API_KEY,$GIBDD_SITEKEY,$SERVICE_URI;
		function __construct()
		{
			$this->SGibdd = "https://xn--b1afk4ade.xn--90adear.xn--p1ai";

			$this->CAPTCHA_API_KEY = 'MYKEY'; 
			$this->GIBDD_SITEKEY = '1d41c81fc10716c1721bc1f514512e1d51291a81d113a129';
			$this->SERVICE_URI = '&version=v3&min_score=0.9';
		}
		public function CheckAuto($type, $vin){
			switch ($type) {
				case 'history':
					$request = $this->SendQueryGIBDD_Car($this->SGibdd."/proxy/check/auto/history", $vin);
					break;
				case 'dtp':
					$request = $this->SendQueryGIBDD_Car($this->SGibdd."/proxy/check/auto/dtp", $vin);
					break;
				case 'wanted':
					$request = $this->SendQueryGIBDD_Car($this->SGibdd."/proxy/check/auto/wanted", $vin);
					break;
				case 'restrict':
					$request = $this->SendQueryGIBDD_Car($this->SGibdd."/proxy/check/auto/restrict", $vin);
					break;
				case 'diagnostic':
					$request = $this->SendQueryGIBDD_Car($this->SGibdd."/proxy/check/auto/diagnostic", $vin);
					break;
				default:
					$request = null;
					break;
			}
            //$registrations = $this->SendQueryGIBDD_Car($vin, $this->SGibdd."/proxy/check/auto/history" ,'check_auto_history');/*Владение*/
            //$dtp = $this->SendQueryGIBDD_Car($vin, $this->SGibdd."/proxy/check/auto/dtp");/*ДТП*/
            //$wanted = $this->SendQueryGIBDD_Car($vin, $this->SGibdd."/proxy/check/auto/wanted");/*Розыск*/
            //$restrict = $this->SendQueryGIBDD_Car($vin, $this->SGibdd."/proxy/check/auto/restrict");/*Запреты*/
            //$diagnostic = $this->SendQueryGIBDD_Car($vin, $this->SGibdd."/proxy/check/auto/diagnostic");/*ТО*/
            return $request;
		}
		public function CheckDriver($type, $num, $date){

			if ($type == 'driver') {
				$request = $this->SendQueryGIBDD_Driver($this->SGibdd . "/proxy/check/driver", $num, $date);
			} else {
				$request = null;
			}

			return $request;
		}
		public function CheckFines($type, $regnum, $regreg, $stsnum, $alien){
			if ($type == 'fines') {
				$request = $this->SendQueryGIBDD_Fines($this->SGibdd . "/proxy/check/fines", $regnum, $regreg, $stsnum, $alien);
			} else {
				$request = null;
			}
			return $request;
		}
		private function SendQueryGIBDD_Car($url, $vin){
			$bValid = false;
            $maxTry = 4;$try = 0;
            while (!$bValid) {
	            $sGoogleToken = $this->GetReCaptchaToken($this->GIBDD_SITEKEY, 'https://xn--90adear.xn--p1ai/about/personal', $this->SERVICE_URI . '&action=check_auto_history');

				$arFields = array(
					"vin"	=>	$vin,
					"reCaptchaToken"	=>	$sGoogleToken['key']
				);
				// echo '<pre>';
				// 	print_r($decoded);
				// echo '</pre>';
				$response = $this->Curl_post($url,$arFields, 'car');
				$decoded = json_decode($response,true);
				if((isset($decoded["code"]) && $decoded["code"] != 201) || (isset($decoded["status"]) && $decoded["status"] != 201) || $try >= $maxTry){
					if (!$try >= $maxTry) {
						$ReportGOOD = "http://rucaptcha.com/res.php?key=" . $this->CAPTCHA_API_KEY. "&action=reportgood&id=" . $sGoogleToken['id'];
						$ResReportGOOD = file_get_contents($ReportGOOD);
					} else {
						$ReportBAD = "http://rucaptcha.com/res.php?key=" . $this->CAPTCHA_API_KEY. "&action=reportbad&id=" . $sGoogleToken['id'];
						$ResReportBAD = file_get_contents($ReportBAD);
					}
					// echo '<pre>';
					// 	echo 'Успешная капча';
					// 	print_r($ResReportGOOD);
					// echo '</pre>';
					$bValid = true;
				} else {
					$ReportBAD = "http://rucaptcha.com/res.php?key=" . $this->CAPTCHA_API_KEY. "&action=reportbad&id=" . $sGoogleToken['id'];
					$ResReportBAD = file_get_contents($ReportBAD);
					// echo '<pre>';
					// 	echo 'Плохая капча';
					// 	print_r($ReportBAD);
					// echo '</pre>';
				}
				$try ++;
			}
			return $decoded;
		}
		private function SendQueryGIBDD_Driver($url, $num, $date){
			$bValid = false;
            $maxTry = 4;$try = 0;
            while (!$bValid) {
	            $sGoogleToken = $this->GetReCaptchaToken($this->GIBDD_SITEKEY, 'https://xn--90adear.xn--p1ai/about/personal', $this->SERVICE_URI . '&action=check_driver');

				$arFields = array(
					"num"	=>	$num,
					"date"	=>	$date,
					"reCaptchaToken"	=>	$sGoogleToken['key']
				);
				// echo '<pre>';
				// print_r($arFields);
				// echo '</pre>';
				// echo $try;
				$response = $this->Curl_post($url,$arFields,'driver');
				$decoded = json_decode($response,true);
				// echo '<pre>';
				// 	print_r($decoded);
				// echo '</pre>';
				if((isset($decoded["code"]) && $decoded["code"] != 201) || (isset($decoded["status"]) && $decoded["status"] != 201) || $try >= $maxTry){
					if (!$try >= $maxTry) {
						$ReportGOOD = "http://rucaptcha.com/res.php?key=" . $this->CAPTCHA_API_KEY. "&action=reportgood&id=" . $sGoogleToken['id'];
						$ResReportGOOD = file_get_contents($ReportGOOD);
					}
					// echo '<pre>';
					// 	echo 'Успешная капча';
					// 	print_r($ResReportGOOD);
					// echo '</pre>';
					$bValid = true;
				} else {
					$ReportBAD = "http://rucaptcha.com/res.php?key=" . $this->CAPTCHA_API_KEY. "&action=reportbad&id=" . $sGoogleToken['id'];
					$ResReportBAD = file_get_contents($ReportBAD);
					// echo '<pre>';
					// 	echo 'Плохая капча';
					// 	print_r($ReportBAD);
					// echo '</pre>';
				}
				$try ++;
			}
			return $decoded;
		}
		private function SendQueryGIBDD_Fines($url, $regnum, $regreg, $stsnum, $alien){
			$bValid = false;
            $maxTry = 4;$try = 0;
            while (!$bValid) {
	            $sGoogleToken = $this->GetReCaptchaToken($this->GIBDD_SITEKEY, 'https://xn--90adear.xn--p1ai/about/personal', $this->SERVICE_URI . '&action=check_fines');

				if ($alien) {
					$arFields = array(
						"regnum"	=>	$regnum,
						"alien" =>	$alien,
						"reCaptchaToken"	=>	$sGoogleToken['key']
					);
				} else {
					$arFields = array(
						"regnum"	=>	$regnum,
						"regreg"	=>	$regreg,
						"stsnum"	=>	$stsnum,
						"reCaptchaToken"	=>	$sGoogleToken['key']
					);
				}
				// echo '<pre>';
				// print_r($arFields);
				// echo '</pre>';
				$response = $this->Curl_post($url,$arFields, 'fines');
				$decoded = json_decode($response,true);
				// echo '<pre>';
				// 	print_r($decoded);
				// echo '</pre>';
				if((isset($decoded["code"]) && $decoded["code"] != 201) || (isset($decoded["status"]) && $decoded["status"] != 201) || $try >= $maxTry){
					if (!$try >= $maxTry) {
						$ReportGOOD = "http://rucaptcha.com/res.php?key=" . $this->CAPTCHA_API_KEY. "&action=reportgood&id=" . $sGoogleToken['id'];
						$ResReportGOOD = file_get_contents($ReportGOOD);
					}
					// echo '<pre>';
					// 	echo 'Успешная капча';
					// 	print_r($ResReportGOOD);
					// echo '</pre>';
					$bValid = true;
				} else {
					$ReportBAD = "http://rucaptcha.com/res.php?key=" . $this->CAPTCHA_API_KEY. "&action=reportbad&id=" . $sGoogleToken['id'];
					$ResReportBAD = file_get_contents($ReportBAD);
					// echo '<pre>';
					// 	echo 'Плохая капча';
					// 	print_r($ReportBAD);
					// echo '</pre>';
				}
				$try ++;
			}
			return $decoded;
		}
	private function GetReCaptchaToken($site, $pageUrl, $action, $sCapService = 'http://rucaptcha.com'){
		$hello = ''; $iretry = 0; $sToken = '';
	    $sEnterUrl = $sCapService."/in.php?key=".$this->CAPTCHA_API_KEY."&method=userrecaptcha&googlekey=".$site.$action."&pageurl=".$pageUrl;
	    //print $sEnterUrl."\n";
	    while (!$hello) {
	        $retrieve= file_get_contents($sEnterUrl);
	        $first = array($retrieve);
	        $result = explode('OK|',$first[0]);
	        //print_r($first);
	        $hello = trim($result[1]);
	        if ($hello) break;
	        sleep(5);
	        $iretry++;
	        if ($iretry==10) break;
	    }
	    if ($hello) {
	        $con=$sCapService."/res.php?key=".$this->CAPTCHA_API_KEY."&action=get&id=".$hello;
	        //print "$con\n";
	        $iTries = 0;
	        $bValid = false;
	        while (!$bValid) {
	            sleep(1);
	            $getting = file_get_contents($con);
	            //print $getting." try: $iTries\n";
	            $second = array($getting);
	            $secondresult = explode('OK|',$second[0]);
	            if (isset($secondresult[1])) $sToken=trim($secondresult[1]); else $sToken='';
	            $iTries++;
	            if ($iTries==25) break;
	            if ($sToken) break;
	        }
	    }
	    return array("id"=>$hello, "key"=>$sToken);
	}

		private function Curl_post( $url, $data = array(), $name ){
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
			curl_setopt($ch, CURLOPT_TIMEOUT, 400);
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			if (substr($url,0,5)=='https') {
		        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		    }

			if ($name == 'car') {
		    	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				    "Accept: application/json, text/javascript, */*; q=0.01",
					"Accept-Encoding: gzip, deflate, br",
					"Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3",
					"Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
					"Host: xn--b1afk4ade.xn--90adear.xn--p1ai",
					"Origin: https://xn--90adear.xn--p1ai",
					"Referer: https://xn--90adear.xn--p1ai/check/auto",
					"User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36"
				));
			} else if($name == 'driver') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					"Accept: application/json, text/javascript, */*; q=0.01",
					"Accept-Encoding: gzip, deflate, br",
					"Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3",
					"Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
					"Host: xn--b1afk4ade.xn--90adear.xn--p1ai",
					"Origin: https://xn--90adear.xn--p1ai",
					"Referer: https://xn--90adear.xn--p1ai/check/driver",
					"User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36"
				));
			} else if($name == 'fines') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					"Accept: application/json, text/javascript, */*; q=0.01",
					"Accept-Encoding: gzip, deflate, br",
					"Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3",
					"Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
					"Host: xn--b1afk4ade.xn--90adear.xn--p1ai",
					"Origin: https://xn--90adear.xn--p1ai",
					"Referer: https://xn--90adear.xn--p1ai/check/fines",
					"User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36"
				));
			}

			$response = curl_exec($ch);

			if($errno = curl_errno($ch)) {
			    $error_message = curl_strerror($errno);
			    $response =  "cURL error ({$errno}):\n {$error_message}";
			}
			curl_close($ch);	
			return $response;
		}
	}
	$GibddParser = new GibddParser();
	//print_r( $class->CheckAuto("X7L5SREAG59222973") );
	if(isset($_REQUEST["action"]) && isset($_REQUEST["vin"])){
		$action = $_REQUEST["action"]; // history, dtp ....
		$vin = $_REQUEST["vin"]; // X7L5SREAG59222973
		echo json_encode($GibddParser->CheckAuto($action,$vin),JSON_UNESCAPED_UNICODE);
	} elseif(isset($_REQUEST["action"]) && isset($_REQUEST["num"]) && isset($_REQUEST["date"])){
		$action = $_REQUEST["action"]; // driver
		$num = $_REQUEST["num"]; // формат 2328120303
		$date = $_REQUEST["date"]; // формат 2016-06-10
		echo json_encode($GibddParser->CheckDriver($action, $num, $date), JSON_UNESCAPED_UNICODE);
	} elseif (isset($_REQUEST["action"]) && isset($_REQUEST["regnum"]) && isset($_REQUEST["regreg"]) && isset($_REQUEST["stsnum"]) || isset($_REQUEST["action"]) && isset($_REQUEST["regnum"]) && isset($_REQUEST["alien"]) ) {
		$action = $_REQUEST["action"]; // fines
		$regnum = $_REQUEST["regnum"]; // с829ме
		$regreg = $_REQUEST["regreg"]; // 50
		$stsnum = $_REQUEST["stsnum"]; // 5028445895
		if (isset($_REQUEST["alien"])) {
			$alien = $_REQUEST["alien"]; // Иностранные авто
		} else {
			$alien = false;
		}
		echo json_encode($GibddParser->CheckFines($action, $regnum, $regreg, $stsnum, $alien), JSON_UNESCAPED_UNICODE);
	}

file_put_contents('filename.txt',date('[Y-m-d H:i:s] ') . json_encode($_REQUEST) . PHP_EOL, FILE_APPEND | LOCK_EX);

?>