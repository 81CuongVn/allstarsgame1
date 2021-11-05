<?php
class Recordset {
	public	$num_rows						= 0;
	public	$sql							= '';
	public static $key_prefix				= '';
	private static	$connections			= [];
	private static	$default_connection		= '';
	private	$recordset						= null;
	private	$hash							= null;

	public static	$count_cache_hits		= 0;
	public static	$count_hard_cache_hits	= 0;
	public static	$count_cache_miss		= 0;
	public static	$count_queries			= 0;
	public static	$count_inserts			= 0;
	public static	$count_selects			= 0;
	public static	$count_updates			= 0;
	public static	$count_deletes			= 0;
	public static	$count_inserts_w_dup	= 0;
	public static	$cache_mode				= 'file';

	private	static	$_result_store			= [];
	public static	$sqls					= [];

	function __construct($sql = '', $cache = false, $connection = true) {
		$this->hash	= md5($sql);

		if (defined('RECORDSET_CACHE_OFF_FORCE') && RECORDSET_CACHE_OFF_FORCE) {
			$cache	= false;
		}

		Recordset::$count_queries++;

		if (DB_LOGGING) {
			if (!isset(Recordset::$sqls[$this->hash])) {
				Recordset::$sqls[$this->hash] = [
					'sql'		=> $sql,
					'count'		=> 1,
					'cached'	=> false,
					'duration'	=> microtime(true),
					'traces'	=> []
				];
			} else {
				Recordset::$sqls[$this->hash]['count']++;
				Recordset::$sqls[$this->hash]['duration']	= microtime(true);
			}

			if (BACKTRACE_SELECTS) {
				$current_trace	= [];
				$count			= 0;
				$traces			= debug_backtrace();

				foreach ($traces as $trace) {
					if (!$count++) {
						continue;
					}

					if (isset($trace['file'])) {
						$current_trace[]	= [
							'file'	=> $trace['file'],
							'line'	=> $trace['line']
						];
					}
				}

				Recordset::$sqls[$this->hash]['traces'][]	= $current_trace;
			}
		}

		if ($cache && isset(Recordset::$_result_store[$this->hash])) {
			$store				= Recordset::$_result_store[$this->hash];
			$this->num_rows		= $store['num_rows'];
			$this->recordset	= $store['recordset'];

			Recordset::$count_hard_cache_hits++;

			if (DB_LOGGING) {
				Recordset::$sqls[$this->hash]['duration']	= 'HARDCACHE';
			}

			return;
		}

		if (!$cache) {
			$this->__do_query($sql);
		} else {
			$this->__do_cached_query($sql);
			Recordset::$sqls[$this->hash]['cached']	= true;
		}
	}

	static function connect($name, $default, $host, $user, $pass, $db) {
		if ($default) {
			Recordset::$default_connection	= $name;
		}

		try {
			Recordset::$connections[$name]	= new PDO('mysql:host=' . $host . ';dbname=' . $db . ';charset=utf8', $user, $pass, [
				PDO::ATTR_PERSISTENT => true
			]);
		} catch (PDOException $e) {
			echo 'Recordset::connect - Error: ' . $e->getMessage();
		}

		if (Recordset::$cache_mode === 'file' && !is_dir(ROOT . '/../tmp/recordset')) {
			mkdir(ROOT . '/../tmp/recordset');
		} elseif (Recordset::$cache_mode === 'shared' && !is_dir(ROOT . '/../tmp/shared')) {
			mkdir(ROOT . '/../tmp/shared');
		}
	}

	function repeat() {
		$this->recordset	= [];
		$this->num_rows		= 0;

		$this->__do_query($this->sql);

		return $this;
	}

	private function __do_query($sql, $connection = true) {
		if (!$sql) {
			return $this;
		}

		$connection	= is_bool($connection) && $connection ? Recordset::$connections[Recordset::$default_connection] : Recordset::$connections[$connection];
		$statement	= $connection->prepare($sql);
		$statement->execute();

		if ((int)$statement->errorCode()) {
			throw new Exception("Query error: " . $sql, 1);
		}

		$this->sql		= $sql;
		$this->num_rows = $statement->rowCount();

		if ($this->num_rows) {
			while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
				$this->recordset[]	= $row;
			}
		}

		if ($this->recordset) {
			reset($this->recordset);
		}

		if (DB_LOGGING) {
			Recordset::$sqls[$this->hash]['duration']	= microtime(true) - Recordset::$sqls[$this->hash]['duration'];
		}

