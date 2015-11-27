<?php

/**
 * @file
 * Contains \InboxSync\Plugin\Drupal\Sync.
 */

namespace InboxSync\Plugin\Drupal;

use DrupalPatchUtils\Command\Login;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Sync extends Command {

  protected function configure() {
    $this
      ->setName('drupal:sync')
      ->setDescription('Trigger the sync between inbox and Drupal.org.');
  }

  public function execute(InputInterface $input, OutputInterface $output) {

    $config = new \InboxSync\Config();

    $client = \InboxSync\Helper::createGoogleOauthClient($config);
    if ($client->isAccessTokenExpired()) {
      $client->getRefreshToken();
    }

    $gmail = new \Google_Service_Gmail($client);
    $label_id = \InboxSync\Helper::findLabel($gmail, 'Notifications/Drupal');

    /**
     * @todo: Fix upstream.
     */
    $input = new ArrayInput(array(
     'command' => 'login',
    ));
    $command = $this->getApplication()->add(new Login());
    $command->run($input, $output);

    $browser = new \DrupalPatchUtils\DoBrowser();
    $iq = new \DrupalPatchUtils\User(254778, $browser);

    $drupal_org_unread = $iq->getUnreadIssues();
    $both_unread = [];

    foreach ($gmail->users_threads->listUsersThreads('me', ['labelIds' => ['UNREAD', $label_id]]) as $thread) {
      $thread = $gmail->users_threads->get('me', $thread['id'], ['format' => 'metadata']);
      $issue_ref = NULL;
      $id = NULL;

      /**
       * @var \Google_Service_Gmail_MessagePartHeader $header
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
          $body = new \Google_Service_Gmail_ModifyThreadRequest();
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
  }

}
