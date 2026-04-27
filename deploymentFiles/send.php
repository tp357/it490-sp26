#!/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$config = json_decode(file_get_contents('./qaSend.json'), true);
$send_cmd = sprintf('rsync %s %s:%s', $config["file"], $config["url"], $config["path"]);
echo $send_cmd;
shell_exec($send_cmd);

$client = new rabbitMQClient('deploymentRabbit.ini', 'AuthServer');
$message = array("type" => $config["type"], "path" => $config["path"], "target" => $config["target"], "file" => $config["file"]);

$response = $client->send_request($message);

if ($response['status'] === 'success')
{
	echo "yes! YES!";
}
else
{
	echo "fuh nah";
}
var_dump($response);
?>
