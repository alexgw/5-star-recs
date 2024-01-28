<?php
require '../vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));


try {
    $dotenv->load();
} catch (Exception $e) {
    echo 'Could not load .env file: ', $e->getMessage(), "\n";
}
$CLIENT_ID = $_ENV['SPOTIFY_CLIENT_ID'];
$CLIENT_SECRET = $_ENV['SPOTIFY_CLIENT_SECRET'];
$REDIRECT_URI = $_ENV['SPOTIFY_REDIRECT_URI'];


$session = new SpotifyWebAPI\Session(
    $CLIENT_ID,
    $CLIENT_SECRET,
    $REDIRECT_URI
);

$api = new SpotifyWebAPI\SpotifyWebAPI();
$options = [
    'scope' => [
        'user-read-email',
        'playlist-read-private'
    ],
];

header('Location: ' . $session->getAuthorizeUrl($options));


print_r($api->me());