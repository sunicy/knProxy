<?php
class CookieHub {
	
	// Caution: it's a referenced variable!
	// Format:
	//	array(
	//		"sunicy.com" => array(
	//			"UID" => array(
	//				"value" => "1001",
	//				"expires" => 1234566
	//			),
	//		)
	//	)
	protected $hub;
	// list cookie we neverset/delete
	protected $skipped_cookies = array(
		"PHPSESSID" => null
	);

	/* returns an array in form of 
		{"domain": "", "path": "", "subdomains":[]} */
	protected function extract_url($url) {
		$result = array(
			"domain" => "*",
			"path" => "/"
		);
		$url_components = parse_url($url);

		if ($url_components === false)
			return $result;
		
		if (isset($url_components["path"]))
			$result["path"] = $url_components["path"];

		if (isset($url_components["host"]))
			$result["domain"] = $url_components["host"];

		return $result;
	}

	/*
		api.lab.sunicy.com => (
			"*", "com", "sunicy.com", "lab.sunicy.com", "api.lab.sunicy.com"
		)
	*/
	protected function extract_domain($domain) {
		if (substr($domain, 0, 1) === ".") // avoiding empty str
			$domain = substr($domain, 1);
		$domains = array("*");
		$tail = "";
		foreach(array_reverse(explode(".", $domain)) as $part) {
			$tail = $part . $tail;
			$domains[] = $tail;
			$tail = "." . $tail;
		}
		return $domains;
	}

	public static function get_instance() {
		$SESSION_KEY = "____cookie_hub";
		if (!isset($_SESSION["$SESSION_KEY"]))
			$_SESSION["$SESSION_KEY"] = array();
		$hub = &$_SESSION["$SESSION_KEY"];
		return new CookieHub($hub);
	}

	public static function value_encode($s) {
		return str_replace(";", "%3B", rawurlencode($s));
	}

	public static function value_decode($s) {
		return urldecode(str_replace("%3B", ";"));
	}

	/*
	/*
	Returns domains containing named cookies
	*/
	protected function lookup_cookies($name, $domain=null, $match_all=false) {
		if ($domain == null)
			$domains = array_keys($this->hub);
		else
			$domains = array_reverse(extract_domain($domain));
		
		$result = array();
		foreach ($domains as $d) {
			if (isset($this->hub[$d]) && isset($this->hub[$d][$name])) {
				$result[] = &$this->hub[$d];
				if ($match_all)
					break;
			}
		}
		return $result;
	}


	protected function fetch_all_cookies($domain_list, $override=true) {
		$result = array();
		foreach ($domain_list as $domain) {
			if (!isset($this->hub[$domain]))
				continue;
			foreach ($this->hub[$domain] as $name => &$cookie)
				if (!isset($result[$name]) || $override)
					$result[$name] = $cookie["value"];
		}
		return $result;
	}

	function __construct(&$hub) {
		$this->hub = &$hub;
	}

	public function &put_cookie($name, $value, $domain="*", $expires=null) {
		if (isset($this->skipped_cookies[$name]))
			return null; // sorry, can't!
		$this->hub[$domain][$name] = array(
			"value" => $value,
			"expires" => $expires
		);
		return $this->hub[$domain][$name];
	}

	/* Given domain and the cookie name, delete cookie items matches.
	Only the first match would be deleted if delete_all is set to false
	Returns the count of deleted items.*/
	public function delete_cookie($name, $domain=null, $match_all=false) {
		$domains = $this->lookup_cookies($name, $domain, $match_all);
		$count = 0;
		foreach ($domains as &$cookies) {
			unset($cookies[$name]);
			++$count;
		}
		return $count;
	}

	/*
		$cookie=array("name"=>"...", "value"=>"...", "expires"=>123)
		$url="http://www.google.com/a/b/c"
	*/
	public function apply_cookie($cookie, $url) {
		$url_info = $this->extract_url($url);
		$domain = $url_info["domain"];
		return $this->put_cookie($cookie["name"], $cookie["value"], $domain);
	}

	public static function encode_cookie($name, $value) {
		return ;
	}

	/*
	 * returns cookies should be sent to Server
	 *   in form of multiple lines with 'Cookie: A=B;C=D'
	 */
	function gen_server_cookies($url) {
		$url_info = $this->extract_url($url);
		$domain = $url_info["domain"];
		$domains = $this->extract_domain($domain);
		
		$cookies_to_set = array();
		foreach ($this->fetch_all_cookies($domains, false) as $name => $value)
			$cookies_to_set[] = 
				self::value_encode($name) . "=" .  self::value_encode($value);
		
		if (count($cookies_to_set) == 0)
			return "";
		return "Cookie: ".implode(";", $cookies_to_set);
	}

	/*
	 * setcookie for the client request
	 */
	function gen_client_cookies($url) {
		$url_info = $this->extract_url($url);
		$domain = $url_info["domain"];
		$all_domains = array_keys($this->hub);
		$domains = $this->extract_domain($domain);
		$cookies_to_set = $this->fetch_all_cookies($domains);
		$cookies_to_del = array_diff_key(
			$this->fetch_all_cookies($all_domains),
			$cookies_to_set
		);
		// TODO: consider cookies sent by client, and less setcookie(s) are needed
		foreach ($cookies_to_del as $name => $value)
			setcookie($name, $value, 1); // Expired already!
		foreach ($cookies_to_set as $name => $value)
			setcookie($name, $value); // set it!
	}

	function flush_hub() {
		foreach ($this->hub as $key => $val) 
			unset($this->hub[$key]);
	}
}
?>
