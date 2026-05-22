<?php
$host = getenv('DATABASE_HOST') ?: 'artic-mysql.mysql.database.azure.com';
$db = getenv('DATABASE_NAME') ?: 'artcwbsv0007-prod';
$user = getenv('DATABASE_USER') ?: 'articadmin';
$pass = getenv('DATABASE_PASSWORD') ?: 'iamAk@00@000';

try {
  $pdo = new PDO("mysql:host=$host;port=3306;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
  $stmt = $pdo->query("SELECT data FROM config WHERE name = 'jsonapi_extras.jsonapi_resource_config.node--portfolio'");
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $data = json_decode($row['data'], true);
  echo "Before: created disabled = " . ($data['resourceFields']['created']['disabled'] ? 'true' : 'false') . "\n";
  $data['resourceFields']['created']['disabled'] = false;
  $update = $pdo->prepare("UPDATE config SET data = :data WHERE name = :name");
  $update->execute([':data' => json_encode($data), ':name' => 'jsonapi_extras.jsonapi_resource_config.node--portfolio']);
  echo "After: updated OK\n";
  $pdo->query("DELETE FROM cache_entity WHERE cid LIKE '%jsonapi%'");
  $pdo->query("DELETE FROM cache_render WHERE cid LIKE '%jsonapi%'");
  echo "Cache cleared\n";
} catch (Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
}
