<?php
require_once(__DIR__.'/lib/path.inc');
require_once(__DIR__.'/lib/get_host_info.inc');
require_once(__DIR__.'/lib/rabbitMQLib.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$client = new rabbitMQClient(__DIR__.'/../servers.ini', 'AuthServer');

$response = $client->send_request(array('type' => 'get_posts'));

if (is_array($response) && ($response['status'] ?? '') === 'success') {
    http_response_code(200);
    echo json_encode($response);
} else {
    http_response_code(200);
    echo json_encode(array('status' => 'success', 'posts' => array()));
}
?>
