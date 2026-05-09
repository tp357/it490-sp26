<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['user_id']) || empty($input['content'])) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'user_id and content required'));
    exit();
}

$client = new rabbitMQClient(__DIR__.'/../../servers.ini', 'AuthServer');
$response = $client->send_request(array(
    'type' => 'add_post',
    'user_id' => $input['user_id'],
    'content' => $input['content']
));

$ok = is_array($response) && ($response['status'] ?? '') === 'success';
http_response_code($ok ? 200 : 400);
echo json_encode(is_array($response) ? $response : array('status' => 'error', 'message' => 'no response'));
?>
