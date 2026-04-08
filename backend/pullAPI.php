<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');
header('Content-Type: application/json');

$api_key="b28607cf";

$client = new rabbitMQClient('config/servers.ini', 'MovieDBServer');
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['title'])) {
    http_response_code(400);
    exit();
}

$title = $input['title'];
$checkRequest = array('type' => 'if_movie_exists', 'title' => $title);
$checkResponse = $client -> send_request($checkRequest);
if ($checkResponse['exists']) {
    $getRequest = array('type' => 'get_movie_by_title', 'title' => $title);
    $response = $client -> send_request($getRequest);
    http_response_code(200);
    echo json_encode(array('status' => 'success', 'source' => 'database', 'movie' => $response));
    exit();
}

$url = "http://www.omdbapi.com/?t=" . urlencode($title) . "&apikey=" . $api_key;
$apiResponse = file_get_contents($url);
$data = json_decode($apiResponse, true);
if ($data['Response'] !== 'True') {
    http_response_code(404);
    echo json_encode($response);
    exit();
}

$request = array(
    'type' => 'add_movie',
    'title' => $data['Title'],
    'year' => $data['Year'],
    'rating' => $data['Rated'],
    'released' => $data['Released'],
    'runtime' => $data['Runtime'],
    'genre' => $data['Genre'],
    'director' => $data['Director'],
    'writer' => $data['Writer'],
    'actors' => $data['Actors'],
    'plot' => $data['Plot'],
    'language' => $data['Language'],
    'country' => $data['Country'],
    'poster' => $data['Poster']
);

$response = $client -> send_request($request);
if ($response['status'] === 'success') {
    http_response_code(200);
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode($response);
}
?>