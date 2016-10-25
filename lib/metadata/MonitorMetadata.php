<?php

class sspmod_cirrusmonitor_metadata_MonitorMetadata implements sspmod_cirrusmonitor_Monitorable
{

    public function __construct($config)
    {
    }

    public function performCheck()
    {
        $metadataHandler = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
        // The entities to check should come from the config file
        $entityId = 'https://example.org';
        // type is: SimpleSAML_Configuration. The set ('saml20-idp-remote') should also come from the config file
        $metadata = $metadataHandler->getMetaDataConfig($entityId, 'saml20-sp-remote');

        // Note: metadata handler will throw an exception if the metadata is expired.
        // 'Metadata for the entity [https://example.org] expired 1051383 seconds ago.'
        //

        // Do some checking on the metadata, and build a response

        // for the moment return expire. note this might not exist.
        return $metadata->getInteger('expire');
    }
}
