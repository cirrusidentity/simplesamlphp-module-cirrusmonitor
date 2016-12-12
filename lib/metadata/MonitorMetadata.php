<?php

/**
 * Checks metadata and returns whether the metadata is ok, expired, will soon expire, or is unable to be found.
 */
class sspmod_cirrusmonitor_metadata_MonitorMetadata implements sspmod_cirrusmonitor_Monitorable
{

    /**
     * @var string Indicates that metadata is expired.
     */
    const METADATA_EXPIRED = 'expired';

    /**
     * @var string Indicates that metadata will soon expire.
     */
    const METADATA_EXPIRING = 'expiring';

    /**
     * @var string Indicates that metadata can not be found.
     */
    const METADATA_NOT_FOUND = 'not-found';

    /**
     * @var string Indicates that metadata is not expired nor will soon expire.
     */
    const METADATA_OK = 'ok';

    /**
     * @var string Indicates that all checked metadata is not expired nor will soon expire.
     */
    const STATUS_OK = 'ok';

    /**
     * @var string Indicates that all or some checked metadata is either expired, will soon expire, or is not found.
     */
    const STATUS_NOT_OK = 'not-ok';

    /**
     * @var string Default duration during which metadata will be considered soon-to-expire, i.e. 'expiring'
     */
    const DEFAULT_VALID_FOR = 'P5D';

    /**
     * @var SimpleSAML_Configuration The configuration.
     */
    private $configuration = null;

    /**
     * @var array Entities whose metadata will be checked.
     */
    private $entitiesToCheck = array();

    /**
     * @var int Timestamp before which metadata is considered to be 'expiring'.
     */
    private $validFor = 0;

    /**
     * Initializes the Metadata Monitor.
     *
     * The configuration array must have an 'entitiesToCheck' array, whose elements are themselves arrays which must
     * contain both 'entityid' and 'metadata-set'.
     *
     * The configuration array may have a 'validFor' duration which defines the interval during which metadata is
     * considered to be 'expiring'. If not specified, the default value of 'validFor' will be used.
     *
     * @see sspmod_cirrusmonitor_metadata_MonitorMetadata::DEFAULT_VALID_FOR
     *
     * @param SimpleSAML_Configuration $config The configuration for this output.
     */
    public function __construct(\SimpleSAML_Configuration $config)
    {
        // save the configuration
        $this->configuration = $config;

        // validate config
        if (!$config->hasValue('entitiesToCheck')) {
            throw new InvalidArgumentException("Missing required 'entitiesToCheck' array");
        }
        foreach ($config->getArray('entitiesToCheck') as $entityToCheck) {
            if (!isset($entityToCheck['entityid'])) {
                throw new InvalidArgumentException("Missing required 'entityid'");
            }
            if (!isset($entityToCheck['metadata-set'])) {
                throw new InvalidArgumentException("Missing required 'metadata-set'");
            }
            if (!is_string($entityToCheck['entityid'])) {
                throw new InvalidArgumentException('entityid is not a string');
            }
            if (!is_string($entityToCheck['metadata-set'])) {
                throw new InvalidArgumentException('metadata-set is not a string');
            }
        }

        // save the entities to check
        $this->entitiesToCheck = $config->getArray('entitiesToCheck');

        // convert validFor duration to a timestamp
        $configValidFor = $this->configuration->getString('validFor', self::DEFAULT_VALID_FOR);
        $this->validFor = \SimpleSAML\Utils\Time::parseDuration($configValidFor);
    }

    /**
     * Check all entities and return an array indicating whether all entities are ok or not as well as the status of
     * each entity.
     *
     * @return array
     */
    public function performCheck()
    {
        $overallStatus = self::STATUS_OK;
        $perEntity = array();

        foreach ($this->entitiesToCheck as $entityToCheck) {
            $perEntityResult = $this->checkEntity($entityToCheck['entityid'], $entityToCheck['metadata-set']);

            array_push($perEntity, $perEntityResult);

            if ($perEntityResult['status'] !== self::METADATA_OK) {
                $overallStatus = self::STATUS_NOT_OK;
            }
        }

        return ['overallStatus' => $overallStatus,
            'perEntityStatus' => $perEntity];
    }

    /**
     * Check an entity and return whether metadata is ok or not.
     *
     * @param $entityId string The entityID to check
     * @param $metadataSet string The metadata source to check
     *
     * @return array
     */
    function checkEntity($entityId, $metadataSet)
    {
        $metadataHandler = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();

        $status = self::METADATA_OK;
        try {
            $metadata = $metadataHandler->getMetaDataConfig($entityId, $metadataSet);

            $expire = $metadata->getInteger('expire', null);

            if ($expire !== null && $expire < $this->validFor) {
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
