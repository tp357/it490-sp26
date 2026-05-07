#!/usr/bin/php

<?php

	shell_exec( "tar -cv $argv[1] -f $argv[2]");
        shell_exec("rsync phoenix@phoenix=0:/home/phoenix/packages/$argv[2] <<< $argv[2]");

	$client=new rabbitMQClient('../hosts.ini', 'DeploymentServer');
	$message= array('type'=>$argv[3], 'path'=>"/home/phoenix/packages/$argv[2]", 'target'=>$argv[4], 'file'=>$argv2);
	$response= $client->send_request($message);
	if ($response['status']==='success'){

	echo "wahoo \m";
	} else {

		echo "dangit \n";
	}


?>
