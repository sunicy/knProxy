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
		$url_components = parse_url($url);
		if ($url_components === false)
			return array(
				"domain" => "-",
				"path" => "/",
				"subdomains" => array("-")
			);
		
		return array(
			"domain" => $url_components["host"],
			"path" => $url_components["path"],

		)
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
		foreach(array_reverse(split(".", $domains)) => $part) {
			$tail = $part . $tail;
			$domains[] = $tail;
			$tail = "." . $tail;
		}
		$domains[] = $domain;
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

	public function put_cookie($name, $value, $domain="*", $expires=null) {
		if (isset($this->skipped_cookies[$name]))
			return null; // sorry, can't!
		$this->hub[$domain][$name] = array(
			"value" => $value,
			"expires" => $expires
		);
		return &$this->hub[$domain][$name];
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

	protected function fetch_all_cookies($domain_list, $override=true) {
		$result = array();
		foreach ($domain_list as $domain => &$cookies)
			foreach ($cookies as $name => &$cookie)
				if (!isset($result[$name]) || $override)
					$result[$name] = $cookie["value"];
		return $result;
	}

	function __construct(&$hub) {
		$this->hub = &$hub;
	}

	function apply_server_cookie($setcookie, $url) {
	}

	function apply_client_cookie($cookie, $url) {

	}

	/*
	 * returns cookies should be sent to Server
	 *   in form of multiple lines with 'Cookie: A=B;C=D'
	 */
	function gen_server_cookies($url) {
		$url_info = $this->extract_url($url);
		$domain = $url_info["domain"];
		$domains = $this->extract_domain($domain);
		
		$cookies_to_set = array_map(function ($cookie) {
			return $
		}, $this->fetch_all_cookies($domains, false);
		
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
			$cookies_to_set, 
			$this->fetch_all_cookies($all_domains)
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
