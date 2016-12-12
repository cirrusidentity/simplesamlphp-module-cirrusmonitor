<?php

namespace SimpleSAML\Test\Cirrusmonitor\Test\Metadata;

class MonitorMetadataTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        putenv('SIMPLESAMLPHP_CONFIG_DIR=' . dirname(dirname(__DIR__)) . '/config');
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMissingEntityIDsToCheck()
    {
        $config = array();
        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMissingEntityID()
    {
        $config = [
            'entitiesToCheck' => [
                0 => [
                    'metadata-set' => 'saml20-sp-remote',
                ]
            ]
        ];

        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNoStringEntityID()
    {
        $config = [
            'entitiesToCheck' => [
                0 => [
                    'entityid' => 1,
                    'metadata-set' => 'saml20-sp-remote'
                ]
            ]
        ];

        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMissingMetadataSource()
    {
        $config = [
            'entitiesToCheck' => [
                0 => [
                    'entityid' => 'https://idp.example.org'
                ]
            ]
        ];

        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNoStringMetadataSource()
    {
        $config = [
            'entitiesToCheck' => [
                0 => [
                    'entityid' => 'https://idp.example.org',
                    'metadata-set' => 1
                ]
            ]
        ];

        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);
    }

    public function testPerformCheck()
    {
        $config = [
            'entitiesToCheck' => [
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
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_OK
                ],
                1 => [
                    'entityid' => $config['entitiesToCheck'][1]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][1]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_NOT_FOUND
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataOk()
    {
        $config = [
            'entitiesToCheck' => [
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
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_OK
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataNotFound()
    {
        $config = [
            'entitiesToCheck' => [
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
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_NOT_FOUND
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataExpired()
    {
        $config = [
            'entitiesToCheck' => [
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
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_EXPIRED
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataExpiring()
    {
        $config = [
            'entitiesToCheck' => [
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
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_EXPIRING
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataNoExpire()
    {
        $config = [
            'entitiesToCheck' => [
                0 => [
                    'entityid' => 'https://no-expire.example.org',
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
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_OK
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataSource()
    {
        $config = [
            'entitiesToCheck' => [
                0 => [
                    'entityid' => 'https://idp.example.org',
                    'metadata-set' => 'saml20-idp-remote',
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
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_OK
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMetadataValidFor()
    {
        $config = [
            'validFor' => 'P1D',
            'entitiesToCheck' => [
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
            'overallStatus' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::STATUS_OK,
            'perEntityStatus' => [
                0 => [
                    'entityid' => $config['entitiesToCheck'][0]['entityid'],
                    'metadata-set' => $config['entitiesToCheck'][0]['metadata-set'],
                    'status' => \sspmod_cirrusmonitor_metadata_MonitorMetadata::METADATA_OK
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

}
