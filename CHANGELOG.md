# Change Log

All notable changes to the "power-lite-pdo" library will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## v1.4.0

- Add case-insensitive search: the `ILIKE` and `NOT ILIKE` operators are now supported in WHERE conditions and translated per driver. PostgreSQL uses its native `ILIKE` operator; Firebird and Oracle get `UPPER(field) LIKE UPPER(value)` (the search value is uppercased automatically for string values); MySQL and other drivers fall back to `LIKE` (case-insensitive with the default `_ci` collations). Usage: `$where['first_name ILIKE'] = '%pen%';` — works everywhere PHP CRUD Generator supports a database driver, no schema or query rewrite needed
- `QueryBuilder` now shares the connected driver name with its `Where` builder (new `Where::setDriver()`), so operator translation follows the actual connection whatever the DI wiring (`Db::class`, `QueryBuilder::class`, `Pagination::class` factories or `Db::create()`)

## v1.3.6

- Fix php-cgi.exe crash (0xc0000005 in KERNELBASE.dll on Windows) at request shutdown with PDO_OCI: a statement holding an open OCI cursor (partial fetch, rows pending) that was freed during shutdown could trigger an access violation depending on the object destruction order in the host application. `Result::fetch()` now closes the cursor deterministically when EOF is reached, and a `Result::__destruct()` closes any remaining open cursor as a safety net

## v1.3.5

- Fix `QueryBuilder::numRows()` regression introduced in v1.3.4: the ORDER BY clause was stripped from the assembled COUNT query AFTER building it, which truncated the derived-table form (`... FROM (SELECT DISTINCT f1, f2 FROM joins) alias` lost its closing parenthesis and alias when the data query had an ORDER BY). The ORDER BY is now stripped from the captured FROM part BEFORE assembling the COUNT query, for every branch (SELECT, RAW, fallback)

## v1.3.4

- Fix `QueryBuilder::numRows()` for `SELECT DISTINCT` on multiple fields: `COUNT(DISTINCT f1, f2, ...)` is valid in MySQL only — PostgreSQL, Oracle and Firebird reject it (SQLSTATE 42883 on PostgreSQL). The portable form counts the rows of a `SELECT DISTINCT` derived table, which also matches the actual data query semantics (rows containing NULLs are now counted as returned by the data query, while MySQL's multi-field `COUNT(DISTINCT ...)` silently skipped them). Single-field `DISTINCT` keeps using `COUNT(DISTINCT f1)`, valid in every driver. The derived-table alias omits the `AS` keyword, unsupported for table aliases in Oracle and Firebird
- Update stale `version` field in composer.json to match the released tags (1.3.4)

## v1.3.0

- Add `Db::create(array $dsn, string $username = '', string $password = '', ?string $driver = null): static` static factory: assembles the driver, connects and wires a QueryBuilder in a single call for arbitrary credentials (installers, connection testers, multi-database applications). Defaults to the PDO_DRIVER constant, or 'mysql'
- Guard every define() in connection.php with `if (!defined())` so the file stays silent when the host application has already declared the constants (own connection file loaded first)
- Fix `$_SERVER['REMOTE_ADDR']` undefined index warning in connection.php when running from CLI (null-safe fallback)

## v1.0.0

- Initial release

## v1.1.0

- Fix exception message in Mysql.php
- solve php 7.4 typing issues
- Update PowerLite PDO autoload path in bootstrap.php
- Update phpDocumentor documentation

## v1.1.1

- fix php 7.4 typing issue
- Add CHANGELOG.md

## v1.2.0

- Fix numRows(): COUNT(f1, f2, ...) was invalid SQL in every driver (SQLSTATE 42000) - row counting now uses COUNT(*) for non-DISTINCT SELECTs (COUNT(DISTINCT f1, f2) preserved for DISTINCT)
- Fix Where::set(): plain array_filter() dropped falsy values (0, '0') from WHERE clauses, silently removing conditions and emitting an orphan " WHERE " (SQL syntax error) when all entries were falsy - only '' and null are dropped now
- Fix CHANGELOG.md header (wrong project name)
