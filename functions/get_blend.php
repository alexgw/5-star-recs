<?php

require '../vendor/autoload.php';
session_start();
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));

try {
    $dotenv->load();
} catch (Exception $e) {
    echo 'Could not load .env file: ', $e->getMessage(), "\n";
}

$CLIENT_ID = $_ENV['SPOTIFY_CLIENT_ID'];
$CLIENT_SECRET = $_ENV['SPOTIFY_CLIENT_SECRET'];
$REDIRECT_URI = $_ENV['SPOTIFY_REDIRECT_URI'];

$database = new SQLite3('../data/db.sqlite');
$statement = $database->prepare('SELECT refresh_token FROM auth ORDER BY created_at DESC LIMIT 1');
$result = $statement->execute();
$refreshToken = $result->fetchArray(SQLITE3_ASSOC)['refresh_token'];

// Create a new SpotifyWebAPI\Session and refresh the access token
$session = new SpotifyWebAPI\Session(
    $CLIENT_ID,
    $CLIENT_SECRET,
    $REDIRECT_URI
);
$session->refreshAccessToken($refreshToken);
$accessToken = $session->getAccessToken();

// Update the session with the new access token
$_SESSION['userAccessToken'] = $accessToken;

$api = new SpotifyWebAPI\SpotifyWebAPI();

// Retry the original operation (in this case, fetching the user's information)
$api->setAccessToken($accessToken);


$data = $api->getPlaylist('37i9dQZF1EJxdz8iRHv1oU');
//https://open.spotify.com/playlist/37i9dQZF1EJxdz8iRHv1oU?si=9bc252791b7c4c81

// https: //open.spotify.com/playlist/7oOethLRNcQNzRo94qCB1x?si=9fbc3e51e29b47fa

// echo "<pre>";
// var_dump($data->tracks->items);
// echo "</pre>";

//if it doesn't exist already, create a new table to store the blend tracks 
$database = new SQLite3('../data/db.sqlite');
$database->exec('CREATE TABLE IF NOT EXISTS blend (
    id INTEGER PRIMARY KEY,
    track_id TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    name TEXT,
    artist_name TEXT,
    external_url TEXT,
    album_name TEXT,
    preview_url TEXT,
    image_url TEXT,
    count INTEGER
)');

$database->close();

foreach ($data->tracks->items as $track) {
    $database = new SQLite3('../data/db.sqlite');
    echo "adding track {$track->track->name} to the database <br>";
    echo "track id: {$track->track->id} <br>";
    $db_track = $database->querySingle("SELECT track_id FROM blend WHERE track_id = '{$track->track->id}'", false);
    echo "db track: {$db_track} <br>";
    $database->close();
    $database = new SQLite3('../data/db.sqlite');
    // $existingCount = false;
    if ($db_track == $track->track->id) {
        echo "track exists, incrementing count <br>";
        $existingCount = $database->querySingle("SELECT count FROM blend WHERE track_id = '{$track->track->id}'", false);
        // Track exists, increment count
        $newCount = $existingCount + 1;
        $updateStatement = $database->prepare("UPDATE blend SET count = {$newCount} WHERE track_id = :track_id");
        $updateStatement->bindValue(':track_id', $track->track->id, SQLITE3_TEXT);
        $updateStatement->execute();
        $database->close();
    } else {
        // Track doesn't exist, insert new entry
        $insertStatement = $database->prepare('INSERT INTO blend (track_id, name, artist_name, external_url, album_name, preview_url, image_url, count) VALUES (:track_id, :name, :artist_name, :external_url, :album_name, :preview_url, :image_url, 1)');
        $insertStatement->bindValue(':track_id', $track->track->id, SQLITE3_TEXT);
        $insertStatement->bindValue(':name', $track->track->name, SQLITE3_TEXT);
        $insertStatement->bindValue(':artist_name', $track->track->artists[0]->name, SQLITE3_TEXT);
        $insertStatement->bindValue(':external_url', $track->track->external_urls->spotify, SQLITE3_TEXT);
        $insertStatement->bindValue(':album_name', $track->track->album->name, SQLITE3_TEXT);
        $insertStatement->bindValue(':preview_url', $track->track->preview_url, SQLITE3_TEXT);
        $insertStatement->bindValue(':image_url', $track->track->album->images[0]->url, SQLITE3_TEXT);
        $insertStatement->execute();
        $database->close();
    }
}
