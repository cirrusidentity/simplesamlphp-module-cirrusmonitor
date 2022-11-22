<?php

namespace SimpleSAML\Module\cirrusmonitor\metadata;

use Exception;
use InvalidArgumentException;
use SimpleSAML\Configuration;
use SimpleSAML\Error\MetadataNotFound;
use SimpleSAML\Metadata\MetaDataStorageHandler;
use SimpleSAML\Module\cirrusmonitor\Monitorable;
use SimpleSAML\Utils\Time;

/**
 * Checks metadata and returns whether the metadata is ok, expired, will soon expire, or is unable to be found.
 */
class MonitorMetadata implements Monitorable
{
    /**
     * @var string Indicates that metadata is expired.
     */
    public const METADATA_EXPIRED = 'expired';

    /**
     * @var string Indicates that metadata will soon expire.
     */
    public const METADATA_EXPIRING = 'expiring';

    /**
     * @var string Indicates that metadata can not be found.
     */
    public const METADATA_NOT_FOUND = 'not-found';

    /**
     * @var string Indicates that metadata is not expired nor will soon expire.
     */
    public const METADATA_OK = 'ok';

    /**
     * @var string Indicates that all checked metadata is not expired nor will soon expire.
     */
    public const STATUS_OK = 'ok';

    /**
     * @var string Indicates that all or some checked metadata is either expired, will soon expire, or is not found.
     */
    public const STATUS_NOT_OK = 'not-ok';

    /**
     * @var string Default duration during which metadata will be considered soon-to-expire, i.e. 'expiring'
     */
    public const DEFAULT_VALID_FOR = 'P5D';

    /**
     * @var Configuration The configuration.
     */
    private Configuration $configuration;

    /**
     * @var CheckableMetadataEntity[] Entities whose metadata will be checked.
     */
    private array $entitiesToCheck = [];

    /**
     * @var int Timestamp before which metadata is considered to be 'expiring'.
     */
    private int $validFor = 0;

    /**
     * Initializes the Metadata Monitor.
     *
     * The configuration array must have an 'entitiesToCheck' array, whose elements are themselves arrays which must
     * contain both 'entityid' and 'metadata-set'.
     *
     * The configuration array may have a 'validFor' duration which defines the interval during which metadata is
     * considered to be 'expiring'. If not specified, the default value of 'validFor' will be used.
     *
     * @param Configuration $config The configuration for this output.
     *  $config = [
     *      'validFor' => 'P5D',
     *      'entitiesToCheck' => [
     *          0 => [
     *              'entityid' => 'sp.example.org',
     *              'metadata-set' => 'saml20-sp-remote'
     *          ],
     *          1 => [
     *              'entityid' => 'idp.example.org',
     *              'metadata-set' => 'saml20-idp-remote'
     *          ]
     *  ]
     * @throws InvalidArgumentException|Exception if config is missing required options
     *@see sspmod_cirrusmonitor_metadata_MonitorMetadata::DEFAULT_VALID_FOR
     *
     */
    public function __construct(Configuration $config)
    {
        // save the configuration
        $this->configuration = $config;

        // validate config
        if (!$config->hasValue('entitiesToCheck')) {
            throw new InvalidArgumentException("Missing required 'entitiesToCheck' array");
        }
        /** @var array $entityToCheck */
        foreach ($config->getArray('entitiesToCheck') as $entityToCheck) {
            $this->entitiesToCheck[] = CheckableMetadataEntity::fromArray($entityToCheck);
        }

        // convert validFor duration to a timestamp
        $configValidFor = $this->configuration->getOptionalString('validFor', self::DEFAULT_VALID_FOR);
        $this->validFor = (new Time())->parseDuration($configValidFor);
    }

    /**
     * Check all entities and return an array indicating whether all entities are ok or not as well as the status of
     * each entity.
     *
     * @return array Returns the status of all entities as well as each entity.
     *      return = [
     *          'overallStatus' => 'ok',
     *          'perEntityStatus' => [
     *              0 => [
     *                  'entityid' => 'sp.example.org',
     *                  'metadata-set' => 'saml20-sp-remote',
     *                  'status' => 'ok'
     *              ]
     *              1 => [
     *                  'entityid' => 'idp.example.org',
     *                  'metadata-set' => 'saml20-idp-remote',
     *                  'status' => 'ok'
     *              ]
     *          ]
     *      ]
     */
    public function performCheck(): array
    {
        $overallStatus = self::STATUS_OK;
        $perEntity = array();

        foreach ($this->entitiesToCheck as $entityToCheck) {
            $perEntityResult = $this->checkEntity(
                $entityToCheck->getEntityId(),
                $entityToCheck->getMetadataSet(),
                $entityToCheck->getValidFor() ?? $this->validFor
            );

            $perEntity[] = $perEntityResult;

            if ($perEntityResult['status'] !== self::METADATA_OK) {
                $overallStatus = self::STATUS_NOT_OK;
            }
        }

        return [
            'overallStatus' => $overallStatus,
            'perEntityStatus' => $perEntity
        ];
    }

    /**
     * Check an entity and return whether metadata is ok or not.
     *
     * @param $entityId string The entityID to check
     * @param $metadataSet string The metadata source to check
     * @param int $validFor Seconds the metadata should be valid for
     *
     * @return array Returns the status of an entity.
     *      return = [
     *          'entityid' => 'sp.example.org',
     *          'metadata-set' => 'saml20-sp-remote',
     *          'status' => 'ok'
     *      ]
     */
    private function checkEntity(string $entityId, string $metadataSet, int $validFor): array
    {
        $metadataHandler = MetaDataStorageHandler::getMetadataHandler();

        $status = self::METADATA_OK;
        try {
            $metadata = $metadataHandler->getMetaDataConfig($entityId, $metadataSet);

            /** @var ?int $expire */
            $expire = $metadata->getOptionalInteger('expire', null);
            if (!is_null($expire) && $expire < $validFor) {
                $status = self::METADATA_EXPIRING;
            }
        } catch (MetadataNotFound) {
            $status = self::METADATA_NOT_FOUND;
        } catch (Exception) {
            $status = self::METADATA_EXPIRED;
        }

        return [
            'entityid' => $entityId,
            'metadata-set' => $metadataSet,
            'status' => $status
        ];
    }
}
