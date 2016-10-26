<?php require "curlOOP.php";?>
<?php require "scraperOOP.php";?>

<?php
class Movie{
	protected $movieArray;
	protected $genreArray;
	protected $actorArray2D;
	protected $curlObject;
	protected $scraper;
	protected $connection;
	protected $actorIDArray;
	protected $genreIDArray;
	protected $movieID;

	function __construct(){
		$this->movieArray=[
			"title" => "",
			"releaseDate" => "",
			"duration" => "",
			"description" => "",
			"fileID" => "",
			"imdbRating" => "",
			"RTRating" => "",
			"youtubeTrailer" => "",
			"poster" => ""
			];
		$this->genreArray = array();
		$this->actorArray2D = array();
		$this->curlObject = new curlOOP();
		$this->connection = new connectToDB();
		$this->connection->setDB("Movies");
		}

	function setMovieData($url,$youtubeTrailer,$filepath){
		$data = $this->curlObject->getHTML($url);
		$this->scraper = new scraper($data);

		$genreArrayExplode = explode("itemprop=&quot;genre&quot;", $data);
		$arrayCount = count($genreArrayExplode)-1;
		for($i=1;$i<$arrayCount;$i++){
			$this->scraper->setData($genreArrayExplode[$i]);
			$this->genreArray[] = $this->scraper->scrapeData("&gt;", "&lt;/span");
		}
		$this->scraper->setData($data);
		$this->movieArray["title"] = trim($this->scraper->scrapeData("&lt;title&gt;", "- IMDb"));
		$this->movieArray["releaseDate"] = $this->scraper->scrapeData("&lt;meta itemprop=&quot;datePublished&quot; content=&quot;", "&quot; /&gt;");
		$this->movieArray["duration"] = $this->scraper->scrapeData("&lt;time itemprop=&quot;duration&quot; datetime=&quot;PT", "&quot;&gt;");
		$this->movieArray["description"] = trim($this->scraper->scrapeData("&lt;div class=&quot;summary_text&quot; itemprop=&quot;description&quot;&gt;", "&lt;/div&gt;"));
		$this->movieArray["imdbRating"] = $this->scraper->scrapeData("itemprop=&quot;ratingValue&quot;&gt;", "&lt;/span&gt;&lt;");
		$posterURL = $this->scraper->scrapeData("Poster&quot;", "itemprop");
		$this->scraper->setData($posterURL);
		$posterURL = $this->scraper->scrapeData("src=&quot;", "&quot;");
		$this->movieArray["poster"] = $this->downloadImage($posterURL, $this->movieArray["title"]);
		$this->movieArray["youtubeTrailer"] = $youtubeTrailer;
		$this->movieArray["RTRating"] = $this->getRTRating($this->movieArray["title"]);
		$this->movieArray["fileID"] = "/home/goodwin/Media/Movies/".$filepath;
	}

	function getRTRating($title){
		$titleString = $title;
		$titleString=explode(" ", $titleString);
		$RTSearchURL="https://www.rottentomatoes.com/search/?search=" . $titleString[0];
		$count=count($titleString);
		for($i=1; $i<$count; $i++){
			$RTSearchURL.="%20" . $titleString[$i];
		}
		$data = $this->curlObject->getHTML($RTSearchURL);
		$dataArray = explode("SummaryResults", $data);
		$RTscraper = new scraper($dataArray[1]);
		$newURL = "www.rottentomatoes.com".$RTscraper->scrapeData("href=&quot;", "&quot;&gt;");
		$data = $this->curlObject->getHTML($newURL);
		$RTscraper->setData($data);
		$RTRating = trim($RTscraper->scrapeData("Average Rating: &lt;/span&gt;", "/10"));
		return $RTRating;
	}

	function downloadImage($url, $name){
		$name = str_replace(" ", "_", $name);
		$fileName="/srv/http/movies/posters/".$name.".img";
		copy($url, $fileName);
		return $fileName;
	}

