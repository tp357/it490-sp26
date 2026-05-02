<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['username'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username required']);
    exit;
}

// generates a code
$code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

$client = new rabbitMQClient('config/servers.ini', 'AuthServer');

$request = array('type' => 'send_2fa', 'username' => $input['username'], 'code' => $code);

$response = $client->send_request($request);

if ($response['status'] === 'success') {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Code sent!]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to send']);
}
?>
