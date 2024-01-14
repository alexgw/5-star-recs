<?php

require '../vendor/autoload.php';
session_start();
$accessToken = $_SESSION['accessToken'];
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));

try {
    $dotenv->load();
} catch (Exception $e) {
    echo 'Could not load .env file: ', $e->getMessage(), "\n";
}
$yourApiKey = $_ENV['OPENAI_API_KEY'];



$client = OpenAI::client($yourApiKey);

$input = $_POST['input'];

$input = strip_tags($input);

$input =

    $result = $client->chat()->create([
        'model' => 'gpt-3.5-turbo',
        // 'max_tokens' => 20,
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a music recommendation converter. You take plain english requests and convert them into Spotify recommendations using the folowing JSON format:
            
                {
                    "limit": 20, "market": GB, "seed_genres": "A comma-separated list of genres for seed values in an array []. Up to 5 seed values may be provided.", "min_acousticness": "The minimum acousticness of recommended tracks (range: 0 - 1)", "max_acousticness": "The maximum acousticness of recommended tracks (range: 0 - 1)", "target_acousticness": "The target acousticness for recommended tracks (range: 0 - 1)", "min_danceability": "The minimum danceability of recommended tracks (range: 0 - 1)", "max_danceability": "The maximum danceability of recommended tracks (range: 0 - 1)", "target_danceability": "The target danceability for recommended tracks (range: 0 - 1)", "min_duration_ms": "The minimum duration of recommended tracks in milliseconds", "max_duration_ms": "The maximum duration of recommended tracks in milliseconds", "target_duration_ms": "The target duration of recommended tracks in milliseconds", "min_energy": "The minimum energy of recommended tracks (range: 0 - 1)", "max_energy": "The maximum energy of recommended tracks (range: 0 - 1)", "target_energy": "The target energy for recommended tracks (range: 0 - 1)", "min_instrumentalness": "The minimum instrumentalness of recommended tracks (range: 0 - 1)", "max_instrumentalness": "The maximum instrumentalness of recommended tracks (range: 0 - 1)", "target_instrumentalness": "The target instrumentalness for recommended tracks (range: 0 - 1)", "min_key": "The minimum key of recommended tracks (range: 0 - 11)", "max_key": "The maximum key of recommended tracks (range: 0 - 11)", "target_key": "The target key for recommended tracks (range: 0 - 11)", "min_liveness": "The minimum liveness of recommended tracks (range: 0 - 1)", "max_liveness": "The maximum liveness of recommended tracks (range: 0 - 1)", "target_liveness": "The target liveness for recommended tracks (range: 0 - 1)", "min_loudness": "The minimum loudness of recommended tracks", "max_loudness": "The maximum loudness of recommended tracks", "target_loudness": "The target loudness for recommended tracks", "min_mode": "The minimum modality (major or minor) of recommended tracks (0 - minor, 1 - major)", "max_mode": "The maximum modality (major or minor) of recommended tracks (0 - minor, 1 - major)", "target_mode": "The target modality (major or minor) for recommended tracks (0 - minor, 1 - major)", "min_popularity": "The minimum popularity of recommended tracks (range: 0 - 100)", "max_popularity": "The maximum popularity of recommended tracks (range: 0 - 100)", "target_popularity": "The target popularity for recommended tracks (range: 0 - 100)", "min_speechiness": "The minimum speechiness of recommended tracks (range: 0 - 1)", "max_speechiness": "The maximum speechiness of recommended tracks (range: 0 - 1)", "target_speechiness": "The target speechiness for recommended tracks (range: 0 - 1)", "min_tempo": "The minimum tempo of recommended tracks", "max_tempo": "The maximum tempo of recommended tracks", "target_tempo": "The target tempo for recommended tracks", "min_time_signature": "The minimum time signature of recommended tracks (maximum value: 11)", "max_time_signature": "The maximum time signature of recommended tracks (maximum value: 11)", "target_time_signature": "The target time signature for recommended tracks (maximum value: 11)", "min_valence": "The minimum valence of recommended tracks (range: 0 - 1)", "max_valence": "The maximum valence of recommended tracks (range: 0 - 1)", "target_valence": "The target valence for recommended tracks (range: 0 - 1)"
                  }
                   

              You have access to this list of genres:

                [
                "acoustic", "afrobeat", "alt-rock", "alternative", "ambient", "anime", "black-metal", "bluegrass", "blues", "bossanova", "brazil", "breakbeat", "british", "cantopop", "chicago-house", "children", "chill", "classical", "club", "comedy", "country", "dance", "dancehall", "death-metal", "deep-house", "detroit-techno", "disco", "disney", "drum-and-bass", "dub", "dubstep", "edm", "electro", "electronic", "emo", "folk", "forro", "french", "funk", "garage", "german", "gospel", "goth", "grindcore", "groove", "grunge", "guitar", "happy", "hard-rock", "hardcore", "hardstyle", "heavy-metal", "hip-hop", "holidays", "honky-tonk", "house", "idm", "indian", "indie", "indie-pop", "industrial", "iranian", "j-dance", "j-idol", "j-pop", "j-rock", "jazz", "k-pop", "kids", "latin", "latino", "malay", "mandopop", "metal", "metal-misc", "metalcore", "minimal-techno", "movies", "mpb", "new-age", "new-release", "opera", "pagode", "party", "philippines-opm", "piano", "pop", "pop-film", "post-dubstep", "power-pop", "progressive-house", "psych-rock", "punk", "punk-rock", "r-n-b", "rainy-day", "reggae", "reggaeton", "road-trip", "rock", "rock-n-roll", "rockabilly", "romance", "sad", "salsa", "samba", "sertanejo", "show-tunes", "singer-songwriter", "ska", "sleep", "songwriter", "soul", "soundtracks", "spanish", "study", "summer", "swedish", "synth-pop", "tango", "techno", "trance", "trip-hop", "turkish", "work-out", "world-music ]

            '
            ],
            ['role' => 'user', 'content' => "Make a JSON based on $input"],
        ],
        // "response_format" => array("type" => "json_object")
    ]);

$variables_json = $result->choices[0]->message->content;

$variables = json_decode($variables_json, true);



$api = new SpotifyWebAPI\SpotifyWebAPI();
$api->setAccessToken($accessToken);


$recs = $api->getRecommendations($variables);

// echo "<pre>";
// var_dump($recs);
// echo "</pre>";

$loader = new \Twig\Loader\FilesystemLoader('../templates');

$twig = new \Twig\Environment($loader, [

    'debug' => true,
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

$template = $twig->load('recs.twig');

echo $template->render(['data' => $recs, 'body' => $variables_json]);