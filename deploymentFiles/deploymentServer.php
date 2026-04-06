#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doUpdateProd($path){

        $mydb = new mysqli('127.0.0.1','testuser','testpassword','deployment');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
}


}
function doUpdateQA($path){

        $mydb = new mysqli('127.0.0.1','testuser','testpassword','deployment');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
}
	

}

function doFallback($target){
  $mydb = new mysqli('127.0.0.1','testuser','testpassword','deployment');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
}
	$query="SELECT path FROM deployment WHERE currentprod=true AND target='$target'";
	$result=$mydb->query($query);
	$row=mysqli_fetch_assoc($result);
	$filepath=$row('path');

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
	  case "updateProd":
		  doUpdateProd($request['path']);
		  break;
	case "updateQA":
		doUpdateQA($request['path']);
		break;
	case "fallback":
		doFallback($request['target']):
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

$server = new rabbitMQServer("deploymentRabbit.ini","AuthServer");
echo "DeploymentListener BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "DeploymentListener".PHP_EOL;
exit();
?>


