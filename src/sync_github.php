<?php

require __DIR__ . '/../vendor/autoload.php';

$google_token = json_decode(file_get_contents('../code.json'), TRUE);
$github_token = json_decode(file_get_contents('../github.json'), TRUE);

$client = new Google_Client();
$client->setApplicationName("GitHubSync");
$client->setAuthConfigFile('../client_secret_863766085506-2rqrslekiif0qhek36vhgl31thqa4rh7.apps.googleusercontent.com.json');
$client->setScopes(['https://www.googleapis.com/auth/gmail.modify']);
$client->setAccessType("offline");
$client->setAccessToken($google_token['access_token']);

if ($client->isAccessTokenExpired()) {
  $client->getRefreshToken();
}

$gmail = new Google_Service_Gmail($client);
$label_id = \InboxSync\Helper::findLabel($gmail, 'Notifications/Github');

if (empty($label)) {
  return;
}

$gh = new \Github\Client();
$gh->authenticate($github_token['token'], \Github\Client::AUTH_HTTP_TOKEN);
$github_notifications = [];
$github_notifications_data = [];
$github_notifications_read = [];
$github_notifications_unread = [];

foreach ($gh->currentUser()->notifications()->all() as $notifications) {
  $id = substr($notifications['subject']['url'], strlen('https://api.github.com/repos/'));
  $github_notifications[$id] = TRUE;
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
    if (!isset($github_notifications[$id])) {
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

$diff = array_diff_key($github_notifications, $github_notifications_unread);
foreach (array_keys($diff) as $id) {
  $data = $github_notifications_data[$id];
  $gh->currentUser()->notifications()->markAsRead($data['id'], []);
}
