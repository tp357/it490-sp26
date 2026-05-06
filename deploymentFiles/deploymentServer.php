#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('RabbitMQLib.inc');

function doUpdateProd($path, $target, $file){

        $mydb = new mysqli('127.0.0.1','testuser','testpassword','deployment');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	return false;
}
	$query= "UPDATE deployment SET currentprod=false WHERE currentprod=true AND service='$target'";
	$mydb->query($query);
	$query="UPDATE deployment SET currentprod=true WHERE path='$path$file'";
	$mydb->query($query);
	$hostquery="SELECT hostname FROM HOSTS WHERE service='$target' AND environment= 'prod'";
	$response=$mydb->query($hostquery);
	$row=mysqli_fetch_assoc($response);
	$hostname=$row['hostname'];
	$landingq="SELECT landing from HOSTS where hostname='$hostname' and target='$target'";
	$landingres=$mydb->query($landingq);
	$row=mysqli_fetch_assoc($landingres);
	$landing=$row['landing'];
	if(strcmp($hostname, "tirth@it490frontendprod") || strcmp($hostname, "tirth@it490frontendqa")){
		shell_exec("rsync  -e 'ssh -i ~/.ssh/deploymentkey' $path$file $hostname:$landing");
		 if(strcmp($target, "qa"){
			 $qaclient= new rabbitMQClient('qa.ini', 'DeployServer');
			 $request= array('target'=$target, 'file'=$path$file);
			 $qaclient->sendrequest($request);
                 } elseif(strcmp($target, "prod") {
			 $prodclient= new rabbitMQClient('prod.ini', 'DeployServer');
                         $request= array('target'=$target,  'file'=$path$file);
                         $prodclient->sendrequest($request);
                 }

	} else {
		 shell_exec("rsync $path $hostname:$landing");
		 if(strcmp($target, "qa"){
                         $qaclient= new rabbitMQClient('qa.ini', 'DeployServer');
                         $request= array('target'=$target);
                         $qaclient->sendrequest($request);
                 } elseif(strcmp($target, "prod") {
                          $prodclient= new rabbitMQClient('prod.ini', 'DeployServer');
                         $request= array('target'=$target, 'file'=$path$file);
                         $prodclient->sendrequest($request);

                 }

	}
	return true;

}

function doUpdateQA($path, $target, $file){

        $mydb = new mysqli('127.0.0.1','testuser','testpassword','deployment');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
}	
	$query= "INSERT INTO deployment (path, currentprod, target, version) VALUES('$path$file', false, '$target',CURRENT_DATE)";
	$mydb->query($query);
	$hostquery="SELECT hostname FROM HOSTS WHERE service='$target' AND environment= 'QA'";
        $response=$mydb->query($hostquery);
        $row=mysqli_fetch_assoc($response);
	$hostname=$row['hostname'];
	echo "here is the host $hostname \n";
        $landingq="SELECT landing from HOSTS where hostname='$hostname' and target='$target'";
        $landingres=$mydb->query($landingq);
        $row=mysqli_fetch_assoc($landingres);
	$landing=$row['landing'];
	echo "here is the landing $landing \n";
	echo "here is the path $path$file \n";
	 if(strcmp($hostname, "tirth@it490frontendprod") || strcmp($hostname, "tirth@it490frontendqa")){
                shell_exec("rsync -e 'ssh -i ~/.ssh/deploymentkey' $path$file $hostname:$landing"); 
		 if(strcmp($target, "qa"){
                         $qaclient= new rabbitMQClient('qa.ini', 'DeployServer');
                         $request= array('target'=$target,  'file'=$path$file);
                         $qaclient->sendrequest($request);
                 } elseif(strcmp($target, "prod") {
                          $prodclient= new rabbitMQClient('prod.ini', 'DeployServer');
                         $request= array('target'=$target,  'file'=$path$file);
                         $prodclient->sendrequest($request);

                 }

	 } else {
         	shell_exec("rsync $path$file $hostname:$landing");
		 if(strcmp($target, "qa"){
                         $qaclient= new rabbitMQClient('qa.ini', 'DeployServer');
                         $request= array('target'=$target,  'file'=$path$file);
                         $qaclient->sendrequest($request);

                 } elseif(strcmp($target, "prod") {
                          $prodclient= new rabbitMQClient('prod.ini', 'DeployServer');
                         $request= array('target'=$target,  'file'=$path$file);
                         $prodclient->sendrequest($request);

                 }
	 }
	 return true;
true;
}

function doFallback($target, $badpath){
  $mydb = new mysqli('127.0.0.1','testuser','testpassword','deployment');
        if ($mydb->errno != 0)
{
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        return false;
}
	$query="SELECT path FROM deployment WHERE currentprod=true AND target='$target'";
	$result=$mydb->query($query);
	$row=mysqli_fetch_assoc($result);
	$filepath=$row['path'];
	$badq="UPDATE deployment SET bad=true WHERE path='$badpath'";
	$mydb->query($badq);
	 $hostquery="SELECT hostname FROM HOSTS WHERE service='$target' AND environment= 'QA'";
        $response=$mydb->query($hostquery);
        $row=mysqli_fetch_assoc($response);
	$hostname=$row['hostname'];
	echo "Here is the host $hostname \n";
        $landingq="SELECT landing from HOSTS where hostname='$hostname' and target='$target'";
        $landingres=$mydb->query($landingq);
        $row=mysqli_fetch_assoc($landingres);
	$landing=$row['landing'];
	 if(strcmp($hostname, "tirth@it490frontendprod") || strcmp($hostname, "tirth@it490frontendqa")){
		 shell_exec("rsync -e 'ssh -i ~/.ssh/deploymentkey' $filepath $hostname:$landing");
		 if(strcmp($target, "qa"){
			 $qaclient= new rabbitMQClient('qa.ini', 'DeployServer');
                         $request= array('target'=$target,  'file'=$path$file);
                         $qaclient->sendrequest($request);

		 } elseif(strcmp($target, "prod") {
			  $prodclient= new rabbitMQClient('prod.ini', 'DeployServer');
                         $request= array('target'=$target,  'file'=$path$file);
                         $prodclient->sendrequest($request);

		 }
	 }else {
		 shell_exec("rsync $filepath $hostname:$landing"); 
		  if(strcmp($target, "qa"){
                         $qaclient= new rabbitMQClient('qa.ini', 'DeployServer');
                         $request= array('target'=$target);
                         $qaclient->sendrequest($request);
                 } elseif(strcmp($target, "prod") {
                          $prodclient= new rabbitMQClient('prod.ini', 'DeployServer');
                         $request= array('target'=$target);
                         $prodclient->sendrequest($request);

                 }

	 }
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
		  $returnstatus=doUpdateProd($request['path'], $request['target'], $request['file']);
		  break;
	  case "updateQA":
		 $returnstatus=true;
		$returnstatus=doUpdateQA($request['path'], $request['target'], $request['file']);
		break;
	case "fallback":
		$returnstatus=doFallback($request['target'], $request['file']);
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

