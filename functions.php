<?php
	/**
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @copyright 2014, Chris Zuber
	 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	 * @package core_shared
	 * @version 2014-07-07
	 */

	if (!defined('PHP_VERSION_ID')) {
		$version = explode('.', PHP_VERSION);
		define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
	}

	if (PHP_VERSION_ID < 50207) {
		define('PHP_MAJOR_VERSION',   $version[0]);
		define('PHP_MINOR_VERSION',   $version[1]);
		define('PHP_RELEASE_VERSION', $version[2]);
	}

	spl_autoload_register('load_class');				 //Load class by naming it

	init();

	function init($site = null) {
		/**
		 * Initial configuration. Setup include_path, gather database
		 * connection information, set undefined properties to
		 * default values, start a new session, and set nonce
		 *
		 * @param string $site
		 * @return array $info
		 */

		//Include current directory, config/, and classes/ directories in include path
		set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . PATH_SEPARATOR . __DIR__  . DIRECTORY_SEPARATOR . 'config' . PATH_SEPARATOR . __DIR__  . DIRECTORY_SEPARATOR . 'classes');

		date_default_timezone_set('America/Los_Angeles');

		$connect = ini::load('connect');
		if(!isset($connect->site)) {
			if(is_null($site)) {
				($_SERVER['DOCUMENT_ROOT'] === __DIR__ . DIRECTORY_SEPARATOR or $_SERVER['DOCUMENT_ROOT'] === __DIR__) ? $connect->site = end(explode('/', preg_replace('/' . preg_quote(DIRECTORY_SEPARATOR, '/') .'$/', null, $_SERVER['DOCUMENT_ROOT']))) : $connect->site = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'])[1];
			}
		}

		if(!isset($connect->user)) $conenct->user = $connect->site;
		if(!isset($connect->database)) $connect->database = $connect->user;
		if(!isset($connect->server)) $connect->server = 'localhost';
		if(!isset($connect->debug)) $connect->debug = true;
		if(!isset($connect->type)) $connect->type = 'mysql';

		if(file_exists('./config/define.ini')) {
			foreach(parse_ini_file('./config/define.ini') as $key => $value) {
				define(strtoupper(preg_replace('/\s|-/', '_', $key)), $value);
			}
		}

		if(!defined('BASE')) define('BASE', __DIR__);
		if(!defined('URL')) ($_SERVER['DOCUMENT_ROOT'] === __DIR__ . DIRECTORY_SEPARATOR or $_SERVER['DOCUMENT_ROOT'] === __DIR__) ? define('URL', "${_SERVER['REQUEST_SCHEME']}://{$_SERVER['SERVER_NAME']}") : define('URL', "${_SERVER['REQUEST_SCHEME']}://{$_SERVER['SERVER_NAME']}/{$connect->site}");
		new session($connect->site);
		nonce(50);									// Set a nonce of n random characters
	}

	function config($settings_file = 'settings') {
		/**
		* Load and configure site settings
		* Loads all files in requires directive
		* Setup custom error handler
		*
		* @parmam void
		* @return void
		*/


		$settings = ini::load($settings_file);
		if(isset($settings->path)) {
			set_include_path(get_include_path() . PATH_SEPARATOR . preg_replace('/(\w)?,(\w)?/', PATH_SEPARATOR, $settings->path));
		}

		$error_handler = (isset($settings->error_handler)) ? $settings->error_handler : 'error_reporter_class';

		if(isset($settings->requires)) {
			foreach(explode(',', $settings->requires) as $file) {
				require_once(__DIR__ . '/' . trim($file));
			}
		}

		//Error Reporting Levels: http://us3.php.net/manual/en/errorfunc.constants.php
		if(isset($settings->debug)) {
			if(is_string($settings->debug)) $settings->debug = strtolower($settings->debug);
			error_reporting(0);
			switch($settings->debug) {
				case 'true': case 'all': case 'on': {
					set_error_handler($error_handler, E_ALL);
				} break;

				case 'false': case 'off': {
					set_error_handler($error_handler, 0);
				} break;

				case 'core': {
					set_error_handler($error_handler, E_CORE_ERROR | E_CORE_WARNING);
				} break;

				case 'strict': {
					set_error_handler($error_handler, E_ALL^E_USER_ERROR^E_USER_WARNING^E_USER_NOTICE);
				} break;

				case 'warning': {
					set_error_handler($error_handler, E_ALL^E_STRICT^E_USER_ERROR^E_USER_WARNING^E_USER_NOTICE);
				} break;

				case 'notice': {
					set_error_handler($error_handler, E_ALL^E_STRICT^E_WARNING^E_USER_ERROR^E_USER_WARNING^E_USER_NOTICE);
				} break;

				case 'developement': {
					set_error_handler($error_handler, E_ALL^E_NOTICE^E_WARNING^E_STRICT^E_DEPRECATED);
				} break;

				case 'production': {
					set_error_handler($error_handler, E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
				} break;

				default: {
					set_error_handler($error_handler, E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
				}
			}
		}

		else {
			error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
		}
	}

	function error_reporter_class($error_level, $error_message, $file, $line, $scope) {
		/**
		 * Default custom error handler function.
		 * Should never be used directly. Ran automatically when an error is caught.
		 *
		 * This function only passes the error details into a statically loaded class
		 *
		 * @param int $error_level (Integer version for E_FATAL, E_DEPRECIATED, etc)
		 * @param string $error_message (Error description generated by PHP)
		 * @param string $file (File in which the error occured)
		 * @param int $line (The line number on which the error occured)
		 * @param mixed $scope (All variables set in the current scope when error occured)
		 * @return mixed (boolen false will result in PHP default error handling)
		 */

		static $reporter = null;

		if(is_null($reporter)) {
			$settings = ini::load('settings');
			$reporter = error_reporter::load((isset($settings->error_method)) ? $settings->error_method : 'log');
			if(is_null($settings->error_method or $settings->error_method === 'log')) {
				$reporter->log = (isset($settings->error_log)) ? $settings->error_log : 'errors.log';
			}
		}

		return $reporter->report($error_level, $error_message, $file, $line, $scope);
	}

	function load() {									// Load resource from components directory
		/**
		 * Optimized resource loading using static variables and closures
		 * Intended to minimize resource usage as well as limit scope
		 * of variables from inluce()s
		 *
		 * Similar to include(), except that it shares limited resources
		 * and does not load into the current scope for security reasons.
		 *
		 * @params mixed args
		 * @return boolean
		 * @usage load(string | array[string | array[, ...]]*)
		 */

		static $DB, $load, $settings, $session, $login;
		$found = true;

		if(is_null($load)) {
			$DB = _pdo::load();
			$settings = ini::load('settings');
			$session = session::load();
			$login = login::load();
			$load = function($fname, &$found) use ($DB, $settings, &$session, $login) {
				(include(BASE . "/components/{$fname}.php")) or $found = false;
			};
		}

		foreach(flatten(func_get_args()) as $fname) {	// Unknown how many arguments passed. Loop through function arguments array
			$load($fname, $found);
		}
		return $found;
	}

	function load_results() {
		/**
		 * Similar to load(), except that it returns rather than prints
		 *
		 * @usage(string | array[string | array[, ...]]*)
		 * @param mixed (string, arrays, ... whatever. They'll be converted to an array)
		 * @return string (results echoed from load())
		 */

		ob_start();
		load(func_get_args());
		return ob_get_clean();
	}

	function load_class($class) {
		/**
		 * Loads a class script from wherever found in include_path
		 * Does not specify a default path to allow classes to be contained in
		 * more than a single directory
		 *
		 * Automatically adds the '.php' file extention, so don't use it when calling
		 *
		 * @params string $class
		 * @return void
		 * @example load_class('my_class')
		 * @example new my_class() //Using autoload
		 */

		require_once "{$class}.php";
	}

	function strip_enclosing_tag($html) {
		/**
		 * strips leading trailing and closing tags, including leading
		 * new lines, tabs, and any attributes in the tag itself.
		 *
		 * @param $html (html content to be stripping tags from)
		 * @return string (html content with leading and trailing tags removed)
		 * @usage strip_enclosing_tags('<div id="some_div" ...><p>Some Content</p></div>')
		 */

		return preg_replace('/^\n*\t*\<.+\>|\<\/.+\>$/', '', $html);
	}

	function debug($data, $comment = false) {
		/**
		 * Prints out information about $data
		 * Wrapped in html comments or <pre><code>
		 *
		 * @param mixed $data[, boolean $comment]
		 * @return void
		 */

		if($comment) {
			echo '<!--';
			print_r($data);
			echo '-->';
		}
		else {
			echo '<pre><code>';
			print_r($data);
			echo '</code></pre>';
		}
	}

	function require_login($role = null, $exit = 'notify') {
		$login = login::load();

		if(!$login->logged_in) {
			switch($exit) {
				case 'notify': {
					$resp = new json_response();
					$resp->notify(
						'We have a problem :(',
						'You must be logged in for that'
					)->send();
					return false;
					exit();
				}

				case 403: case '403': case 'exit': {
					http_status_code(403);
					exit();
				}

				case 'return' : {
					return false;
				}

				default: {
					http_status_code(403);
					exit();
				}
			}
		}

		elseif(isset($role) and strlen($role)) {
			$role = strtolower($role);
			$resp = new json_response();
			$roles = ['new', 'user', 'admin'];

			$user_level = array_search($login->role, $roles);
			$required_level = array_search($role, $roles);

			if(!$user_level or !$required_level) {
				$resp->notify(
					'We have a problem',
					'Either your user\'s role or the required role are invalid',
					'images/icons/info.png'
				)->send();
				return false;
				exit();
			}

			elseif($required_level > $user_level) {
				$resp->notify(
					'We have a problem :(',
					"You are logged in as {$login->role} but this action requires {$role}",
					'images/icons/info.png'
				)->send();
				return false;
				exit();
			}

			else {
				return true;
			}
		}
		else {
			return true;
		}
	}

	function check_nonce() {
		/**
		 * A nonce is a random string used for validation.
		 * One is generated for every session, and is used to
		 * prevent such things as brute force attacks on form submission.
		 * Without checking a nonce, it becomes easier to brute force login attempts
		 *
		 * @param void
		 * @return void
		 */

		if(!(array_key_exists('nonce', $_POST) and array_key_exists('nonce', $_SESSION)) or $_POST['nonce'] !== $_SESSION['nonce']) {
			$resp = new json_response();
			$resp->notify(
				'Something went wrong :(',
				'Your session has exired. Try refreshing the page',
				'images/icons/network-server.png'
			)->error(
				"nonce not set or does not match"
			)->sessionStorage(
				'nonce',
				nonce()
			)->attributes(
				'[name=nonce]',
				'value',
				$_SESSION['nonce']
			)->send();
			exit();
		};
	}

	function CSP() {
		/**
		 * Content-Security-Policy is a set of rules given to a browser
		 * via an HTTP header, providing a list of allowable resources.
		 *
		 * If a resources is requested that is not specifically allowed
		 * in CSP, it is blocked. This prevents such things as key-loggers,
		 * adware, and other forms of malware from having any effect.
		 *
		 * @link http://www.html5rocks.com/en/tutorials/security/content-security-policy/
		 * @param void
		 * @return void
		 */

		$CSP = '';									 // Begin with an empty string
		$CSP_Policy = parse_ini_file('csp.ini');	// Read ini
		if(!$CSP_Policy) return;
		$enforce = array_remove('enforce', $CSP_Policy);
		if(is_null($enforce)) $enforce = true;
		foreach($CSP_Policy as $type => $src) {		// Convert config array to string for CSP header
			$CSP .= "{$type} {$src};";
		}
		$reg = new regexp($CSP);					// Prepare to use regexp to set CSP nonce with the one in $_SESSION
		$CSP = $reg->replace('%NONCE%')->with("{$_SESSION['nonce']}")->execute();
		if($enforce) {								// If in debug mode, CSP should be "report-only"
													// Set headers for all prefixed versions
													//[TODO] Use UA sniffing to only set correct header
			header("Content-Security-Policy: $CSP");
			//header("X-Content-Security-Policy: $CSP");
			//header("X-Webkit-CSP: $CSP");
		}
		else{										// If not, CSP will be enforced
			header("Content-Security-Policy-Report-Only: $CSP");
		}
	}

	function localhost() {
		/**
		 * Checks to see if the server is also the client.
		 *
		 * @param void
		 * @return boolean
		 */

		return ($_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR']);
	}

	function https() {
		/**
		 * Returns whether or not this is a secure (HTTPS) connection
		 *
		 * @params void
		 * @return boolean
		 */

		return (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']);
	}

	function DNT() {
		/**
		 * Checks and returns whether or not Do-Not-Track header
		 * requests that we not track the client
		 *
		 * @params void
		 * @return boolean
		 */

		return (isset($_SERVER['HTTP_DNT']) and $_SERVER['HTTP_DNT']);
	}

	function is_ajax() {							// Try to determine if this is and ajax request
		/**
		 * Checks for the custom Request-Type header sent in my ajax requests
		 *
		 * @param void
		 * @return boolean
		 */

		return (isset($_SERVER['HTTP_REQUEST_TYPE']) and $_SERVER['HTTP_REQUEST_TYPE'] === 'AJAX');
	}

	function header_type($type) {							// Set content-type header.
		/**
		 * Sets HTTP Content-Type header
		 * @params string $type
		 * @return void
		 */

		header("Content-Type: {$type}\n");
	}

	function define_UA() {								// Define Browser and OS according to user-agent string
		/**
		 * Defines a variety of things using the HTTP_USER_AGENT header,
		 * such as operating system and browser
		 *
		 * @params void
		 * @return void
		 */

		if(!defined('UA')){
			if(isset($_SERVER['HTTP_USER_AGENT'])) {
				define('UA', $_SERVER['HTTP_USER_AGENT']);
				if(preg_match("/Firefox/i", UA)) define('BROWSER', 'Firefox');
				elseif(preg_match("/Chrome/i", UA)) define('BROWSER', 'Chrome');
				elseif(preg_match("/MSIE/i", UA)) define('BROWSER', 'IE');
				elseif(preg_match("/(Safari)||(AppleWebKit)/i", UA)) define('BROWSER', 'Webkit');
				elseif(preg_match("/Opera/i", UA)) define('BROWSER', 'Opera');
				else define('BROWSER', 'Unknown');
				if(preg_match("/Windows/i", UA)) define('OS', 'Windows');
				elseif(preg_match("/Ubuntu/i", UA)) define('OS', 'Ubuntu');
				elseif(preg_match("/Android/i", UA)) define('OS', 'Android');
				elseif(preg_match("/(IPhone)|(Macintosh)/i", UA)) define('OS', 'Apple');
				elseif(preg_match("/Linux/i", UA)) define('OS', 'Linux');
				else define('OS', 'Unknown');
			}
			else{
				define('BOWSER', 'Unknown');
				define('OS', 'Unknown');
			};
		}
	}

	function nonce($length = 50) {						// generate a nonce of $length random characters
		/**
		 * Generates a random string to be used for form validation
		 *
		 * @link http://www.html5rocks.com/en/tutorials/security/content-security-policy/
		 * @params integer $length
		 * @return string
		 */

		if(array_key_exists('nonce', $_SESSION)) {	// Use existing nonce instead of a new one
			return $_SESSION['nonce'];
		}
		//We are going to shuffle an alpha-numeric string to get random characters
		$str = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
		if(strlen($str) < $length) {					// $str length is limited to length of available characters. Be recursive for extra length
			$str .= nonce($length - strlen($str));
		}
		$_SESSION['nonce'] = $str;							// Save this to session for re-use
		return $str;
	}

	function same_origin() {							// Determine if request is from us
		/**
		 * Checks whether or not the current request was sent
		 * from the same domain
		 *
		 * @params void
		 * @return boolean
		 */

		if(isset($_SERVER['HTTP_ORIGIN'])) {
			$origin = $_SERVER['HTTP_ORIGIN'];
		}
		elseif(isset($_SERVER['HTTP_REFERER'])) {
			$origin = $_SERVER['HTTP_REFERER'];
		}
		$name = '/^http(s)?' .preg_quote('://' . $_SERVER['SERVER_NAME'], '/') . '/';
		return (isset($origin) and preg_match($name, $origin));
	}

	function sub_root() {
		/**
		 * @params void
		 * @return string (Directory one level below DOCUMENT_ROOT)
		 */

		$root = preg_replace('/\/$/', '', $_SERVER['DOCUMENT_ROOT']);	// Strip off the '/' at the end of DOCUMENT_ROOT
		$sub = preg_replace('/' . preg_quote(end(explode('/', $root))) . '/', '', $root);
		return $sub;
	}

	function array_remove($key, &$array) {
		/**
		 * Remove from array by key and return it's value
		 *
		 * @param string $key, array $array
		 * @return array | null
		 */

		if(array_key_exists($key, $array)) {
			$val = $array[$key];					// Need to store to variable before unsetting, then return the variable
			unset($array[$key]);
			return $val;
		}
		else return null;
	}

	function array_keys_exist() {
		/**
		* Checks if the array that is the product
		* of array_diff is empty or not.
		*
		* First, store all arguments as an array using
		* func_get_arg() as $keys.
		*
		* Then, pop off the last argument as $arr, which is assumed
		* to be the array to be searched and save it as its
		* own variable. This will also remove it from
		* the arguments array.
		*
		* Then, convert the array to its keys using $arr = array_keys($arr)
		*
		* Finally, compare the $keys by lopping through and checking if
		* each $key is in $arr using in_array($key, $arr)
		*
		 * @params string[, string, .... string] array
		 * @return boolean
		 * @example array_keys_exist('red', 'green', 'blue', ['red' => '#f00', 'green' => '#0f0', 'blue' => '#00f']) // true
		 */

		$keys = func_get_args();
		$arr = array_pop($keys);
		$arr = array_keys($arr);

		foreach($keys as $key) {
			if(!in_array($key, $arr, true)) return false;
		}
		return true;
}

	function flatten() {
		/**
		 * Convert a multi-dimensional array into a simple array
		 *
		 * Can't say that I'm entirely sure how it does what it does,
		 * only that it works
		 *
		 * @param mixed args
		 * @return array
		 */

		return iterator_to_array(new RecursiveIteratorIterator(
			new RecursiveArrayIterator(func_get_args())),FALSE);
	}

	function is_a_number($n) {
		/**
		 * Because I was tired of writing this... the ultimate point of programming, after all
		 *
		 * @params mixed $n
		 * @return boolean
		 */

		return preg_match('/^\d+$/', $n);
	}

	function is_not_a_number($n) {
		/**
		 * Opposite of previous.
		 *
		 * @params mixed $n
		 * @return boolean
		 */

		return !is_a_number($n);
	}

	function ls($path = null, $ext = null, $strip_ext = null) {
		/**
		 * List files in given path. Optional extension and strip extension from results
		 *
		 * @param [string $path[, string $ext[, boolean $strip_ext]]]
		 * @return array
		 */

		if(is_null($path)) $path = BASE;
		$files = array_diff(scandir($path), array('.', '..'));				// Get array of files. Remove current and previous directory (. & ..)
		$results = array();
		if(isset($ext)) {													//$ext has been passed, so let's work with it
			//Convert $ext into regexp
			$ext = '/' . preg_quote('.' . $ext, '/') .'/';					// Convert for use in regular expression
			if(isset($strip_ext)) {
				foreach($files as $file) {
					(preg_match($ext, $file)) ? array_push($results, preg_replace($ext, '', $file)) : null;
				}
			}
			else{
				foreach($files as $file) {
					(preg_match($ext, $file)) ? array_push($results, $file) : null;
				}
			}
			return $results;
		}
		else return $files;
	}

	function encode($file) {
		/**
		 * Base 64 encode $file. Does not set data: URI
		 * @params string $file
		 * @return string (base_64 encoded)
		 */

		if(file_exists($file)) return base64_encode(file_get_contents($file));
	}

	function mime_type($file) {
		/**
		 * Determine the mime-type of a file
		 * using file info or file extension
		 *
		 * @param string $file
		 * @return string (mime-type)
		 * @example mime_type(path/to/file.txt) //Returns text/plain
		 */

		//Make an absolute path if given a relative path in $file
		if(substr($file, 0, 1) !== '/') $file = BASE . "/$file";

		$unsupported_types = [
			'css' => 'text/css',
			'js' => 'application/javascript',
			'svg' => 'image/svg+xml',
			'woff' => 'application/font-woff',
			'appcache' => 'text/cache-manifest',
			'm4a' => 'audio/mp4',
			'ogg' => 'audio/ogg',
			'oga' => 'audio/ogg',
			'ogv' => 'vidoe/ogg'
		];

		if(array_key_exists(extension($file), $unsupported_types)) {
			$mime = $unsupported_types[extension($file)];
		}
		else {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $file);
			finfo_close($finfo);
		}
		return $mime;
	}

	function data_uri($file) {
		/**
		 * Reads the contents of a file ($file) and returns
		 * the base64 encoded data-uri
		 *
		 * Useful for decreasing load times and storing resources client-side
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/data_URIs
		 * @param strin $file
		 * @return string (base64 encoded data-uri)
		 */

		return 'data:' . mime_type($file) . ';base64,' . encode($file);
	}

	function extension($file) {
		/**
		 * Returns the extension for the specified file
		 *
		 * Does not depend on whether or not the file exists.
		 * This function operates with the string, not the
		 * filesystem
		 *
		 * @param string $file
		 * @return string
		 * @example extension('path/to/file.ext') //returns '.ext'
		 */

		return '.' . pathinfo($file, PATHINFO_EXTENSION);
	}

	function filename($file) {
		/**
		 * Returns the filename without path or extension
		 * Does not depend on whether or not the file exists.
		 * This function operates with the string, not the
		 * filesystem
		 *
		 * @param string $file
		 * @return string
		 * @example filename('/path/to/file.ext') //returns 'file'
		 */
		return pathinfo($file, PATHINFO_FILENAME);
	}

	function unquote($str) {
		/**
		 * Remove Leading and trailing single quotes
		 *
		 * @params string $str
		 * @return string
		 */

		return preg_replace("/^\'|\'$/", '', $str);
	}

	function caps($str) {
		/**
		 * Receives a string, returns same string with all words capitalized
		 *
		 * @params string $str
		 * @return string
		 */

		return ucwords(strtolower($str));
	}

	function average() {
		/**
		 * Finds the numeric average average of its arguments
		 *
		 * @param mixed args (All values should be numbers, int or float)
		 * @return float (average)
		 * @example average(1, 2) //Returns 1.5
		 * @example average([1.5, 1.6]) //Returns 1.55
		 */

		$args = flatten(func_get_args());
		return array_sum($args) / count($args);
	}

	function list_array($array) {
		/**
		 * Prints out an unordered list from an array
		 * @params array
		 * @return void
		 */

		echo "<ul>";
		foreach($array as $key => $entry) {
			if(is_array($entry)) {
				list_array($value);
			}
			else {
				echo "<li>{$key}: {$entry}</li>";
			}
		}
		echo "</ul>";
	}

	function curl($request, $method = 'get') {
		/**
		 * Returns http content from request.
		 *
		 * @link http://www.php.net/manual/en/book.curl.php
		 * @params string $request[, string $method]
		 * @return string
		 */

		//[TODO] Handle both GET and POST methods
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT,30);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	function curl_post($url, $request) {			//cURL for post instead of get
		/**
		 * See previous curl()
		 *
		 * @params string $url, string $request
		 * @return string
		 */

		$requestBody = http_build_query($request);
		$connection = curl_init();
		curl_setopt($connection, CURLOPT_URL, $url);
		curl_setopt($connection, CURLOPT_TIMEOUT, 30 );
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($connection, CURLOPT_POST, count($request));
		curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($connection, CURLOPT_FAILONERROR, 0);
		curl_setopt($connection, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($connection, CURLOPT_HTTP_VERSION, 1);		// HTTP version must be 1.0
		$response = curl_exec($connection);
		return $response;
	}

	function minify($src) {
		/**
		 * Trims extra spaces and removes tabs and new lines.
		 *
		 * @params string $src
		 * @return string
		 */

		return preg_replace(array('/\t/', '/\n/', '/\r\n/'), array(), trim($src));
	}

	function pattern($type = null) {
		/**
		 * Useful for pattern attributes as well as server-side input validation
		 * Must add regexp breakpoints for server-side use ['/^$pattern$/']
		*
		 * @params string $type
		 * @return string (regexp)
		 */
		switch($type) {
			case "text": {
				$pattern = "(\w+(\ )?)+";
			} break;

			case "name": {
				$pattern = "[A-Za-z]{3,30}";
			} break;

			case "password": {
				$pattern = "(?=^.{8,35}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$";
			} break;

			case "email": {
				$pattern = ".+@.+\.+[\w]+";
			} break;

			case "url": {
				$pattern = "(http[s]?://)?[\S]+\.[\S]+";
			} break;

			case "tel": {
				$pattern = "([+]?[1-9][-]?)?((\([\d]{3}\))|(\d{3}[-]?))\d{3}[-]?\d{4}";
			} break;

			case "number": {
				$pattern = "\d+(\.\d{1,})?";
			} break;

			case "color": {
				$pattern = "#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})";
			} break;

			case "date": {
				$pattern = "((((0?)[1-9])|(1[0-2]))(-|/)(((0?)[1-9])|([1-2][\d])|3[0-1])(-|/)\d{4})|(\d{4}-(((0?)[1-9])|(1[0-2]))-(((0?)[1-9])|([1-2][\d])|3[0-1]))";
			} break;

			case "time": {
				$pattern = "(([0-1]?\d)|(2[0-3])):[0-5]\d";
			} break;

			case 'datetime': {
				$pattern = '(19|20)\d{2}-(0?[1-9]|1[12])-(0?[1-9]|[12]\d?|3[01]) T([01]\d|2[0-3])(:[0-5]\d)+';
			} break;

			case "credit": {
				$pattern = "\d{13,16}";
				} break;

			default: {
				$pattern = null;
			}
		}
		return $pattern;
	}

	function utf($string) {
		/**
		 * Concerts characters to UTF-8. Replaces special chars.
		 *
		 * @param string $string
		 * @return (string UTF-8 converted)
		 * @example utf('This & that') //Returns 'This &amp; that'
		 */

		return htmlentities($string, ENT_QUOTES | ENT_HTML5,"UTF-8");
	}
?>
