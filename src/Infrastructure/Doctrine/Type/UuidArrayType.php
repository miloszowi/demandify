<?php

declare(strict_types=1);

namespace Demandify\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Array of anything else than string will lose its type upon doctrine hydrating the object so that's why UuidArrayType exists.
 *
 * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/4.2/reference/types.html#simple-array
 */
class UuidArrayType extends Type
{
    public const string UUID_ARRAY = 'uuid_array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function getName(): string
    {
        return self::UUID_ARRAY;
    }

    /**
     * @param mixed $value
     *
     * @return null|UuidInterface[]
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?array
    {
        if (null === $value) {
            return null;
        }

        $uuids = json_decode($value, true);

        return array_map(static fn (string $uuid) => Uuid::fromString($uuid), $uuids);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        return json_encode(array_map(static fn (UuidInterface $uuid) => $uuid->toString(), $value));
    }
}
