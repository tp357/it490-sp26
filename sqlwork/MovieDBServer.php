#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function getMovie(){
 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }

}

function addMovie($title, $year, $rating, $released, $runtime, $genre, $director, $writer, $actors, $plot, $language, $country, $poster){

 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }
	$checkq="SELECT * FROM movies WHERE TITLE='$title'";
	$response=$mydb->query($checkq);
	if(mysqli_num_rows($response)!=0){
		$addquery="INSERT INTO movies VALUES ('$title', '$year', '$rating', $released', $runtime', '$genre', '$director', '$writer', '$actors', '$plot', '$language', '$country', '$poster')";
		$mydb->query($addquery);
		return true;
	}

}
function movieCheck($title){
 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }
	$checkquery="SELECT * FROM movies WHERE TITLE='$title'";
	$response=$mydb->query($checkquery);
	if($mysqli_num_rows($response)=0){
		return true;
	} else {
		return false;
	}
}


function requestProcessor($request)
{
        $returnstatus = false;
        $message = array();
	$exists=NULL;
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
          echo "bad message type";
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
 		 case "add_movie":
	  		 add_Movie($request['title'], $request ['year'], $request['rating'], $request['released'], $request['runtime'], $request['genre'], $request['director'], $request['writer'], $request['actors'], $request['plot'], $request['language'], $request['country'], $request['poster']);
			 echo "debugging add movie\n";
			 break; 
 		 case "get_one_movie":
			echo "hi this is debugging get :D";
			break;
		case "if_movie_exists";
			$returnstatus=movieCheck($request['title'];	
			$exists=$returnstatus
			echo "hi this is debugging movie check\n";		
			break;
		case "get_movies":
			echo "hi this is debugging get movies";
			break;

		case "sendreviews":
			echo "hi this is debugging send :D";
			break;
  	}
  if($returnstatus && $exists){
        $message = array("status" => 'success', 'message'=>"Server received request and processed", 'exists'=>"$exists");
  } elseif {
        $message = array("status" => 'success', 'message'=>"Server received request and processed");
	}else {
	$exists=false;
        $message = array("exists"=>"$exists");
  }
echo $message;
  return $message;
}


$server = new rabbitMQServer("Moviedb.ini","AuthServer");

echo "MovieDBServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "MovieDBServer END".PHP_EOL;
exit();

?>

