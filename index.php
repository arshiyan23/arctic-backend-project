<?php
if (isset($_GET['clearcache'])) {
  header('Content-Type: text/plain');
  try {
    $host = getenv('DATABASE_HOST') ?: 'artic-mysql.mysql.database.azure.com';
    $db = getenv('DATABASE_NAME') ?: 'artcwbsv0007-prod';
    $user = getenv('DATABASE_USER') ?: 'articadmin';
    $pass = getenv('DATABASE_PASSWORD') ?: 'iamAk@00@000';
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $tables = $pdo->query("SHOW TABLES LIKE 'cache%'");
    foreach ($tables as $t) {
      $name = array_values($t)[0];
      $pdo->exec("TRUNCATE TABLE `$name`");
      echo "Cleared: $name\n";
    }
    echo "All caches cleared.\n";
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
  }
  return;
}
if (isset($_GET['restore'])) {
  header('Content-Type: text/plain');
  try {
    $host = getenv('DATABASE_HOST') ?: 'artic-mysql.mysql.database.azure.com';
    $db = getenv('DATABASE_NAME') ?: 'artcwbsv0007-prod';
    $user = getenv('DATABASE_USER') ?: 'articadmin';
    $pass = getenv('DATABASE_PASSWORD') ?: 'iamAk@00@000';
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query("SELECT data FROM config WHERE name = 'jsonapi_extras.jsonapi_resource_config.node--portfolio'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data = json_decode($row['data'], true);
    echo "Before: " . count($data['resourceFields']) . " fields\n";
    echo "Has title: " . (isset($data['resourceFields']['title']) ? 'yes' : 'no') . "\n";
    $default_field = function($name, $disabled = true) {
      return ['disabled' => $disabled, 'fieldName' => $name, 'publicName' => $name, 'enhancer' => ['id' => '']];
    };
    $data['resourceFields']['title'] = $default_field('title', false);
    $data['resourceFields']['created'] = $default_field('created', false);
    $data['resourceFields']['changed'] = $default_field('changed', true);
    $data['resourceFields']['uid'] = $default_field('uid', true);
    $data['resourceFields']['status'] = $default_field('status', true);
    $data['resourceFields']['nid'] = $default_field('nid', true);
    $json = json_encode($data);
    $upd = $pdo->prepare("UPDATE config SET data = :data WHERE name = :name");
    $upd->execute([':data' => $json, ':name' => 'jsonapi_extras.jsonapi_resource_config.node--portfolio']);
    $pdo->exec("DELETE FROM cache_entity WHERE cid LIKE '%jsonapi%'");
    $pdo->exec("DELETE FROM cache_render WHERE cid LIKE '%jsonapi%'");
    echo "After: " . count($data['resourceFields']) . " fields, title enabled, created enabled\n";
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
  }
  return;
}
chdir(__DIR__ . '/web');
require __DIR__ . '/web/index.php';
