#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');



function addMovie($title, $year, $rating, $released, $runtime, $genre, $director, $writer, $actors, $plot, $language, $country, $poster, $movieid){

 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
	}
	$checkq= "SELECT * FROM movies WHERE TITLE='$title'";
	$checkres=$mydb->query($checkq);
	if(mysqli_num_rows($checkres)>0){
		echo "movie already in db \n";
		return true;
	}
	$addquery="INSERT INTO movies (TITLE, YEAR, RATING, RELEASEDATE, RUNTIME, GENRE, DIRECTOR, WRITER, ACTORS, PLOT, LANGUAGE, COUNTRY, POSTER, MOVIEID) VALUES ('$title', '$year', '$rating', '$released', '$runtime', '$genre', '$director', '$writer', '$actors', '$plot', '$language', '$country', '$poster', '$movieid')";
		$mydb->query($addquery);
		return true;

}
function movieCheck($movieid){
 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }
	$checkquery="SELECT * FROM movies WHERE MOVIEID='$movieid'";
	$response=$mydb->query($checkquery);
	if(mysqli_num_rows($response)!==0){
		return true;
	} else {
		return false;
	}
}


function getMovie($movieid){
    $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }
	$getquery="SELECT * FROM movies WHERE MOVIEID='$movieid'";
	$result=$mydb->query($getquery);
	$movie=mysqli_fetch_assoc($result);
	return $movie;

}
function get_recs($sesid) {
	$mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
	{
      	  echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        	return false;
        }
	$userq="SELECT * FROM users WHERE SESSIONID='$sessionid";
	$response=$mydb->query($userq);
        $row=mysqli_fetch_row($response);
        $username=$row('USERNAME');
	$reviewsq= "SELECT MOVIE from ratings WHERE USERNAME=`$username' AND RATING>3`";
	$reviews=$mydb->query($reviewsq);
	$row=mysqli_fetch_row($reviews);
	if ($rows==null){
		$recs= "Review some movies for recommendations";
		return $recs;
	}
	$movies=$row('MOVIE');
	$moviename=array_rand($movies, 1);
	$genreq="SELECT GENRE FROM movies WHERE TITLE='$moviename'";
	$response=$mydb->query($genreq);
	$row=mysqli_fetch_row($response);
	$genre=$row('GENRE');
	$recq="SELECT TITLE FROM movies WHERE GENRE='$genre' ORDER BY RAND() LIMIT=5";
	$result=$mydb->query($recq);
	$row=mysqli_fetch_row($result);
	$recs=$row('TITLE');
	return $recs;
}

function requestProcessor($request)
{
        $returnstatus = false;
        $message = array();
	$exists=NULL;
	$ratings=NULL;
	$movie=null;
	$recs=null;

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
	  		 $returnstatus=addMovie($request['title'], $request ['year'], $request['rating'], $request['released'], $request['runtime'], $request['genre'], $request['director'], $request['writer'], $request['actors'], $request['plot'], $request['language'], $request['country'], $request['poster'], $request['id']);
			 echo "debugging add movie\n";
			 break; 
		 case "get_one_movie":
			$movie=getMovie($request['movie_id']);
			if ($movie!==null){
				$returnstatus=true;
			}
			echo "hi this is debugging get :D";
			break;
		case "if_movie_exists";
			$returnstatus=movieCheck($request['movie_id']);	
			$exists=$returnstatus;
			echo "exists = $exists\n";
			echo "hi this is debugging movie check\n";		
			break;
		 
		 case "get_one_movie":
			 $movie=getMovie($request['movie_id']);
			 if ($movie!=NULL){
				 $returnstatus=true;
			 }
			break;

		case "get_recommendations":
			$recs=get_recs($request['sessionid']);	
			$returnstatus=false;
			break;

  	}
  if($returnstatus && $exists){
        $message = array("status" => 'success', 'message'=>"Server received request and processed", 'exists'=>true);
  } elseif ($movie!=NULL){
	  return $movie;
	 $message = array("status" => 'success', 'message'=>"Server received request and processed", 'movie'=>$movie);
  }
  elseif ($returnstatus==false && $exists==false){
	$message = array("status" => 'success');
  }
  elseif ($returnstatus) {
        $message = array("status"=>"success", "messaage"=> "movie added");
  }
  else {
	$message=array("status"=>"failure");
  }
echo "$message[status] \n";
  return $message;
}


$server = new rabbitMQServer("../hosts.ini","MovieDBServer");

echo "MovieDBServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "MovieDBServer END".PHP_EOL;
exit();

?>

