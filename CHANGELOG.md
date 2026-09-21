# Change Log

All notable changes to the "power-lite-pdo" library will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
