<?php

declare(strict_types=1);

namespace Acme;

use RuntimeException;

final class UnknownProductException extends RuntimeException
{
    public function __construct(public readonly string $productCode)
    {
        parent::__construct("Unknown product code: {$productCode}");
    }
}
