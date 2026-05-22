<?php
if (isset($_GET['fixcreated'])) {
  require __DIR__ . '/web/applyfix.php';
  return;
}
chdir(__DIR__ . '/web');
require __DIR__ . '/web/index.php';
