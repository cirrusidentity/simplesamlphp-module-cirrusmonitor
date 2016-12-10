<?php

$metadata['https://example.org'] = array (
    'entityid' => 'https://example.org',
    'metadata-set' => 'saml20-sp-remote',
    'expire' => 1576384259,
    'AssertionConsumerService' =>
        array (
            0 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'Location' => 'https://cirrusidentity.freshdesk.com/login/saml',
                    'index' => 1,
                ),
        ),
    'SingleLogoutService' =>
        array (
        ),
);

$metadata['https://expired.example.org'] = array (
    'entityid' => 'https://expired.example.org',
    'metadata-set' => 'saml20-sp-remote',
    'expire' => \SimpleSAML\Utils\Time::parseDuration("-P1D"),
    'AssertionConsumerService' =>
        array (
            0 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'Location' => 'https://cirrusidentity.freshdesk.com/login/saml',
                    'index' => 1,
                ),
        ),
    'SingleLogoutService' =>
        array (
        ),
);

$metadata['https://expiring.example.org'] = array (
    'entityid' => 'https://expiring.example.org',
    'metadata-set' => 'saml20-sp-remote',
    'expire' => \SimpleSAML\Utils\Time::parseDuration("P3D"),
    'AssertionConsumerService' =>
        array (
            0 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'Location' => 'https://cirrusidentity.freshdesk.com/login/saml',
                    'index' => 1,
                ),
        ),
    'SingleLogoutService' =>
        array (
        ),
);