<?php

require __DIR__ . '/../vendor/autoload.php';

$config = new \InboxSync\Config();
$client = \InboxSync\Helper::createGoogleOauthClient($config);

$client->authenticate($_GET['code']);
$access_token = $client->getAccessToken();

$config->storeGoogleToken($access_token);
print "YEAH!";