		return $this;
	}

	private function __do_cached_query($sql) {
		if (!$sql) {
			return $this;
		}

		$do_query	= true;
		$store		= false;
		$key		= 'RECSET_' . Recordset::$key_prefix . md5($sql);

		if (Recordset::$cache_mode == 'file') {
			$cache_file	= ROOT . '/../tmp/recordset/' . $key . '.sqlcache';
			$cache_data	= @file_get_contents($cache_file);

			if (!($cache_data === false)) {
				$cache			= unserialize($cache_data);
				$do_query		= false;

				$data			= $cache['data'];
				$this->num_rows	= $cache['rows'];
			} else {
				$store	= true;
			}
		} elseif (Recordset::$cache_mode == 'shared') {
			$cache_data = SharedStore::G($key);

			if (is_null($cache_data)) {
				$store = true;
			} else {
				$cache = unserialize($cache_data);
				$do_query = false;

				$data = $cache['data'];
				$this->num_rows = $cache['rows'];
			}
		}

		if ($do_query) {
			$this->__do_query($sql);

			Recordset::$count_cache_miss++;
		} else {
			if ($this->num_rows) {
				if (!is_array($data)) {
					$this->__do_query($sql);

					$store	= true;
				} else {
					foreach ($data as $r) {
						$this->recordset[] = $r;
					}
				}
			}

			unset($data, $cache);

			Recordset::$count_cache_hits++;
		}

		$this->sql = $sql;

		if ($this->recordset) {
			reset($this->recordset);
		}

		if ($store) {
			$data_store = [
				'data'	=> $this->recordset,
				'rows'	=> $this->num_rows,
				'sql'	=> $sql
			];

			if (Recordset::$cache_mode === 'file') {
				file_put_contents($cache_file, serialize($data_store));
			} elseif (Recordset::$cache_mode === 'shared') {
				SharedStore::S($key, $data_store);
			}
		}

		if (!isset(Recordset::$_result_store[$this->hash])) {
			Recordset::$_result_store[$this->hash]	= [
				'num_rows'	=> $this->num_rows,
				'recordset'	=> $this->recordset
			];
		}

		if (DB_LOGGING) {
			Recordset::$sqls[$this->hash]['duration']	= microtime(true) - Recordset::$sqls[$this->hash]['duration'];
		}

		return $this;
	}

	function row_array() {
		if ($this->recordset) {
			$current = current($this->recordset);
		} else {
			$current = false;
		}

		return $current;
	}

	function result_array() {
		return $this->recordset ? $this->recordset : [];
	}

	function result() {
		if (!$this->recordset) {
			return [];
		}

		$result	= [];

		foreach ($this->recordset as $record) {
			$class	= new stdClass();
			foreach ($record as $field => $data) {
				$class->{$field}	= $data;
			}

			$result[]	= $class;
		}

		return $result;
	}

	function row() {
		$current = $this->recordset[key($this->recordset)];

		$ret = new stdClass();
		foreach ($current as $k => $v) {
			$ret->$k = $v;
		}

		return $ret;
	}

	function next_row() {
		next($this->recordset);
	}

	function previous_row() {
		prev($this->recordset);
	}

	function bof() {
		return key($this->recordset) == 0;
	}

	function eof() {
		return key($this->recordset) == $this->num_rows - 1;
	}

	function set_records($array) {
		$this->recordset	= $array;
		$this->num_rows		= sizeof($array);
	}

	static function insert($table, $fields, $duplicate = null, $connection = true) {
		$dp		= [];
		$keys	= [];
		$sets	= Recordset::_parse_set($fields, true);

		foreach ($fields as $k => $v) {
			$keys[] = '`' . $k . '`';
		}

		if ($duplicate) {
			$dp = Recordset::_parse_where($duplicate);
		}

		$sql = 'INSERT INTO ' . $table . ' (' . join(', ', $keys) . ') VALUES (' . join(', ', $sets) . ')' . ($duplicate ? ' ON DUPLICATE KEY UPDATE ' . join(',', $dp) : '');

		$connection	= is_bool($connection) && $connection ? Recordset::$connections[Recordset::$default_connection] : Recordset::$connections[$connection];
		$result		= $connection->query($sql);

		if ($result === false) {
			throw new Exception("Insert query error: " . $sql, 1);
		}

		Recordset::$count_inserts++;

		if ($duplicate) {
			Recordset::$count_inserts_w_dup++;
		}

		unset($dp);
		unset($keys);
		unset($sets);

		return $connection->lastInsertId();
	}

	static function update($table, $fields, $where = NULL, $connection = true) {
		$wh		= [];
		$sets	= Recordset::_parse_set($fields);

		if ($where) {
			$wh = Recordset::_parse_where($where);
		}

		$sql	= 'UPDATE ' . $table . ' SET ' . join(', ', $sets) . ($where ? ' WHERE ' . join(' AND ', $wh) : '');

		if (DB_LOGGING) {
			$hash	= md5($sql);
			Recordset::$sqls[$hash] = [
				'sql'		=> $sql,
				'count'		=> 1,
				'cached'	=> false,
				'duration'	=> microtime(true),
				'traces'	=> []
			];
		}

		$connection	= is_bool($connection) && $connection ? Recordset::$connections[Recordset::$default_connection] : Recordset::$connections[$connection];
		$statement	= $connection->prepare($sql);
		$statement->execute();

		if ((int)$statement->errorCode()) {
			throw new Exception("Update query error " . $sql, 1);
		}

		if (DB_LOGGING) {
			Recordset::$sqls[$hash]['duration']	= microtime(true) - Recordset::$sqls[$hash]['duration'];
		}

		Recordset::$count_updates++;
	}

	static function delete($table, $where, $connection = true) {
		$wh = [];

		if ($where) {
			$wh = Recordset::_parse_where($where);
		}

		$sql		= 'DELETE FROM ' . $table . ($where ? ' WHERE ' . join(' AND ', $wh) : '');

		if (DB_LOGGING) {
			$hash	= md5($sql);
			Recordset::$sqls[$hash] = [
				'sql'		=> $sql,
				'count'		=> 1,
				'cached'	=> false,
				'duration'	=> microtime(true),
				'traces'	=> []
			];
		}

		$connection	= is_bool($connection) && $connection ? Recordset::$connections[Recordset::$default_connection] : Recordset::$connections[$connection];
		$statement	= $connection->prepare($sql);
		$statement->execute();

		if ((int)$statement->errorCode()) {
			throw new Exception("Delete query error: " . $sql, 1);
		}

		if (DB_LOGGING) {
			Recordset::$sqls[$hash]['duration']	= microtime(true) - Recordset::$sqls[$hash]['duration'];
		}

		Recordset::$count_deletes++;
	}

	static function fromArray($array) {
		$r = new Recordset();
		$r->set_records($array);

		return $r;
	}

	static function query($sql, $cache = false, $connection = true) {
		return new Recordset($sql, $cache, $connection);
	}

	private static function _parse_where($where) {
		$wh = [];

		foreach ($where as $k => $v) {
			$raw_v = $v;

			if (is_array($v)) {
				if (isset($v['escape']) && $v['escape'] !== false) {
					$v = is_null($v['value']) ? 'NULL' : '\'' . addslashes($v['value']) . '\'';
				} else {
					$v = $v['value'];
				}

				if (isset($raw_v['mode'])) {
					switch ($raw_v['mode']) {
						case 'equal':
						default:
							$wh[] = '`' . $k . '` = ' . $raw_v['value'];
							break;
						case 'is_null':
							$wh[] = '`' . $k . '` IS NULL';
							break;
						case 'not_null':
							$wh[] = '`' . $k . '` IS NOT NULL';
							break;
						case 'between':
							$wh[] = '(' . $k . ' BETWEEN ' . $raw_v['value'][0] . ' AND ' . $raw_v['value'][1] . ')';
							break;
						case 'in':
							$wh[] = '`' . $k . '` IN (' . $raw_v['value'] . ')';
							break;
						case 'not_in':
							$wh[] = '`' . $k . '` NOT IN (' . $raw_v['value'] . ')';
							break;
						case 'not':
							$wh[] = '`' . $k . '` != ' . $raw_v['value'];
							break;
						case 'lt':
							$wh[] = '`' . $k . '` < ' . $raw_v['value'];
							break;
						case 'lte':
							$wh[] = '`' . $k . '` <= ' . $raw_v['value'];
							break;
						case 'gt':
							$wh[] = '`' . $k . '` > ' . $raw_v['value'];
							break;
						case 'gte':
							$wh[] = '`' . $k . '` >= ' . $raw_v['value'];
							break;
					}
				} else {
					$wh[] = '`' . $k . '` = ' . $v;
				}
			} else {
				$v		= is_null($v) ? 'NULL' : '\'' . addslashes($v) . '\'';
				$wh[]	= '`' . $k . '` = ' . $v;
			}
		}

		return $wh;
	}

	private static function _parse_get($fields, $insert = false) {
		$sets	= [];

		foreach ($fields as $k => $v) {
			if (is_array($v)) {
				if ($v['escape'] !== false) {
					$v = is_null($v['value']) ? 'NULL' : '\'' . addslashes($v['value']) . '\'';
				} else {
					$v = $v['value'];
				}
			} else {
				$v = is_null($v) ? 'NULL' : '\'' . addslashes($v) . '\'';
			}

			if ($insert) {
				$sets[] = $v;
			} else {
				$sets[]	= '`' . $k . '` = ' . $v;
			}
		}

		return $sets;
	}

	private static function _parse_set($fields, $insert = false) {
		$sets	= [];

		foreach ($fields as $k => $v) {
			if (is_array($v)) {
				if ($v['escape'] !== false) {
					$v = is_null($v['value']) ? 'NULL' : '\'' . addslashes($v['value']) . '\'';
				} else {
					$v = $v['value'];
				}
			} else {
				$v = is_null($v) ? 'NULL' : '\'' . addslashes($v) . '\'';
			}

			if ($insert) {
				$sets[] = $v;
			} else {
				$sets[]	= '`' . $k . '` = ' . $v;
			}
		}

		return $sets;
	}
}
