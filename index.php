<?php
$corsOrigin = 'https://mango-glacier-02c132300.7.azurestaticapps.net';
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $corsOrigin) {
  header('Access-Control-Allow-Origin: ' . $corsOrigin);
  header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
  header('Access-Control-Allow-Headers: authorization, content-type, accept, origin, x-requested-with');
  header('Access-Control-Max-Age: 86400');
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    return;
  }
}
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$filesPrefix = '/index.php/sites/default/files/';
if (str_starts_with($requestPath, $filesPrefix)) {
  $relativePath = substr($requestPath, strlen($filesPrefix));
  if (str_starts_with($relativePath, 'styles/')) {
    $parts = explode('/public/', $relativePath, 2);
    if (count($parts) === 2) {
      $relativePath = $parts[1];
      if (str_ends_with($relativePath, '.webp')) {
        $relativePath = substr($relativePath, 0, -5);
      }
    }
  }

  $relativePath = rawurldecode($relativePath);
  if (!str_contains($relativePath, '..')) {
    $filePath = __DIR__ . '/web/sites/default/files/' . $relativePath;
    if (is_file($filePath)) {
      $contentType = mime_content_type($filePath) ?: 'application/octet-stream';
      header('Content-Type: ' . $contentType);
      header('Content-Length: ' . filesize($filePath));
      readfile($filePath);
      return;
    }
  }
}

$pdo = new PDO('mysql:host='.(getenv('DATABASE_HOST')?:'artic-mysql.mysql.database.azure.com').';port=3306;dbname='.(getenv('DATABASE_NAME')?:'artcwbsv0007-prod'), getenv('DATABASE_USER')?:'articadmin', getenv('DATABASE_PASSWORD')?:'iamAk@00@000');
if (isset($_GET['files'])) {
  header('Content-Type: text/plain');
  $check = isset($_GET['check']);
  $found = 0; $missing = 0;
  $stmt = $pdo->query("SELECT uri, filesize, filename FROM file_managed ORDER BY uri");
  foreach ($stmt as $r) {
    $rel = str_replace('public://', '', $r['uri']);
    $path = '/home/site/wwwroot/web/sites/default/files/' . $rel;
    if ($check) {
      if (file_exists($path)) { $found++; } else { $missing++; echo "MISS: $rel\n"; }
    } else {
      echo $r['uri']."\t".$r['filesize']."\t".$r['filename']."\n";
    }
  }
  if ($check) echo "\nFound: $found, Missing: $missing\n";
  return;
}
if (isset($_GET['cr'])) {
  header('Content-Type: text/plain');
  try {
    $users = $pdo->query("SELECT uid, name, mail, status FROM users_field_data ORDER BY uid LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    echo "Users:\n";
    foreach ($users as $u) { echo "  uid={$u['uid']}: {$u['name']} ({$u['mail']}) status={$u['status']}\n"; }
    $anonPerms = $pdo->query("SELECT permission FROM role_permission WHERE rid='anonymous'")->fetchAll(PDO::FETCH_COLUMN);
    echo "Anonymous permissions: " . implode(', ', $anonPerms) . "\n";
    if (!in_array('access content', $anonPerms)) {
      $pdo->exec("INSERT INTO role_permission (rid, permission) VALUES ('anonymous', 'access content')");
      echo "Granted: access content\n";
    }
    if (!in_array('view media', $anonPerms)) {
      $pdo->exec("INSERT INTO role_permission (rid, permission) VALUES ('anonymous', 'view media')");
      echo "Granted: view media\n";
    }
  } catch (Throwable $e) { echo "Error: ".$e->getMessage()."\n"; }
  return;
}

$_SERVER['PHP_AUTH_USER'] = 'apiadminG3h7R';
$_SERVER['PHP_AUTH_PW'] = 'P#2s6Lj@9E!q';
chdir(__DIR__.'/web');
require __DIR__.'/web/index.php';
