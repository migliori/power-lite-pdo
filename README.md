
# PowerLite PDO

![Static Badge](https://img.shields.io/badge/php%207.4+-fafafa?logo=php) [![GPLv3 License](https://img.shields.io/badge/License-GPL%20v3-yellow.svg)](https://opensource.org/licenses/) ![GitHub Release](https://img.shields.io/github/v/release/migliori/power-lite-pdo)

[PowerLite PDO](https://www.powerlitepdo.com) is a lightweight, powerful PHP library that provides a simple and efficient way to interact with databases using PHP Data Objects (PDO).
It supports multiple database drivers and includes features like easy connection management, query execution, result handling and pagination.

[![PowerLite PDO](https://www.powerlitepdo.com/images/powerlite-pdo-logo-horizontal.77bd9d4b320ef4747c625e729a75ec4b.png)](https://www.powerlitepdo.com)

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Documentation](#documentation)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage/Examples](#usageexamples)
- [Running Tests](#running-tests)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Containerized Connections:** The containers are used to connect your database and handle the configuration and dependencies seamlessly.
- **Db & QueryBuilder Methods:** Execute SQL queries and retrieve results using Db methods or the fluent QueryBuilder.
- **Efficient Result Handling:** Fetch single row, all rows, or specific column's value with dedicated methods.
- **Comprehensive Database Operations:** Provides a wide range of methods for diverse database interactions.
- **Pagination Support:** Handle paginated results effortlessly with the Pagination class.
- **Error Management:** User-friendly handling of database errors and exceptions.
- **Debug Mode:** Provides detailed information about the requests for debugging purposes.
- **Prepared Statements:** Support for prepared statements to prevent SQL injection attacks.
- **Transaction Control:** Manage database transactions with methods to start, commit, and rollback.
- **High Code Quality Standards:** The code follows best practices and coding standards.

## Requirements

PHP ^7.4, PHP 8.x

## Documentation

The documentation for PowerLite PDO is available on the [PowerLite PDO website](https://www.powerlitepdo.com).

In addition to the documentation, a PHPDoc is also available [here](doc/index.html) for more detailed information about the classes, methods, and their parameters.

## Installation

Clone / download or install with Composer

```bash
composer require migliori/power-lite-pdo
```

## Configuration

Open `src/connection.php` in your code editor and replace the constant's values with your database connection settings (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_CHARSET).

### Security concerns

For enhanced safety, store the file outside of your web server's document root (the directory that is served to the internet) and change the path accordingly in the configuration file (`src/config.php`). This prevents the file from being directly accessible via a URL.

## Usage/Examples

### Select records using the main Db class

1. Include the bootstrap file and get the Db instance from the container:

    ```php
    use Migliori\PowerLitePdo\Db;

    // Build the container and connect to the database
    $container = require_once __DIR__ . '/vendor/migliori/power-lite-pdo/src/bootstrap.php';
    $db = $container->get(Db::class);
    ```

2. Use the select method from the Db class to select some records:

    ```php
    $from = 'users'; // The table name
    $fields = ['id', 'username', 'email']; // The columns you want to select
    $where = ['status' => 'active']; // The conditions for the WHERE clause

    $db->select($from, $fields, $where);
    ```

3. Fetch the selected records one by one:

    ```php
    while ($record = $db->fetch()) {
        echo $record->id . ', ' . $record->username . ', ' . $record->email . "\n";
    }
    ```

### Select records using the fluent QueryBuilder

1. Include the bootstrap file and get the QueryBuilder instance from the container:

    ```php
    use Migliori\PowerLitePdo\Query\QueryBuilder;

    // Build the container and connect to the database
    $container = require_once __DIR__ . '/vendor/migliori/power-lite-pdo/src/bootstrap.php';
    $queryBuilder = $container->get(QueryBuilder::class);
    ```

2. Use the QueryBuilder to select some records:

    ```php
    $queryBuilder->select(['id', 'username', 'email'])->from('users')->where(['status' => 'active'])->execute();
    ```

3. Fetch the selected records one by one:

    ```php
    while ($record = $queryBuilder->fetch()) {
        echo $record->id . ', ' . $record->username . ', ' . $record->email . "\n";
    }
    ```

### Select records with Pagination

1. Include the bootstrap file and get the Pagination instance from the container:

    ```php
    use Migliori\PowerLitePdo\Pagination;

    // Build the container and connect to the database
    $container = require_once __DIR__ . '/vendor/migliori/power-lite-pdo/src/bootstrap.php';
    $pagination = $container->get(Pagination::class);
    ```

2. Select some records:

    ```php
    $from = 'users'; // The table name
    $fields = ['id', 'username', 'email']; // The columns you want to select
    $where = ['status' => 'active']; // The conditions for the WHERE clause

    $pagination->select($from, $fields, $where);
    ```

3. Fetch the selected records one by one:

    ```php
    while ($record = $pagination->fetch()) {
        echo $record->id . ', ' . $record->username . ', ' . $record->email . "\n";
    }
    ```

4. Display the pagination:

    ```php
    $url = '/users'; // The URL for the pagination links
    echo $pagination->pagine($url);
    ```

## Running Tests

To run tests, run the following command

```bash
php ./vendor/bin/phpunit test
```

## Dependencies

- **Composer:** A dependency management tool for PHP.
- **PHP-DI:** A dependency injection container for PHP.
- **PDO:** The PHP Data Objects extension for accessing databases.
- **Database Drivers:** The specific drivers for the databases you want to connect to (e.g., MySQL, PostgreSQL, Oracle, Firebird, ...).

## Dev Dependencies

- **PHPUnit:** A testing framework for unit testing PHP code.
- **PHPStan:** A static analysis tool that helps find bugs in PHP code.
- **PHP CodeSniffer:** A set of rules to ensure that PHP code follows coding standards.

## Contributing

Contributions are always welcome!

Please contact us for any improvement suggestions or send your pull requests

## License

[GNU General Public License v3.0](https://choosealicense.com/licenses/gpl-3.0/)
