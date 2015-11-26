<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new Google_Client();
$client->setApplicationName("GitHubSync");
$client->setAuthConfigFile('../client_secret_863766085506-2rqrslekiif0qhek36vhgl31thqa4rh7.apps.googleusercontent.com.json');
$client->setAccessType("offline");
$client->setRedirectUri('http://inbox.dev/callback.php');

$url = $client->createAuthUrl(['https://www.googleapis.com/auth/gmail.modify']);
header('Location: ' . $url);
