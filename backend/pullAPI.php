<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');
header('Content-Type: application/json');

$api_key="b28607cf";

$client = new rabbitMQClient('config/servers.ini', 'MovieDBServer');
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['query'])) {
    http_response_code(400);
    exit();
}

$title = $input['query'];
$checkRequest = array('type' => 'if_movie_exists', 'title' => $title);
$checkResponse = $client -> send_request($checkRequest);
if ($checkResponse && $checkResponse['exists']) {
    $getRequest = array('type' => 'get_movie_by_title', 'title' => $title);
    $response = $client -> send_request($getRequest);
    http_response_code(200);
    echo json_encode(array('status' => 'success', 'source' => 'database', 'movies' => array($response)));
    exit();
}

$url = "http://www.omdbapi.com/?t=" . urlencode($title) . "&apikey=" . $api_key;
$apiResponse = file_get_contents($url);
$data = json_decode($apiResponse, true);
if ($data['Response'] !== 'True') {
    http_response_code(404);
    echo json_encode(array('status' => 'error', 'message' => 'Movie not found'));
    exit();
}

$movieData = array(
    'type' => 'add_movie',
    'id' => $data['imdbID'] ?? uniqid(),
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

$response = $client -> send_request($movieData);
if ($response && $response['status'] === 'success') {
    http_response_code(200);
    echo json_encode(array('status' => 'success', 'source' => 'api', 'movies' => array($movieData)));
} else {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => 'Failed to save movie to database'));
}
?>