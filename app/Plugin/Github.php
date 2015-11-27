<?php

/**
 * @file
 * Contains \InboxSync\Plugin\Github.
 */

namespace InboxSync\Plugin;

use InboxSync\Config;

class Github {

  public function getToken() {
    $config = new Config();
    return $config->get('github')['token'];
  }

  public function getClient() {
    $gh = new \Github\Client();
    $gh->authenticate($this->getToken(), \Github\Client::AUTH_HTTP_TOKEN);
    return $gh;
  }

}
