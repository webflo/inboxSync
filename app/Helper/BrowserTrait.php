<?php

/**
 * @file
 * Contains \InboxSync\Helper\BrowserTrait.
 */

namespace InboxSync\Helper;

use Symfony\Component\Process\ProcessBuilder;

trait BrowserTrait {

  /**
   * opens a url in your system default browser
   *
   * @param string $url
   */
  protected function openBrowser($url) {
    $process = new ProcessBuilder(['open', $url]);
    $process->getProcess()->run();

    /*
      $url = ProcessExecutor::escape($url);
      if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
          return passthru('start "web" explorer "' . $url . '"');
      }
      passthru('which xdg-open', $linux);
      passthru('which open', $osx);
      if (0 === $linux) {
          passthru('xdg-open ' . $url);
      } elseif (0 === $osx) {
          passthru('open ' . $url);
      } else {
          $this->getIO()->writeError('no suitable browser opening command found, open yourself: ' . $url);
      }
    */
  }

}
