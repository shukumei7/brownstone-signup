<?php

class MahoAPI {

	public static $timezone = 'Asia/Manila';
	public static $api = null;
	private static $__errors = array();
	private static $__retryLimit = 0;
	private static $__retryWait = 5;
	
	public static function start($url, $username, $apikey) {
		return self::$api = new MahoAPI($url, $username, $apikey);
	}
	
	public static function call($command, $parameters = array(), $data = array()) {
		return self::$api->request($command, $parameters, $data);
	}
 	
	public static function token($username, $api_key, $time) {
		$timestamp = time();
		$dt = new DateTime("now", new DateTimeZone(self::$timezone)); //first argument "must" be a string
		$dt->setTimestamp($timestamp); //adjust the object to correct timestamp
		return sha1($api_key.$time.$username);
	}
	
	public static function errors($flush = false) {
		$out = self::$__errors;
		$flush && self::$__errors = array();
		return $out;
	}
	
	private $__url = '';
	private $__username = null;
	private $__apikey = null;
	
	public function __construct($url, $username, $apikey, $timezone = null) {
		if(($suffix = '/') !== substr($url, -1 * strlen($suffix))) {
			$url = $url.$suffix;
		} 
		$timezone && self::$timezone = $timezone;
 		$this->__url = $url.'api/';
		$this->__username = $username;
		$this->__apikey = $apikey;
	}
	
	private function __build($command, $parameters = array()) {
		if(!empty($parameters)) {
			if(!is_array($parameters)) {
				$parameters = array($parameters);
			}
			$params = $query = array();
			foreach($parameters as $key => $value) {
				if($key === '?') {
					if(is_array($value)) {
						foreach($value as $k => $v) {
							$query []= $k.'='.$v;							
						}
						continue;
					} else if(!empty($value)) {
						$query []= $value.'=';
					}
					continue;
				}
				if(is_string($key)) {
					$params []= $key.':'.$value;
					continue;
				}
				$params []= $value;
			}
			$extra = '';
			$params && $extra = '/'.implode('/', $params);
			if(!empty($query)) {
				$extra .= '?'.implode('&', $query);
			}
		}
		$url = $this->__url.ltrim($command, '/').@$extra;
		return $url;
	}
	
	public function request($command, $parameters = array(), $data = array()) {
		$context = null;
		
		$data['MahoAuth'] = array(
			'username'	=> $this->__username,
			'token'		=> MahoAPI::token($this->__username, $this->__apikey, $time = time()),
			'time'		=> $time
		);
		
		if(!empty($data)) {
			$options = array(
				'http' => array(
					'header'  => array(
						"Connection: close\r\n",
						// "Content-Type: application/x-www-form-urlencoded\r\n\r\n"
					),
					'method'  => 'POST',
					'content' => http_build_query($data)
				)
			);
			$context  = stream_context_create($options);
		}
		
		session_write_close();
		$tries = 0;
		$url = $this->__build($command, $parameters);
		
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		// TODO: finish curl with data and credentials
		
		while(false === ($result = @file_get_contents($url, false, $context)) && $tries++ < self::$__retryLimit) {
			self::$__errors []= $message = 'Retry '.$url.' : '.number_format($tries).json_encode(compact('url', 'options', 'result'), JSON_PRETTY_PRINT);
			error_log($message);
			sleep(self::$__retryWait);
		}
// @$_GET['debug'] && debug($result);
		if($result === false) {
			self::$__errors []= $message = 'Failed to retrieve: '.$url.json_encode(compact('options', 'result'), JSON_PRETTY_PRINT);
			error_log($message);
			return false;
		}
		
		return json_decode($result, true);
	}
	
}
