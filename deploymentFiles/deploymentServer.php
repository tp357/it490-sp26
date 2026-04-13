#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('RabbitMQLib.inc');

function doUpdateProd($path, $target){

        $mydb = new mysqli('127.0.0.1','testuser','testpassword','deployment');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	return false;
	$query= "UPDATE deployment SET currentprod=false WHERE currentprod=true AND target='$target'";
	$mydb->query($query);
	$query="UPDATE deployment SET currentprod=true WHERE path='$path'";
	$mydb->query($query);
	$hostquery="SELECT hostname FROM HOSTS WHERE service='$target' AND enviro
		nment= 'prod'";
	$response=$mydb->query($hostquery);
	$row=mysqli_fetch_assoc($response);
	$hostname=$row('hostname');
	$landingq="SELECT landing from HOSTS where hostname='$hostname'";
	$landingres=$mydb->query($landingq);
	$row=mysqli_fetch_assoc($landingres);
	$landing=$row('landing');
	 shell_exec("rsync $path $hostname:$landing");

	return true;

}


}
function doUpdateQA($path, $target){

        $mydb = new mysqli('127.0.0.1','testuser','testpassword','deployment');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
}	
	$query= "INSERT INTO deployment (path, currentprod, target, version) VALUES('$path', false, '$target',CURRENT_DATE)";
	$mydb->query($query);
	$hostquery="SELECT hostname FROM HOSTS WHERE service='$target' AND environment= 'QA'";
        $response=$mydb->query($hostquery);
        $row=mysqli_fetch_assoc($response);
        $hostname=$row('hostname');
        $landingq="SELECT landing from HOSTS where hostname='$hostname'";
        $landingres=$mydb->query($landingq);
        $row=mysqli_fetch_assoc($landingres);
        $landing=$row('landing');
         shell_exec("rsync $path $hostname:$landing");
        return true;
true;
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
	$badq="UPDATE deployment SET bad=true WHERE path='$path'";
	$mydb->query($badq);
	 $hostquery="SELECT hostname FROM HOSTS WHERE service='$target' AND environment= 'QA'";
        $response=$mydb->query($hostquery);
        $row=mysqli_fetch_assoc($response);
        $hostname=$row('hostname');
        $landingq="SELECT landing from HOSTS where hostname='$hostname'";
        $landingres=$mydb->query($landingq);
        $row=mysqli_fetch_assoc($landingres);
        $landing=$row('landing');
   	shell_exec("rsync $path $hostname:$landing");
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
          echo "bad messafe type \n";
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
	  case "updateProd":
		  $returnstatus=doUpdateProd($request['path'], $request['target']);
		  break;
	  case "updateQA":
		 $returnstatus=true;
		$returnstatus=doUpdateQA($request['path'], $request['target']);
		break;
	case "fallback":
		$returnstatus=doFallback($request['target']);
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

