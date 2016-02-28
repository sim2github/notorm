<?php

namespace NotORM;

/** Cache using $_SESSION["NotORM"]
 */
class CacheSession implements CacheInterface
{

	function load($key)
	{
		if (!isset($_SESSION["NotORM"][$key])) {
			return null;
		}
		return $_SESSION["NotORM"][$key];
	}

	function save($key, $data)
	{
		$_SESSION["NotORM"][$key] = $data;
	}

}