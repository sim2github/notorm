<?php
namespace NotORM\Tests;

/**
 * Class TestRow
 * For tesRowClass()
 */
class TestRow extends \NotORM\Row {

	function offsetExists($key) {
		return parent::offsetExists(preg_replace('~^test_~', '', $key));
	}

	function offsetGet($key) {
		return parent::offsetGet(preg_replace('~^test_~', '', $key));
	}

}