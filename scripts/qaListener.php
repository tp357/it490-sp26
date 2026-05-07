#!/usr/bin.php

<?php

        require_once('path.inc');
        require_once('get_host_info.inc');
        require_once('RabbitMQLib.inc');

        function requestProcessor($request)
        {
                $returnstatus = false;
                $message = array();
                $sessionID=NULL;
                $file=$request['file'];
          echo "received request".PHP_EOL;
          var_dump($request);
          if(!isset($request['target']))
        {
                  echo "bad messafe type \n";
                 return "ERROR: unsupported message type";
        }
          switch ($request['target'])
        {
                  case "frontend":
                            shell_exec("rm -rf ~/git/it490-sp26/sqlwork/frontend");
                            shell_exec("tar -xf $file -C ~/git/it490-sp26");
                            break;
                      case "backend":
                                shell_exec("rm -rf ~/git/it490-sp26/backend");
                                shell_exec("tar -xf $file -C ~/git/it490-sp26");
                                break;
                      case "dmz":
                              shell_exec("rm -rf ~/git/it490-sp26/backend");
                              shell_exec("tar -xf $file -C ~/git/it490-sp26");
                       break;

                   case "database":
                           shell_exec("rm -rf ~/git/it490-sp26/sqlwork");
                           shell_exec("tar -xf $file -C ~/git/it490-sp26");

                           break;


         }
	}
        $server = new rabbitMQServer("qa.ini","DeploymentServer");
        echo "DeploymentListener BEGIN".PHP_EOL;
        $server->process_requests('requestProcessor');
        echo "DeploymentListener".PHP_EOL;
        exit();




?>

