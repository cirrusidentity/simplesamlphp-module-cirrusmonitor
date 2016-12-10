<?php

namespace SimpleSAML\Test\Cirrusmonitor\Test\Metadata;

class MonitorMetadataTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        putenv('SIMPLESAMLPHP_CONFIG_DIR=' . dirname(dirname(__DIR__)) . '/config');
    }

    /**
     * Test null entityIDs to check
     */
    public function testNoEntityIDsToCheck()
    {
        $config = array();
        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        $monitor = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);

        $this->assertNull($monitor->getEntityIdsToCheck());
    }

    /**
     * Test entityIDs to check
     */
    public function testEntityIDsToCheck()
    {
        $entityIDsToCheck = [
            0 => [
                'entityid' => 'entity1.example.org',
                'metadata-set' => 'saml20-sp-remote',
            ],
            1 => [
                'entityid' => 'entity2.example.org',
                'metadata-set' => 'saml20-sp-remote',
            ]
        ];
        $config = array(
            'entityIDsToCheck' => $entityIDsToCheck
        );
        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        $monitor = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);

        $this->assertEquals($entityIDsToCheck, $monitor->getEntityIDsToCheck());
    }

    public function testPerformCheck()
    {
        $config = [
            'entityIDsToCheck' => [
                0 => [
                    'entityid' => 'https://example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ],
                1 => [
                    'entityid' => 'https://not-found.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        $monitor = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::STATUS_NOT_OK,
            'perEntityStatus' => [
                0 => [
                    'entityid' => $config['entityIDsToCheck'][0]['entityid'],
                    'metadata-set' => $config['entityIDsToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_OK
                ],
                1 => [
                    'entityid' => $config['entityIDsToCheck'][1]['entityid'],
                    'metadata-set' => $config['entityIDsToCheck'][1]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_NOT_FOUND
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataOk()
    {
        $config = [
            'entityIDsToCheck' => [
                0 => [
                    'entityid' => 'https://example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        $monitor = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::STATUS_OK,
            'perEntityStatus' => [
                0 => [
                    'entityid' => $config['entityIDsToCheck'][0]['entityid'],
                    'metadata-set' => $config['entityIDsToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_OK
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataNotFound()
    {
        $config = [
            'entityIDsToCheck' => [
                0 => [
                    'entityid' => 'https://not-found.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        $monitor = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::STATUS_NOT_OK,
            'perEntityStatus' => [
                0 => [
                    'entityid' => $config['entityIDsToCheck'][0]['entityid'],
                    'metadata-set' => $config['entityIDsToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_NOT_FOUND
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataExpired()
    {
        $config = [
            'entityIDsToCheck' => [
                0 => [
                    'entityid' => 'https://expired.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        $monitor = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::STATUS_NOT_OK,
            'perEntityStatus' => [
                0 => [
                    'entityid' => $config['entityIDsToCheck'][0]['entityid'],
                    'metadata-set' => $config['entityIDsToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_EXPIRED
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataExpiring()
    {
        $config = [
            'entityIDsToCheck' => [
                0 => [
                    'entityid' => 'https://expiring.example.org',
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        $monitor = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);

        $result = $monitor->performCheck();

        $expected = [
            'overallStatus' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::STATUS_NOT_OK,
            'perEntityStatus' => [
                0 => [
                    'entityid' => $config['entityIDsToCheck'][0]['entityid'],
                    'metadata-set' => $config['entityIDsToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_EXPIRING
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}