	function setActorData($url){
                $data = $this->curlObject->getHTML($url);
                $this->scraper->setData($data);
                $actorArray = explode("class=&quot;itemprop&quot; itemprop=&quot;actor&quot;", $data);
                $count = 0;
                $arraySize = count($actorArray);
                for($i = 0; $i<$arraySize; $i++){
                        $this->scraper->setData($actorArray[$i]);
                        $name = $this->scraper->scrapeData("temprop=&quot;name&quot;&gt;", "&lt;/span&gt;");
                        $link = $this->scraper->scrapeData("&lt;a href=&quot;/name/", "&quot;");
                        $link = "www.imdb.com/name/" . $link;
                        $actorData = $this->curlObject->getHTML($link);
                        $this->scraper->setData($actorData);
                        $actorDOB = $this->scraper->scrapeData("&lt;time datetime=&quot;", "&quot;");
                        $actorDOD = $this->scraper->scrapeData("&lt;time datetime=&quot;", "deathDate");
                        $this->scraper->setData($actorDOD);
                        $actorDOD = $this->scraper->scrapeData("&lt;time datetime=&quot;", "&quot");
                        if($actorDOB !== ""){
                                $nameArray = explode(" ", $name);
                                if(count($nameArray) == 2){
                                        $this->actorArray2D[$count]=array($nameArray[0], "", $nameArray[1], $actorDOB, $actorDOD);
                                }
                                else{
                                        $this->actorArray2D[$count]=array($nameArray[0], $nameArray[1], $nameArray[2], $actorDOB, $actorDOD);
                                }
                                $count++;
                        }
                }
        }	

	function printMovieArray(){
		echo "Title: ".$this->movieArray["title"]."\n";
		echo "Release Date: ".$this->movieArray["releaseDate"]."\n";
		echo "Duration: ".$this->movieArray["duration"]."\n";
		echo "Description: ".$this->movieArray["description"]."\n";
		echo "IMDB Rating: ".$this->movieArray["imdbRating"]."\n";
		echo "RTRating: ".$this->movieArray["RTRating"]."\n";
		echo "Youtube trailer link: ".$this->movieArray["youtubeTrailer"]."\n";
		echo "Poster file location: ".$this->movieArray["poster"]."\n";
	}

	function printActorArray(){
		echo "Director: ".$this->actorArray2D[0][0]." ".$this->actorArray2D[0][2]."\n";
		$count=count($this->actorArray2D);
		echo "Actors: \n";
		for($i=1;$i<$count;$i++){
			echo $this->actorArray2D[$i][0]." ";
			echo $this->actorArray2D[$i][2]."\n";
		}
	}

	function printGenreArray(){
		echo "Genres: ";
		$count = count($this->genreArray);
		for($i=0;$i<$count;$i++){
			echo $this->genreArray[$i]." ";
		}
		echo "\n";
	}


	function loadMovieToDB(){
		echo "Loading Movie to DB\n";
		$query = "INSERT INTO Movies(Name,DateMade,Duration,Description,IMDBRating,RTRating,TrailerLink,poster,FileID) VALUES (\"".$this->movieArray["title"]."\",\"".$this->movieArray["releaseDate"]."\",\"".$this->movieArray["duration"]."\",\"".$this->movieArray["description"]."\",\"".$this->movieArray["imdbRating"]."\",\"".$this->movieArray["RTRating"]."\",\"".$this->movieArray["youtubeTrailer"]."\",\"".$this->movieArray["poster"]."\",\"".$this->movieArray["fileID"]."\");";
		$result = $this->connection->queryDB($query);
		$this->movieID = $this->connection->conn->insert_id;
		return $this->movieID;
	}
	
