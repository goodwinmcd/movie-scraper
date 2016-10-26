<?php require "MovieOOP.php";?>
<?php require "ActorOOP.php";?>
<?php require "connectDBOOP.php";?>

<?php
	$movieArray=new Movie();
	$movieArray->setMovieData($argv[1], $argv[2],$argv[3]);
	$movieArray->printMovieArray();
	$movieArray->printGenreArray();
	$movieArray->setActorData($argv[1]);
	$movieArray->printActorArray();
	echo "Does this information look correct? (please enter \"y\" or \"n\") ";
	$line = fgets(STDIN);
//	if($line != "yes" || $line != "no"){
//		echo "That is incorrect input. Try again.\n";
//		$line = readline("Does this information look correct? (please enter \"y\" or \"n\") ");
//	}
//	else{
		if($line == "y\n"){
			echo "loading\n";
			$movieArray->loadAllData();
		}
		if($line == "n\n"){
			exit(0);
		}
//	}

?>
