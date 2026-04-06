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



function requestProcessor($request)
{
        $returnstatus = false;
        $message = array();
        $sessionID=NULL;
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
          echo "bad message type";
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
	{
 		 case "get_one_movie":
			echo "hi this is debugging get :D";
			break;

		case "get_movies":
			echo "hi this is debugging get movies";
			break;

		case "sendreviews":
			echo "hi this is debugging send :D";
			break;
  	}
  if($returnstatus){
        $message = array("status" => 'success', 'message'=>"Server received request and processed");
  } else {
        $message = array("status"=>"This shit ain't work");
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

