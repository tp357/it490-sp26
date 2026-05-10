#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

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
		$sestest="select * from sessions where USERNAME='$username'";
		$testresult=$mydb->query($sestest);
		if(mysqli_num_rows($testresult)==0){
		$sesadd="INSERT into sessions VALUES (UUID(), '$username')";
		$mydb->query($sesadd);	
		}
		else {
			$row=mysqli_fetch_assoc($testresult);
			$sesID=$row['SESSIONID'];
		}
		return $sesID;
	}
	else {
	echo "login failed";
		return false;
	}

    // lookup username in databas
// check password
    //return false if not valid
}

function doValidate($sesID){
 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
	}

	echo "successfully connected to database".PHP_EOL;
	$valquery="SELECT * FROM sessions WHERE SESSIONID='$sesID'";
	$valresponse=$mydb->query($valquery);
	if(mysqli_num_rows($valresponse)!=0){
		echo "found your session";
		echo "returning session now";
		return $sesID;
	}
	else{
		echo "you have no session bro";
		return NULL;
	}
}
function doRegister($username, $password,$phone) {
 $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
        }
	$usercheck="SELECT * FROM users WHERE USERNAME='$username'";
	$namecheck=$mydb->query($usercheck);
	if(mysqli_num_rows($namecheck)!=0){
		echo "be original get your own user";
		return false;
	}
	$timestamp= date('Y-m-d H:i:s');
	$regq="INSERT into users VALUES ('$username','$timestamp','$password', '$phone')";
	$mydb->query($regq);
	echo "REGISTRATION WORKS YIPPEE";
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
	  echo "bad messafe type";
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
	    $sessionID=doLogin($request['username'],$request['password']);
	    if($sessionID!=NULL){
		$returnstatus=true;
	    }
	    break;
	    
    case "validate_session":
	    $sessionID=doValidate($request['sessionID']);
	    if($sessionID!=NULL){
                $returnstatus=true;
            }

	    break;
    case "registration":
	    
	    $returnstatus=doRegister($request['username'],$request['password'], $request['phone']);
	    break;
  }
  
  if($returnstatus){
	  echo "here is the session ID $sessionID \n";
  	$message = array("status" => 'success', "sessionID" =>"$sessionID", 'message'=>"Server received request and processed");
  } else {
	$message = array("status"=>"This shit ain't work");
  }
echo "$message[status] \n";	 
  return $message;
}

$server = new rabbitMQServer("../hosts.ini","AuthServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

