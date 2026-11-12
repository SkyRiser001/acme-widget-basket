<?php

declare(strict_types=1);

namespace Acme;

use InvalidArgumentException;

final readonly class Discount
{
    public string $description;

    public function __construct(string $description, public Money $amount)
    {
        $description = trim($description);

        if ($description === '') {
            throw new InvalidArgumentException('A discount needs a description.');
        }

        $this->description = $description;
    }
}
