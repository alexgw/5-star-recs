<?php

require '../vendor/autoload.php';
session_start();
$accessToken = $_SESSION['accessToken'];


$api = new SpotifyWebAPI\SpotifyWebAPI();
$api->setAccessToken($accessToken);

// It's now possible to request data from the Spotify catalog

$data = $api->getPlaylist('7oOethLRNcQNzRo94qCB1x');
//https://open.spotify.com/playlist/37i9dQZF1EJxdz8iRHv1oU?si=9bc252791b7c4c81

//https: //open.spotify.com/playlist/7oOethLRNcQNzRo94qCB1x?si=9fbc3e51e29b47fa

$index = random_int(0, count($data->tracks->items) - 1);

$track = $data->tracks->items[$index]->track->id;
$seed = $data->tracks->items[$index]->track->name . " - " . $data->tracks->items[$index]->track->artists[0]->name;
// echo "<pre>";
// var_dump($track);
// echo "</pre>";

$recs = $api->getRecommendations([
    'seed_tracks' => [$track],
    'limit' => 10,
    'market' => 'GB',
]);



if (count($recs->tracks) > 1 && isset($recs->tracks)) {
    $recs_index = random_int(0, count($recs->tracks) - 1);
} else {
    $recs_index = 0;
    echo "<pre>";
    var_dump($recs);
    echo "</pre>";
}

$recommendation = $recs->tracks[$recs_index];

$loader = new \Twig\Loader\FilesystemLoader('../templates');

$twig = new \Twig\Environment($loader, [

    'debug' => true,
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

$template = $twig->load('track.twig');



echo $template->render(['data' => $recommendation, 'index' => $recs_index, 'seed' => $seed]);