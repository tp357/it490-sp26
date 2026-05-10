<?php
require_once('lib/path.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['movie_id']) || empty($input['sessionID']) || !isset($input['rating'])) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'missing fields'));
    exit();
}

$client = new rabbitMQClient(__DIR__.'/../servers.ini', 'ReviewServer');
$response = $client->send_request(array(
    'type' => 'add_review',
    'movie_id' => $input['movie_id'],
    'user_id' => $input['sessionID'],
    'rating' => $input['rating'],
    'review_text' => $input['review_text'] ?? ''
));

$ok = is_array($response) && ($response['status'] ?? '') === 'success';
http_response_code($ok ? 200 : 400);
echo json_encode(is_array($response) ? $response : array('status' => 'error', 'message' => 'no response'));
?>