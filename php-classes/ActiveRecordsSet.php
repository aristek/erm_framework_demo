<?php

	/**
	 * Active Records Array
	 */
	final class ActiveRecordsSet extends SplFixedArray {
		/**
		 * Class Constructor
		 *
		 * @param AbstractActiveRecord[] $records
		 */
		public function __construct(array $records) {
			parent::__construct(count($records));
			# import record
			foreach ($records as $i => $rec) {
				$this[$i] = $rec;
			}
		}

		/**
		 * Executes method in all instances and returns result of each call in simple indexed array
		 *
		 * @param string $name
		 * @param array $arguments
		 * @return array
		 */
		public function __call($name, $arguments) {
			$out = [];
			foreach ($this as $rec) {
				$out[] = call_user_func_array([$rec, $name], $arguments);
			}
			return $out;
		}

		/**
		 * Reads property in all instances and return each value in simple indexed array
		 *
		 * @param string $name
		 * @return array
		 */
		public function __get($name) {
			$out = [];
			foreach ($this as $rec) {
				$out[] = $rec->$name;
			}
			return $out;
		}

		/**
		 * Magic method for setting property value
		 *
		 * @param string $name
		 * @param mixed $value
		 * @return void
		 */
		public function __set($name, $value) {
			throw new RuntimeException('Invalid usage. Cannot set property value for set of records');
		}
	}