<?php

use DI\ContainerBuilder;
use Migliori\PowerLitePdo\Db;
use Migliori\PowerLitePdo\Exception\DbException;
use Migliori\PowerLitePdo\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    protected Db $db;

    protected function setUp(): void
    {
        // use src\bootstrap.php to create a container and load definitions
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../src/config.php');
        $container = $containerBuilder->build();

        $this->db = $container->get(Db::class);
    }

    public function testConnection()
    {
        $this->assertInstanceOf(\PDO::class, $this->db->getPdo());
    }

    public function testGetPdoReturnsPdoObject()
    {
        $pdo = $this->db->getPdo();

        $this->assertInstanceOf(\PDO::class, $pdo);
    }

    public function testGetQueryBuilderReturnsInstanceOfQueryBuilder()
    {
        $expectedResult = QueryBuilder::class;

        $actualResult = $this->db->getQueryBuilder();

        $this->assertInstanceOf($expectedResult, $actualResult);
    }

    public function testQueryExecutesQueryAndReturnsResult()
    {
        $sql = "SELECT * FROM customers WHERE country = 'Indonesia'";
        $result = true; // Replace with your expected result

        $actualResult = $this->db->query($sql);

        $this->assertEquals($result, $actualResult);
    }

    public function testQueryExecutesQueryWithPlaceholdersAndReturnsResult()
    {
        $sql = "SELECT id, first_name, last_name FROM customers WHERE country = :country";
        $placeholders = ['country' => 'Indonesia'];

        $actualResult = $this->db->query($sql, $placeholders);

        $this->assertTrue($actualResult);
    }

    public function testQueryExecutesQueryWithDebugModeAndReturnsResult()
    {
        $sql = "SELECT * FROM customers WHERE country = 'Indonesia'";

        $actualResult = $this->db->debugOnce('on')->query($sql);

        $this->assertTrue($actualResult);
    }

    public function testQueryRowExecutesQueryAndReturnsOneRow()
    {
        $sql = "SELECT * FROM customers WHERE country = 'Indonesia'";

        $actualResult = $this->db->queryRow($sql);

        $this->assertIsObject($actualResult);
        $this->assertObjectHasProperty('id', $actualResult);
        $this->assertObjectHasProperty('first_name', $actualResult);
    }

    public function testQueryRowExecutesQueryWithPlaceholdersAndReturnsOneRow()
    {
        $sql = "SELECT * FROM customers WHERE country = :country";
        $placeholders = ['country' => 'Indonesia'];

        $actualResult = $this->db->queryRow($sql, $placeholders);

        $this->assertIsObject($actualResult);
        $this->assertObjectHasProperty('id', $actualResult);
        $this->assertObjectHasProperty('first_name', $actualResult);
    }

    public function testQueryRowExecutesQueryWithDebugModeAndReturnsOneRow()
    {
        $sql = "SELECT * FROM customers WHERE country = 'Indonesia'";

        $actualResult = $this->db->debugOnce('on')->queryRow($sql);

        $this->assertIsObject($actualResult);
        $this->assertObjectHasProperty('id', $actualResult);
        $this->assertObjectHasProperty('first_name', $actualResult);
    }

    public function testQueryValueExecutesQueryAndReturnsSingleValue()
    {
        $sql = "SELECT COUNT(*) FROM customers WHERE country = 'Indonesia'";

        $actualResult = $this->db->queryValue($sql);

        $this->assertIsInt($actualResult);
        $this->assertNotNull($actualResult);
    }

    public function testQueryValueExecutesQueryWithPlaceholdersAndReturnsSingleValue()
    {
        $sql = "SELECT COUNT(*) FROM customers WHERE country = :country";
        $placeholders = ['country' => 'Indonesia'];

        $actualResult = $this->db->queryValue($sql, $placeholders);

        $this->assertIsInt($actualResult);
        $this->assertNotNull($actualResult);
    }

    public function testQueryValueExecutesQueryWithDebugModeAndReturnsSingleValue()
    {
        $sql = "SELECT COUNT(*) FROM customers WHERE country = 'Indonesia'";

        $actualResult = $this->db->debugOnce('on')->queryValue($sql);

        $this->assertIsInt($actualResult);
        $this->assertNotNull($actualResult);
    }

    public function testSelectExecutesQueryAndReturnsResult()
    {
        $from = 'customers';
        $fields = ['id', 'first_name', 'last_name'];
        $where = ['country' => 'Indonesia'];
        $parameters = ['selectDistinct' => true, 'orderBy' => 'last_name ASC'];
        $debug = null;

        $actualResult = $this->db->select($from, $fields, $where, $parameters, $debug);

        $this->assertTrue($actualResult);
    }

    public function testSelectCountReturnsRecordCount()
    {
        $from = 'customers';
        $fields = ['*' => 'rowsCount'];
        $where = [];
        $parameters = [];
        $debug = null;

        $actualResult = $this->db->selectCount($from, $fields, $where, $parameters, $debug);

        $this->assertIsObject($actualResult);
        $this->assertObjectHasProperty('rowsCount', $actualResult);
        $this->assertIsInt($actualResult->rowsCount);
        $this->assertNotNull($actualResult->rowsCount);
    }

    public function testSelectCountWithWhereAndParametersReturnsRecordCount()
    {
        $from = 'customers';
        $fields = ['*' => 'rowsCount'];
        $where = ['country' => 'Indonesia'];
        $parameters = ['selectDistinct' => true, 'orderBy' => 'last_name ASC'];
        $debug = null;

        $actualResult = $this->db->selectCount($from, $fields, $where, $parameters, $debug);

        $this->assertIsObject($actualResult);
        $this->assertObjectHasProperty('rowsCount', $actualResult);
        $this->assertIsInt($actualResult->rowsCount);
        $this->assertNotNull($actualResult->rowsCount);
    }

    public function testSelectCountReturnsRecordCountWithArrayFields()
    {
        $from = 'customers';
        $fields = [
            'id' => 'idCount',
            'country' => 'countryCount'
        ];
        $where = [];
        $parameters = [];
        $debug = null;

        $actualResult = $this->db->selectCount($from, $fields, $where, $parameters, $debug);

        $this->assertIsObject($actualResult);
        $this->assertObjectHasProperty('idCount', $actualResult);
        $this->assertObjectHasProperty('countryCount', $actualResult);
        $this->assertIsInt($actualResult->idCount);
        $this->assertNotNull($actualResult->idCount);
        $this->assertIsInt($actualResult->countryCount);
        $this->assertNotNull($actualResult->countryCount);
    }

    public function testSelectCountReturnsRecordCountWithStringFields()
    {
        $from = 'customers';
        $fields = 'id AS idCount, country AS countryCount';
        $where = [];
        $parameters = [];
        $debug = null;

        $actualResult = $this->db->selectCount($from, $fields, $where, $parameters, $debug);

        $this->assertIsObject($actualResult);
        $this->assertObjectHasProperty('idCount', $actualResult);
        $this->assertObjectHasProperty('countryCount', $actualResult);
        $this->assertIsInt($actualResult->idCount);
        $this->assertNotNull($actualResult->idCount);
        $this->assertIsInt($actualResult->countryCount);
        $this->assertNotNull($actualResult->countryCount);
    }

    public function testSelectRowExecutesSelectQueryAndReturnsOneRow()
    {
        $from = 'customers';
        $fields = ['id', 'first_name', 'last_name'];
        $where = ['country' => 'Indonesia'];

        $actualResult = $this->db->selectRow($from, $fields, $where);

        $this->assertIsObject($actualResult);
        $this->assertObjectHasProperty('id', $actualResult);
        $this->assertObjectHasProperty('first_name', $actualResult);
        $this->assertObjectHasProperty('last_name', $actualResult);
        $this->assertIsInt($actualResult->id);
        $this->assertNotNull($actualResult->id);
    }

    public function testSelectRowExecutesSelectQueryWithDebugModeAndReturnsOneRow()
    {
        $from = 'customers';
        $fields = ['id', 'first_name', 'last_name'];
        $where = ['country' => 'Indonesia'];

        $actualResult = $this->db->debugOnce('on')->selectRow($from, $fields, $where);

        $this->assertIsObject($actualResult);
        $this->assertObjectHasProperty('id', $actualResult);
        $this->assertObjectHasProperty('first_name', $actualResult);
        $this->assertObjectHasProperty('last_name', $actualResult);
        $this->assertIsInt($actualResult->id);
        $this->assertNotNull($actualResult->id);
    }

    public function testSelectValueReturnsValueWhenRecordExists()
    {
        $from = 'customers';
        $field = 'email';
        $where = ['country' => 'Indonesia'];
        $debug = null;

        $actualResult = $this->db->selectValue($from, $field, $where, $debug);

        $this->assertIsString($actualResult);
        $this->assertStringContainsString('@', $actualResult);
    }

    public function testSelectValueReturnsFalseWhenNoRecordExists()
    {
        $from = 'customers';
        $field = 'email';
        $where = ['country' => 'DummyCountry'];
        $debug = null;

        $actualResult = $this->db->selectValue($from, $field, $where, $debug);

        $this->assertFalse($actualResult);
    }

    public function testInsertExecutesInsertAndReturnsAffectedRows()
    {
        $table = 'customers';
        $values = ['first_name' => 'Cathy', 'last_name' => 'Baldwin', 'phone' => '05 24 54 21 52', 'email' => 'cathy.baldwin@gmail.com', 'address' => '2 baker street', 'city' => 'Cardiff', 'country' => 'Guatemala'];
        $debug = null;

        $actualResult = $this->db->insert($table, $values, $debug);

        $this->assertEquals(1, $actualResult);
    }

    public function testInsertThrowsExceptionWhenValuesArrayIsEmpty()
    {
        $this->expectException(DbException::class);

        $table = 'customers';
        $values = [];
        $debug = null;

        $this->db->insert($table, $values, $debug);
    }

    public function testUpdateExecutesUpdateAndReturnsAffectedRows()
    {
        $table = 'customers';
        $values = ['first_name' => 'Cathy', 'city' => 'Cardiff'];
        $where = ['id' => 3];
        $debug = null;

        $actualResult = $this->db->update($table, $values, $where, $debug);

        $this->assertEquals(1, $actualResult);
    }

    public function testUpdateThrowsExceptionWhenValuesArrayIsEmpty()
    {
        $this->expectException(DbException::class);

        $table = 'customers';
        $values = [];
        $where = ['id' => 1];
        $debug = null;

        $this->db->update($table, $values, $where, $debug);
    }

    public function testDeleteExecutesDeleteAndReturnsAffectedRows()
    {
        $table = 'payments';
        $where = ['customers_id' => 103, 'payment_date' => '2004-10-19'];
        $debug = null;

        $actualResult = $this->db->delete($table, $where, $debug);

        $this->assertIsInt($actualResult);
    }

    public function testConvertQueryToSimpleArrayWithKeyField()
    {
        $array = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
            ['id' => 3, 'name' => 'Bob']
        ];
        $valueField = 'name';
        $keyField = 'id';
        $expectedResult = [
            1 => 'John',
            2 => 'Jane',
            3 => 'Bob'
        ];

        $actualResult = $this->db->convertToSimpleArray($array, $valueField, $keyField);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testConvertToSimpleArrayWithoutKeyField()
    {
        $array = [
            ['name' => 'John'],
            ['name' => 'Jane'],
            ['name' => 'Bob']
        ];
        $valueField = 'name';
        $expectedResult = ['John', 'Jane', 'Bob'];

        $actualResult = $this->db->convertToSimpleArray($array, $valueField);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testConvertToSimpleArrayWithEmptyArray()
    {
        $array = [];
        $valueField = 'name';
        $expectedResult = [];

        $actualResult = $this->db->convertToSimpleArray($array, $valueField);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testGetColumnsReturnsColumnsData()
    {
        $table = 'customers';

        $actualResult = $this->db->getColumns($table);


        $this->assertIsArray($actualResult);
        $this->assertIsObject($actualResult[0]);
        $this->assertNotEmpty($actualResult[0]);
    }

    public function testGetTablesReturnsArrayOfTables()
    {
        $tables = ['customers', 'orders', 'payments', 'productlines', 'products'];

        $debug = null;
        $actualResult = $this->db->getTables($debug);

        $this->assertEquals($tables, $actualResult);
    }

    public function testTransactionBeginStartsTransactionSuccessfully()
    {
        $result = $this->db->transactionBegin();

        $this->assertTrue($result);
    }

    public function testTransactionCommitCommitsTransactionSuccessfully()
    {
        $this->db->transactionBegin();
        $actualResult = $this->db->transactionCommit();

        $this->assertTrue($actualResult);
    }

    public function testTransactionRollbackRollsBackTransactionSuccessfully()
    {
        $this->db->transactionBegin();
        $result = $this->db->transactionRollback();

        $this->assertTrue($result);
    }

    public function testTransactionRollbackThrowsExceptionOnError()
    {
        $this->expectException(\Exception::class);

        $this->db->transactionRollback();
    }

    public function testFetchReturnsNextRow()
    {
        $this->db->query('SELECT * FROM customers');
        $actualResult = $this->db->fetch();

        $this->assertIsObject($actualResult);
        $this->assertObjectHasProperty('id', $actualResult);
        $this->assertObjectHasProperty('first_name', $actualResult);
        $this->assertObjectHasProperty('last_name', $actualResult);
        $this->assertIsInt($actualResult->id);
        $this->assertNotNull($actualResult->id);
    }

    public function testFetchAllReturnsRowsAccordingToFetchParameters()
    {
        $fetchParameters = \PDO::FETCH_ASSOC;

        $this->db->query('SELECT * FROM customers');
        $actualResult = $this->db->fetchAll($fetchParameters);

        $this->assertIsArray($actualResult);
        $this->assertNotEmpty($actualResult);
        $this->assertIsArray($actualResult[0]);
        $this->assertArrayHasKey('id', $actualResult[0]);
        $this->assertArrayHasKey('first_name', $actualResult[0]);
        $this->assertArrayHasKey('last_name', $actualResult[0]);
    }

    public function testFetchAllReturnsRowsAccordingToDefaultFetchParameters()
    {
        $from = 'customers';
        $fields = ['id', 'first_name'];
        $parameters = [
            'limit'   => 3,
            'orderBy' => 'id ASC'
        ];

        $this->db->select($from, $fields, [], $parameters);
        $actualResult = $this->db->fetchAll();

        $this->assertIsArray($actualResult);
        $this->assertCount(3, $actualResult);
        $this->assertIsObject($actualResult[0]);
        $this->assertObjectHasProperty('id', $actualResult[0]);
        $this->assertObjectHasProperty('first_name', $actualResult[0]);
    }

    public function testGetHTMLReturnsTableWithRecords()
    {
        $from = 'customers';
        $fields = ['id', 'first_name'];
        $parameters = [
            'limit'   => 3,
            'orderBy' => 'id ASC'
        ];

        $this->db->select($from, $fields, [], $parameters);
        $records = $this->db->fetchAll();
        $actualResult = $this->db->getHTML($records);

        $this->assertStringContainsString('&lt;table', $actualResult);
        $this->assertStringEndsWith('&lt;/table&gt;', $actualResult);
    }

    public function testGetHTMLReturnsTableWithoutRowCount()
    {
        $from = 'customers';
        $fields = ['id', 'first_name'];
        $parameters = [
            'limit'   => 3,
            'orderBy' => 'id ASC'
        ];

        $this->db->select($from, $fields, [], $parameters);
        $records = $this->db->fetchAll();

        $actualResult = $this->db->getHTML($records, false);

        $this->assertStringStartsWith('&lt;table', $actualResult);
        $this->assertStringEndsWith('&lt;/table&gt;', $actualResult);
    }

    public function testGetHTMLReturnsTableWithCustomAttributes()
    {
        $records = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ];

        $actualResult = $this->db->getHTML($records, true, 'class=my-table, style=color:#222', 'class=my-header, style=font-weight:bold', 'class=my-cell, style=font-weight:normal');

        $this->assertStringContainsString('&lt;table', $actualResult);
        $this->assertStringEndsWith('&lt;/table&gt;', $actualResult);
    }

    public function testGetHTMLReturnsEmptyTableForEmptyRecords()
    {
        $records = [];
        $expectedResult = 'No records were returned.';

        $actualResult = $this->db->getHTML($records);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testGetLastInsertIdReturnsLastInsertId()
    {
        $table = 'customers';
        $values = ['first_name' => 'Cathy', 'last_name' => 'Baldwin', 'phone' => '05 24 54 21 52', 'email' => 'cathy.baldwin@gmail.com', 'address' => '2 baker street', 'city' => 'Cardiff', 'country' => 'Guatemala'];
        $debug = null;
        $this->db->insert($table, $values, $debug);
        $actualLastInsertId = $this->db->getLastInsertId();

        $this->assertIsInt((int) $actualLastInsertId);
    }

    public function testGetMaximumValueReturnsMaxValueWhenValueExists()
    {
        $table = 'customers';
        $field = 'id';

        $actualResult = $this->db->getMaximumValue($table, $field);

        $this->assertIsInt($actualResult);
    }

    public function testNumRowsReturnsNumberOfRowsAffected()
    {
        $this->db->query('SELECT * FROM customers');
        $actualResult = $this->db->numRows();

        $this->assertIsInt($actualResult);
        $this->assertNotNull($actualResult);
    }

    public function testSetDebugSetsDebugMode()
    {
        $mode = 'on';

        $this->db->setDebug($mode);

        $this->assertEquals($mode, $this->db->getDebugMode());
    }

    public function testSetDebugDisablesDebugMode()
    {
        $mode = 'silent';

        $this->db->setDebug($mode);

        $result = $this->db->getDebugMode();

        $this->assertEquals($mode, $result);
    }
}
