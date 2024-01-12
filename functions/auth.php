<?php
require '../vendor/autoload.php';

$session = new SpotifyWebAPI\Session(
    '313745648d1a448e87b8f9018e406799',
    'ec81ea52420e4d9cac58208aa88cef22'
);



$session->requestCredentialsToken();
$accessToken = $session->getAccessToken();

#start the session
session_start();

#store the access token in the PHP session
$_SESSION['accessToken'] = $accessToken;

// Store the access token somewhere. In a database for example.

// Send the user along and fetch some data!
// header('Location: ../index.php');
die();