	function loadActorToDB(){
		
		$count = count($this->actorArray2D);
		$this->actorIDArray = array();
		echo "Loading actors into DB\n";
		for($i=0;$i<$count;$i++){
			$query = "SELECT * FROM People WHERE FName = \"".$this->actorArray2D[$i][0]."\" AND LName = \"".$this->actorArray2D[$i][2]."\" AND DOB = \"".$this->actorArray2D[$i][3]."\";";
			$result = $this->connection->selectQuery($query);
			if($result != "derp"){
				$count2 = count($result);
				if($count2 == 0){
					echo "Inserting ".$this->actorArray2D[$i][0]." ".$this->actorArray2D[$i][0]."into table\n";
					$insertQuery = "INSERT INTO People(FName, MName, LName, DOB, DOD) VALUES(\"".$this->actorArray2D[$i][0]."\",\"".$this->actorArray2D[$i][1]."\",\"".$this->actorArray2D[$i][2]."\",\"".$this->actorArray2D[$i][3]."\",\"".$this->actorArray2D[$i][4]."\");";
					$this->connection->queryDB($insertQuery);
					$this->actorIDArray[]=$this->connection->conn->insert_id;
				}
				else{
					echo $this->actorArray2D[$i][0]." ".$this->actorArray2D[$i][0]." exist. Retrieving ID\n";
					$this->actorIDArray[]=$result[0]["ID"];
				}
			}
		}
		return $this->actorIDArray;
	}

	function loadGenreToDB(){
		$count = count($this->genreArray);
		$this->genreIDArray = array();
		echo "Loading genre into DB\n";
		for($i=0; $i<$count; $i++){
			$query = "SELECT * FROM Genre WHERE Description = \"".$this->genreArray[$i]."\";";
			$result = $this->connection->selectQuery($query);
			if($result != "derp"){
				$count2 = count($result);
				if($count2 == 0){
					echo "Inserting genre ".$this->genreArray[$i]." into table.\n";
					$query = "INSERT INTO Genre(Description) VALUES (\"".$this->genreArray[$i]."\");";
					$this->connection->queryDB($query);
					$this->genreIDArray[] = $this->connection->conn->insert_id;
				}
				else{
					echo $this->genreArray[$i]." already exist.  Retrieving ID\n";
					$this->genreIDArray[] = $result[0]["id"];
				}
			}
		}
		return $this->genreIDArray;
	}

	function syncPeopleRole(){
		echo "Syncing PeopleRole table\n";
		$query = "INSERT INTO PeopleMovieRole(PeopleId, MovieId, RoleId) VALUES(\"".$this->actorIDArray[0]."\",\"".$this->movieID."\",\"1\");";
		$this->connection->queryDB($query);
		$count = count($this->actorArray2D);
		for($i=1;$i<$count;$i++){
			$query = "INSERT INTO PeopleMovieRole(PeopleId, MovieId, RoleId) VALUES(\"".$this->actorIDArray[$i]."\",\"".$this->movieID."\",\"2\");";
			$this->connection->queryDB($query);
		}
	}

	function syncMovieGenre(){
		echo "Syncing MovieGenre table";
		$count = count($this->genreIDArray);
		for($i=0; $i<$count; $i++){
			$query = "INSERT INTO MovieGenre(MovieId, GenreId) values(\"".$this->movieID."\",\"".$this->genreIDArray[$i]."\");";
			$this->connection->queryDB($query);
		}
	}

	function syncMoviePeople(){
		echo "Syncing MoviePeople table\n";
		$count = count($this->actorIDArray);
		for($i=0; $i<$count; $i++){
			$query = "INSERT INTO MoviePeople(MovieId, PeopleId) VALUES(\"".$this->movieID."\",\"".$this->actorIDArray[$i]."\")";
			$this->connection->queryDB($query);
		}
	}

//	function getMovieData($title){
//		$query = "SELECT * FROM
//		$this->conn
//	}

	function loadAllData(){
		$query = "Select * FROM Movies WHERE Name = \"".$this->movieArray["title"]."\";";
		$result = $this->connection->selectQuery($query);
		if(count($result) > 0){
			echo "This movie is already in the DB";
		}
		else{
			$this->loadMovieToDB();
			$this->loadActorToDB();
			$this->loadGenreToDB();
			$this->syncPeopleRole();
			$this->syncMovieGenre();
			$this->syncMoviePeople();
		}
	}
}
?>
