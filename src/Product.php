<?php

declare(strict_types=1);

namespace Acme;

use InvalidArgumentException;

final readonly class Product
{
    public string $code;

    public string $name;

    public function __construct(string $code, string $name, public Money $price)
    {
        $code = strtoupper(trim($code));
        $name = trim($name);

        if ($code === '' || $name === '') {
            throw new InvalidArgumentException('A product needs a code and a name.');
        }

        $this->code = $code;
        $this->name = $name;
    }
}
