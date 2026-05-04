#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');



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

function addReview($title, $rating, $sessionid) {
 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
	}
	$userq="SELECT * FROM users WHERE SESSIONID='$sessionid";
	$row=mysqli_fetch_row($userq);
	$username=$row('USERNAME');
	$ratingid= $username . $title;
	$addreviewq="INSERT INTO reviews (RATING, TITLE, USERNAME, ratingid)  VALUES ('$rating', '$title', '$username', '$ratingid')";
	$mydb->query($addreviewq);
	return true;
}


function getReviews($movieid){
	 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }
	$getquery="SELECT * FROM reviews WHERE MOVIE='$movieid'";
	$reviews=$mydb->query($getquery);
	return $reviews;
}

function getMovie($movie){
    $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }
	$getquery="SELECT * FROM movies WHERE TITLE='$movie'";
	$result=$mydb->query($getquery);
	return $result;

}

function updateReview($title, $sessionid, $rating){
 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }
        $userq="SELECT * FROM users WHERE SESSIONID='$sessionid";
        $row=mysqli_fetch_row($userq);
        $username=$row('USERNAME');
        $ratingid= $username . $title;
	$updateq= "UPDATE reviews set RATING='$rating' where ratingid='$ratingid'";
	$mydb->query($updateq);
	return true;

	}

function requestProcessor($request)
{
        $returnstatus = false;
        $message = array();
	$exists=NULL;
	$ratings=NULL;
	$movie=null;

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
		 
		 case "add_review":
			$returnstatus=addReview($request['movie_id'], $request['rating'], $request['sessionid']);
			echo "this is me debugging addmovie which adds a rating\n "
		 case "get_one_movie":
			 $movie=getMovie($request['movie_id']);
			 if ($movie!=NULL){
				 $returnstatus=true;
			 }
			break;

		case "get_reviews":
			$ratings=getReviews($request['movie_id']);
			if ($ratings!=NULL){
				$returnstatus=true;
			}
			break;

		case "update_review":
			$returnstatus=updateReview($request['movie_id'], $request['sessionid'], $request['rating']);
			break;

  	}
  if($returnstatus && $exists){
        $message = array("status" => 'success', 'message'=>"Server received request and processed", 'exists'=>"$exists");
  } elseif($ratings!=NULL) {
        $message = array("status" => 'success', 'message'=>"Server received request and processed". 'ratings'=>"$ratings");
  } elseif ($movie!=NULL){
	 $message = array("status" => 'success', 'message'=>"Server received request and processed". 'movie'=>"$movie");
  }
  else {
	$exists=false;
        $message = array("exists"=>"$exists");
  }
echo $message;
  return $message;
}


$server = new rabbitMQServer("Moviedb.ini","MovieDBServer");

echo "MovieDBServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "MovieDBServer END".PHP_EOL;
exit();

?>

