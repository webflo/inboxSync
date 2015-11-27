<?php

$issue = array_values(array_filter(explode('/', $_GET['issue'])));
$nid = $issue[1];

file_put_contents(__DIR__ . '/../config/drupal_nids.txt', $nid . "\n", FILE_APPEND);
