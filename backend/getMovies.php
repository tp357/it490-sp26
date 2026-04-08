<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$client = new rabbitMQClient('config/servers.ini', 'MovieDBServer');

$request = array('type' => 'get_movies');

$response = $client->send_request($request);

if ($response['status'] === 'success') {
    http_response_code(200);
    echo json_encode($response);
} else {
    http_response_code(400); // placeholder error code
    echo json_encode($response);
}
?>