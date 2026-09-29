<?php
/**
 * Live validation of the ILIKE operator translation per driver.
 * Usage: php like-ilike-test.php <driver>
 * Requires the matching pdo_<driver> extension (and Oracle Home in PATH for oci).
 */
$driver = $argv[1] ?? '';

switch ($driver) {
    case 'pgsql':
        define('PDO_DRIVER', 'pgsql');
        define('DB_HOST', 'localhost');
        define('DB_PORT', '5433');
        define('DB_NAME', 'sakila_pgsql');
        define('DB_USER', 'postgres');
        define('DB_PASS', 'postgres');
        break;
    case 'mysql':
        define('PDO_DRIVER', 'mysql');
        define('DB_HOST', 'localhost');
        define('DB_PORT', '3306');
        define('DB_NAME', 'sakila');
        define('DB_USER', 'root');
        define('DB_PASS', 'mysql');
        break;
    case 'firebird':
        define('PDO_DRIVER', 'firebird');
        define('DB_HOST', 'localhost');
        define('DB_PORT', '3050');
        define('DB_NAME', 'localhost:C:/www/crud-generator-v3/DATABASES/firebird/SAKILA_FIREBIRD.FDB');
        define('DB_USER', 'SYSDBA');
        define('DB_PASS', 'masterkey');
        break;
    case 'oci':
        define('PDO_DRIVER', 'oci');
        define('DB_HOST', 'localhost');
        define('DB_PORT', '1521');
        define('DB_NAME', 'localhost:1521/FREEPDB1');
        define('DB_USER', 'SAKILA_OCI');
        define('DB_PASS', 'oracle');
        define('DB_CHARSET', 'AL32UTF8'); // required by pdo_oci (MAMP): avoids "unknown character set name"
        break;
    default:
        fwrite(STDERR, "unknown driver: {$driver}\n");
        exit(1);
}

$table = $driver === 'oci' ? 'ACTOR' : 'actor';
$field = $driver === 'oci' ? 'FIRST_NAME' : 'first_name';

// composer autoloader + container built the same way as src/bootstrap.php
// (which expects a vendored layout 3 levels up — bypassed here)
require __DIR__ . '/../../vendor/autoload.php';

$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../../src/config.php');
$container = $containerBuilder->build();
$db = $container->get('Migliori\PowerLitePdo\Db');

// 1. ILIKE mixed case: must match on EVERY driver (case-insensitive)
$db->select($table, $field, [$field . ' ILIKE' => '%Pen%']);
$ilike_count = (int) $db->numRows();

// 2. plain LIKE mixed case: reference (case-sensitive on pgsql/firebird, ci on mysql/oci)
$db->select($table, $field, [$field . ' LIKE' => '%Pen%']);
$like_count = (int) $db->numRows();

// 3. NOT ILIKE: total - ILIKE matches
$db->select($table, $field, [$field . ' NOT ILIKE' => '%Pen%']);
$not_ilike_count = (int) $db->numRows();

// 4. total rows
$db->select($table, $field);
$total = (int) $db->numRows();

echo "driver={$driver} table={$table}\n";
echo "  ILIKE '%Pen%' : {$ilike_count}\n";
echo "  LIKE  '%Pen%' : {$like_count}\n";
echo "  NOT ILIKE     : {$not_ilike_count}\n";
echo "  total rows    : {$total}\n";

$ok = ($ilike_count > 0) && ($total === $ilike_count + $not_ilike_count);
echo $ok ? 'RESULT: PASS' : 'RESULT: FAIL';
echo "\n";
