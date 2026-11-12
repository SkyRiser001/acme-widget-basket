<?php

declare(strict_types=1);

namespace Acme;

final readonly class BasketLine
{
    public function __construct(
        public string $code,
        public string $name,
        public int $quantity,
        public Money $unitPrice,
        public Money $lineTotal,
    ) {
    }
}
