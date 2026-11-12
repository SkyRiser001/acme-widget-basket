<?php

declare(strict_types=1);

namespace Acme\Delivery;

use Acme\Money;
use InvalidArgumentException;

// Used while the goods total is under `below`. Null is the last band, which is free delivery.
final readonly class DeliveryTier
{
    public function __construct(
        public ?Money $below,
        public Money $charge,
    ) {
        if ($below !== null && $below->isZero()) {
            throw new InvalidArgumentException('A delivery threshold must be greater than zero.');
        }
    }
}
