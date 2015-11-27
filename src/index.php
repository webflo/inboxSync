<?php

require __DIR__ . '/../vendor/autoload.php';

$config = new \InboxSync\Config();
$client = \InboxSync\Helper::createGoogleOauthClient($config);

$url = $client->createAuthUrl(['https://www.googleapis.com/auth/gmail.modify']);
header('Location: ' . $url);
