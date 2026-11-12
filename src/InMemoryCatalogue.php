<?php

declare(strict_types=1);

namespace Acme;

use InvalidArgumentException;

final readonly class InMemoryCatalogue implements Catalogue
{
    /** @var array<string, Product> */
    private array $products;

    /**
     * @param list<Product> $products
     */
    public function __construct(array $products)
    {
        if ($products === []) {
            throw new InvalidArgumentException('A catalogue needs at least one product.');
        }

        $indexed = [];

        foreach ($products as $product) {
            if (!$product instanceof Product) {
                throw new InvalidArgumentException('A catalogue can only contain products.');
            }

            if (isset($indexed[$product->code])) {
                throw new InvalidArgumentException("Duplicate product code: {$product->code}");
            }

            $indexed[$product->code] = $product;
        }

        $this->products = $indexed;
    }

    public function get(string $code): Product
    {
        $code = strtoupper(trim($code));

        if ($code === '' || !isset($this->products[$code])) {
            throw new UnknownProductException($code === '' ? '(blank)' : $code);
        }

        return $this->products[$code];
    }

    public function all(): array
    {
        return array_values($this->products);
    }
}
