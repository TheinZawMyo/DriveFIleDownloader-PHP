<?php
require_once __DIR__.'/vendor/autoload.php';

session_start();

$client = new Google\Client();
$client->setAuthConfigFile('credentials.json');
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/callback.php');
$client->addScope([
    Google\Service\Drive::DRIVE,
    Google\Service\Drive::DRIVE_METADATA_READONLY,
    'https://www.googleapis.com/auth/drive.readonly',
]);
$client->setAccessType("offline");

if (!isset($_GET['code'])) {
    // Generate and set state value
    $state = bin2hex(random_bytes(16));
    $client->setState($state);
    $_SESSION['state'] = $state;

    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
    if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['state']) {
        die('State mismatch. Possible CSRF attack.');
    }
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}