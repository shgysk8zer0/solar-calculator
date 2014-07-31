<?php
	class _pdo {
		/**
		 * @author Chris Zuber <shgysk8zer0@gmail.com>
		 * @copyright 2014, Chris Zuber
		 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
		 * @package core_shared
		 * @version 2014-04-19
		 */

		protected $pdo, $prepared, $connect, $data = array();
		private $query;
		protected static $instances = [];
		public $connected;

		public static function load($ini = 'connect') {
			/**
			 * Static load function avoids creating multiple instances/connections
			 * It stores an array of instances in the static instances array.
			 * It uses $ini as the key to the array, and the _pdo instance as
			 * the value.
			 *
			 * @params string $ini (.ini file to use for database credentials)
			 * @return pdo_object/class
			 * @example $pdo = _pdo::load or $pdo = _pdo::load('connect')
			 */

			if(!array_key_exists($ini, self::$instances)) {
				self::$instances[$ini] = new self($ini);
			}
			return self::$instances[$ini];
		}

		public function __construct($ini = 'connect') {
			/**
			 * Gets database connection info from /connect.ini (using ini::load)
			 * The default ini file to use is connect, but can be passed another
			 * in the $ini argument.
			 *
			 * Uses that data to create a new PHP Data Object
			 *
			 * @param string $ini (.ini file to use for database credentials)
			 * @return void
			 * @example $pdo = new _pdo()
			 */
			if(is_string($ini)) {
				$this->connect = ini::load($ini);
			}
			elseif(is_object($ini)) {
				$this->connect = $ini;
			}

			try{
				if(!(isset($this->connect->user) and isset($this->connect->password))) throw new Exception('Missing credentials to connect to database');
				$connect_string = (isset($this->connect->type)) ? "{$this->connect->type}:" : 'mysql:';
				$connect_string .= (isset($this->connect->database)) ?  "dbname={$this->connect->database}" : "dbname={$this->connect->user}";
				if(isset($this->connect->server)) $connect_string .= ";host={$this->connect->server}";
				if(isset($this->connect->port) and $this->connect->server !== 'localhost') $connect_string .= ";port={$this->connect->port}";
				$this->pdo = new PDO($connect_string, $this->connect->user, $this->connect->password);
				$this->connected = true;
			}
			catch(Exception $e) {
				$this->log(__METHOD__, __LINE__, $connect_string . PHP_EOL . $e->getMessage());
				//exit('Failed to connect to database.');
				$this->connected = false;
			}
		}

		public function log($method, $line, $message = '') {
			file_put_contents(BASE . '/' . __CLASS__ . '.log', "Error in $method in line $line: $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
		}

		public function __set($key, $value) {
			/**
			 * Setter method for the class.
			 *
			 * @param string $key, mixed $value
			 * @return void
			 * @example "$pdo->key = $value"
			 */

			$key = preg_replace('/_/', '-', preg_quote($key, '/'));
			$this->data[$key] = $value;
		}

		public function __get($key) {
			/**
			 * The getter method for the class.
			 *
			 * @param string $key
			 * @return mixed
			 * @example "$pdo->key" Returns $value
			 */

			$key = preg_replace('/_/', '-', preg_quote($key, '/'));
			if(array_key_exists($key, $this->data)) {
				return $this->data[$key];
			}
			return false;
		}

		public function __isset($key) {
			/**
			 * @param string $key
			 * @return boolean
			 * @example "isset({$pdo->key})"
			 */

			return array_key_exists(preg_replace('/_/', '-', $key), $this->data);
		}

		public function __unset($key) {
			/**
			 * Removes an index from the array.
			 *
			 * @param string $key
			 * @return void
			 * @example "unset($pdo->key)"
			 */

			unset($this->data[preg_replace('/_/', '-', $key)]);
		}

		public function __call($name, $arguments) {
			/**
			 * Chained magic getter and setter
			 * @param string $name, array $arguments
			 * @example "$pdo->[getName|setName]($value)"
			 */

			$name = strtolower($name);
			$act = substr($name, 0, 3);
			$key = preg_replace('/_/', '-', substr($name, 3));
			switch($act) {
				case 'get': {
					if(array_key_exists($key, $this->data)) {
						return $this->data[$key];
					}
					else{
						return false;
					}
				} break;
				case 'set': {
					$this->data[$key] = $arguments[0];
					return $this;
				} break;
				default: {
					throw new Exception("Unknown method: {$name} in " . __CLASS__ .'->' . __METHOD__);
				}
			}
		}

		public function keys() {
			/**
			 * Show all keys for entries in $this->data array
			 *
			 * @param void
			 * @return array
			 */

			return array_keys($this->data);
		}

		public function prepare($query) {
			/**
			 * Argument $query is a SQL query in prepared statement format
			 * "SELECT FROM `$table` WHERE `column` = ':$values'"
			 * Note the use of the colon. These are what we are going to be
			 * binding values to a little later
			 *
			 * Returns $this for chaining. Most further functions will do the same where useful
			 *
			 * @param string $query
			 * @return self
			*/

			$this->prepared = $this->pdo->prepare($query);
			return $this;
		}

		public function bind($array) {
			/**
			 * Binds values to prepared statements
			 *
			 * @param array $array
			 * @return self
			 * @example $pdo->prepare(...)->bind([
			 * 	'col_name' => $value,
			 * 	'col2' => 'something else'
			 * ])
			 */

			foreach($array as $paramater => $value) {
				$this->prepared->bindValue(':' . $paramater, $value);
			}
			return $this;
		}

		public function execute() {
			/**
			 * Executes prepared statements. Does not return results
			 *
			 * @param void
			 * @return self
			 */

			if($this->prepared->execute()) {
				return $this;
			}
			return false;
		}

		public function get_results($n = null) {
			/**
			 * Gets results of prepared statement. $n can be passed to retreive a specific row
			 *
			 * @param [int $n]
			 * @return mixed
			 */

			$arr = $this->prepared->fetchAll(PDO::FETCH_CLASS);
			$results = array();
			foreach($arr as $data) {							//Convert from an associative array to a stdClass object
				$row = new stdClass();
				foreach($data as $key => $value) {
					$row->$key = trim($value);
				}
				array_push($results, $row);
			}
			//If $n is set, return $results[$n] (row $n of results) Else return all
			if(!count($results)) return false;
			if(is_null($n)) return $results;
			else return $results[$n];
		}

		public function close() {
			/**
			 * Need PDO method to close database connection
			 *
			 * @param void
			 * @return void
			 * @todo Make it actually close the connection
			 */

			unset($this->pdo);
			unset($this);
		}

		public function prepare_keys($arr) {
			/**
			 * Converts array_keys to something safe for
			 * queries
			 *
			 * @param array $arr
			 * @return array
			 */

			$keys = array_keys($arr);
			$key_walker = function(&$key) {
				$this->escape($key);
				$key = "`{$key}`";
			};
			array_walk($keys, $key_walker);
			$arr = array_combine($keys, array_values($arr));
			return $arr;
		}

		public function prepare_key_value(&$arr) {
			/**
			 * While this works with multi-dimensional
			 * array, it is intended to be used for things
			 * like array_insert that use a simple array where
			 * the keys are columns and the values are the values
			 *
			 * It takes a pointer to the array as its argument,
			 * so $arr = $pdo->prepare_key_value($arr) is the same as
			 * $$pdo->prepare_key_value($arr)
			 *
			 * @param array $arr
			 * @return array
			 * @usage $arr = [
			 * 	'Key' => 'Value',
			 * 'int' => 42,
			 * 'Index Only',
			 * 'Second_Level' => [...]
			 * ];
			 * $pdo->prepare_key_value($arr)
			 */

			$keys = array_keys($arr);
			$values = array_values($arr);
			$key_walker = function(&$key) {
				if(is_string($key)) {
					$this->escape($key);
					$key = "`{$key}`";
				}
			};
			$value_walker = function(&$value) {
				if(is_array($value)) $this->prepare_key_value($value);
				elseif (is_string($value)) $this->quote($value);
			};
			array_walk($keys, $key_walker);
			array_walk($values, $value_walker);
			$arr = array_combine($keys, $values);
			return $arr;
		}

		public function quote(&$val) {
			/**
			 * Makes a string safer to use in a query
			 * When possible, use prepared statements instead
			 * It returns the value, but it is also uses
			 * a pointer, so $str = $pdo->quoute($str)
			 * has the same effect as $pdo->quote($str)
			 *
			 * @param mixed $val
			 * @return mixed
			 * @usage
			 * $str = 'Some string'
			 * $arr = ['String1', $str];
			 * $pdo->quote($str)
			 * $pdo->quote($arr)
			 */

			if(is_array($val)) {
				foreach($val as &$v) $this->quote($v);
			}
			else $val = $this->pdo->quote(trim((string) $val));
			return $val;
		}

		public function escape(&$val) {
			/**
			 * For lack of a pdo escape, use quote, trimming off the quotations
			 *
			 * @param mixed $str
			 * @return mixed
			 */

			if(is_array($val)) {
				foreach($val as &$v) $this->escape($v);
			}
			else {
				$this->quote($val);
				$val = preg_replace('/^\'|\'$/', '',(string) $val);
			}
			return $val;
		}

		public function binders($arr, $prefix = null, $suffix = null) {
			/**
			 * Make setting up prepared statements much easier by
			 * setting up all of the components using $key => $value of $arr
			 *
			 * $binds->cols[] is an array of column names
			 * taken from the keys of $arr in the format of
			 * `{$pdo->escape($key)}`
			 *
			 * $binds->bindings is created from the keys to $arr
			 * and used in prepared statements as what will be bound to
			 * in the format of :{$prefix}{$key}{$suffix} with whitespaces
			 * converted to underscores
			 *
			 * $binds->values is array_values($arr) {original values
			 * without the keys}
			 *
			 * @param array $arr
			 * @param string $prefix
			 * @param string $suffix
			 * @return object {cols:[], bindings: [], values: []}
			 * @usage:
			 * 	$binders = $pdo->binders([...])
			 * 	$pdo->prepare("
			 * 		INSERT INTO {$pdo->escape($table)}
			 * 		(" . join(',', $binders->cols) . ")
			 * 		VALUES(" . join(',', $binders->bindings) . ")
			 * ")->bind(array_combine($binders->bindings, $binders->values))->execute();
			 */

			$binds = new stdClass();
			$binds->cols = array_keys($this->prepare_keys($arr));
			$binds->bindings = [];
			$binds->values = array_values($arr);
			foreach(array_keys($arr) as $key) {
				array_push($binds->bindings, preg_replace('/\s/', '_', ":{$prefix}{$key}{$suffix}"));
			}
			return $binds;
		}

		public function query($query) {
			/**
			 * Get the results of a SQL query
			 *
			 * @param string $query
			 * @return
			 */

			return $this->pdo->query($query);
		}

		public function restore($fname = null) {
			/**
			 * Restores a MySQL database from file $fname
			 *
			 * @param string $fname
			 * @return self
			 */

			if(is_null($fname)) {
				//$connect = ini::load('connect');
				$fname = $this->connect->database;
			}

			$sql = file_get_contents(BASE ."/{$fname}.sql");
			if($sql) {
				return $this->pdo->query($sql);
			}
			else {
				return false;
			}
		}

		public function dump($filename = null) {
			//$connect = ini::load('connect');

			if(is_null($filename)) {
				$filename = $this->connect->database;
			}

			$command = "mysqldump -u {$this->connect->user} -p" . escapeshellcmd($this->connect->password);

			if(isset($this->connect->server) and $this->connect->server !== 'localhost') {
				$command .= " -h {$this->connect->server}";
			}

			$command .= " {$this->connect->database} > {$filename}.sql";

			exec($command);
		}

		public function fetch_array($query, $n = null) {
			/**
			 * Return the results of a query as an associative array
			 *
			 * @param string $query
			 * @return array
			 */

			$data = $this->query($query)->fetchAll(PDO::FETCH_CLASS);
			if(is_array($data)){
				return (is_null($n)) ? $data : $data[$n];
			}
			return [];
		}

		public function get_table($table, $these = '*') {
			/**
			 * @param string $table[, string $these]
			 * @return array
			 */

			if($these !== '*') $these ="`{$these}`";
			return $this->fetch_array("SELECT {$these} FROM {$this->escape($table)}");
		}

		public function array_insert($table, $content) {
			/**
			 *
			 * @param string $table, array $content
			 * @return self
			 * @example "$pdo->array_insert($table, array('var1' => 'value1', 'var2' => 'value2'))"
			 */

			return $this->prepare("
				INSERT INTO `{$this->escape($table)}`
				(" . join(', ', array_keys($this->prepare_keys($content))) . ")
				VALUES(:" . join(', :', array_keys($content)) . ")
			")->bind($content)->execute();
		}

		public function sql_table($table_name) {
			/**
			 * Prints out a SQL table in HTML formnatting. Used for updating via Ajax
			 *
			 * @param string $table_name
			 * @return string (html table)
			 */

			$table_data = $this->get_table($table_name);
			if(!is_array($table_data)) return false;
			(count($table_data)) ? $cols = array_keys(get_object_vars($table_data[0])) : $cols = $this->table_headers($table_name);
			$table = "<table border=\"1\" data-nonce=\"{$_SESSION['nonce']}\" data-sql-table=\"{$table_name}\"><caption>{$table_name}</caption>";
			$thead = '<thead><tr>';
			foreach($cols as $col) {
				if($col !== 'id') {
					$thead .= "<th>{$col}</th>";
				}
			}
			$thead .= "</tr></thead>";
			$tbody = "<tbody>";
			if(count($table_data)) {
				foreach($table_data as $tr) {
					$tbody .= "<tr data-sql-id=\"{$tr->id}\">";
					foreach($tr as $key => $td) {
						if($key !== 'id') {
							$tbody .= "<td><input name={$key} type=\"text\" value=\"{$td}\" class=\"sql\">";
						}
					}
				}
			}
			$tbody .="</tbody>";
			$table .= $thead . $tbody .= "</table>";
			return $table;
		}

		public function update($table, $name, $value, $where) {
			/**
			 * Updates a table according to these arguments
			 *
			 * @param string $table, string $name, string $value, string $where
			 * @return
			 */

			return $this->query("UPDATE `{$table}` SET `{$name}` = {$this->quote($value)} WHERE {$where}");
		}

		public function show_tables() {
			/**
			 * Returns a 0 indexed array of tables in database
			 *
			 * @param void
			 * @return array
			 */

			$query = "SHOW TABLES";
			$results = $this->pdo->query($query);
			$tables = $results->fetchAll(PDO::FETCH_COLUMN, 0);
			return $tables;
		}

		public function show_databases() {
			/**
			 * Returns a 0 indexed array of tables in database
			 *
			 * @param void
			 * @return array
			 */

			$query = 'SHOW DATABASES';
			$results = $this->pdo->query($query);
			$databases = $results->fetchAll(PDO::FETCH_COLUMN, 0);
			return $databases;
		}

		public function table_headers($table) {
			/**
			 * Returns a 0 indexed array of column headers for $table
			 *
			 * @param string $table
			 * @return array
			 */

			$query = "DESCRIBE {$this->escape($table)}";
			$results = $this->pdo->query($query);
			$headers = $results->fetchAll(PDO::FETCH_COLUMN, 0);
			return $headers;
		}

		public function describe($table) {
			/**
			 * Describe $table, including:
			 * Field {name}
			 * Type {varchar|int... & (length)}
			 * Null (boolean)
			 * Default {value}
			 * Extra {auto_increment, etc}
			 *
			 * @param string $table
			 * @return array
			 */
			return $this->pdo->query("DESCRIBE `{$this->escape($table)}`")->fetchAll(PDO::FETCH_CLASS);
		}

		public function value_properties($query) {
			/**
			 * Returns the results of a SQL query as a stdClass object
			 *
			 * @param string $query
			 * @return array
			 */

			$array = array();
			$results = $this->fetch_array($query);
			foreach($results as $result) {
				$data = new stdClass();
				foreach($results as $key => $value) {
					$key = trim($key);
					$value = trim($value);
					$data->$key = $value;
				}
				array_push($array, $data);
			}
			return $array;
		}

		public function name_value($table = null) {
			/**
			 * For simple Name/Value tables. Gets all name/value pairs. Returns stdClass object
			 *
			 * @param [string $table]
			 * @return obj
			 */

			$data = $this->fetch_array("SELECT `name`, `value` FROM `{$this->escape($table)}`");
			$values = new stdClass();
			foreach($data as $row) {
				$name = trim($row->name);
				$value = trim($row->value);
				$values->$name = $value;
			}
			return $values;
		}

		public function reset_table($table) {
			/**
			 * Removes all entries in a table and resets AUTO_INCREMENT to 1
			 *
			 * @param string $table
			 * @return void
			 */

			$table = $this->escape($table);
			$this->query("DELETE FROM `{$table}`");
			$this->query("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
			return $this;
		}
	}
?>
