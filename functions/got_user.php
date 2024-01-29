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

// Request a access token using the code from Spotify
$session->requestAccessToken($_GET['code']);

$accessToken = $session->getAccessToken();
$refreshToken = $session->getRefreshToken();

session_start();

#store the access token in the PHP session
$_SESSION['userAccessToken'] = $accessToken;
$_SESSION['userRefreshToken'] = $refreshToken;

$database = new SQLite3('../data/db.sqlite');

//create a new table if it doesn't exist already
$database->exec('CREATE TABLE IF NOT EXISTS auth (id INTEGER PRIMARY KEY, access_token TEXT, refresh_token TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)');
$database->close();
$database = new SQLite3('../data/db.sqlite');

//insert the access token into the database
$statement = $database->prepare('INSERT INTO auth (access_token, refresh_token) VALUES (:access_token, :refresh_token)');

$statement->bindValue(':access_token', $accessToken, SQLITE3_TEXT);
$statement->bindValue(':refresh_token', $refreshToken, SQLITE3_TEXT);

$statement->execute();


// Send the user along and fetch some data!
header('Location: ./get_blend.php');