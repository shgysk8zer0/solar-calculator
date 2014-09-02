<?php
	interface magic_methods {
		public function __set($key, $value);
		public function __get($key);
		public function __isset($key);
		public function __unset($key);
		public function __call($name, array $arguments);
	}
?>
