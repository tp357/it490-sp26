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

$client = new rabbitMQClient(__DIR__.'../servers.ini', '2FAServer');

$verifyRequest = array('type' => 'verify_2fa', 'username' => $input['username'], 'code' => $input['code']);
$verifyResponse = $client->send_request($verifyRequest);

if ($verifyResponse['status'] !== 'success') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired code']);
    exit;
}

$loginRequest = array('type' => 'login_after_2fa', 'username' => $input['username']);
$loginResponse = $client->send_request($loginRequest);

if ($loginResponse['status'] === 'success') {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'sessionID' => $loginResponse['sessionID'],
        'user_id' => $loginResponse['user_id']
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Login failed after 2FA']);
}
?>
