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
	$userquery="SELECT USERNAME from users WHERE SESSIONID='$sessionid'";
	$userresult=$mydb->query($userquery);
	$row=mysqli_fetch_row($useresult);
	$username=$row["USERNAME"];
	$addquery="INSERT into REVIEWS VALUES ('$review', '$username', '$movie', '$reasoning')";
	$mydb->query($addquery);
		
	$checkquery="SELECT * FROM REVIEWS WHERE MOVIE='$movie' AND USERNAME='$username'";
	$checkresponse=$mydb->query($checkquery);
	if(mysqli_num_rows($checkresult)!=0){
		echo "query added\n"
		return true;
	} else {
		echo "failed to add query\n"
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
	$moviequery="SELECT * FROM reviews WHERE MOVIE='$movie'";
	$movieresponse=$mydb->query($moviequery);
	if(mysqli_num_rows($movieresponse)!=0){
		echo "reviews gotten\n"
		return $movieresponse;.
	} else {
		echo "No reviews for this movie\n"
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
			echo "hi this is debugging get :D";
			if($reviews!=null){
				$returnstatus=true;
			}
			break;
		  case "add_review":
			 $returnstatus= addReview($request['movie_id'], $request['user_id'], $request['rating'], $request['review_text']);

                        echo "hi this is debugging send :D";
                        break;
        }
  if($returnstatus){
        $message = array("status" => 'success','reviews'=>"$reviews" 'message'=>"Server received request and processed");
                                 } else {
        $message = array("status"=>"This shit ain't work");
  }
echo $message;
  return $message;
}


$server = new rabbitMQServer("testRabbitMQ.ini","AuthServer");

echo "reviewListener BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "reviewListener END".PHP_EOL;
exit();

?>
         
