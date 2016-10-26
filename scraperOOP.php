<?php
	class scraper{
		public $originalData;

		function __construct($givenData){
			$this->originalData=$givenData;
		}

		function scrapeData($start,$end){
			$newData=stristr($this->originalData, $start);
			$newData=substr($newData,strlen($start));
			$endPos=stripos($newData, $end);
			$newData = substr($newData, 0, $endPos);
			return $newData;
		}

		function setData($newData){
			$this->originalData=$newData;
		}

		function printData(){
			echo $this->originalData;
		}
	}
?>
