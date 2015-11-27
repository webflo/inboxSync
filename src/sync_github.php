<?php

require __DIR__ . '/../vendor/autoload.php';

$config = new \InboxSync\Config();

$gh_plugin = new InboxSync\Plugin\Github();
$gh = $gh_plugin->getClient();

$client = \InboxSync\Helper::createGoogleOauthClient($config);
if ($client->isAccessTokenExpired()) {
  $result = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
  $config->storeGoogleToken($result);
}

$gmail = new Google_Service_Gmail($client);
$label_id = \InboxSync\Helper::findLabel($gmail, 'Notifications/Github');

if (empty($label_id)) {
  return;
}


$drupal_org_unread = [];
$github_notifications_data = [];
$github_notifications_read = [];
$github_notifications_unread = [];

foreach ($gh->currentUser()->notifications()->all() as $notifications) {
  $id = substr($notifications['subject']['url'], strlen('https://api.github.com/repos/'));
  $drupal_org_unread[$id] = TRUE;
  $github_notifications_data[$id] = $notifications;
}

foreach ($gmail->users_threads->listUsersThreads('me', ['labelIds' => ['UNREAD', $label_id]]) as $thread) {
  $thread = $gmail->users_threads->get('me', $thread['id'], ['format' => 'metadata']);
  $issue_ref = NULL;
  $id = NULL;

  /**
   * @var Google_Service_Gmail_MessagePartHeader $header
   */
  foreach ($thread->getMessages() as $message) {
    foreach ($message->getPayload()->getHeaders() as $header) {
      if ($header->getName() == 'References' || $header->getName() == 'Message-ID') {
        $issue_ref = $header->getValue();
        break 2;
      }
    }
  }

  $match = [];
  preg_match('/^<(.*)@github\.com/', $issue_ref, $match);
  if (!empty($match)) {
    $id = explode('/', $match[1]);
    $id = implode('/', array_slice($id, 0, 4));
  }

  if (isset($id)) {
    // No unread GitHub notification, mark mail as read.
    if (!isset($drupal_org_unread[$id])) {
      $body = new Google_Service_Gmail_ModifyThreadRequest();
      $body->setRemoveLabelIds(['UNREAD', 'INBOX']);
      $gmail->users_threads->modify('me', $thread->getId(), $body);
      $github_notifications_read[$id] = TRUE;
    } else {
      $github_notifications_unread[$id] = TRUE;
    }
  }

  print '.' . PHP_EOL;
}

$diff = array_diff_key($drupal_org_unread, $github_notifications_unread);
foreach (array_keys($diff) as $id) {
  $data = $github_notifications_data[$id];
  $gh->currentUser()->notifications()->markAsRead($data['id'], []);
}
