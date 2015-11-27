<?php

/**
 * @file
 * Contains \InboxSync\Command\Configure.
 */

namespace InboxSync\Command;

use InboxSync\Helper\BrowserTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Configure extends Command {

  use BrowserTrait;

  protected function configure() {
    $this
      ->setName('configure')
      ->setDescription('Configure the app, do the OAuth Dance with Google.');
  }

  public function execute(InputInterface $input, OutputInterface $output) {
    $config = new \InboxSync\Config();
    $client = \InboxSync\Helper::createGoogleOauthClient($config, FALSE);

    $this->openBrowser($client->createAuthUrl());

    $qh = new QuestionHelper();
    $code = $qh->ask($input, $output, new Question("Enter Google OAuth code:"));

    $token = $client->fetchAccessTokenWithAuthCode($code);
    $config->setGoogleAccessToken($token);
  }

}
