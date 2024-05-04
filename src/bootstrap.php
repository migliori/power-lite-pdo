<?php
use DI\ContainerBuilder;

// Load the Composer autoloader
require_once __DIR__ . '/../../../autoload.php';

$containerBuilder = new ContainerBuilder();

// Load definitions from a file
$containerBuilder->addDefinitions(__DIR__ . '/config.php');

return $containerBuilder->build();
