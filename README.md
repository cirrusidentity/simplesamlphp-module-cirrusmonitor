[![Build Status](https://travis-ci.org/cirrusidentity/simplesamlphp-module-cirrusmonitor.svg?branch=master)](https://travis-ci.org/cirrusidentity/simplesamlphp-module-cirrusmonitor)
[![Coverage Status](https://coveralls.io/repos/github/cirrusidentity/simplesamlphp-module-cirrusmonitor/badge.svg?branch=master)](https://coveralls.io/github/cirrusidentity/simplesamlphp-module-cirrusmonitor?branch=master)
# simplesamlphp-module-cirrusmonitor
SSP Module for providing monitoring endpoints to SSP

# Usage

## Install

The module is installable with composer.

```bash
composer config repositories.cirrus-cirrusmonitor git https://github.com/cirrusidentity/simplesamlphp-module-cirrusmonitor
composer require cirrusidentity/simplesamlphp-module-cirrusmonitor:dev-master
```

## Configuration

TBD

# Development

## PHP Version

Module targets php 5.3 and later. This prevent use of phpunit veresions greater than 4.8.

## SSP Integration

For automated tests we need:
 * the test framework to find our classes and SSP's classes
 * SSP to find its necessary configuration files
 * SSP to resolve any module specific files.

The env variable `SIMPLESAMLPHP_CONFIG_DIR` is used to tell SSP where the test configuration files are.
SSP assumes certain files, like templates, will be in its `module` directory. The `bootstrap.php` symlinks the root of this project
into the composer installed SSP's module directory. This takes care of having the SSP autoloader find our classes and takes care of SSP
assuming certain files are installed relative to it.

## Style Guide

Code should conform to PSR-2. Exceptions are made for namespace and class names since SSP has its own autoloader and conventions.

```bash
phpcs --standard=PSR2 lib
```

# Testing

The version installed in vendor is compatible with our tests which use phpunit 4.8

`vendor/bin/phpunit`

