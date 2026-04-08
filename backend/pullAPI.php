<?php
require_once('lib/path.inc');
require_once('lib/get_host_info.inc');
require_once('lib/rabbitMQLib.inc');

$api_key="b28607cf";
$movies=["Inception", "Interstellar"];

$client = new rabbitMQClient('config/servers.ini', 'MovieDBServer');

foreach ($movies as $title) {
    $url = "http://www.omdbapi.com/?t=" . urlencode($title) . "&apikey=" . $api_key;
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    if ($data['Response'] === 'True') {
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
        $db_response = $client->send_request($request);
        if ($db_response['status'] === 'success') {
            echo "Added: " . $data['Title'] . "\n";
        } else {
            echo "An issue occured: " . $data['Title'] . $db_response['message'] . "\n";
        }
    } else {
        echo "Not found: " . $title . "\n";
    }
    sleep(1);
}
?>