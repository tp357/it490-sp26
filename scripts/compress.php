#!/usr/bin/php

<?php
 require_once("path.inc");
 require_once("RabbitMQLib.inc");
 require_once("get_host_info.inc");

	shell_exec( "tar -cv $argv[1] -f /home/phoenix/packages/$argv[2]");
        shell_exec("rsync /home/phoenix/packages/$argv[2] phoenix@phoenix-0:/home/phoenix/packages/");


	$client=new rabbitMQClient('../hosts.ini', 'DeploymentServer');
	$message= array('type'=>"$argv[3]", 'path'=>"/home/phoenix/packages/$argv[2]", 'target'=>"$argv[4]", 'file'=>"$argv[2]");
	$response= $client->send_request($message);
	if ($response['status']==='success'){


	echo "wahoo \n";
	} else {

		echo "dangit \n";
	}


?>
