<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

// required fields
if (!isset($input['title']) || !isset($input['year']) || !isset($input['rating'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'required fields: title, year, rating']);
    exit();
}

$client = new rabbitMQClient('config/servers.ini', 'MovieDBServer');

// this checks if the movie already is in the db
$checkRequest = array( 'type' => 'if_movie_exists','title' => $input['title']);

$checkResponse = $client->send_request($checkRequest);
// just a boolean response. 
if ($checkResponse['exists']) {
    http_response_code(409); // conflict
    echo json_encode(array('status' => 'error', 'message' => 'Movie already exists.'));
    exit();
}

$request = array('type' => 'add_movie', 'title' => $input['title'], 
'year' => $input['year'], 'rating' => $input['rating']);

$response = $client->send_request($request);

if ($response['status'] === 'success') {
    http_response_code(200);
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode($response);
}
?>