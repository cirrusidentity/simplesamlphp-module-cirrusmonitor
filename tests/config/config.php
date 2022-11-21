<?php

/**
 * Minimal configuration needed for testing
 */
$config = array(
    // It is easier to test with phps embedded webserver if run from root path
    'baseurlpath' => '/',
    'logging.level' => SimpleSAML\Logger::DEBUG,
    'logging.handler' => 'errorlog',
    'metadata.sources' => array(
        array('type' => 'flatfile', 'directory' => dirname(__DIR__) . '/metadata'),
    ),
    'tempdir' => '/tmp/simplesaml',
    'loggingdir' => sys_get_temp_dir(),
    'module.enable' => ['cirrusmonitor' => true],
    'secretsalt' => 'salt'

);
