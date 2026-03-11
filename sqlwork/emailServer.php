

#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doRegister($username $email) {
$mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }

        echo "successfully connected to database".PHP_EOL;
	$query="INSERT INTO email ('$email', '$username')";
	$mydb->query($query);
	echo "email registered wahoo";
	return true;
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
                case "register":
                        $returnstatus=doRegister($request['email'], $request['username'];
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
}


$server = new rabbitMQServer("testRabbitMQ.ini","EmailServer");

echo "emailServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "emailServer END".PHP_EOL;
exit();

?>

