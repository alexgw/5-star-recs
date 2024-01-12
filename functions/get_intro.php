<?php
require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));


try {
    $dotenv->load();
} catch (Exception $e) {
    echo 'Could not load .env file: ', $e->getMessage(), "\n";
}

$yourApiKey = $_ENV['OPENAI_API_KEY'];



$client = OpenAI::client($yourApiKey);

$result = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    // 'max_tokens' => 20,
    'messages' => [
        ['role' => 'system', 'content' => 'You are a classic radio DJ with a cool demeanor and a wide vocabulary'],
        ['role' => 'user', 'content' => "ask the listener if they're ready for their next recommendation in one short sentence"],
    ],
]);

echo $result->choices[0]->message->content;