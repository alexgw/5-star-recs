<?php
require 'vendor/autoload.php';
session_start();
$accessToken = $_SESSION['accessToken'];
$loader = new \Twig\Loader\FilesystemLoader('./templates');

$twig = new \Twig\Environment($loader, [

    'debug' => true,
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

$template = $twig->load('history.twig');


echo $template->render();