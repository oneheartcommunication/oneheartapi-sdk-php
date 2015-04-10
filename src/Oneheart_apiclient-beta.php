<?php

/**
* One Heart API SDK
* @author Teddy Gandon
*/

define("OHC_API_URL", "http://www.oneheartcommunication.com/api-beta/");
define("OHC_API_METHOD_GET", "get");
define("OHC_API_METHOD_POST", "post");
define("OHC_API_METHOD_PUT", "put");
define("OHC_API_METHOD_DELETE", "delete");

/**
* The main client class.
*/

class Oneheart_apiclient {
	
	public $users, $spots, $videos, $insights, $client_id, $client_secret, $debug;
	
	/**
	* __construct code.
 	* @param string $client_id The public key access
	* @param string $client_secret The private key
	* @param boolean $debug Set on TRUE to see all the requests. DO NOT USE IT IN PRODUCTION ENVIRONMENT!
	*/
	
	public function __construct($client_id, $client_secret, $debug = FALSE) {
		// Set credentials
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		
		// Set debug mode
		$this->debug = $debug;
		
		// Create API modules
		$this->users = new Oneheart_users($this);
		$this->spots = new Oneheart_spots($this);
		$this->videos = new Oneheart_videos($this);
		$this->insights = new Oneheart_insights($this);
	}
	
	/**
	* Build a request to the endpoint server with cURL.
 	* @param string $path The path after OHC_API_URL
	* @param array $data An array of parameters
	* @param string $method The method used with the HTTP request. By default OHC_API_METHOD_GET.
	* @return array
	*/
	
	public function _request($path, $data = NULL, $method = OHC_API_METHOD_GET) {
		if(is_null($data)) $data = array(); // If $data is null, create it empty.
		
		// Create the query body
		$body = http_build_query($data);
		
		// Debug stack
		if($this->debug) var_dump($data);
		
		// Switch methods
		if($method != OHC_API_METHOD_GET) {
			$c = curl_init(OHC_API_URL.$path);
			curl_setopt($c, CURLOPT_POSTFIELDS, $body);
		} else {
			$c = curl_init(OHC_API_URL.$path."?".$body);
		}
		if($method == OHC_API_METHOD_POST) curl_setopt($c, CURLOPT_POST, TRUE);
		if($method == OHC_API_METHOD_PUT) curl_setopt($c, CURLOPT_PUT, TRUE);
		if($method == OHC_API_METHOD_DELETE) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		
		// Tell to cURL to return datas
		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
		
		// Set HTTP basic auth with client_id/client_secret
		curl_setopt($c, CURLOPT_USERPWD, $this->client_id . ":" . $this->client_secret);
		
		// Execute query
		$content = curl_exec($c);
		
		// Debug stack
		if($this->debug) var_dump($content);
		
		// Decode content and return
		$content = json_decode($content, TRUE);
		return $content;
	}
	
}

/**
* The APIModule class. Each module onto APIClient must extends this abstract class. It provides usefull methods such as summary, single, submit, etc.
* @abstract
*/

abstract class APIModule {
	
	/**
	* The reference to the master APIClient class.
	* @access protected
	* @var APIClient
	*/
	
	protected $master;
	
	/**
	* The resource type (e.g. videos, events, spots, users...)
	* @access protected
	* @var string
	*/
	protected $resource;
	
	/**
	* __construct code.
 	* @param APIClient $master The APIClient master reference
	*/
	
	public function __construct($master) {
		$this->master = $master; // Set master
		$this->initialize(); // Init
	}
	
	/**
	* Main init function. Override it to set datas.
	*/
	
	protected function initialize() {
		// Override me =)
	}
	
	/**
	* Get the summary (many posts) of the resource type.
 	* @param int $offset The offset of the summary. By default 0.
	* @param int $max The max amount of posts returned into the summary. You can have a maximum of 50 posts. By default 10.
	* @param array $fields The fields of each post returned into the summary. By default, each post will return the pair id/name.
	* @param string $sort The sort method (-id, name...). By default, the "id" sort will be chosen by the API.
	* @return array
	*/
	
