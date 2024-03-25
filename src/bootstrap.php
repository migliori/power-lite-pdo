<?php
use DI\ContainerBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

// Load definitions from a file
$containerBuilder->addDefinitions(__DIR__ . '/config.php');

return $containerBuilder->build();
