<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');
header('Content-Type: application/json');

$api_key="b28607cf";
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['query'])) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'incomplete query'));
    exit();
}
$title = trim($input['query']);
if ($title === '') {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'empty query'));
    exit();
}

function omdbFetch($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $body = curl_exec($ch);
        curl_close($ch);
        return $body;
    }
    $context = stream_context_create(array('http' => array('timeout' => 8)));
    return @file_get_contents($url, false, $context);
}

$searchUrl = "https://www.omdbapi.com/?s=" . urlencode($title) . "&apikey=" . $api_key;
$searchResponse = omdbFetch($searchUrl);
$searchData = json_decode($searchResponse, true);

if ($searchData && ($searchData['Response'] ?? '') === 'True' && !empty($searchData['Search'])) {
    $movies = array();
    foreach ($searchData['Search'] as $item) {
        $movies[] = array(
            'id' => $item['imdbID'],
            'title' => $item['Title'],
            'year' => $item['Year'],
            'poster' => $item['Poster']
        );
    }
    http_response_code(200);
    echo json_encode(array('status' => 'success', 'source' => 'api', 'movies' => $movies));
    exit();
}

$exactUrl = "https://www.omdbapi.com/?t=" . urlencode($title) . "&apikey=" . $api_key;
$apiResponse = omdbFetch($exactUrl);
$data = json_decode($apiResponse, true);
if (!$data || ($data['Response'] ?? '') !== 'True') {
    http_response_code(404);
    $msg = is_array($data) && isset($data['Error']) ? $data['Error'] : 'Movie not found';
    echo json_encode(array('status' => 'error', 'message' => $msg));
    exit();
}

$client = new rabbitMQClient(__DIR__.'/../servers.ini', 'MovieDBServer');

$movieData = array(
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
);

http_response_code(200);
echo json_encode(array('status' => 'success', 'source' => 'api', 'movies' => array(array(
    'id' => $data['imdbID'],
    'title' => $data['Title'],
    'year' => $data['Year'],
    'poster' => $data['Poster']
))));
if (function_exists('fastcgi_finish_request')) { fastcgi_finish_request(); }
else { @ob_end_flush(); @flush(); }

$client->publish($movieData);
?>
