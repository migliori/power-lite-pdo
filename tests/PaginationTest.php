<?php

use PHPUnit\Framework\TestCase;
use Migliori\PowerLitePdo\Pagination;
use Migliori\PowerLitePdo\PaginationOptions;
use Migliori\PowerLitePdo\View\View;
use Migliori\PowerLitePdo\Db;
use DI\ContainerBuilder;

class PaginationTest extends TestCase
{
    protected Pagination $pagination;

    protected function setUp(): void
    {
        // use src\bootstrap.php to create a container and load definitions
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../src/config.php');
        $container = $containerBuilder->build();

        $this->pagination = $container->get(Pagination::class, ['recordsPerPage' => 20]);
    }

    public function testSelect()
    {
        // Set the necessary properties
        $this->pagination->setRecordsPerPage(5);
        $this->pagination->setCurrentPage(1);

        $debugMode = 'on';

        // Call the select method
        $this->pagination->select(
            'customers',
            ['first_name', 'last_name'],
            ['id >' => 5],
            [],
            $debugMode
        );

        // Assertions
        $this->assertIsInt($this->pagination->getTotalRecordsCount());
        $this->assertIsInt($this->pagination->getNumberOfPages());
        $this->assertIsInt($this->pagination->getCurrentPage());
        $this->assertIsInt($this->pagination->getRecordsPerPage());
        $this->assertIsInt($this->pagination->getCurrentNumberOfRecords());
    }

    public function testPagine()
    {
        // Set the necessary properties
        $this->pagination->setRecordsPerPage(5);
        $this->pagination->setCurrentPage(1);

        $debugMode = 'on';

        // Call the select method
        $this->pagination->select(
            'customers',
            ['first_name', 'last_name'],
            ['id >' => 5],
            [],
            $debugMode
        );

        // Call the paginate method
        $paginationHtml = $this->pagination->pagine('/products');

        // Assertions
        $this->assertIsString($paginationHtml);
    }
}
