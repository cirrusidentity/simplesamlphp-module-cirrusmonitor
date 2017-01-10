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

Create `config/module_cirrusmonitor.php`

```php
$config = array(
    'metadata' => [
        # Ensure metadata is valid for at least 6 more days.
        'validFor' => 'P60D',
        'entitiesToCheck' => [
            [
                'entityid' => 'urn:mace:incommon:uchicago.edu',
                'metadata-set' => 'saml20-idp-remote',
            ],
            [
                'entityid' => 'https://google.cirrusidentity.com/gateway1',
                'metadata-set' => 'saml20-idp-remote',
            ],
            [
                'entityid' => 'https://standard.monitor.cirrusidentity.com',
                'metadata-set' => 'saml20-sp-remote',
            ]
        ]
    ],
);
```
## Checking

Visit https://hostname/module.php/cirrusmonitor/monitor.php
Note the response will likely change in future versions.

Sample response below. In the sample one entity is expiring soon, another entity id couldn't be found and the last was found and isn't expiring soon.

```json
{
    "overallStatus": "not-ok",
    "metadata": {
        "overallStatus": "not-ok",
        "perEntityStatus": [
            {
                "entityid": "urn:mace:incommon:uchicago.edu",
                "metadata-set": "saml20-idp-remote",
                "status": "expiring"
            },
            {
                "entityid": "https://google.cirrusidentity.com/gateway",
                "metadata-set": "saml20-idp-remote",
                "status": "not-found"
            },
            {
                "entityid": "https://standard.monitor.cirrusidentity.com",
                "metadata-set": "saml20-sp-remote",
                "status": "ok"
            }
        ]
    }
}
```
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

## Built-In PHP Server

You can test `www` functionality by used the built in php server

```bash
export SIMPLESAMLPHP_CONFIG_DIR=$PWD/tests/config
php -S 0.0.0.0:8123 -t $PWD/vendor/simplesamlphp/simplesamlphp/www/
```

Then visit http://localhost:8123/module.php/cirrusmonitor/monitor.php or http://localhost:8123/module.php/cirrusmonitor/monitor.php/metadata 

Using the php webserver makes use of two, non-obvious configuration settings: 

* `config.php` has the `baseurlpath` set to `/`. Without this ssp thinks it is running under `/simplesaml` and we would need to configure a router script to alias that to root.
* The module is symlinked into the vendor/composer installation of SSP's module directory. This ensures class loading, template resolution, etc work.

## Style Guide

Code should conform to PSR-2. Exceptions are made for namespace and class names since SSP has its own autoloader and conventions.

```bash
phpcs --standard=PSR2 lib
```

# Testing

The version installed in vendor is compatible with our tests which use phpunit 4.8

`vendor/bin/phpunit`

