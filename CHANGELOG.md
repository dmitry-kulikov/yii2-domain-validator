# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.3] - 2021-10-22

### Changed

- Running of tests using Docker and documentation for this.

## [1.1.2] - 2020-02-19

### Added

- Added possibility to run tests using Docker and documentation for this.

## [1.1.1] - 2019-11-02

### Fixed

- checkDNS = true leads to ErrorException for some bad domains on Alpine Linux.

## [1.1.0] - 2019-06-11

### Added

- \#5: Added the ability to extend the verification of DNS (maranqz).

### Fixed

- Fixed `kdn\yii2\validators\DomainValidator::$checkDNS` tells that every domain is correct on Alpine Linux.

## [1.0.3] - 2018-03-07

### Fixed

- \#4: Fixed error for $allowUnderscore = true in PHP 7.3.

## [1.0.2] - 2017-02-19

### Fixed

- \#3: Fixed checkDNS returns false positives.
- Fixed typo in Russian translation.

## [1.0.1] - 2016-07-15

### Added

- Documentation.

## [1.0.0] - 2016-07-07

- Initial release.
