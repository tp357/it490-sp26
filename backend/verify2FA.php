<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username']) || !isset($input['code'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username and code both required']);
    exit;
}

$client = new rabbitMQClient('config/servers.ini', 'AuthServer');

$request = array('type' => 'verify_2fa', 'username' => $input['username'], 'code' => $input['code']);

$response = $client->send_request($request);

if ($response['status'] === 'success') {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'sessionID' => $response['sessionID'],
        'user_id' => $response['user_id']
    ]);
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid code']);
}
?>
