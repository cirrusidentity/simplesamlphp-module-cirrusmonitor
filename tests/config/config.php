<?php

/**
 * Minimal configuration needed for testing
 */
$config = array(
    'debug' => true,
    'logging.level' => SimpleSAML_Logger::DEBUG,
    'logging.handler' => 'errorlog',
    'metadata.sources' => array(
        array('type' => 'flatfile', 'directory' => dirname(__DIR__) . '/metadata'),
    ),
);
