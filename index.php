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
  } catch (Throwable $e) { echo "Error: ".$e->getMessage()."\n"; }
  return;
}

if (isset($_GET['fixperms'])) {
  header('Content-Type: text/plain');
  $rid = 'anonymous';
  $needed = ['access content', 'view media'];
  $existing = $pdo->prepare("SELECT permission FROM role_permission WHERE rid = ?");
  $existing->execute([$rid]);
  $have = $existing->fetchAll(PDO::FETCH_COLUMN);
  $add = array_diff($needed, $have);
  $ins = $pdo->prepare("INSERT IGNORE INTO role_permission (rid, permission) VALUES (?, ?)");
  foreach ($add as $p) { $ins->execute([$rid, $p]); }
  echo "Granted: " . implode(', ', $add) . "\n";
  return;
}
if (isset($_GET['fixperms'])) {
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
    $k->preHandle($r);
    $role = \Drupal\user\Entity\Role::load('anonymous');
    $role->grantPermission('access content');
    $role->grantPermission('view media');
    $role->save();
    echo "Permissions granted";
  } catch (Throwable $e) { echo "Error: ".$e->getMessage()."\n"; }
  return;
}
chdir(__DIR__.'/web');
require __DIR__.'/web/index.php';
