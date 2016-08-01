<?php
namespace NotORM;

/**
 * Class Row
 * Single row representation
 * @package NotORM
 */
class Row extends AbstractClass implements \IteratorAggregate, \ArrayAccess, \Countable, \JsonSerializable {
	private $modified = array();
	protected $row, $result, $id;

	/**
	 * Row constructor.
	 * @param array $row
	 * @param \NotORM\Result $result
	 */
	public function __construct(array $row, Result $result, $id = false) {
		$this->row = $row;
		$this->result = $result;
		$this->id = $id;
	}

	/**
	 * Get primary key value
	* @return string
	*/
	public function __toString() {
		return (string) $this[$this->result->primary]; // (string) - PostgreSQL returns int
	}

	/**
	 * Get referenced row
	 * @param string
	 * @return \NotORM\Row if the row does not exist
	 */
	public function __get($name) {
		$column = $this->result->notORM->structure->getReferencedColumn($name, $this->result->table);
		$referenced = &$this->result->referenced[$name];
		if (!isset($referenced)) {
			$keys = array();
			foreach ($this->result->rows as $row) {
				if ($row[$column] !== null) {
					$keys[$row[$column]] = null;
				}
			}
			$table = $this->result->notORM->structure->getReferencedTable($name, $this->result->table);
			$referenced = new Result($table, $this->result->notORM);
			$referenced->where("$table." . $this->result->notORM->structure->getPrimary($table), array_keys($keys));
		}
		return new $this->result->notORM->rowClass(array(), $referenced, $this[$column]); // lazy loading
	}

	/**
	 * Test if referenced row exists
	 * @param string
	 * @return bool
	 */
	function __isset($name) {
		$row = $this->__get($name);
		return $row[$row->result->primary] !== false;
	}

	/**
	 * Store referenced value
	 * @param $name
	 * @param \NotORM\Row $value
	 * @return null
	 */
	public function __set($name, Row $value = null) {
		$column = $this->result->notORM->structure->getReferencedColumn($name, $this->result->table);
		$this[$column] = $value;
	}

	/**
	 * Remove referenced column from data
	 * @param string
	 * @return null
	 */
	public function __unset($name) {
		$column = $this->result->notORM->structure->getReferencedColumn($name, $this->result->table);
		unset($this[$column]);
	}

	/**
	 * Get referencing rows
	 * @param string $name
	 * @param array $args (["condition"[, array("value")]])
	 * @return \NotORM\MultiResult
	 */
	public function __call($name, array $args) {
		$table = $this->result->notORM->structure->getReferencingTable($name, $this->result->table);
		$column = $this->result->notORM->structure->getReferencingColumn($table, $this->result->table);
		$return = new MultiResult($table, $this->result, $column, $this[$this->result->primary]);
		$return->where("$table.$column", array_keys((array) $this->result->rows)); // (array) - is null after insert
		if ($args) {
			call_user_func_array(array($return, 'where'), $args);
		}
		return $return;
	}

	/**
	 * Update row
	 * @param array|null $data for all modified values
	 * @return int number of affected rows or false in case of an error
	 */
	public function update($data = null) {
		// update is an SQL keyword
		if (!isset($data)) {
			$data = $this->modified;
		}
		$result = new NotORM_Result($this->result->table, $this->result->notORM);
		return $result->where($this->result->primary, $this[$this->result->primary])->update($data);
	}

	/**
	 * Delete row
	 * @return int number of affected rows or false in case of an error
	 */
	public function delete() {
		// delete is an SQL keyword
		$result = new Result($this->result->table, $this->result->notORM);
		return $result->where($this->result->primary, $this[$this->result->primary])->delete();
	}

	/**
	 * @inheritdoc
     */
	protected function access($key, $delete = false) {
		if ($this->id === null) { // couldn't be found
			return false;
		}
		if ($this->row === array()) { // lazy loading
			$row = $this->result[$this->id];
			$this->row = ($row ? $row->row : null);
		}
		if ($this->row === null) { // not found
			return false;
		}
		if ($this->result->notORM->cache && !isset($this->modified[$key]) && $this->result->access($key, $delete)) {
			$id = (isset($this->row[$this->result->primary]) ? $this->row[$this->result->primary] : $this->row);
			$this->row = $this->result[$id]->row;
		}
		return true;
	}

	// IteratorAggregate implementation

	public function getIterator() {
		$this->access(null);
		return new \ArrayIterator((array) $this->row);
	}

	// Countable implementation

	public function count() {
		return count($this->row);
	}

	// ArrayAccess implementation

	/**
	 * Test if column exists
	 * @param string $key column name
	 * @return bool
	 */
	public function offsetExists($key) {
		$this->access($key);
		$return = array_key_exists($key, $this->row);
		if (!$return) {
			$this->access($key, true);
		}
		return $return;
	}

	/**
	 * Get value of column
	 * @param string $key column name
	 * @return mixed false for non existent rows
	 */
	public function offsetGet($key) {
		if (!$this->access($key)) {
			return false;
		}
		if (!array_key_exists($key, $this->row)) {
			$this->access($key, true);
		}
		return $this->row[$key];
	}

	/**
	 * Store value in column
	 * @param string $key column name
	 * @param mixed $value
	 * @return null
	 */
	public function offsetSet($key, $value) {
		$this->row[$key] = $value;
		$this->modified[$key] = $value;
	}

	/**
	 * Remove column from data
	 * @param string $key column name
	 * @return null
	 */
	public function offsetUnset($key) {
		unset($this->row[$key]);
		unset($this->modified[$key]);
	}

	// JsonSerializable implementation

	public function jsonSerialize() {
		return $this->row;
	}

}
