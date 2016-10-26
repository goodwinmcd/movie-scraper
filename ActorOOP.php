
<?php
class ActorOOP{
	protected $actorArray2D;

	function __construct(){
		$this->actorArray2D = array();
	}

	function setActorData($url){
		$curlObject = new curlOOP();
		$data = $curlObject->getHTML($url);
		$scraper = new scraper($data);
		$actorArray = explode("class=&quot;itemprop&quot; itemprop=&quot;actor&quot;", $data);
		$count = 0;
		$arraySize = count($actorArray);
		for($i = 0; $i<$arraySize; $i++){
			$scraper->setData($actorArray[$i]);
			$name = $scraper->scrapeData("temprop=&quot;name&quot;&gt;", "&lt;/span&gt;");
			$link = $scraper->scrapeData("&lt;a href=&quot;/name/", "&quot;");
			$link = "www.imdb.com/name/" . $link;
			$actorData = $curlObject->getHTML($link);
			$scraper->setData($actorData);
			$actorDOB = $scraper->scrapeData("&lt;time datetime=&quot;", "&quot;");
			$actorDOD = $scraper->scrapeData("&lt;time datetime=&quot;", "deathDate");
			$scraper->setData($actorDOD);
			$actorDOD = $scraper->scrapeData("&lt;time datetime=&quot;", "&quot");
			if($actorDOB !== ""){
				$nameArray = explode(" ", $name);
				if(count($nameArray) == 2){
					$this->actorArray2D[$count]=array($nameArray[0], $nameArray[1], $actorDOB, $actorDOD);
				}
				else{
					$this->actorArray2D[$count]=array($nameArray[0], $nameArray[1], $nameArray[2], $actorDOB, $actorDOD);
				}
				$count++;
			}
		}
	}

	function printActorArray(){
		var_dump($this->actorArray2D);
	}
}
?>
