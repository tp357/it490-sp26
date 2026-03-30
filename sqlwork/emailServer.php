

#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doRegister($username $email, $sessionid) {
$mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }
	
        echo "successfully connected to database".PHP_EOL;
	$valuserquery= "SELECT USERNAME from sessions where SESSIONID='$sessionid' ";
	$valres= $mydb->query($valuserquery);
	$row=mysqli_fetch_assoc($valres);
	$usercheck=$row['USERNAME'];
	if($usercheck==$username){
		$regquery="INSERT INTO email ('$email', '$username')";
		$mydb->query($regquery);
		echo "email registered wahoo";
		return true;
	}
	else {
		echo "SessionID does not match username";
		return false;
	
	}
}



function requestProcessor($request)
{
        $returnstatus = false;
        $message = array();
	$sessionID=NULL;
	$status=NULL;
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
                       $status= doRegister($request['email'], $request['username', $register['sessionID'];
                        break;
                
  }
  if($status!=NULL){
	$returnstatus=true;
	
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

