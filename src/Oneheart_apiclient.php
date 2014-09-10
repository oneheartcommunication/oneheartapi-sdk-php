<?php

define("OHC_API_URL", "http://www.oneheartcommunication.com/api/");
define("OHC_API_METHOD_GET", "get");
define("OHC_API_METHOD_POST", "post");
define("OHC_API_METHOD_PUT", "put");
define("OHC_API_METHOD_DELETE", "delete");

class Oneheart_apiclient {
	
	public $users, $spots, $client_id, $client_secret, $debug;
	
	public function __construct($client_id, $client_secret, $debug = FALSE) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->debug = $debug;
		$this->users = new Oneheart_users($this);
		$this->spots = new Oneheart_spots($this);
	}
	
	public function _request($path, $data = NULL, $method = OHC_API_METHOD_GET) {
		if(is_null($data)) $data = array();
		
		$body = http_build_query($data);
		
		if($this->debug) var_dump($data);
		
		if($method != OHC_API_METHOD_GET) {
			$c = curl_init(OHC_API_URL.$path);
			curl_setopt($c, CURLOPT_POSTFIELDS, $body);
		} else {
			$c = curl_init(OHC_API_URL.$path."?".$body);
		}
		
		if($method == OHC_API_METHOD_POST) curl_setopt($c, CURLOPT_POST, true);
		if($method == OHC_API_METHOD_PUT) curl_setopt($c, CURLOPT_PUT, true);
		if($method == OHC_API_METHOD_DELETE) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_USERPWD, $this->client_id . ":" . $this->client_secret);
		
		$content = curl_exec ($c);
		
		if($this->debug) var_dump($content);
		
		$content = json_decode($content, true);
		
		return $content;
	}
	
}

abstract class APIModule {
	
	protected $master;
	protected $ressource;
	
	public function __construct($master) {
		$this->master = $master;
		$this->initialize();
	}
	
	protected function initialize() {
		// Override me =)
	}
	
	public function summary($offset = 0, $max = 10, $fields = NULL) {
		$d = array(
			"offset"=>$offset,
			"max"=>$max
		);
		
		if($fields) $d["fields"] = implode(",",$fields);
		
		$response = $this->master->_request($this->ressource, $d);
		
		if($response["status"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		return $response["datas"];
	}
	
	public function single($id, $fields = NULL) {
		$d = array();
		
		if($fields) $d["fields"] = implode(",",$fields);
		
		$response = $this->master->_request($this->ressource."/".$id, $d);
		
		if($response["status"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		return $response["datas"];
	}
	
}

class Oneheart_users extends APIModule {
	
	protected function initialize() {
		$this->ressource = "users";
	}
	
	public function donate($id, $first_name, $last_name, $email, $address, $zip_code, $city, $country, $amount, $currency, $redirect_success, $redirect_fail, $redirect_cancel, $ping_url = "", $monthly = FALSE) {
		if($monthly === TRUE) $monthly = "true";
		else $monthly = "false";
		$d = array(
			"first_name"=>$first_name,
			"last_name"=>$last_name, 
			"email"=>$email, 
			"address"=>$address, 
			"zip_code"=>$zip_code, 
			"city"=>$city, 
			"country"=>$country, 
			"amount"=>$amount, 
			"currency"=>$currency, 
			"redirect_success"=>$redirect_success, 
			"redirect_fail"=>$redirect_fail, 
			"redirect_cancel"=>$redirect_cancel, 
			"ping_url"=>$ping_url, 
			"monthly"=>$monthly
		);
		
		$response = $this->master->_request($this->ressource."/".$id."/donate", $d, OHC_API_METHOD_POST);
		
		if($response["status"] === FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		return $response["datas"];
	}
	
	public function me($token, $fields = NULL) {
		$d = array("oauth_token"=>$token);
		
		if($fields) $d["fields"] = implode(",",$fields);
		
		$response = $this->master->_request($this->ressource."/me", $d);
		
		if($response["status"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		return $response["datas"];
	}
	
}

class Oneheart_spots extends APIModule {
	
	protected function initialize() {
		$this->ressource = "spots";
	}
	
}