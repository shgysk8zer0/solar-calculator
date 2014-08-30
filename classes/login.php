<?php
	class login extends _pdo {
		/**
		 * Class to handle login or create new users from form submissions or $_SESSION
		 * Can check login role as well (new, user, admin, etc)
		 *
		 * @author Chris Zuber <shgysk8zer0@gmail.com>
		 * @copyright 2014, Chris Zuber
		 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
		 * @package core_shared
		 * @version 2014-04-19
		 * @uses /classes/_pdo.php
		 * @var array $useer_data
		 * @var login $instance
		 */

		public $user_data = array();
		protected static $instance = null;

		public static function load($ini = 'connect') {
			/**
			 * Static load function avoids creating multiple instances/connections
			 * It checks if an instance has been created and returns that or a new instance
			 *
			 * @param string $ini (ini file to use for database connection configuration)
			 * @return login object/class
			 * @example $login = _login::load
			 */

			if(is_null(self::$instance)) {
				self::$instance = new self($ini);
			}
			return self::$instance;
		}

		public function __construct($ini = 'connect') {
			/**
			 * Gets database connection info from /connect.ini (stored in $site)
			 * Uses that data to create a new PHP Data Object
			 *
			 * @param string $ini (ini file to use for database connection configuration)
			 * @return void
			 * @example $login = new login()
			 */
			parent::__construct($ini);					#login extends _pdo, so create new instance of parent.
			#[TODO] Use static parent::load() instead, but this causes errors

			$this->user_data = array(
				'user' => null,
				'password' => null,
				'role' => null,
				'logged-in' => false
			);
		}

		public function create_from(array $source) {
			/**
			 * Creates new user using an array passed as source. Usually $_POST or $_SESSION
			 *
			 * @param array $source
			 * @return boolean
			 * @example $login->create_from($_POST|$_GET|$_REQUEST|array())
			 */

			if(array_keys_exist('user', 'password', $source)) {
				if(array_key_exists('repeat', $source) and $source['password'] !== $source['repeat']) return false;
				return $this->prepare("
					INSERT INTO `users` (
						`user`,
						`password`
					) VALUES (
						:user,
						:password
					)
				")->bind([
					'user' => trim($source['user']),
					'password' => password_hash(trim((string)$source['password']), PASSWORD_BCRYPT, [
						'cost' => 11,
						'salt' => mcrypt_create_iv(50, MCRYPT_DEV_URANDOM)
					])
				])->execute();
			}
			else {
				return false;
			}
		}

		public function login_with(array $source) {
			/**
			 * Intended to find login info from $_COOKIE, $_SESSION, or $_POST
			 *
			 * @param array $source
			 * @return void
			 * @example $login->login_with($_POST|$_GET|$_REQUEST|$_SESSION|array())
			 */

			if(array_keys_exist('user', 'password', $source)) {
				$results = $this->prepare("
					SELECT `user`,
					`password`,
					`role`
					FROM `users`
					WHERE `user` = :user
					LIMIT 1
				")->bind([
					'user' => $source['user']
				])->execute()->get_results(0);
				if(password_verify(
					trim($source['password']),
					$results->password
				) and $results->role !== 'new') {
					$this->setUser($results->user)->setPassword($results->password)->setRole($results->role)->setLogged_In(true);
				}
			}
			return ($this->user_data['logged-in']);
		}

		public function __set($key, $value) {
			/**
			 * Setter method for the class.
			 *
			 * @param string $key
			 * @param mixed $value
			 * @return void
			 * @example "$login->key = $value"
			 */

			$key = str_replace('_', '-', strtolower($key));
			$this->user_data[$key] = $value;
			return $this;
		}

		public function __get($key) {
			/**
			 * The getter method for the class.
			 *
			 * @param string $key
			 * @return mixed
			 * @example "$login->key" Returns $value
			 */

			$key = str_replace('_', '-', strtolower($key));
			if(array_key_exists($key, $this->user_data)) {
				return $this->user_data[$key];
			}
			return false;
		}

		public function __isset($key) {
			/**
			 * @param string $key
			 * @return boolean
			 * @example "isset({$login->key})"
			 */

			$key = str_replace('_', '-', strtolower($key));
			return array_key_exists($key, $this->user_data);
		}

		public function __unset($key) {
			/**
			 * Removes an index from the array.
			 *
			 * @param string $key
			 * @return void
			 * @example "unset($login->key)"
			 */

			$key = str_replace('_', '-', strtolower($key));
			unset($this->user_data[$key]);
		}

		public function __call($name, array $arguments) {
			/**
			 * Chained magic getter and setter
			 * @param string $name, array $arguments
			 * @example "$login->[getName|setName]($value)"
			 */

			$name = strtolower($name);
			$act = substr($name, 0, 3);
			$key = str_replace('_', '-', substr($name, 3));
			switch($act) {
				case 'get':
					if(array_key_exists($key, $this->user_data)) {
						return $this->user_data[$key];
					}
					else{
						die('Unknown variable.');
					}
					break;
				case 'set':
					$this->user_data[$key] = $arguments[0];
					return $this;
					break;
				default:
					die('Unknown method.');
			}
		}

		public function logout() {
			/**
			 * Undo the login. Destroy it. Removes session and cookie. Sets logged_in to false
			 *
			 * @param void
			 * @return void
			 */

			$this->setUser(null)->setPassword(null)->setRole(null)->setLogged_In(false);
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
