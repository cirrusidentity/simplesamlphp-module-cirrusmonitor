<?php

namespace SimpleSAML\Module\cirrusmonitor\metadata;

use InvalidArgumentException;
use SimpleSAML\Utils\Time;

class CheckableMetadataEntity
{
    /**
     * @param string $entityId
     * @param string $metadataSet
     * @param int|null $validFor
     */
    public function __construct(protected string $entityId, protected string $metadataSet, protected ?int $validFor)
    {
    }

    public static function fromArray(array $config): CheckableMetadataEntity
    {
        if (!isset($config['entityid'])) {
            throw new InvalidArgumentException("Missing required 'entityid'");
        }
        if (!isset($config['metadata-set'])) {
            throw new InvalidArgumentException("Missing required 'metadata-set'");
        }
        if (!is_string($config['entityid'])) {
            throw new InvalidArgumentException('entityid is not a string');
        }
        if (!is_string($config['metadata-set'])) {
            throw new InvalidArgumentException('metadata-set is not a string');
        }
        /** @var mixed $validForDuration */
        $validForDuration = $config['validFor'] ?? null;
        $validForInt = null;
        if (is_string($validForDuration)) {
            $validForInt = (new Time())->parseDuration($validForDuration);
        }

        return new CheckableMetadataEntity($config['entityid'], $config['metadata-set'], $validForInt);
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getMetadataSet(): string
    {
        return $this->metadataSet;
    }

    /**
     * @return int|null
     */
    public function getValidFor(): ?int
    {
        return $this->validFor;
    }
}
