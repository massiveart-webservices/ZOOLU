<?php

class SQLException extends Exception {}

class DBConnection {
	
	private static $link;
	
	private function __construct() { }
	
	public static function getInstance() {
		global $simplepo_config;
		if(!self::$link)
			self::$link = mysql_connect( $simplepo_config['db_host'], $simplepo_config['db_user'], $simplepo_config['db_pass'] );
			mysql_select_db( $simplepo_config['db_name'] );
		return self::$link;
	}
}

class Query {

	private $sql;
	private $cursor;
	private $link;
	private $table_prefix;

	function __construct($link = null) {
		global $simplepo_config;
		$this->sql = "";
		$this->table_prefix = $simplepo_config['table_prefix'];
		if(!$link) {
			$this->link = DBConnection::getInstance(); 
		}
	}
	function reset() {
		if($this->cursor) {
				@mysql_free_result($this->cursor);
				$this->cursor = null; 
		}
		$this->sql = "";
		return $this;
	}
	function sql() {
		if($this->cursor) {
				@mysql_free_result($this->cursor);
				$this->cursor = null;
		}
		$args = func_get_args();
		$sql = array_shift($args);
		// replace {} with table prefix
		$sql = preg_replace('/{([^}]*)}/',$this->table_prefix . '${1}',$sql);
		// escape arguments 
		$sql = str_replace('%','%%',$sql);
		$sql = str_replace('?','%s',$sql);
		$args = array_map(array('Query','escape'),$args);
		array_unshift($args,$sql);			
		$this->sql = call_user_func_array("sprintf",$args);
				
		return $this;
	}
	function appendSql() {
		if($this->cursor) {
			mysql_free_result($this->cursor);
			$this->cursor = null;
		}
		$args = func_get_args();
		$sql = array_shift($args);
		$sql = str_replace('?','%s',$sql);
		$args = array_map(array('Query','escape'),$args);
		array_unshift($args,$sql);			
		$this->sql .= call_user_func_array("sprintf",$args);
				
		return $this;
	
	}
	function fetchAll() {
		if(!$this->cursor) {
			$this->makeCursor();
		}
		$res = array();
		while($row = mysql_fetch_assoc($this->cursor)) $res[] = $row;
		return $res;
	}
	function fetch() {
		if(!$this->cursor) {
			$this->makeCursor();
		}
		return mysql_fetch_assoc($this->cursor);
	}
	function fetchOne() {
		if(!$this->cursor) {
			$this->makeCursor();
		}
		$r = mysql_fetch_row($this->cursor);
		return $r[0];
	}
	function count() {
		if(!$this->cursor) {
				$this->makeCursor();
		}
		return mysql_num_rows($this->cursor);
	}
	function affectedRows() {
		if(!$this->cursor) {
				$this->makeCursor();
		}
		return mysql_affected_rows($this->link);
	}
	function insertId() {
		if(!$this->cursor) {
			$this->makeCursor();
		}
		return mysql_insert_id($this->link);
	}
	function execute() {
		$this->makeCursor();
	}
	function fetchRow() {
		if(!$this->cursor) {
			$this->makeCursor();
		}
		return mysql_fetch_row($this->cursor);
	}
	function fetchCol($index = 0) {
		if(!$this->cursor) {
			$this->makeCursor();
		}
		$result = array();
		if(is_int($index)) {
			while($a = mysql_fetch_row($this->cursor)) $result[] = $a[$index];
		} else {
			while($a = mysql_fetch_assoc($this->cursor)) $result[] = $a[$index];
		}
		return $result;
	}
	function fetchAllKV() {
		if(!$this->cursor) {
			$this->makeCursor();
		}
		$res = array();
		while(list($a,$b) = mysql_fetch_row($this->cursor)) $r[$a] = $b;
		return $r;
	}
	protected function makeCursor() {
		$this->cursor = mysql_query($this->sql,$this->link);
		if($this->cursor === false) {
			$err = $this->getError();
			throw new SQLException("\n" . $err ."\n");
		}
	}
	function getError() {
		$err = "<pre>" . mysql_error() . "\n" . $this->sql . "</pre>";
		return $err;
	}
	function getSQL() {
		return $this->sql;
	}
	function getCursor() {
		return $this->cursor;
	}
	public function __toString() {
		return $this->sql;
	}
	public static function escape($value) {
		if (is_int($value) || is_float($value) ) {
			return "$value";
		} elseif(is_array($value)) {
			if(!empty($value)) {
				return implode(',',array_map(array('Query','escape'),$value));
			} else {
				return "NULL";
			}
		} elseif (is_null($value)) {
			return "''";
		} else {
			return "'" . mysql_real_escape_string($value) . "'";
		}
	}
}