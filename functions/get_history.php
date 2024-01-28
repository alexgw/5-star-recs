<?php

require '../vendor/autoload.php';

//get blend data from the database
$database = new SQLite3('../data/db.sqlite');
$statement = $database->prepare('SELECT * FROM blend ORDER BY count DESC');
$result = $statement->execute();

$data = []; // Initialize an array to hold the data

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $data[] = $row; // Append each row to the data array
}

$loader = new \Twig\Loader\FilesystemLoader('../templates');

$twig = new \Twig\Environment($loader, [

    'debug' => true,
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

$template = $twig->load('history-list.twig');

echo $template->render(['data' => $data]);