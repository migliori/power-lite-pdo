<?php

use DI\ContainerBuilder;
use Migliori\PowerLitePdo\Exception\QueryBuilderException;
use Migliori\PowerLitePdo\Query\QueryBuilder;
use Migliori\PowerLitePdo\Query\Where;
use Migliori\PowerLitePdo\Query\Parameters;
use Migliori\PowerLitePdo\Result\Result;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    protected $queryBuilder;

    protected function setUp(): void
    {
        // use src\bootstrap.php to create a container and load definitions
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../src/config.php');
        $container = $containerBuilder->build();

        $this->queryBuilder = $container->get(QueryBuilder::class);
    }

    public function testQuery()
    {
        $sql = "SELECT * FROM customers";

        $qBuilder = $this->queryBuilder->query($sql);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals('RAW', $qBuilder->getQueryType());
        $this->assertEquals($sql, $qBuilder->getRawQuery());
    }

    public function testSelect()
    {
        $fields = ['id', 'name'];

        $qBuilder = $this->queryBuilder->select($fields);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals('SELECT', $qBuilder->getQueryType());
        $this->assertEquals('id, name', $qBuilder->getFields());
    }

    public function testFrom()
    {
        $from = 'customers';

        $qBuilder = $this->queryBuilder->from($from);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals($from, $qBuilder->getFrom());
    }

    public function testWhere()
    {
        $where = ['country' => 'Indonesia'];

        $qBuilder = $this->queryBuilder->where($where);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertInstanceOf(Where::class, $qBuilder->getWhere());
    }

    public function testDistinct()
    {
        $qBuilder = $this->queryBuilder->distinct();

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertTrue($qBuilder->getParameters()->get('selectDistinct'));
    }

    public function testGroupBy()
    {
        $groupBy = 'country';

        $qBuilder = $this->queryBuilder->groupBy($groupBy);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals($groupBy, $qBuilder->getParameters()->get('groupBy'));
    }

    public function testLimit()
    {
        $limit = 10;

        $qBuilder = $this->queryBuilder->limit($limit);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals($limit, $qBuilder->getParameters()->get('limit'));
    }

    public function testOrderBy()
    {
        $orderBy = 'name ASC';

        $qBuilder = $this->queryBuilder->orderBy($orderBy);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals($orderBy, $qBuilder->getParameters()->get('orderBy'));
    }

    public function testParameters()
    {
        $parameters = [
            'selectDistinct' => true,
            'groupBy' => 'country',
            'limit' => 10,
            'orderBy' => 'name ASC'
        ];

        $qBuilder = $this->queryBuilder->parameters($parameters);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals($parameters, $qBuilder->getParameters()->getAll());
    }

    public function testPlaceholders()
    {
        $where = [
            'country' => 'Indonesia',
            'date' => new DateTime('2022-01-01')
        ];

        $expected = [
            'a_country' => 'Indonesia',
            'a_date' => '2022-01-01 00:00:00'
        ];

        $qBuilder = $this->queryBuilder->where($where);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals($expected, $qBuilder->getWhere()->getPlaceholders());
    }

    public function testInsert()
    {
        $table = 'customers';
        $values = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com'
        ];

        $qBuilder = $this->queryBuilder->insert($table, $values);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals('INSERT', $qBuilder->getQueryType());
        $this->assertEquals($table, $qBuilder->getTable());
        $this->assertEquals($values, $qBuilder->getValues());
    }

    public function testUpdate()
    {
        $table = 'customers';
        $values = [
            'first_name' => 'John',
            'last_name' => 'Doe'
        ];
        $where = ['id' => 1];

        $qBuilder = $this->queryBuilder->update($table, $values, $where);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals('UPDATE', $qBuilder->getQueryType());
        $this->assertEquals($table, $qBuilder->getTable());
        $this->assertEquals($values, $qBuilder->getValues());
        $this->assertInstanceOf(Where::class, $qBuilder->getWhere());
    }

    public function testDelete()
    {
        $table = 'customers';
        $where = ['id' => 1];

        $qBuilder = $this->queryBuilder->delete($table, $where);

        $this->assertInstanceOf(QueryBuilder::class, $qBuilder);
        $this->assertEquals('DELETE', $qBuilder->getQueryType());
        $this->assertEquals($table, $qBuilder->getTable());
        $this->assertInstanceOf(Where::class, $qBuilder->getWhere());
    }

    public function testExecuteWithSelectRawQuery()
    {
        $sql = "SELECT * FROM orders WHERE status = :status AND order_date = :order_date";
        $placeholders = [
            'status' => 'Shipped',
            'order_date' => new DateTime('2003-01-06')
        ];
        $debug = 'on';

        $this->queryBuilder
            ->query($sql)
            ->placeholders($placeholders)
            ->debugOnce($debug)
            ->execute();

        $this->assertEquals('RAW', $this->queryBuilder->getQueryType());
        $this->assertInstanceOf(Result::class, $this->queryBuilder->getResult());
    }

    public function testExecuteWithSelectQuery()
    {
        $fields = ['id', 'last_name'];
        $from = 'customers';
        $where = ['country' => 'Indonesia'];
        $parameters = [
            'selectDistinct' => true,
            'groupBy' => 'id, last_name',
            'limit' => 10,
            'orderBy' => 'last_name ASC'
        ];
        $debug = 'on';

        $this->queryBuilder
            ->select($fields)
            ->from($from)
            ->where($where)
            ->parameters($parameters)
            ->debugOnce($debug)
            ->execute();

        $this->assertEquals('SELECT', $this->queryBuilder->getQueryType());
        $this->assertInstanceOf(Result::class, $this->queryBuilder->getResult());
    }

    public function testExecuteWithInsertStatement()
    {
        $table = 'customers';
        $values = ['first_name' => 'Cathy', 'last_name' => 'Baldwin', 'phone' => '05 24 54 21 52', 'email' => 'cathy.baldwin@gmail.com', 'address' => '2 baker street', 'city' => 'Cardiff', 'country' => 'Guatemala'];

        $affectedRows = $this->queryBuilder->insert($table, $values)->execute();

        $this->assertIsInt($affectedRows);
        $this->assertEquals('INSERT', $this->queryBuilder->getQueryType());
    }

    public function testExecuteWithUpdateStatement()
    {
        $table = 'customers';
        $values = ['first_name' => 'Cathy', 'last_name' => 'Baldwin'];
        $where = ['id' => 3];

        $affectedRows = $this->queryBuilder->update($table, $values, $where)->execute();

        $this->assertIsInt($affectedRows);
        $this->assertEquals('UPDATE', $this->queryBuilder->getQueryType());
    }

    public function testExecuteWithDeleteStatement()
    {
        $table = 'orders';
        $where = ['id' => 3];

        $affectedRows = $this->queryBuilder->delete($table, $where)->execute();

        $this->assertIsInt($affectedRows);
        $this->assertEquals('DELETE', $this->queryBuilder->getQueryType());
    }

    public function testExecuteWithDeleteWithConstraintStatementFails()
    {
        $this->expectException(QueryBuilderException::class);

        $table = 'customers';
        $where = ['id' => 3];

        $this->queryBuilder->delete($table, $where)->execute();
    }
}
