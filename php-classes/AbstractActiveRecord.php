<?php

	/**
	 * Active Record abstract base class for CRUD
	 */
	abstract class AbstractActiveRecord extends RegularClass {
		/**
		 * Raw record data
		 *
		 * @var array
		 */
		protected $data = [];

		/**
		 * Records data loader callback
		 *
		 * @var callable
		 */
		private $loaderCallback;

		/**
		 * DB Table name
		 *
		 * @var string
		 */
		private $tableName = '';

		/**
		 * DB Table primary key name
		 *
		 * @var string
		 */
		private $primaryKeyName = '';

		/**
		 * Class Constructor
		 *
		 * @param null|int|string|array $source Id or raw data of the record
		 */
		final public function __construct($source = null) {
			parent::__construct();

			$this->setUp();

			if ((is_int($source) || is_string($source)) && !empty($source)) {
				$this->data = $this->loadRecords([$this->escape((string)$source)])[0];
			} else if (is_array($source)) {
				$this->data = $source;
			}
		}

		/**
		 * Creates and returns one instance or many instances of the records.
		 * Usage example:
		 *  SomeActiveRecord::factory(1);
		 *  SomeActiveRecord::factory([1, 2]);
		 *
		 * @param null|int|string|array $ids
		 * @return static|static[]
		 */
		final public static function factory($ids = null) {
			if ($ids === null) {
				return new static();
			}

			if (!is_array($ids)) {
				return new static($ids);
			}

			if (empty($ids)) {
				/** @noinspection PhpIncompatibleReturnTypeInspection */
				return new ActiveRecordsSet([]);
			}

			$emptyInst = new static();

			# escape all ids
			$ids = array_map(function ($id) use ($emptyInst) {
				return $emptyInst->escape((string)$id);
			}, $ids);

			# create list of instnances for each record
			$out = [];
			foreach ($emptyInst->loadRecords($ids) as $rec) {
				$out[] = new static($rec);
			}

			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return new ActiveRecordsSet($out);
		}

		/**
		 * Configures active record class: sets source table, loaders and so on
		 *
		 * @return void
		 */
		abstract protected function setUp();

		/**
		 * Loads data for the specified list of records
		 *
		 * @param array $ids
		 * @return array
		 */
		private function loadRecords(array $ids) {
			if ($this->loaderCallback !== null) {
				return call_user_func($this->loaderCallback, $ids);
			}

			if ($this->tableName !== null) {
				$SQL = "
					SELECT " . $this->primaryKeyName . ", * 
					  FROM " . $this->tableName . " 
					 WHERE " . $this->primaryKeyName . " IN (" . implode(', ', $ids) . ")
				";
				return $this->execSQL($SQL)->assocAll();
			}

			throw new RuntimeException('Cannot load records: the data source table/callback was not defined');
		}

		/**
		 * Sets source table name and primary key name
		 *
		 * @param string $tableName
		 * @param string $primaryKeyName
		 * @return static
		 */
		protected function setSourceTable($tableName, $primaryKeyName) {
			$this->tableName = $tableName;
			$this->primaryKeyName = $primaryKeyName;
			return $this;
		}

		/**
		 * Sets loader callback function which should receive a list of ids and returns
		 * a list of records data as two-dimensional associative arrat.
		 * NOTE! The first item in nedsted array must be refered to record id!
		 * Example:
		 *  ->setRecordsLoader(function(array $ids) {
		 *      return $this->execSQL("SELECT * FROM table WHERE id IN (" . implode(',', $ids) . ")")->assocAll();
		 *  })
		 *
		 * @param callable $cb
		 * @return static
		 */
		protected function setRecordsLoader(callable $cb) {
			$this->loaderCallback = $cb;
			return $this;
		}

		/**
		 * Updates or inserts record with the specified data
		 *
		 * @param array $data
		 * @param bool $provideUpdateInfo
		 * @return static
		 */
		public function upsert(array $data, $provideUpdateInfo = true) {
			if ($this->tableName === '') {
				throw new RuntimeException('The Active Record is not configured to insert/update data');
			}

			$rec = new DBImportRecord($this->tableName, $this->primaryKeyName, $this->db);

			if (isset($this->data[$this->primaryKeyName])) {
				if (isset($data[$this->primaryKeyName])
					&& $data[$this->primaryKeyName] !== $this->data[$this->primaryKeyName]) {
					throw new RuntimeException(
						'Invalid data for upsertion: the primary key value does not match to the Active Record'
					);
				}
				$data[$this->primaryKeyName] = $this->data[$this->primaryKeyName];
			}

			foreach ($data as $key => $value) {
				$this->data[$key] = $value;
				if ($key === $this->primaryKeyName) {
					$rec->key($key, $value);
				} else {
					$rec->set($key, $value);
				}
			}

			if ($provideUpdateInfo) {
				$this->data['lastuser'] = SystemCore::$userUID;
				$this->data['lastupdate'] = time();
				$rec->setUpdateInformation();
			}

			$rec->import();
			$this->data[$this->primaryKeyName] = $rec->recordID();
			return $this;
		}

		/**
		 * Deletes record
		 *
		 * @return static
		 */
		public function delete() {
			if ($this->tableName === '') {
				throw new RuntimeException('The Active Record is not configured to deletion');
			}

			if ($this->getId() === 0) {
				return $this;
			}

			$SQL = "
				DELETE
				  FROM " . $this->tableName . "
				 WHERE " . $this->primaryKeyName . " = " . $this->getId() . "
			";
			$this->execSQL($SQL);

			return $this;
		}

		/**
		 * Returns record id
		 *
		 * @return int|string
		 */
		public function getId() {
			if (empty($this->data)) {
				return 0;
			}

			if ($this->primaryKeyName && isset($this->data[$this->primaryKeyName])) {
				return $this->data[$this->primaryKeyName];
			}

			reset($this->data);
			$pkName = key($this->data);
			return $this->data[$pkName];
		}

		/**
		 * Reads record property
		 *
		 * @param string $name
		 * @return mixed
		 */
		public function __get($name) {
			if (!array_key_exists($name, $this->data)) {
				throw new RuntimeException('Try to access undefined property');
			}
			return $this->data[$name];
		}

		/**
		 * Magic method for setting property value
		 *
		 * @param string $name
		 * @param mixed $value
		 * @return void
		 */
		public function __set($name, $value) {
			throw new RuntimeException('Invalid usage. Cannot set property value');
		}
	}