	public function summary($offset = 0, $max = 10, $fields = NULL, $sort = NULL) {
		// Create basic datas
		$d = array(
			"offset"=>$offset,
			"max"=>$max
		);
		
		// Add fields option
		if($fields) $d["fields"] = implode(",",$fields);
		
		// Add sort option
		if($sort) $d["sort"] = $sort;
		
		// Fetch response
		$response = $this->master->_request($this->resource, $d);
		
		// Throw an error if there is an error
		if($response["status"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Return response datas
		return $response["datas"];
	}
	
	/**
	* Get a single post from the resource type.
 	* @param int $id The ID of the post.
	* @param array $fields The fields of the post. By default, each post will return the pair id/name.
	* @return array
	*/
	
	public function single($id, $fields = NULL) {
		// Create options
		$d = array();
		if($fields) $d["fields"] = implode(",",$fields);
		
		// Fetch response
		$response = $this->master->_request($this->resource."/".$id, $d);
		
		// Throw an error if there is an error
		if($response["status"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Return response datas
		return $response["datas"];
	}
	
	/**
	* Submit a post onto the API. You must provide an oauth_token with this method.
	* @param array $datas The datas of the posts. Each post has different fields to submit. Please see the official documentation.
	* @param string $oauth_token The OAuth token provided from a user accreditation.
	* @return array
	* @see http://www.oneheartcommunication.com/docs/api#c1
	* @see http://www.oneheartcommunication.com/docs/oauth
	*/
	
	public function submit($datas, $oauth_token) {
		// Fetch response
		$response = $this->master->_request($this->resource."?oauth_token=".$oauth_token, $datas, OHC_API_METHOD_POST);
		
		// Throw an error if there is an error
		if($response["status"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Return response datas
		return $response["datas"];
	}
	
}

/**
* This class represents the users resource type. You can <u>DONATE</u> to user.
* @see http://www.oneheartcommunication.com/docs/api#c1
*/

class Oneheart_users extends APIModule {
	
	/**
	* Main init function. Override it to set datas.
	* @override
	*/
	
	protected function initialize() {
		$this->resource = "users"; // Set resource type
	}
	
	/**
	* Donate to a user. This method will return an URL to redirect him.
	* @param int $id The user ID to donate.
	* @param string $first_name The first name of the giver.
	* @param string $last_name The last name of the giver.
	* @param string $email The email address of the giver.
	* @param string $address The postal address of the giver.
	* @param string $zip_code The zip code of the giver.
	* @param string $city The city of the giver.
	* @param string $country The country of the giver.
	* @param int $amount The amout of the donation.
	* @param string $currency The currency of the donation (only EUR is supported at this time)
	* @param string $redirect_success The URL where the user is redirected if the donation succeed.
	* @param string $redirect_fail The URL where the user is redirected if the donation fail.
	* @param string $redirect_cancel The URL where the user is redirected if the donation is canceled.
	* @param string $ping_url The API can ping an URL when the donation is completed.
	* @param boolean $monthly Set on TRUE if the donation is monthly.
	* @return array
	* @see http://www.oneheartcommunication.com/docs/api#c1
	*/
	
	public function donate(
		$id, 
		$first_name, 
		$last_name, 
		$email, 
		$address, 
		$zip_code, 
		$city, 
		$country, 
		$amount, 
		$currency, 
		$redirect_success, 
		$redirect_fail, 
		$redirect_cancel, 
		$ping_url = "", 
		$monthly = FALSE
	) {
		// Build parameters
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
		
		// Fetch response
		$response = $this->master->_request($this->resource."/".$id."/donate", $d, OHC_API_METHOD_POST);
		
		// Throw an error if there is an error
		if($response["status"] === FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Return response datas
		return $response["datas"];
	}
	
	/**
	* Returns a user with an OAuth token.
	* @param string $oauth The oauth_token provided by user accreditation.
	* @param array $fields The fields of the post. By default, each post will return the pair id/name.
	* @return array
	* @see http://www.oneheartcommunication.com/docs/api#c1
	* @see http://www.oneheartcommunication.com/docs/oauth
	*/
	
	public function me($token, $fields = NULL) {
		// Build parameters
		$d = array("oauth_token"=>$token);
		if($fields) $d["fields"] = implode(",",$fields);
		
		// Fetch response
		$response = $this->master->_request($this->resource."/me", $d);
		
		// Throw an error if there is an error
		if($response["status"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Return response datas
		return $response["datas"];
	}
	
}

/**
* This class represents the spots resource type.
* @see http://www.oneheartcommunication.com/docs/api#c1
*/

class Oneheart_spots extends APIModule {
	
	/**
	* Main init function. Override it to set datas.
	* @override
	*/
	
	protected function initialize() {
		$this->resource = "spots";
	}
	
}

/**
* This class represents the events resource type.
* @see http://www.oneheartcommunication.com/docs/api#c1
*/

class Oneheart_events extends APIModule {
	
	/**
	* Main init function. Override it to set datas.
	* @override
	*/
	
	protected function initialize() {
		$this->resource = "events";
	}
	
}

/**
* This class represents the videos resource type. You can <u>WATCH</u> a video (captain obvious).
* @see http://www.oneheartcommunication.com/docs/api#c1
*/

class Oneheart_videos extends APIModule {
	
	/**
	* Main init function. Override it to set datas.
	* @override
	*/
	
	protected function initialize() {
		$this->resource = "videos";
	}
	
	/**
	* Returns some streams URLs for a given video.
	* @param int $id The ID of the video.
	* @return array
	* @see http://www.oneheartcommunication.com/docs/api#c1
	*/
	
	public function watch($id) {
		// Fetch response
		$response = $this->master->_request($this->resource."/".$id."/watch", NULL, OHC_API_METHOD_POST);
		
		// Throw an error if there is an error
		if($response["status"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Return response datas
		return $response["datas"];
	}
	
}

/**
* This class represents the public insights resource type.
* @see http://www.oneheartcommunication.com/docs/api#c1
*/

class Oneheart_insights extends APIModule {
	
	/**
	* Returns global insights from One Heart platform
	* @param string $oauth_token A valid oauth_token
	* @param array $types The stats types to return. Can contains: "spots:app", "spots:widget", "videos:widget", "donation:widget"
	* @param array $fields The fields to return for the query. You can return: "analytics.users", "analytics.newUsers", "analytics.pageviews", "analytics.uniquePageviews", "analytics.date", "user.theme", "post.theme", "post.date"
	* @param string $group_by Group by a field. You must chose a field contained in the $fields array.
	* @param string $sort Sort by a field. You must chose a field contained in the $fields array.
	* @return array
	* @see http://www.oneheartcommunication.com/docs/api#c1
	*/
	
	public function get(
	  $oauth_token, 
	  $types, 
	  $fields, 
	  $group_by, 
	  $sort = NULL
	) {
		
		// Build request
		$datas = array(
			"oauth_token"=>$oauth_token,
			"types"=>implode(",", $types),
			"fields"=>implode(",", $fields),
			"group_by"=>$group_by,
			"sort"=>$sort
		);
		
		// Fetch response
		$response = $this->master->_request("insights/", $datas, OHC_API_METHOD_GET);
		
		// Throw an error if there is an error
		if($response["status"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Trigger a warning
		if(isset($response["warning"])) {
			trigger_error($response["warning"]);
		}
		
		// Return response datas
		return $response["datas"];
	}
	
}