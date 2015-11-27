<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

require __DIR__ . '/../vendor/autoload.php';

$config = new \InboxSync\Config();

$client = \InboxSync\Helper::createGoogleOauthClient($config);
if ($client->isAccessTokenExpired()) {
  $client->getRefreshToken();
}

$gmail = new Google_Service_Gmail($client);
$label_id = \InboxSync\Helper::findLabel($gmail, 'Notifications/Drupal');

$input = new ArrayInput(array(
 'command' => 'login',
));

$patchUtils = new Application();
$patchUtils->setAutoExit(FALSE);
$patchUtils->add(new \DrupalPatchUtils\Command\Login());

$output = new \Symfony\Component\Console\Output\ConsoleOutput();
$patchUtils->run($input, $output);

$browser = new \DrupalPatchUtils\DoBrowser();
$iq = new \DrupalPatchUtils\User(254778, $browser);

$drupal_org_unread = $iq->getUnreadIssues();

// $file = __DIR__ . '/../config/drupal_nids.txt';
// $drupal_org_browser = array_values(array_unique(array_map('trim', file($file))));
// $drupal_org_browser = array_diff($drupal_org_browser, $drupal_org_unread);

$both_unread = [];

foreach ($gmail->users_threads->listUsersThreads('me', ['labelIds' => ['UNREAD', $label_id]]) as $thread) {
  $thread = $gmail->users_threads->get('me', $thread['id'], ['format' => 'metadata']);
  $issue_ref = NULL;
  $id = NULL;

  /**
   * @var Google_Service_Gmail_MessagePartHeader $header
   */
  foreach ($thread->getMessages() as $message) {
    foreach ($message->getPayload()->getHeaders() as $header) {
      if ($header->getName() == 'Message-Id') {
        $issue_ref = $header->getValue();
        break 2;
      }
    }
  }

  preg_match('/nid=([0-9]*)/', $issue_ref, $matches);
  if (isset($matches[1])) {
    $id = $matches[1];

    if (isset($drupal_org_unread[$id])) {
      $both_unread[] = $id;
    }
    else {
      // No unread Drupal.org issue, mark mail as read.
      $body = new Google_Service_Gmail_ModifyThreadRequest();
      $body->setRemoveLabelIds(['UNREAD', 'INBOX']);
      $gmail->users_threads->modify('me', $thread->getId(), $body);
    }
  }
}

// $drupal_org_unread contains all unread issues on drupal.org, the Gmail client
// found no unread mails in gmail, Mark all issues without an related mail as
// read.
$mark_as_read = array_diff($drupal_org_unread, $both_unread);
foreach ($mark_as_read as $nid) {
  $iq->getIssue($nid);
}

