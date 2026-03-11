#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


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
		case "getmovie":
			echo "hi this is debugging get :D";
			break;
		case "sendmovie":
			echo "hi this is debugging send :D";
			break;
  	}
  if($returnstatus){
          echo "here is the session ID", $sessionID;
        $message = array("status" => 'success', 'message'=>"Server received request and processed");
  } else {
        $message = array("status"=>"This shit ain't work");
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

