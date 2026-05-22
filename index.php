<?php
$pdo = new PDO('mysql:host='.(getenv('DATABASE_HOST')?:'artic-mysql.mysql.database.azure.com').';port=3306;dbname='.(getenv('DATABASE_NAME')?:'artcwbsv0007-prod'), getenv('DATABASE_USER')?:'articadmin', getenv('DATABASE_PASSWORD')?:'iamAk@00@000');
if (isset($_GET['r'])) {
  header('Content-Type: text/plain');
  try {
    // Remove all config entries with fieldable_path references
    $names = $pdo->query("SELECT name, data FROM config WHERE data LIKE '%fieldable_path%' OR name LIKE '%field_path%'");
    $deleted = [];
    foreach ($names as $row) {
      if ($row['name'] == 'core.extension') {
        // Remove fieldable_path from module list
        $data = unserialize($row['data']);
        unset($data['module']['fieldable_path']);
        $new = serialize($data);
        $pdo->prepare("UPDATE config SET data = :data WHERE name = :name")->execute([':data' => $new, ':name' => 'core.extension']);
        $deleted[] = $row['name'].' (removed module)';
      } else {
        $pdo->prepare("DELETE FROM config WHERE name = :name")->execute([':name' => $row['name']]);
        $deleted[] = $row['name'];
      }
    }
    echo implode("\n", $deleted)."\n";
    echo count($deleted)." entries cleaned\n";
  } catch (Exception $e) { echo "Error: ".$e->getMessage()."\n"; }
  return;
}
if (isset($_GET['cr'])) {
  header('Content-Type: text/plain');
  try {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/web/index.php';
    $app_root = __DIR__ . '/web';
    $autoloader = require $app_root . '/autoload.php';
    $r = Symfony\Component\HttpFoundation\Request::createFromGlobals();
    Drupal\Core\DrupalKernel::bootEnvironment($app_root);
    $k = Drupal\Core\DrupalKernel::createFromRequest($r, $autoloader, 'prod', true, $app_root);
    $k->boot();
    echo "Booted ".Drupal::VERSION."\n";
    Drupal::service('cache_tags.invalidator')->invalidateTags(['*']);
    echo "Rebuilt\n";
  } catch (Exception $e) { echo "Error: ".$e->getMessage()."\n"; }
  return;
}
if (isset($_GET['q'])) {
  header('Content-Type: text/plain');
  try {
    $name = $_GET['q'];
    // Search in config table
    $stmt = $pdo->query("SELECT name, collection, LEFT(data,200) as data FROM config WHERE name LIKE '%$name%' OR data LIKE '%$name%'");
    foreach ($stmt as $row) { echo $row['name']." [".$row['collection']."] ".$row['data']."\n\n"; }
  } catch (Exception $e) { echo "Error: ".$e->getMessage()."\n"; }
  return;
}
chdir(__DIR__.'/web');
require __DIR__.'/web/index.php';
