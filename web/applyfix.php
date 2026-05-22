<?php
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$autoloader = require_once 'autoload.php';
$kernel = new DrupalKernel('prod', $autoloader);
$kernel->boot();

$config_factory = \Drupal::configFactory();
$config = $config_factory->getEditable('jsonapi_extras.jsonapi_resource_config.node--portfolio');
$resourceFields = $config->get('resourceFields');
$resourceFields['created']['disabled'] = false;
$config->set('resourceFields', $resourceFields);
$config->save();

echo "OK: portfolio created field enabled.\n";
