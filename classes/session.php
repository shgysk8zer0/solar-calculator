<?php
	/**
	* Since this class is using $_SESSION for all data, there are few variables
	* There are several methods to make better use of $_SESSION, and it adds the ability to chain
	* As $_SESSION is used for all storage, there is no pro or con to using __construct vs ::load()
	*
	* @author Chris Zuber <shgysk8zer0@gmail.com>
	* @copyright 2014, Chris Zuber
	* @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	* @package core_shared
	* @version 2014-04-19
	* @var string $name
	* @var int? $expires
	* @var string $path
	* @var string $domain
	* @var boolean $secure
	* @var boolean $httponly
	* @var session $instance
	*/
	class session {
		private $name, $expires, $path, $domain, $secure, $httponly;
		private static $instance = null;

		public static function load($site = null) {
			/**
			 * Static load function avoids creating multiple instances/connections
			 * It checks if an instance has been created and returns that or a new instance
			 *
			 * @params [string $site] optional name for session
			 * @return session object/class
			 * @example $session = session::load([$site])
			 */

			if(is_null(self::$instance)) {
				self::$instance = new self($site);
			}
			return self::$instance;
		}

		public function __construct($name = null) {
			/**
			 * Creates new instance of session. $name is optional, and sets session_name if session has not been started
			 *
			 * @params [string $site] optional name for session
			 * @return void
			 * @example $session = new session([$site])
			 */

			if(session_status() !== PHP_SESSION_ACTIVE) {							#Do not create new session of one has already been created
				$this->expires = 0;
				$this->path = '/' . trim(str_replace("{$_SERVER['REQUEST_SCHEME']}://{$_SERVER['SERVER_NAME']}", '/', URL), '/');
				$this->domain = $_SERVER['HTTP_HOST'];
				$this->secure = https();
				$this->httponly = true;

				if(is_null($name)) {
					$name = end(explode('/', trim(BASE, '/')));
				}
				$this->name = preg_replace('/[^\w]/', null, strtolower($name));
				session_name($this->name);
				if(!array_key_exists($this->name, $_COOKIE)) {
					session_set_cookie_params($this->expires, $this->path, $this->domain, $this->secure, $this->httponly);
				}
				session_start();
			}
		}

		public function __get($key) {
			/**
			 * The getter method for the class.
			 *
			 * @param string $key
			 * @return mixed
			 * @example "$session->key" Returns $value
			 */

			$key = strtolower(str_replace('_', '-', $key));
			if(array_key_exists($key, $_SESSION)) {
				return $_SESSION[$key];
			}
			return false;
		}

		public function __set($key, $value) {
			/**
			 * Setter method for the class.
			 *
			 * @param string $key, mixed $value
			 * @return void
			 * @example "$session->key = $value"
			 */
			$key = strtolower(str_replace('_', '-', $key));
			$_SESSION[$key] = trim($value);
		}

		public function __call($name, $arguments) {
			/**
			 * Chained magic getter and setter
			 * @param string $name, array $arguments
			 * @example "$session->[getName|setName]($value)"
			 */

			$name = strtolower($name);
			$act = substr($name, 0, 3);
			$key = str_replace('_', '-', substr($name, 3));
			switch($act) {
				case 'get':
					if(array_key_exists($key, $_SESSION)) {
						return $_SESSION[$key];
					}
					else{
						return false;
					}
					break;
				case 'set':
					$_SESSION[$key] = $arguments[0];
					return $this;
					break;
				default:
					die('Unknown method.');
			}
		}

		public function __isset($key) {
			/**
			 * @param string $key
			 * @return boolean
			 * @example "isset({$session->key})"
			 */

			$key = strtolower(str_replace('_', '-', $key));
			return array_key_exists($key, $_SESSION);
		}

		public function __unset($key) {
			/**
			 * Removes an index from the array.
			 *
			 * @param string $key
			 * @return void
			 * @example "unset($session->key)"
			 */

			$key = strtolower(str_replace('_', '-', $key));
			unset($_SESSION[$key]);
		}

		public function destroy() {
			/**
			* Destroys $_SESSION and attempts to destroy the associated cookie
			*
			* @param void
			* @return void
			*/

			session_destroy();
			unset($_SESSION);
			if(array_key_exists($this->name, $_COOKIE)){
				unset($_COOKIE[$this->name]);
				setcookie($this->name, null, -1, $this->path, $this->domain, $this->secure, $this->httponly);
			}
		}

		public function restart() {
			/**
			 * Clear $_SESSION. All data in $_SESSION is unset
			 *
			 * @param void
			 * @example $session->restart()
			 */

			session_unset();
			return $this;
		}

		public function debug() {
			/**
			 * Prints out class information using print_r
			 * wrapped in <pre> and <code>
			 *
			 * @param void
			 * @return void
			 */

			debug($this);
		}
	}
?>
