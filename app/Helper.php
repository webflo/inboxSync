<?php

/**
 * @file
 * Contains \InboxSync\Helper.
 */

namespace InboxSync;

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

}
