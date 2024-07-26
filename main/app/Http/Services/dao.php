<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\DB;

class dao
{

	var $conn;
	public static $query;
	var $sgbd;
	var $dbhost;
	var $dbuser;
	var $dbpassword;
	var $dbname;
	var $fetchMode = "FETCH_ASSOC";
	var $error = 0;
	var $debug = false;


	function selectDB()
	{

		if ($this->conn) {
			$this->doDebug();
			//mysql_select_db($this->dbname, $this->conn);
			mysqli_select_db($this->conn, $this->dbname) or die('No se pudo seleccionar la base de datos');
		}
	}

	function doDebug($query = "")
	{
		// if ($this->debug){
		// 	echo @mysql_errno() . " : " . @mysql_error();
		// 	echo "\n";
		// 	echo $query;
		// 	echo "\n";
		// }
	}

	function __construct($sgbd)
	{
		$this->sgbd = $sgbd;
	}

	function connect($dbhost, $dbuser, $dbpassword, $dbname)
	{
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpassword = $dbpassword;
		$this->dbname = $dbname;
		// mysqli_connect($db_host,$db_user,$db_password, $db_name )
		if ($this->conn = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname)) {
			$this->selectDB();
		}



		$this->doDebug();
	}

	function close()
	{
		if ($this->conn) {
			mysqli_close($this->conn);
			$this->doDebug();
		}
	}

	function execute($query)
	{

		self::$query = $query;

		return DB::select(self::$query);

		//$result = mysqli_query($link,$this->query) or die('Consulta fallida: ' . mysqli_error($link));
		// $result = mysqli_query($this->conn, $query);
		// $this->doDebug($query);
		// if ($result) {
		// 	$return = $result;
		// 	@mysqli_free_result($result);
		// 	return $return;
		// } else {
		// 	die('Consulta no válida: ' . mysqli_error($this->conn));
		// }

	}

	function getAll($query)
	{

		return DB::select($query);

		// $result = mysqli_query($this->conn, $query);

		// $this->doDebug($query);
		// if ($result) {
		// 	$return = array();
		// 	if ($this->fetchMode == "FETCH_ASSOC") {
		// 		while ($row = mysqli_fetch_assoc($result)) {
		// 			$return[] = $row;
		// 		}
		// 	}
		// 	if ($this->fetchMode == "FETCH_FIELD") {
		// 		while ($row = mysqli_fetch_field($result)) {
		// 			$return[] = $row;
		// 		}
		// 	}
		// 	if ($this->fetchMode == "FETCH_OBJECT") {
		// 		while ($row = mysqli_fetch_object($result)) {
		// 			$return[] = $row;
		// 		}
		// 	}
		// 	if ($this->fetchMode == "FETCH_ROW") {
		// 		while ($row = mysqli_fetch_row($result)) {
		// 			$return[] = $row;
		// 		}
		// 	}
		// 	@mysqli_free_result($result);
		// 	return $return;
		// }
	}

	function selectLimit($query, $items, $init)
	{

		$query = $query . " limit " . $init . "," . $items;
		$result = mysqli_query($this->conn, $query);
		$this->doDebug($query);
		if ($result) {
			$return = array();
			if ($this->fetchMode == "FETCH_ASSOC") {
				while ($row = mysqli_fetch_assoc($result)) {
					$return[] = $row;
				}
			}
			if ($this->fetchMode == "FETCH_FIELD") {
				while ($row = mysqli_fetch_field($result)) {
					$return[] = $row;
				}
			}
			if ($this->fetchMode == "FETCH_OBJECT") {
				while ($row = mysqli_fetch_object($result)) {
					$return[] = $row;
				}
			}
			if ($this->fetchMode == "FETCH_ROW") {
				while ($row = mysqli_fetch_row($result)) {
					$return[] = $row;
				}
			}
			@mysqli_free_result($result);
			return $return;
		}
	}

	function getRow($query)
	{
		$return = $this->getAll($query);
		return $return;
	}

	function setFetchMode($fetchmode)
	{
		$this->fetchMode = $fetchmode;
	}

	function numRows($query)
	{
		// $result = mysqli_query($this->conn,$query );
		$result = mysqli_query($this->conn, $query);
		$this->doDebug($query);
		if ($result) {
			$return = mysqli_num_rows($result);
			@mysqli_free_result($result);
			return $return;
		}
	}

	function insertID()
	{
		return DB::select('SELECT LAST_INSERT_ID() as ID')[0]->ID;
	}
}
