<?php
if (isset($_GET['drush'])) {
  header('Content-Type: text/plain');
  $cmd = isset($_GET['cmd']) ? $_GET['cmd'] : 'cr';
  $drush = __DIR__ . '/vendor/bin/drush';
  $output = shell_exec("php $drush $cmd 2>&1");
  echo "Drush $cmd:\n$output";
  return;
}
chdir(__DIR__ . '/web');
require __DIR__ . '/web/index.php';
