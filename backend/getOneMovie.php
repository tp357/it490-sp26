<?php
require_once('lib/path.inc');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (empty($input['movie_id']) && empty($input['title'])) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'movie_id or title required'));
    exit();
}

$lookup = !empty($input['title']) ? $input['title'] : $input['movie_id'];

$client = new rabbitMQClient(__DIR__.'/../../servers.ini', 'MovieDBServer');
$response = $client->send_request(array(
    'type' => 'get_one_movie',
    'movie_id' => $lookup
));

if (is_array($response) && ($response['status'] ?? '') === 'success' && isset($response['movie'])) {
    http_response_code(200);
    echo json_encode($response);
    exit();
}

$api_key = 'b28607cf';
$imdbID = $input['movie_id'] ?? '';
$title = $input['title'] ?? '';

$omdbUrl = '';
if ($imdbID && preg_match('/^tt\d+$/', $imdbID)) {
    $omdbUrl = "https://www.omdbapi.com/?i=" . urlencode($imdbID) . "&apikey=" . $api_key;
} elseif ($title) {
    $omdbUrl = "https://www.omdbapi.com/?t=" . urlencode($title) . "&apikey=" . $api_key;
}

if ($omdbUrl) {
    if (function_exists('curl_init')) {
        $ch = curl_init($omdbUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $apiResponse = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create(array('http' => array('timeout' => 8)));
        $apiResponse = @file_get_contents($omdbUrl, false, $context);
    }
    $data = json_decode($apiResponse, true);
    if ($data && ($data['Response'] ?? '') === 'True') {
        $movie = array(
            'id' => $data['imdbID'],
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
        http_response_code(200);
        echo json_encode(array('status' => 'success', 'movie' => $movie));
        exit();
    }
}

http_response_code(404);
echo json_encode(array('status' => 'error', 'message' => 'not found'));
?>