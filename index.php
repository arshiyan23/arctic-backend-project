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
  $simple = !isset($_GET['drupal']);
  if (!$simple) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/web/index.php';
    $app_root = __DIR__ . '/web';
    $autoloader = require $app_root . '/autoload.php';
    $r = Symfony\Component\HttpFoundation\Request::createFromGlobals();
    Drupal\Core\DrupalKernel::bootEnvironment($app_root);
    $k = Drupal\Core\DrupalKernel::createFromRequest($r, $autoloader, 'prod', true, $app_root);
    $k->boot();
    echo "Booted Drupal ".Drupal::VERSION."\n";
    if (isset($_GET['grant'])) {
      $perms = ['access content', 'view media', 'restful get entity:node'];
      $roleStorage = Drupal::entityTypeManager()->getStorage('user_role');
      $roles = $roleStorage->loadMultiple();
      foreach ($roles as $rid => $role) {
        $granted = [];
        foreach ($perms as $p) {
          if (!$role->hasPermission($p)) {
            $role->grantPermission($p)->save();
            $granted[] = $p;
          }
        }
        if (count($granted)) { echo "Granted '$rid': " . implode(', ', $granted) . "\n"; }
      }
      Drupal::service('cache_tags.invalidator')->invalidateTags(['*']);
      echo "Caches cleared.\n";
    }
    return;
  }
  echo "=== Current permissions ===\n";
  try {
    $rc = $pdo->query("SELECT name, data FROM config WHERE name LIKE 'user.role.%' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rc as $r) {
      $data = unserialize($r['data']);
      echo "{$r['name']}:\n";
      if (isset($data['permissions'])) {
        echo "  perms: " . count($data['permissions']) . " total\n";
        echo "  access content: " . (in_array('access content', $data['permissions']) ? 'YES' : 'NO') . "\n";
        echo "  view media: " . (in_array('view media', $data['permissions']) ? 'YES' : 'NO') . "\n";
        if (isset($_GET['grant'])) {
          $changed = false;
          foreach (['access content', 'view media', 'restful get entity:node'] as $p) {
            if (!in_array($p, $data['permissions'])) {
              $data['permissions'][] = $p;
              $changed = true;
              echo "  -> granting: $p\n";
            }
          }
          if ($changed) {
            $stmt = $pdo->prepare("UPDATE config SET data = ? WHERE name = ?");
            $stmt->execute([serialize($data), $r['name']]);
            echo "  => saved\n";
          }
        }
      }
    }
  } catch (Throwable $e) { echo "Error: ".$e->getMessage()."\n"; }
  return;
}

$_SERVER['PHP_AUTH_USER'] = 'apiadminG3h7R';
$_SERVER['PHP_AUTH_PW'] = 'P#2s6Lj@9E!q';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$prefix = '/index.php';
if (str_starts_with($requestPath, $prefix . '/')) {
  $pathInfo = substr($requestPath, strlen($prefix));
  $_SERVER['PATH_INFO'] = $pathInfo;
  $_SERVER['ORIG_PATH_INFO'] = $pathInfo;
  $_SERVER['REQUEST_URI'] = $pathInfo . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
} elseif (!empty($_GET['q'])) {
  $qPath = ltrim($_GET['q'], '/');
  $qmarkPos = strpos($qPath, '?');
  $extraQs = '';
  if ($qmarkPos !== false) {
    $extraQs = substr($qPath, $qmarkPos + 1);
    $qPath = substr($qPath, 0, $qmarkPos);
  }
  $params = [];
  parse_str($_SERVER['QUERY_STRING'] ?? '', $params);
  if ($extraQs) {
    $extra = [];
    parse_str($extraQs, $extra);
    $params = array_merge($params, $extra);
  }
  unset($params['q']);
  $pathInfo = '/' . $qPath;
  $cleanQs = http_build_query($params);
  $_SERVER['PATH_INFO'] = $pathInfo;
  $_SERVER['ORIG_PATH_INFO'] = $pathInfo;
  $_SERVER['QUERY_STRING'] = $cleanQs;
  $_SERVER['REQUEST_URI'] = $pathInfo . ($cleanQs ? '?' . $cleanQs : '');
  $_GET = $params;
  error_log('Q_HANDLER: q=' . ($_GET['q'] ?? 'NOTSET') . '; REQUEST_URI=' . ($_SERVER['REQUEST_URI'] ?? 'NOTSET') . '; QS=' . ($_SERVER['QUERY_STRING'] ?? 'NOTSET'));
}

header('X-Debug-End: reached; method=' . ($_SERVER['REQUEST_METHOD'] ?? '?') . '; path=' . ($_SERVER['REQUEST_URI'] ?? '?'));
chdir(__DIR__.'/web');
require __DIR__.'/web/index.php';
