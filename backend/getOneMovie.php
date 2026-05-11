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

$movie_id = $input['movie_id'];

$client = new rabbitMQClient(__DIR__.'/../servers.ini', 'MovieDBServer');

$existsResponse = $client->send_request(array(
    'type' => 'if_movie_exists',
    'movie_id' => $movie_id
));

if (is_array($existsResponse) && !empty($existsResponse['exists']) && $existsResponse['exists'] === true) {
    $response = $client->send_request(array(
        'type' => 'get_one_movie',
        'movie_id' => $movie_id
    ));
    if (is_array($response) && ($response['status'] ?? '') === 'success' && isset($response['movie'])) {
        http_response_code(200);
        echo json_encode($response);
        exit();
    }
}

$api_key = 'b28607cf';

if (preg_match('/^tt\d+$/', $movie_id)) {
    $omdbUrl = "https://www.omdbapi.com/?i=" . urlencode($movie_id) . "&apikey=" . $api_key;
} else {
    $omdbUrl = "https://www.omdbapi.com/?t=" . urlencode($movie_id) . "&apikey=" . $api_key;
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
        if (function_exists('fastcgi_finish_request')) { fastcgi_finish_request(); }
        else { @ob_end_flush(); @flush(); }

        $client->publish(array(
            'type' => 'add_movie',
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
        ));
        exit();
    }
}

http_response_code(404);
echo json_encode(array('status' => 'error', 'message' => 'not found'));
?>
