<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new Google_Client();
$client->setApplicationName("GitHubSync");
$client->setAuthConfigFile('../client_secret_863766085506-2rqrslekiif0qhek36vhgl31thqa4rh7.apps.googleusercontent.com.json');
$client->setScopes(['https://www.googleapis.com/auth/gmail.modify']);
$client->setRedirectUri('http://inbox.dev/callback.php');
$client->setAccessType("offline");
$client->authenticate($_GET['code']);
$access_token = $client->getAccessToken();

file_put_contents('./../code.json', json_encode(['code' => $_GET['code'], 'access_token' => $access_token]));
print "YEAH!";
