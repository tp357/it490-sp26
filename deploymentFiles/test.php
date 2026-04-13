#!/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$config = json_decode(file_get_contents('./qaSend.json'), true);

$client = new rabbitMQClient('deploymentRabbit.ini', 'AuthServer');
$message = array("message"=>"boobs");

$response = $client->send_request($message);

if ($response['status'] === 'success')
{
	echo "yes! YES!";
}
else
{
	echo "fuh nah";
}
?>
