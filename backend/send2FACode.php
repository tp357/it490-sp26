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

$client = new rabbitMQClient(__DIR__.'../servers.ini', '2FAServer');
$request = array('type' => 'get_phone', 'username' => $input['username']);
$response = $client->send_request($request);

if ($response['status'] !== 'success') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$phone = $response['phone'];

// generates a code
$code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

// temp add twilio logic

$storeRequest = array('type' => 'store_2fa_code', 'username' => $input['username'], 'code' => $code);

$storeResponse = $client->send_request($storeRequest);

if ($storeResponse['status'] === 'success') {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Code sent!']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to store code']);
}
?>
