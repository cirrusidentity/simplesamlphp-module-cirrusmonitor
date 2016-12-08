<?php

namespace SimpleSAML\Test\Cirrusmonitor\Test\Metadata;

class MonitorMetadataTest extends \PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        putenv('SIMPLESAMLPHP_CONFIG_DIR=' . dirname(dirname(__DIR__)) . '/config');
    }


    /**
     * Place holder test to ensure metadata test files can be loaded
     */
    public function testSanity()
    {
        $config = array(
            'validFor' => 'P5D', //Metadata that will expire in less than this time will be an alert. See http://php.net/manual/en/dateinterval.createfromdatestring.php
            'some-entityId' => array(
                'source' => 'saml20-sp-remote',
                'otherConfigOption' => 'abc'
            ),
            'entityId2' => array(),
        );
        //isset($config['some-entityId']['source'])
        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        $monitorable = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);

        $result = $monitorable->performCheck();

        $this->assertEquals(1576384259, $result);
    }

    /**
     * Test null entityIDs to check
     */
    public function testNoEntityIDsToCheck()
    {
        $config = array();
        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        $monitorable = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);

        $this->assertNull($monitorable->getEntityIdsToCheck());
    }

    /**
     * Test entityIDs to check
     */
    public function testEntityIDsToCheck()
    {
        $entityIDsToCheck = array('entity1.example.org', 'entity2.example.org');
        $config = array(
            'entityIDsToCheck' => $entityIDsToCheck
        );
        $configuration = \SimpleSAML_Configuration::loadFromArray($config);
        $monitorable = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($configuration);

        $this->assertEquals($entityIDsToCheck, $monitorable->getEntityIDsToCheck());
    }

}