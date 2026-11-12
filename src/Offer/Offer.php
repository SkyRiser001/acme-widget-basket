<?php

declare(strict_types=1);

namespace Acme\Offer;

use Acme\Discount;

interface Offer
{
    public function description(): string;

    /**
     * @param list<\Acme\Product> $items
     */
    public function apply(array $items): Discount;
}
