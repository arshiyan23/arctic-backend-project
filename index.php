<?php
if (isset($_GET['restore'])) {
  header('Content-Type: text/plain');
  try {
    require_once __DIR__ . '/web/core/lib/Drupal/Component/Serialization/Yaml.php';
    $yaml_path = __DIR__ . '/config/sync/jsonapi_extras.jsonapi_resource_config.node--portfolio.yml';
    $data = \Drupal\Component\Serialization\Yaml::decode(file_get_contents($yaml_path));
    if (isset($data['third_party_settings']['jsonapi_defaults'])) {
      unset($data['third_party_settings']['jsonapi_defaults']);
    }
    $data['resourceFields']['created']['disabled'] = false;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $host = getenv('DATABASE_HOST') ?: 'artic-mysql.mysql.database.azure.com';
    $db = getenv('DATABASE_NAME') ?: 'artcwbsv0007-prod';
    $user = getenv('DATABASE_USER') ?: 'articadmin';
    $pass = getenv('DATABASE_PASSWORD') ?: 'iamAk@00@000';
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare("UPDATE config SET data = :data WHERE name = :name");
    $stmt->execute([':data' => $json, ':name' => 'jsonapi_extras.jsonapi_resource_config.node--portfolio']);
    $pdo->exec("DELETE FROM cache_entity WHERE cid LIKE '%jsonapi%'");
    echo "OK: Portfolio config restored (" . count($data['resourceFields']) . " fields, created disabled: " . ($data['resourceFields']['created']['disabled'] ? 'true' : 'false') . ")\n";
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
  }
  return;
}
chdir(__DIR__ . '/web');
require __DIR__ . '/web/index.php';
