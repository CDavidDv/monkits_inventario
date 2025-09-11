<?php

/**
 * This file is part of the ramsey/uuid library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Uuid;

use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;

/**
 * Returns a version 1 (Gregorian time) UUID as a string
 *
 * @param Hexadecimal|int|string|null $node A 48-bit number representing the hardware address
 * @param int|null $clockSeq A 14-bit number used to help avoid duplicates
 *
 * @return non-empty-string Version 1 UUID as a string
 */
function v1($node = null, ?int $clockSeq = null): string
{
    return Uuid::uuid1($node, $clockSeq)->toString();
}

/**
 * Returns a version 4 (random) UUID as a string
 *
 * @return non-empty-string Version 4 UUID as a string
 */
function v4(): string
{
    return Uuid::uuid4()->toString();
}

/**
 * Returns a version 3 (name-based) UUID based on the MD5 hash
 *
 * @param string|UuidInterface $ns The namespace (must be a valid UUID)
 * @param string $name The name to use for creating a UUID
 *
 * @return non-empty-string Version 3 UUID as a string
 */
function v3($ns, string $name): string
{
    return Uuid::uuid3($ns, $name)->toString();
}

/**
 * Returns a version 5 (name-based) UUID based on the SHA-1 hash
 *
 * @param string|UuidInterface $ns The namespace (must be a valid UUID) 
 * @param string $name The name to use for creating a UUID
 *
 * @return non-empty-string Version 5 UUID as a string
 */
function v5($ns, string $name): string
{
    return Uuid::uuid5($ns, $name)->toString();
}