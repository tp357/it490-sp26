<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['username']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'username and password required'));
    exit();
}

$client = new rabbitMQClient(__DIR__.'/../../servers.ini', 'AuthServer');

$request = array('type' => 'login', 'username' => $input['username'], 'password' => $input['password']);

$response = $client->send_request($request);

$ok = is_array($response) && ($response['status'] ?? '') === 'success';
http_response_code($ok ? 200 : 401);
echo json_encode(is_array($response) ? $response : array('status' => 'error', 'message' => 'no response'));
?>