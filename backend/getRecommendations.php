<?php
require_once('lib/path.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['sessionID'])) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'sessionID required'));
    exit();
}

$client = new rabbitMQClient(__DIR__.'/../../servers.ini', 'MovieDBServer');
$response = $client->send_request(array(
    'type' => 'get_recommendations',
    'sessionid' => $input['sessionID']
));

$ok = is_array($response) && ($response['status'] ?? '') === 'success';
http_response_code($ok ? 200 : 400);
echo json_encode(is_array($response) ? $response : array('status' => 'error', 'message' => 'no response'));
?>