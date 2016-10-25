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
        $config = array();
        $monitorable = new \sspmod_cirrusmonitor_metadata_MonitorMetadata($config);

        $result = $monitorable->performCheck();

        $this->assertEquals(1576384259, $result);
    }
}