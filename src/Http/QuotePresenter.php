<?php

declare(strict_types=1);

namespace Acme\Http;

use Acme\BasketLine;
use Acme\BasketSummary;
use Acme\Delivery\DeliveryTier;
use Acme\Discount;
use Acme\Product;
use Acme\SalesConfig;

final readonly class QuotePresenter
{
    /**
     * @return array<string, mixed>
     */
    public function catalogue(SalesConfig $config): array
    {
        return [
            'products' => array_map(
                static fn (Product $product): array => [
                    'code' => $product->code,
                    'name' => $product->name,
                    'price' => $product->price->format(),
                    'priceCents' => $product->price->cents(),
                ],
                $config->catalogue->all(),
            ),
            'offers' => $config->offers->descriptions(),
            'deliveryTiers' => array_map(
                static fn (DeliveryTier $tier): array => [
                    'below' => $tier->below?->format(),
                    'belowCents' => $tier->below?->cents(),
                    'charge' => $tier->charge->isZero() ? 'Free' : $tier->charge->format(),
                    'chargeCents' => $tier->charge->cents(),
                ],
                $config->deliveryTiers,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function quote(BasketSummary $summary): array
    {
        return [
            'items' => array_map(
                static fn (BasketLine $line): array => [
                    'code' => $line->code,
                    'name' => $line->name,
                    'quantity' => $line->quantity,
                    'unitPrice' => $line->unitPrice->format(),
                    'lineTotal' => $line->lineTotal->format(),
                ],
                $summary->lines,
            ),
            'subtotal' => $summary->subtotal->format(),
            'subtotalCents' => $summary->subtotal->cents(),
            'discounts' => array_map(
                static fn (Discount $discount): array => [
                    'description' => $discount->description,
                    'amount' => $discount->amount->format(),
                    'amountCents' => $discount->amount->cents(),
                ],
                $summary->discounts,
            ),
            'discountTotal' => $summary->discountTotal->format(),
            'discountTotalCents' => $summary->discountTotal->cents(),
            'goodsTotalCents' => $summary->goodsTotal->cents(),
            'delivery' => $summary->delivery->format(),
            'deliveryCents' => $summary->delivery->cents(),
            'total' => $summary->total->format(),
            'totalCents' => $summary->total->cents(),
        ];
    }
}
