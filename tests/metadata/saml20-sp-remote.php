<?php

$metadata['https://example.org'] = [
    'entityid' => 'https://example.org',
    'metadata-set' => 'saml20-sp-remote',
    'expire' => 1576384259,
];

$metadata['https://expired.example.org'] = [
    'entityid' => 'https://expired.example.org',
    'metadata-set' => 'saml20-sp-remote',
    'expire' => \SimpleSAML\Utils\Time::parseDuration("-P1D"),
];

$metadata['https://expiring.example.org'] = [
    'entityid' => 'https://expiring.example.org',
    'metadata-set' => 'saml20-sp-remote',
    'expire' => \SimpleSAML\Utils\Time::parseDuration("P3D"),
];

$metadata['https://no-expire.example.org'] = [
    'entityid' => 'https://expiring.example.org',
    'metadata-set' => 'saml20-sp-remote'
];