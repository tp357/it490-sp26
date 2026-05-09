!#/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function send2FA($username, $code){

  $mydb = new mysqli('127.0.0.1','testuser','testpassword','490db');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
}
echo "successfully connected to database".PHP_EOL;
 $phonequery= "SELECT phone from users WHERE username='$username";
 $response=$mydb->query($phonequery);
 $row= mysqli_fetch_row($reponse);
 $phone= $row['phone'];
 $codeq="INSERT INTO 2fa VALIES('$code', '$username')";
 $mydb->query($codeq);
 return $phone;
}



function requestProcessor($request)
{
        $returnstatus = false;
        $message = array();
        $phone=NULL;
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
          echo "bad messafe type";
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "send_2fa":
            $phone=send2FA($request['username'],$request['code']);
            if($phone!=NULL){
                $returnstatus=true;
            }
            break;

    case "validate_session":
            $sessionID=doValidate($request['sessionID']);
            if($sessionID!=NULL){
                $returnstatus=true;


  if($returnstatus){
          echo "here is the session ID", $sessionID;
        $message = array("status" => 'success', "phone" =>"$phone", 'message'=>"Server received request and processed");
  } else {
        $message = array("status"=>"This shit ain't work");
  }
echo $message;
  return $message;
}

$server = new rabbitMQServer("testRabbitMQ.ini","2FAServer");

echo "2FAServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();






?>
