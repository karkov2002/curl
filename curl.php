<?php

/**
 * Class test autoload/vendor
 */

namespace curl;

use curl\http;

class curl
{

	public function sendRequest($requestHttp, $logs = false, $timeout = 30)
	{
		//Some constante values
		$protocol	 = 'HTTP/1.0';
		$verify_ssl	 = true;

		//CURL INIT
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $requestHttp->uri . $requestHttp->get);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "");

		//HTTP HEADER
		$header = array();
		foreach ($requestHttp->header as $key => $value) {
			$header[] = $key . ": " . $value;
		}

		//Cookies
		if ($requestHttp->cookies !== null && is_array($requestHttp->cookies) && count($requestHttp->cookies) > 0) {
			$cookiesString = '';
			foreach ($requestHttp->cookies as $key => $value) {
				$cookiesString .= $key . '=' . $value . ';';
			}
			$header[] = "Cookie: " . substr($cookiesString, 0, -1);
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		//HTTP PROTOCOL
		//CURL_HTTP_VERSION_NONE (curl decide which protocol to use)
		//CURL_HTTP_VERSION_1_0 (force HTTP/1.0)
		//CURL_HTTP_VERSION_1_1 (force HTTP/1.1)
		switch ($protocol) {
			case 'HTTP/1.0':
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
				break;
			case 'HTTP/1.1':
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				break;
			default :
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_NONE);
		}

		//PORT
		curl_setopt($ch, CURLOPT_PORT, $requestHttp->port);

		//FORCE USER AGENT
		$userAgent = 'PHP script';
		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

		//SSL VERIFICATION
		if ($verify_ssl === true) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}

		//Body request	
		if ($requestHttp->body !== null && $requestHttp->body != '') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $requestHttp->body);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		}

		//Get header and body response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		//curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
		//Curl request for logging
		if ($logs === true) {
			$verbose = fopen('php://temp', 'w+');
			curl_setopt($ch, CURLOPT_STDERR, $verbose);
		}

		//Send http request
		$response = curl_exec($ch);

		//curl error
		if ($response === false) {
			throw new \Exception("Curl Error : " . curl_error($ch));
		}

		//Logs curl
		if ($logs === true) {
			$contentLog	 = $this->constructLog($verbose, $requestHttp, $response);
			$filename	 = date('Ymd') . '_curl_response';
			flog($contentLog, $filename);
		}

		//Construct response
		$responseHttp			 = new http();
		$header_size			 = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$responseHttp->header	 = substr($response, 0, $header_size);
		$responseHttp->body		 = substr($response, $header_size);

		return $responseHttp;
	}

	/**
	 * Construct the log content
	 * @param type $verbose
	 * @param type $requestHttp
	 * @param type $response
	 * @return type
	 */
	private function constructLog($verbose, $requestHttp, $response)
	{
		//header and curl connection
		rewind($verbose);
		$verboseLog = stream_get_contents($verbose);

		//Message
		ob_start();
		echo '-------------------------------------' . PHP_EOL;
		echo 'CURL HEADER SENDED AT ' . date('Y-m-d H:i:s') . PHP_EOL;
		echo '-------------------------------------' . PHP_EOL;
		print_r($verboseLog);
		echo '-------------------------------------' . PHP_EOL;
		echo 'BODY SENDED AT ' . date('Y-m-d H:i:s') . PHP_EOL;
		echo '-------------------------------------' . PHP_EOL;
		print_r($requestHttp->body);
		echo PHP_EOL . '-------------------------------------' . PHP_EOL;
		echo 'CURL RESPONSE RECEIVED AT ' . date('Y-m-d H:i:s') . PHP_EOL;
		echo '-------------------------------------' . PHP_EOL;
		print_r($response);
		echo PHP_EOL . '=====================================' . PHP_EOL . PHP_EOL;
		$contentLog = ob_get_clean();

		return $contentLog;
	}

}
