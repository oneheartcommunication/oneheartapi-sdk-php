<?php

/**
* One Heart API SDK
* @author Teddy Gandon
*/

define("OHC_API_URL", "http://www.oneheart.fr/api/");
define("OHC_API_METHOD_GET", "get");
define("OHC_API_METHOD_POST", "post");
define("OHC_API_METHOD_PUT", "put");
define("OHC_API_METHOD_DELETE", "delete");

/**
* The main client class.
*/

class Oneheart_apiclient {
	
	public $users, $spots, $videos, $news, $events, $actions, $client_id, $client_secret, $debug;
	
	/**
	* __construct code.
 	* @param string $client_id The public key access
	* @param string $client_secret The private key
	* @param boolean $debug Set on TRUE to see all the requests. DO NOT USE IT IN PRODUCTION ENVIRONMENT!
	* @see https://oneheart.zendesk.com/hc/fr/articles/204933641-API
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
		$this->news = new Oneheart_news($this);
		$this->events = new Oneheart_events($this);
		$this->actions = new Oneheart_actions($this);
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
		
		// Tell to cURL to return data
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
	* Main init function. Override it to set data.
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
	
	public function summary($offset = 0, $max = 10, $fields = NULL, $sort = NULL, $where = NULL) {
		// Create basic data
		$d = array(
			"offset"=>$offset,
			"max"=>$max
		);
		
		// Add fields option
		if($fields) $d["fields"] = implode(",",$fields);
		
		// Add sort option
		if($sort) $d["sort"] = $sort;
		
		// Add where option
		if($where) {
			foreach($where as $label=>$value) {
				$d[$label] = $value;
			}
		}
		
		// Fetch response
		$response = $this->master->_request($this->resource, $d);
		
		// Throw an error if there is an error
		if($response["success"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Return response data
		return $response["data"];
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
		if($response["success"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Return response data
		return $response["data"];
	}
	
	/**
	* Register an action on the post. You must provide an oauth_token with this method.
 	* @param int $id The ID of the post.
	* @param string $action_id The ID of the action.
	* @param string $oauth_token The OAuth token provided from a user accreditation.
	* @return array
	*/
	
	public function do_action($id, $action_id, $oauth_token) {
		// Fetch response
		$response = $this->master->_request($this->resource."/".$id."/".$action_id."?oauth_token=".$oauth_token, NULL, OHC_API_METHOD_POST);
		
		// Throw an error if there is an error
		if($response["success"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Return response data
		return $response["data"];
	}
	
}

/**
* This class represents the users resource type. You can <u>DONATE</u> to user.
* @see https://oneheart.zendesk.com/hc/fr/articles/204933641-API
*/

class Oneheart_users extends APIModule {
	
	/**
	* Main init function. Override it to set data.
	* @override
	*/
	
	protected function initialize() {
		$this->resource = "users"; // Set resource type
	}
	
	/**
	* Returns a user with an OAuth token.
	* @param string $oauth The oauth_token provided by user accreditation.
	* @param array $fields The fields of the post. By default, each post will return the pair id/name.
	* @return array
	*/
	
	public function me($token, $fields = NULL) {
		// Build parameters
		$d = array("oauth_token"=>$token);
		if($fields) $d["fields"] = implode(",",$fields);
		
		// Fetch response
		$response = $this->master->_request("me", $d);
		
		// Throw an error if there is an error
		if($response["success"] == FALSE) {
			throw new Exception($response["error"]);
			return FALSE;
		}
		
		// Return response data
		return $response["data"];
	}
	
}

/**
* This class represents the spots resource type.
* @see https://oneheart.zendesk.com/hc/fr/articles/204933641-API
*/

class Oneheart_spots extends APIModule {
	
	/**
	* Main init function. Override it to set data.
	* @override
	*/
	
	protected function initialize() {
		$this->resource = "spots";
	}
	
}

/**
* This class represents the events resource type.
* @see https://oneheart.zendesk.com/hc/fr/articles/204933641-API
*/

class Oneheart_events extends APIModule {
	
	/**
	* Main init function. Override it to set data.
	* @override
	*/
	
	protected function initialize() {
		$this->resource = "events";
	}
	
}

/**
* This class represents the videos resource type.
* @see https://oneheart.zendesk.com/hc/fr/articles/204933641-API
*/

class Oneheart_videos extends APIModule {
	
	/**
	* Main init function. Override it to set data.
	* @override
	*/
	
	protected function initialize() {
		$this->resource = "videos";
	}
	
}

/**
* This class represents the news resource type.
* @see https://oneheart.zendesk.com/hc/fr/articles/204933641-API
*/

class Oneheart_news extends APIModule {
	
	/**
	* Main init function. Override it to set data.
	* @override
	*/
	
	protected function initialize() {
		$this->resource = "news";
	}
	
}

/**
* This class represents the actions resource type.
* @see https://oneheart.zendesk.com/hc/fr/articles/204933641-API
*/

class Oneheart_actions extends APIModule {
	
	/**
	* Main init function. Override it to set data.
	* @override
	*/
	
	protected function initialize() {
		$this->resource = "actions";
	}
	
}