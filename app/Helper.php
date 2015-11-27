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

  public static function createGoogleOauthClient(Config $config, $initialize_access_token = TRUE) {
    $client = new Google_Client();
    $client->setApplicationName("GitHubSync");
    $client->setAuthConfigFile($config->getAuthConfigFile());
    $client->setScopes(['https://www.googleapis.com/auth/gmail.modify']);
    // $client->setRedirectUri('http://inbox.dev/callback.php');
    $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
    $client->setAccessType('offline');

    if ($initialize_access_token) {
      $access_token = $config->getGoogleAccessToken();
      if (!empty($access_token)) {
        $client->setAccessToken($access_token);
      }
    }

    return $client;
  }

}
