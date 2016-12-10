<?php

class sspmod_cirrusmonitor_metadata_MonitorMetadata implements sspmod_cirrusmonitor_Monitorable
{

    /**
     * The string used to identify expired metadata.
     */
    const METADATA_EXPIRED = 'expired';

    /**
     * The string used to identify expiring metadata.
     */
    const METADATA_EXPIRING = 'expiring';

    /**
     * The string used to identify unknown metadata.
     */
    const METADATA_NOT_FOUND = 'not-found';

    /**
     * The string used to identify valid metadata.
     */
    const METADATA_OK = 'ok';

    /**
     * The string used ...
     */
    const STATUS_OK = 'ok';

    /**
     * The string used ...
     */
    const STATUS_NOT_OK = 'not-ok';

    /**
     * Default ...
     */
    const DEFAULT_VALID_FOR = 'P5D';

    /**
     * @var SimpleSAML_Configuration
     */
    private $configuration = null;

    /**
     * @var array entityIDs to check
     */
    private $entityIDsToCheck = array();

    /**
     * Initializes the Metadata Monitor
     *
     * @param SimpleSAML_Configuration $config The configuration for this output.
     */
    public function __construct(\SimpleSAML_Configuration $config)
    {
        $this->configuration = $config;

        $this->entityIDsToCheck = $config->getArray('entityIDsToCheck', null);
    }

    /**
     * Get the entityIDs to check
     * @return array|null
     */
    public function getEntityIDsToCheck()
    {
        return $this->entityIDsToCheck;
    }

    public function getValidFor()
    {
        $validFor = $this->configuration->getString('validFor', self::DEFAULT_VALID_FOR);
        return \SimpleSAML\Utils\Time::parseDuration($validFor);
    }

    public function performCheck()
    {
        $overallStatus = self::STATUS_OK;
        $perEntity = array();

        foreach ($this->getEntityIDsToCheck() as $entityIDtoCheck) {
            $entityId = $entityIDtoCheck['entityid'];
            $metadataSet = $entityIDtoCheck['metadata-set'];

            $perEntityResult = $this->checkEntity($entityId, $metadataSet);

            array_push($perEntity, $perEntityResult);

            if ($perEntityResult['status'] !== self::METADATA_OK) {
                $overallStatus = self::STATUS_NOT_OK;
            }
        }

        return [
            'overallStatus' => $overallStatus,
            'perEntityStatus' => $perEntity
        ];
    }

    function checkEntity($entityId, $metadataSet)
    {
        $metadataHandler = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();

        $status = self::METADATA_OK;

        try {
            $metadata = $metadataHandler->getMetaDataConfig($entityId, $metadataSet);

            $expire = $metadata->getInteger('expire');

            if ($expire < $this->getValidFor()) {
                $status = self::METADATA_EXPIRING;
            }

        } catch (SimpleSAML_Error_MetadataNotFound $e) {
            $status = self::METADATA_NOT_FOUND;
        } catch (Exception $e) {
            $status = self::METADATA_EXPIRED;
        }

        return [
            'entityid' => $entityId,
            'metadata-set' => $metadataSet,
            'status' => $status
        ];
    }
}
