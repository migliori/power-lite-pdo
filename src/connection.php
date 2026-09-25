<?php

/* database connection

Databases supported by PDO
==========================

Driver name     DSN parameter        Supported databases
--------------------------------------------------------
PDO_CUBRID      cubrid               Cubrid
PDO_DBLIB       dblib                FreeTDS / Microsoft SQL Server / Sybase
PDO_FIREBIRD    firebird             Firebird
PDO_IBM         ibm                  IBM DB2
PDO_INFORMIX    informix             IBM Informix Dynamic Server
PDO_MYSQL       mysql                MySQL 3.x/4.x/5.x
PDO_OCI         oci                  Oracle Call Interface
PDO_ODBC        odbc                 ODBC v3 (IBM DB2, unixODBC et win32 ODBC)
PDO_PGSQL       pgsql                PostgreSQL
PDO_SQLITE      sqlite               SQLite 3 et SQLite 2
PDO_SQLSRV      sqlsrv               Microsoft SQL Server / SQL Azure
PDO_4D          4d                   4D

*/

// DSN parameter of PDO::__construct()
// Every define() below is guarded with if (!defined()) so this file stays silent when
// the host application has already declared the constants (own connection file loaded first).
if (!defined('PDO_DRIVER')) {
    define('PDO_DRIVER', 'mysql');
}

$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
if (defined('PHPUNIT_TESTSUITE_RUNNIG') || $remoteAddr == '127.0.0.1' || $remoteAddr == '::1') {
    // settings for local server
    if (!defined('DB_HOST')) {
        define('DB_HOST', 'localhost');
    }
    if (!defined('DB_NAME')) {
        define('DB_NAME', 'sampledatabase');
    }
    if (!defined('DB_USER')) {
        define('DB_USER', 'root');
    }
    if (!defined('DB_PASS')) {
        define('DB_PASS', 'mysql');
    }
    if (!defined('DB_PORT')) {
        define('DB_PORT', '3306'); // leave empty to use the default port
    }
    if (!defined('DB_CHARSET')) {
        define('DB_CHARSET', 'utf8mb4'); // leave empty to use the default charset
    }
} else {
    // settings for production server
    if (!defined('DB_HOST')) {
        define('DB_HOST', 'production-db_host');
    }
    if (!defined('DB_NAME')) {
        define('DB_NAME', 'production-db_name');
    }
    if (!defined('DB_USER')) {
        define('DB_USER', 'production-db_user');
    }
    if (!defined('DB_PASS')) {
        define('DB_PASS', 'production-db_pass');
    }
    if (!defined('DB_PORT')) {
        define('DB_PORT', 'production-db_port'); // leave empty to use the default port
    }
    if (!defined('DB_CHARSET')) {
        define('DB_CHARSET', 'utf8mb4'); // leave empty to use the default charset
    }
}
