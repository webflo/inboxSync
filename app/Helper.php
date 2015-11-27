<?php

/**
 * @file
 * Contains \InboxSync\Helper.
 */

namespace InboxSync;

use Google_Client;
use Google_Service_Gmail;

class Helper {

  public static function findLabel(Google_Service_Gmail $gmail, $label_name) {
    foreach ($gmail->users_labels->listUsersLabels('me') as $label) {
      if ($label['name'] == $label_name) {
        return $label['id'];
      }
    }
    return NULL;
  }

  public static function createGoogleOauthClient(Config $config) {
    $client = new Google_Client();
    $client->setApplicationName("GitHubSync");
    $client->setAuthConfigFile($config->getAuthConfigFile());
    $client->setScopes(['https://www.googleapis.com/auth/gmail.modify']);
    $client->setAccessType("offline");
    $client->setRedirectUri('http://inbox.dev/callback.php');

    $access_token = $config->getGoogleToken();
    if (!empty($access_token)) {
      $client->setAccessToken($access_token);
    }

    return $client;
  }

}
