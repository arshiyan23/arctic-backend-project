<?php
if (isset($_GET['boot'])) {
  header('Content-Type: text/plain');
  $autoloader = require __DIR__ . '/web/autoload.php';
  $kernel = new Drupal\Core\DrupalKernel('prod', $autoloader);
  try {
    $kernel->setSitePath('sites/default');
    chdir(__DIR__ . '/web');
    $kernel->boot();
    echo "Drupal booted successfully!\n";
    echo "Container rebuilt.\n";
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
  }
  return;
}
chdir(__DIR__ . '/web');
require __DIR__ . '/web/index.php';
