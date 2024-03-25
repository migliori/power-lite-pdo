<?php

use DI\Container;
use DI\ContainerBuilder;
use Migliori\PowerLitePdo\Db;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testContainerBuilderReturnsInstanceOfContainer()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../src/config.php');
        $container = $containerBuilder->build();

        $this->assertInstanceOf(Container::class, $container);
    }

    public function testDbClassCanBeInstantiated()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../src/config.php');
        $container = $containerBuilder->build();

        $db = $container->get(Db::class);

        $this->assertInstanceOf(Db::class, $db);
    }

    public function testBootstrapFileReturnsContainerInstance()
    {
        $container = require_once __DIR__ . '/../src/bootstrap.php';

        $this->assertInstanceOf(Container::class, $container);
    }
}
