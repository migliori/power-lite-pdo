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

declare(strict_types=1);

namespace Migliori\PowerLitePdo;

use Migliori\PowerLitePdo\Driver\DriverBase;
use Migliori\PowerLitePdo\Driver\Firebird;
use Migliori\PowerLitePdo\Driver\Mysql;
use Migliori\PowerLitePdo\Driver\Oci;
use Migliori\PowerLitePdo\Driver\Pgsql;
use Migliori\PowerLitePdo\Exception\DriverManagerException;

/**
 * Class DriverManager
 *
 * This class manages the database drivers for the PHP PDO DB Class.
 */
class DriverManager
{
    /**
     * Returns a connection object for the specified driver.
     *
     * @param string $driver The name of the driver.
     * @return DriverBase The connection object.
     */
    public static function getConnection(string $driver): DriverBase
    {
        switch ($driver) {
            case 'mysql':
                return new Mysql();
            case 'pgsql':
                return new Pgsql();
            case 'firebird':
                return new Firebird();
            case 'oci':
                return new Oci();
            default:
                throw new DriverManagerException('Unsupported driver: ' . $driver);
        }
    }
}
