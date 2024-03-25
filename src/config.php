<?php

use DI\Container;
use Migliori\PowerLitePdo\DriverManager;
use Migliori\PowerLitePdo\Db;
use Migliori\PowerLitePdo\Pagination;
use Migliori\PowerLitePdo\PaginationOptions;
use Migliori\PowerLitePdo\Query\Parameters;
use Migliori\PowerLitePdo\Query\QueryBuilder;
use Migliori\PowerLitePdo\Query\Where;
use Migliori\PowerLitePdo\Result\Result;
use Migliori\PowerLitePdo\View\View;

require_once __DIR__ . '/connection.php';

return [
    Db::class => static function () : Db {
        $dsn = [
            'host'     => DB_HOST,
            'port'     => DB_PORT,
            'dbname'   => DB_NAME,
            'charset'  => DB_CHARSET,
        ];
        $driver = DriverManager::getConnection(PDO_DRIVER);
        $connection = $driver->connect($dsn, DB_USER, DB_PASS);
        $parameters = new Parameters();
        $where = new Where();
        $result = new Result();
        $queryBuilder = new QueryBuilder($connection, $where, $parameters, $result);
        return new Db($connection, $queryBuilder);
    },
    QueryBuilder::class => static function () : QueryBuilder {
        $dsn = [
            'host'     => DB_HOST,
            'port'     => DB_PORT,
            'dbname'   => DB_NAME,
            'charset'  => DB_CHARSET,
        ];
        $driver = DriverManager::getConnection(PDO_DRIVER);
        $connection = $driver->connect($dsn, DB_USER, DB_PASS);
        $parameters = new Parameters();
        $where = new Where();
        $result = new Result();
        return new QueryBuilder($connection, $where, $parameters, $result);
    },
    Pagination::class => static function () : Pagination {
        $dsn = [
            'host'     => DB_HOST,
            'port'     => DB_PORT,
            'dbname'   => DB_NAME,
            'charset'  => DB_CHARSET,
        ];
        $driver = DriverManager::getConnection(PDO_DRIVER);
        $connection = $driver->connect($dsn, DB_USER, DB_PASS);
        $parameters = new Parameters();
        $where = new Where();
        $result = new Result();
        $queryBuilder = new QueryBuilder($connection, $where, $parameters, $result);
        $db = new Db($connection, $queryBuilder);
        $paginationOptions = new PaginationOptions([]);
        $view = new View();
        return new Pagination($db, $paginationOptions, $view, 10);
    }
];
