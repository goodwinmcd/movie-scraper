<?php
class curlOOP{
	protected static $data;
	protected static $settings = Array(
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_FOLLOWLOCATION => TRUE,
		CURLOPT_AUTOREFERER => TRUE,
		CURLOPT_CONNECTTIMEOUT => 120,
		CURLOPT_TIMEOUT => 120,
		CURLOPT_MAXREDIRS => 10,
		);

	function getHTML($url){
		$ch = curl_init($url);
		curl_setopt_array($ch, self::$settings);
		$data = curl_exec($ch);
		$data = htmlspecialchars($data);
		curl_close($ch);
		return $data;
	}

	function printData(){
		echo $data;
	}
}
?>
