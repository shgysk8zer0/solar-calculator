<?php
	class cookies {
		/**
		 * @author Chris Zuber <shgysk8zer0@gmail.com>
		 * @copyright 2014, Chris Zuber
		 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
		 * @package core_shared
		 * @version 2014-05-21
		 */

		public $expires, $path, $domain, $secure, $httponly;

		public function __construct($expires = 0, $path = null, $domain = null, $secure = null, $httponly = null){
			/**
			 * @param mixed $expires (Takes a variety of date formats, including timestamps)
			 * @param string $path (example.com/path would be /path)
			 * @param string $domain (example.com/path would be example.com)
			 * @param boolean secure (Whether or not to limit cookie to https connections)
			 * @param boolean $httponly (Setting to true prevents access by JavaScript, etc)
			 * @example $cookies = new cookies('Tomorrow', '/path', 'example.com', true, true);
			 */
			
			$this->expires = (int) (preg_match('/^\d+$/', $expires)) ? $expires : $this->data = date_timestamp_get(date_create($expires));
			$this->path = (isset($path)) ? $path : preg_replace('/^' . preg_quote("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['SERVER_NAME']}", '/') . '/', '', URL);
			$this->domain = (isset($domain)) ? $domain : $_SERVER['HTTP_HOST'];
			$this->secure = (isset($secure)) ? $secure : false;
			$this->httponly = (isset($httponly)) ? $httponly : false;
		}

		public function __set($name, $value) {
			/**
			 * Magic setter for the class.
			 * Sets a cookie using only $name and $value. All
			 * other paramaters set in __construct
			 * 
			 * @param string $name
			 * @param string $value
			 * @example $cookies->test = 'Works'
			 */
			
			setcookie(preg_replace('/_/', '-', $name), $value, $this->expires, $this->path, $this->domain, $this->secure, $this->httponly);
		}

		public function __get($name) {
			/**
			 * Magic getter for the class
			 * 
			 * Returns the requested cookie's value or false
			 * if not set
			 * 
			 * @param string $name
			 * @return mixed (cookie's value or false if not set)
			 * @example $cookies->test ('Works')
			 */
			
			$name = preg_replace('/_/', '-', $name);
			return (array_key_exists($name, $_COOKIE)) ? $_COOKIE[$name] : false;
		}

		public function __call($name, $arguments) {
			/**
			 * Chained magic getter and setter
			 * @param string $name, array $arguments
			 * @example "$cookies->[getName|setName]($value)?"
			 */

			$key = preg_replace('/_/', '-', substr(strtolower($name), 3));
			switch(substr($name, 0, 3)) {
				case 'get': {
					return (array_key_exists($key, $_COOKIE)) ? $_COOKIE[$key] : false;
				} break;
				
				case 'set':{
					setcookie($key, $arguments[0], (int)$this->expires, $this->path, $this->domain, $this->secure, $this->httponly);
					return $this;
				} break;
				
				default: {
					return $this;
				}
			}
		}

		public function __isset($name) {
			/**
			 * Checks if $_COOKIE[$name] exists
			 * 
			 * @param string $name
			 * @return boolean
			 * @example isset($cookies->test) (true)
			 */
			
			return array_key_exists(preg_replace('/_/', '-', $name), $_COOKIE);
		}

		public function keys() {
			/**
			 * Lists all cookies by name
			 * 
			 * @param void
			 * @return array
			 * @example $cookies->keys() (['test', ...])
			 */
			
			return array_keys($_COOKIE);
		}

		public function __unset($name) {
			/**
			 * Completely desttroys a cookie on server and client
			 * 
			 * @param string $name
			 * @return boolean (Whether or not cookie existed)
			 * @example unset($cookies->test) (true)
			 */
			
			$name = preg_replace('/_/', '-', $name);
			if(array_key_exists($name, $_COOKIE)) {
				unset($_COOKIE[$name]);
				setcookie($name, null, -1, $this->path, $this->domain, $this->secure, $this->httponly);
				return true;
			}
			return false;
		}
	}
?>
