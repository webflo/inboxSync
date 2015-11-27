<?php

/**
 * @file
 * Contains \InboxSync\Config.
 */

namespace InboxSync;

use Symfony\Component\Yaml\Yaml;

class Config {

  protected $data;

  protected $filepath;

  public function __construct() {
    $this->filepath = __DIR__ . '/../config/config.yml';
    $this->data = Yaml::parse($this->filepath);
  }

  public function get($key) {
    return $this->data[$key];
  }

  public function set($key, $value) {
    $this->data[$key] = $value;
    $this->save();
  }

  public function remove($key) {
    unset($this->data[$key]);
    $this->save();
  }

  public function save() {
    file_put_contents($this->filepath, Yaml::dump($this->data, 2, 2));
  }

  public function getGoogleAccessToken() {
    return json_decode(file_get_contents(__DIR__ . '/../config/google_access_token.json'), TRUE);
  }

  public function setGoogleAccessToken($token) {
    file_put_contents(__DIR__ . '/../config/google_access_token.json', json_encode($token));
  }

  public function getAuthConfigFile() {
    return realpath(__DIR__ . '/../config/' . $this->get('google.auth_config_file'));
  }

}
