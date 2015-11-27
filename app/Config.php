<?php

/**
 * @file
 * Contains \InboxSync\Config.
 */

namespace InboxSync;

use Symfony\Component\Yaml\Yaml;

class Config {

  protected $data;

  public function __construct() {
    $this->data = Yaml::parse(__DIR__ . '/../config/config.yml');
  }

  public function get($key) {
    return $this->data[$key];
  }

  public function getGoogleToken() {
    return json_decode(file_get_contents(__DIR__ . '/../config/code.json'), TRUE);
  }

  public function storeGoogleToken($token) {
    $data = json_decode(file_get_contents(__DIR__ . '/../config/code.json'), TRUE);
    if (empty($data)) {
      $data = [];
    }
    $data = array_merge($data, $token);
    file_put_contents(__DIR__ . '/../config/code.json', json_encode($data));
  }

  public function getAuthConfigFile() {
    return realpath(__DIR__ . '/../config/' . $this->get('google')['auth_config_file']);
  }

}
