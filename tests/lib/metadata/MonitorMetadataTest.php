<?php

namespace SimpleSAML\Test\Cirrusmonitor\Test\Metadata;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Configuration;
use SimpleSAML\Module\cirrusmonitor\metadata\MonitorMetadata;

class MonitorMetadataTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        putenv('SIMPLESAMLPHP_CONFIG_DIR=' . dirname(__DIR__, 2) . '/config');
    }


    public function testMissingEntityIDsToCheck(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = array();
        $configuration = Configuration::loadFromArray($config);
        new MonitorMetadata($configuration);
    }

    public function testMissingEntityID(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = [
            'entitiesToCheck' => [
                [
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        new MonitorMetadata($configuration);
    }

    public function testNoStringEntityID(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 1,
                    'metadata-set' => 'saml20-sp-remote'
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        new MonitorMetadata($configuration);
    }

    public function testMissingMetadataSource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://idp.example.org'
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        new MonitorMetadata($configuration);
    }

    public function testNoStringMetadataSource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://idp.example.org',
                    'metadata-set' => 1
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        new MonitorMetadata($configuration);
    }

    public function testPerformCheck(): void
    {
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ],
                [
                    'entityid' => 'https://not-found.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        $monitor = new MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => MonitorMetadata::STATUS_NOT_OK,
            'perEntityStatus' => [
                [
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => MonitorMetadata::METADATA_OK
                ],
                [
                    'entityid' => $config['entitiesToCheck'][1]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][1]['metadata-set'],
                    'status' => MonitorMetadata::METADATA_NOT_FOUND
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataOk(): void
    {
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        $monitor = new MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => MonitorMetadata::STATUS_OK,
            'perEntityStatus' => [
                [
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => MonitorMetadata::METADATA_OK
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataNotFound(): void
    {
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://not-found.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        $monitor = new MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => MonitorMetadata::STATUS_NOT_OK,
            'perEntityStatus' => [
                [
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => MonitorMetadata::METADATA_NOT_FOUND
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataExpired(): void
    {
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://expired.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        $monitor = new MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => MonitorMetadata::STATUS_NOT_OK,
            'perEntityStatus' => [
                [
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => MonitorMetadata::METADATA_EXPIRED
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataExpiring(): void
    {
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://expiring.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        $monitor = new MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => MonitorMetadata::STATUS_NOT_OK,
            'perEntityStatus' => [
                [
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => MonitorMetadata::METADATA_EXPIRING
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataExpiringButShortWindowMakesItOkay(): void
    {
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://expiring.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                    'validFor' => 'P2D'
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        $monitor = new MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => MonitorMetadata::STATUS_OK,
            'perEntityStatus' => [
                [
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => MonitorMetadata::METADATA_OK
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataNoExpire(): void
    {
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://no-expire.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        $monitor = new MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => MonitorMetadata::STATUS_OK,
            'perEntityStatus' => [
                [
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => MonitorMetadata::METADATA_OK
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataSource(): void
    {
        $config = [
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://idp.example.org',
                    'metadata-set' => 'saml20-idp-remote',
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        $monitor = new MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => MonitorMetadata::STATUS_OK,
            'perEntityStatus' => [
                [
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => MonitorMetadata::METADATA_OK
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataValidFor(): void
    {
        $config = [
            'validFor' => 'P1D',
            'entitiesToCheck' => [
                [
                    'entityid' => 'https://expiring.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = Configuration::loadFromArray($config);
        $monitor = new MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => MonitorMetadata::STATUS_OK,
            'perEntityStatus' => [
                [
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => MonitorMetadata::METADATA_OK
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}
