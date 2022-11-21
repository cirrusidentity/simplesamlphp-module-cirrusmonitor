<?php
use SimpleSAML\Utils\Time;
$time = new Time();
$metadata['https://idp.example.org'] = [
    'entityid' => 'https://idp.example.org/shibboleth',
    'metadata-set' => 'saml20-idp-remote',
    'expire' => $time->parseDuration("P7D"),
];
