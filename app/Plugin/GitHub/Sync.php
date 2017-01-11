<?php

/**
 * @file
 * Contains \InboxSync\Plugin\GitHub\Sync.
 */

namespace InboxSync\Plugin\GitHub;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Sync extends Command {

  /**
   * @var \InboxSync\Config
   */
  protected $config;

  protected function getClient() {
    $gh = new \Github\Client();
    $gh->authenticate($this->config->get('github.token'), \Github\Client::AUTH_HTTP_TOKEN);
    return $gh;
  }

  protected function configure() {
    $this
      ->setName('github:sync')
      ->setDescription('Trigger the sync between inbox and github.');
  }

  public function execute(InputInterface $input, OutputInterface $output) {
    $this->config = new \InboxSync\Config();
    $gh = $this->getClient();

    $client = \InboxSync\Helper::createGoogleOauthClient($this->config);

    $gmail = new \Google_Service_Gmail($client);
    $label_id = \InboxSync\Helper::findLabel($gmail, 'Notifications/Github');

    if (empty($label_id)) {
      return;
    }

    $github_unread = [];
    $github_notifications_data = [];
    $github_notifications_read = [];
    $github_notifications_unread = [];

    foreach ($gh->currentUser()->notifications()->all() as $notifications) {
      $id = substr($notifications['subject']['url'], strlen('https://api.github.com/repos/'));
      $github_unread[$id] = TRUE;
      $github_notifications_data[$id] = $notifications;
    }

    foreach ($gmail->users_threads->listUsersThreads('me', ['labelIds' => ['UNREAD', $label_id]]) as $thread) {
      $thread = $gmail->users_threads->get('me', $thread['id'], ['format' => 'metadata']);
      $issue_ref = NULL;
      $id = NULL;

      /**
       * @var \Google_Service_Gmail_MessagePartHeader $header
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
        if (!isset($github_unread[$id])) {
          $body = new \Google_Service_Gmail_ModifyThreadRequest();
          $body->setRemoveLabelIds(['UNREAD', 'INBOX']);
          $gmail->users_threads->modify('me', $thread->getId(), $body);
          $github_notifications_read[$id] = TRUE;
        }
        else {
          $github_notifications_unread[$id] = TRUE;
        }
      }

      print '.';
    }

    print PHP_EOL;

    $diff = array_diff_key($github_unread, $github_notifications_unread);
    foreach (array_keys($diff) as $id) {
      $data = $github_notifications_data[$id];
      $gh->currentUser()->notifications()->markAsRead($data['id'], []);
    }
  }

}
