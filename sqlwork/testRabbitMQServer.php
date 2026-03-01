#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini", "SessionServer");

function doLogin($username,$password)
{
	$mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
	if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	return false;
}
echo "successfully connected to database".PHP_EOL;
    $query = "select * from users where USERNAME='$username' AND PASSWORD='$password';";
$response = $mydb->query($query);
echo mysqli_num_rows($response);
	if ($mydb->errno != 0)
		{
       		 echo "failed to execute query:".PHP_EOL;
	        echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	        exit(0);
		}			
	elseif(mysqli_num_rows($response)!=0){
		echo "succesful login";
		$testses="SELECt SESSIONID from sessions WHERE USERNAME='$username')";
		$sesadd="INSERT into sessions VALUES (UUID(), '$username')";
		$sesresult=$mydb->query($testses);
		if(mysqli_num_rows($sesresult)==0){
			$mydb->query($sesadd);	
		}
		return true;
	}
	else {
	echo "login failed";
		return false;
	}

    // lookup username in databas
// check password
    //return false if not valid
}

function doValidate($sessionId){
 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
}
echo "successfully connected to database".PHP_EOL;



}

function requestProcessor($request)
{
	$returnstatus = false;
	$message = array();
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
       $returnstatus=doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
  }
  
  if($returnstatus){
  	$message = array("status" => 'success', 'message'=>"Server received request and processed");
  } else {
	$message = array("status"=>"This shit ain't work");
  }
  
  return $client->publish($message);
}

$server = new rabbitMQServer("testRabbitMQ.ini","AuthServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

