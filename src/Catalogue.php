<?php

declare(strict_types=1);

namespace Acme;

interface Catalogue
{
    /**
     * @throws UnknownProductException
     */
    public function get(string $code): Product;

    /**
     * @return list<Product>
     */
    public function all(): array;
}
