<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['movie_id'])) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'movie_id required'));
    exit();
}

$client = new rabbitMQClient(__DIR__.'/../servers.ini', 'ReviewServer');
$response = $client->send_request(array(
    'type' => 'get_reviews',
    'movie_id' => $input['movie_id']
));

$response = array('status' => 'success', 'reviews' => $response);

http_response_code(200);
echo json_encode($response);
?>
