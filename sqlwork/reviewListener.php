#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function addReview($movie, $sessionid, $review, $reasoning){
 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
	}
	$userquery="SELECT * from sessions WHERE SESSIONID='$sessionid'";
	$userresult=$mydb->query($userquery);
	$row=mysqli_fetch_assoc($userresult);
	$username=$row["USERNAME"];
	$addquery="INSERT into reviews VALUES ('$review', '$username', '$movie', '$reasoning')";
	$mydb->query($addquery);
		
	$checkquery="SELECT * FROM reviews WHERE MOVIE='$movie' AND USERNAME='$username'";
	$checkresult=$mydb->query($checkquery);
	if(mysqli_num_rows($checkresult)!=0){
		echo "query added\n";
		return true;
	} else {
		echo "failed to add query\n";
		return false;
	}

}	
	
function getReview($movieid) {
	 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }
	$moviequery="SELECT * FROM reviews WHERE MOVIE='$movieid' ORDER BY RAND() LIMIT 5";
	$movieresult=$mydb->query($moviequery);
	$movieresponse=mysqli_fetch_assoc($movieresult);
	if(mysqli_num_rows($movieresult)!=0){
		echo "reviews gotten\n";
		return $movieresponse;
	} else {
		echo "No reviews for this movie\n";
		return null;
	}
}

function requestProcessor($request)
{
        $returnstatus = false;
        $message = array();
	$sessionID=NULL;
	$reviews=null;
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
          echo "bad message type";
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
        {
		  case "get_reviews":
	  		$reviews=getReview($request['movie_id']);
			if($reviews!=null){
				$returnstatus=true;

			}
			break;
		  case "add_review":
			 $returnstatus= addReview($request['movie_id'], $request['user_id'], $request['rating'], $request['reasoning']);
                        echo "hi this is debugging send :D";
                        break;
        }
  if($returnstatus){
	  return $reviews;
        $message = array("status" => 'success','reviews'=>$reviews, 'message'=>"Server received request and processed");
                                 } else {
        $message = array("status"=>"This shit ain't work");
  }
  return $message;
}


$server = new rabbitMQServer("../hosts.ini","ReviewServer");

echo "reviewListener BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "reviewListener END".PHP_EOL;
exit();

?>
         
