<?php
function clearcookies() {
	$oldtime = time() - 1000;
	foreach ($_COOKIE as $key => $val) {
		if (substr($key, "knp") !== 0)
			setcookie($key, "", $oldtime);
	}
}

function parse_fullurl($encoder, $fullurl) {
	$url_components = parse_url($fullurl);
	if (!isset($url_components["query"]))
		return array(
			"url" => "",
			"bind_addr" => ""
		);
	parse_str($url_components["query"], $query);
	if (isset($query["____url"])) {
		$url = $query["____url"];
		if (!preg_match("~/~", $url)) {
			$encoder->serverKey = KNPROXY_SECRET;
			if (isset($query["____encrypt_key"])) {
				$key = (int)$query['____encrypt_key'];
				$encoder->setKey($key);
				$encoder->serverKey='';
			}
			$url = $encoder->decode($url);
			$encoder->serverKey = KNPROXY_SECRET;
			$encoder->setKey(0);
		}
	}
	else
		$url = ""; // forget about referer!
	
	$bind_addr = (isset($query["____bind_addr"])) ? 
		$query["____bind_addr"] : null;
	return array(
		"url" => $url,
		"bind_addr" => $bind_addr
	);
}

function checkHttpURL($url){
	//CHECKS URL
	if(strtolower(substr($url,0,4))!='http'){
		return 'http://' . $url;
	}
	return $url;
}
function fetch_error($code){
	$errors=Array( 400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Auth Required',
			408 => 'Request Timed Out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			414 => 'URI Too Long',
			413 => 'Request Entity Too Large',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			503 => 'Service Unavailable',
			502 => 'Bad Gateway',
			504 => 'Gateway Timeout',
			1001=> 'URL Restricted',
			1002=> 'Not Changed',
			1003=> 'Continue',
			1004=> 'Upgrade in Progress',
			1005=> 'Exceeded Bandwidth Limit',
			1006=> 'Server Down for Maintenence',
			1007=> 'Connection Closed by Remote Host',
			1008=> 'Protocol Not Supported',
			1009=> 'Not a valid URL',
			1010=> 'Bad Information Returned',
			1011=> 'Connection Failed',
			1012=> 'Redirection Loop',
			1013=> 'Access Denied',
			1014=> 'No Content',
			1015=> 'Remote Server Went Away',
			1016=> 'SSL Not Supported',
			1994=> 'Debug Mode',
			2001=> 'Sysinfo'
			);
	if($code!=0)
		return $code . ' - ' . $errors[$code];
	else
		return 'Remote Fetch Operation Failed';
}
?>
