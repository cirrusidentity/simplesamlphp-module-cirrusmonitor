<?php


/**
 * Used for www testing
 */
$config = array(
    'metadata' => [
        # Ensure metadata is valid for at least 6 more days.
        'validFor' => 'P6D',
        'entitiesToCheck' => [
            [
                'entityid' => 'https://example.org',
                'metadata-set' => 'saml20-sp-remote',
            ]
        ],
    ]
);