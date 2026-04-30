!#/usr/bin/php

<?php
	require_once('path.inc');
	require_once('get_host_info.inc');
	require_once('RabbitMQLib.inc');
	
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
	  switch ($request['target'])
  	{
        	  case "frontend":
			    shell_exec("rm -rm ~/git/it490-sp26/sqlwork/frontend");
			  break;
    		      case "backend":
			        shell_exec("rm -rm ~/git/it490-sp26/backend");
			      break;
 	 	      case "dmz":
         	         shell_exec("rm -rm ~/git/it490-sp26/backend");
		       break;

		   case "database":
			shell_exec("rm -rm ~/git/it490-sp26/sqlwork");
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

	$server = new rabbitMQServer("prod.ini","DeployServer");
	echo "DeploymentListener BEGIN".PHP_EOL;
	$server->process_requests('requestProcessor');
	echo "DeploymentListener".PHP_EOL;
	exit();

?>
