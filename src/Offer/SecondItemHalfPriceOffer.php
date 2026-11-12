<?php

declare(strict_types=1);

namespace Acme\Offer;

use Acme\Discount;
use Acme\Money;
use Acme\Product;
use InvalidArgumentException;

final readonly class SecondItemHalfPriceOffer implements Offer
{
    private string $productCode;

    private string $label;

    public function __construct(string $productCode, string $description)
    {
        $productCode = strtoupper(trim($productCode));
        $description = trim($description);

        if ($productCode === '') {
            throw new InvalidArgumentException('An offer needs a product code.');
        }

        if ($description === '') {
            throw new InvalidArgumentException('An offer needs a description.');
        }

        $this->productCode = $productCode;
        $this->label = $description;
    }

    public function description(): string
    {
        return $this->label;
    }

    public function apply(array $items): Discount
    {
        $matching = [];

        foreach ($items as $item) {
            if (!$item instanceof Product) {
                throw new InvalidArgumentException('Offers can only be applied to products.');
            }

            if ($item->code === $this->productCode) {
                $matching[] = $item;
            }
        }

        // One half-price widget per pair. Three reds discount one, four discount two.
        $pairs = intdiv(count($matching), 2);

        if ($pairs === 0) {
            return new Discount($this->label, Money::zero());
        }

        // If the same code was added at two prices, the cheaper one is half price.
        usort(
            $matching,
            static fn (Product $left, Product $right): int => $left->price->cents() <=> $right->price->cents(),
        );

        $discount = Money::zero();

        for ($index = 0; $index < $pairs; $index++) {
            $price = $matching[$index]->price->cents();
            // Drop the half cent. $32.95 / 2 is charged as $16.47, a $16.48 discount.
            $discount = $discount->plus(Money::fromCents($price - intdiv($price, 2)));
        }

        return new Discount($this->label, $discount);
    }
}
