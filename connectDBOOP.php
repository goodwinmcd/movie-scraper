<?php
	class connectToDB{
		public $conn;
		
		function __construct(){
			$config = parse_ini_file("/home/goodwin/.scripts/php_scripts/testScripts/init/config.ini");
			$this->conn = new mysqli($config["server"], $config["username"],$config["password"]);
			if(mysqli_connect_errno()){
				echo "Connection failed: ".mysqli_connect_error()."\n";
			}
			else{
				echo "Successful Connection\n";
			}
		}

		function setDB($DBName){
			$result = $this->conn->select_db($DBName);
			if($result){
				echo "Successful Connection to ".$DBName."\n";
			}
			else{
				echo "Failed\n";
			}
		}

		function queryDB($query){
			$result = $this->conn->query($query);
			if($result == true){
				echo "Successful query\n";
				return $result;
			}
			else{
				echo "Query faied: ".mysqli_error($this->conn)."\n";
				return $result;
			}
		}

		function selectQuery($query){
			$rowsArray = array();
			$result = $this->conn->query($query);
			if($result == false){
				echo "Query did not work: ". mysqli_error($this->conn)."\n";
				return "derp";
			}
			else{
				echo "Successful SELECT Query\n";
				while($row = $result->fetch_assoc()){
					$rowsArray[] = $row;
				}
				return $rowsArray;
			}
		}
	}
?>
