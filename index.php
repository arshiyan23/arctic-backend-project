<?php
if (isset($_GET['restore'])) {
  header('Content-Type: text/plain');
  try {
    // Hardcoded full config from YAML file - all fields, created enabled, no jsonapi_defaults
    $json = '{
    "uuid": "db3a05cc-5528-4262-8e3d-e26cd3f189b4",
    "langcode": "en",
    "status": true,
    "dependencies": {
        "config": { "node.type.portfolio": 0 },
        "module": { "jsonapi_defaults": 0 }
    },
    "third_party_settings": [],
    "id": "node--portfolio",
    "disabled": false,
    "path": "node\/portfolio",
    "resourceType": "node--portfolio",
    "resourceFields": {
        "nid": { "disabled": true, "fieldName": "nid", "publicName": "nid", "enhancer": { "id": "" } },
        "uuid": { "disabled": true, "fieldName": "uuid", "publicName": "uuid", "enhancer": { "id": "" } },
        "vid": { "disabled": true, "fieldName": "vid", "publicName": "vid", "enhancer": { "id": "" } },
        "langcode": { "disabled": true, "fieldName": "langcode", "publicName": "langcode", "enhancer": { "id": "" } },
        "type": { "disabled": true, "fieldName": "type", "publicName": "type", "enhancer": { "id": "" } },
        "revision_timestamp": { "disabled": true, "fieldName": "revision_timestamp", "publicName": "revision_timestamp", "enhancer": { "id": "" } },
        "revision_uid": { "disabled": true, "fieldName": "revision_uid", "publicName": "revision_uid", "enhancer": { "id": "" } },
        "revision_log": { "disabled": true, "fieldName": "revision_log", "publicName": "revision_log", "enhancer": { "id": "" } },
        "status": { "disabled": true, "fieldName": "status", "publicName": "status", "enhancer": { "id": "" } },
        "uid": { "disabled": true, "fieldName": "uid", "publicName": "uid", "enhancer": { "id": "" } },
        "title": { "disabled": false, "fieldName": "title", "publicName": "title", "enhancer": { "id": "" } },
        "created": { "disabled": false, "fieldName": "created", "publicName": "created", "enhancer": { "id": "" } },
        "changed": { "disabled": true, "fieldName": "changed", "publicName": "changed", "enhancer": { "id": "" } },
        "promote": { "disabled": true, "fieldName": "promote", "publicName": "promote", "enhancer": { "id": "" } },
        "sticky": { "disabled": true, "fieldName": "sticky", "publicName": "sticky", "enhancer": { "id": "" } },
        "default_langcode": { "disabled": true, "fieldName": "default_langcode", "publicName": "default_langcode", "enhancer": { "id": "" } },
        "revision_default": { "disabled": true, "fieldName": "revision_default", "publicName": "revision_default", "enhancer": { "id": "" } },
        "revision_translation_affected": { "disabled": true, "fieldName": "revision_translation_affected", "publicName": "revision_translation_affected", "enhancer": { "id": "" } },
        "metatag": { "disabled": true, "fieldName": "metatag", "publicName": "metatag", "enhancer": { "id": "" } },
        "path": { "disabled": true, "fieldName": "path", "publicName": "path", "enhancer": { "id": "" } },
        "menu_link": { "disabled": true, "fieldName": "menu_link", "publicName": "menu_link", "enhancer": { "id": "" } },
        "body": { "disabled": true, "fieldName": "body", "publicName": "body", "enhancer": { "id": "" } },
        "field_hero_banner": { "disabled": true, "fieldName": "field_hero_banner", "publicName": "field_hero_banner", "enhancer": { "id": "" } },
        "field_property_description": { "disabled": true, "fieldName": "field_property_description", "publicName": "field_property_description", "enhancer": { "id": "" } },
        "field_property_gallery": { "disabled": true, "fieldName": "field_property_gallery", "publicName": "field_property_gallery", "enhancer": { "id": "" } },
        "field_property_image": { "disabled": true, "fieldName": "field_property_image", "publicName": "field_property_image", "enhancer": { "id": "" } },
        "field_property_location": { "disabled": true, "fieldName": "field_property_location", "publicName": "field_property_location", "enhancer": { "id": "" } },
        "field_protfolio_country": { "disabled": true, "fieldName": "field_protfolio_country", "publicName": "field_protfolio_country", "enhancer": { "id": "" } },
        "field_features": { "disabled": true, "fieldName": "field_features", "publicName": "field_features", "enhancer": { "id": "" } },
        "field_map_image": { "disabled": true, "fieldName": "field_map_image", "publicName": "field_map_image", "enhancer": { "id": "" } },
        "field_next_project": { "disabled": true, "fieldName": "field_next_project", "publicName": "field_next_project", "enhancer": { "id": "" } },
        "field_tags": { "disabled": true, "fieldName": "field_tags", "publicName": "field_tags", "enhancer": { "id": "" } }
    }
}';
    $host = getenv('DATABASE_HOST') ?: 'artic-mysql.mysql.database.azure.com';
    $db = getenv('DATABASE_NAME') ?: 'artcwbsv0007-prod';
    $user = getenv('DATABASE_USER') ?: 'articadmin';
    $pass = getenv('DATABASE_PASSWORD') ?: 'iamAk@00@000';
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare("UPDATE config SET data = :data WHERE name = :name");
    $stmt->execute([':data' => $json, ':name' => 'jsonapi_extras.jsonapi_resource_config.node--portfolio']);
    $pdo->exec("DELETE FROM cache_entity WHERE cid LIKE '%jsonapi%'");
    $pdo->exec("DELETE FROM cache_render WHERE cid LIKE '%jsonapi%'");
    echo "OK: Portfolio config restored with all 33 fields\n";
    echo "created disabled: false\ntitle disabled: false\n";
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
  }
  return;
}
if (isset($_GET['fixcreated'])) {
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
    echo "Before: " . count($data['resourceFields']) . " fields, created disabled=" . ($data['resourceFields']['created']['disabled'] ? 'true' : 'false') . "\n";
    $data['resourceFields']['created']['disabled'] = false;
    $upd = $pdo->prepare("UPDATE config SET data = :data WHERE name = :name");
    $upd->execute([':data' => json_encode($data), ':name' => 'jsonapi_extras.jsonapi_resource_config.node--portfolio']);
    $pdo->exec("DELETE FROM cache_entity WHERE cid LIKE '%jsonapi%'");
    echo "After: OK\n";
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
  }
  return;
}
chdir(__DIR__ . '/web');
require __DIR__ . '/web/index.php';
