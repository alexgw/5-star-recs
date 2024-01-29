<?php

require '../vendor/autoload.php';
session_start();
$accessToken = $_SESSION['userAccessToken'];

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));


try {
    $dotenv->load();
} catch (Exception $e) {
    echo 'Could not load .env file: ', $e->getMessage(), "\n";
}

$CLIENT_ID = $_ENV['SPOTIFY_CLIENT_ID'];
$CLIENT_SECRET = $_ENV['SPOTIFY_CLIENT_SECRET'];
$REDIRECT_URI = $_ENV['SPOTIFY_REDIRECT_URI'];

try {
    // Try to make a request to the Spotify API using the current access token
    $api = new SpotifyWebAPI\SpotifyWebAPI();
    if ($accessToken) {
        $api->setAccessToken($accessToken);
    }

    // Example: Make a request to the Spotify API
    $me = $api->me();



} catch (SpotifyWebAPI\SpotifyWebAPIException $e) {
    // If there's an issue with the current access token, catch the exception

    // Log the error or handle it based on the specific error message
    echo 'Spotify API Exception: ', $e->getMessage(), "\n";

    // Check if the exception is due to an expired access token
    if ($e->getCode() === 401 && strpos($e->getMessage(), 'expired') !== false) {
        // Access token expired, try to refresh it
        echo "access token expired";

        // Fetch the refresh token from the database
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

        // Retry the original operation (in this case, fetching the user's information)
        $api->setAccessToken($accessToken);

        // Continue with other operations using the Spotify API...
    }
} catch (Exception $e) {
    // Handle other exceptions (if any)
    // Log the error or perform any other necessary actions
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

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

foreach ($data->tracks->items as $track) {
    echo "adding track {$track->track->name} to the database <br>";
    $existingCount = $database->querySingle("SELECT count FROM blend WHERE track_id = '{$track->track->id}'", false);
    // $existingCount = false;
    if ($existingCount !== false) {
        echo "track exists, incrementing count <br>";
        // Track exists, increment count
        $newCount = $existingCount + 1;
        $updateStatement = $database->prepare("UPDATE blend SET count = {$newCount} WHERE track_id = :track_id");
        $updateStatement->bindValue(':track_id', $track->track->id, SQLITE3_TEXT);
        $updateStatement->execute();
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
    }
}
