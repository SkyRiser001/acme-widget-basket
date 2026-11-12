<?php

declare(strict_types=1);

namespace Acme;

use LogicException;

final readonly class BasketSummary
{
    /**
     * @param list<BasketLine> $lines
     * @param list<Discount> $discounts
     */
    public function __construct(
        public array $lines,
        public Money $subtotal,
        public array $discounts,
        public Money $discountTotal,
        public Money $goodsTotal,
        public Money $delivery,
        public Money $total,
    ) {
        if ($subtotal->cents() - $discountTotal->cents() !== $goodsTotal->cents()) {
            throw new LogicException('The goods total must equal the subtotal minus the discounts.');
        }

        if (!$goodsTotal->plus($delivery)->equals($total)) {
            throw new LogicException('The basket total must equal the goods plus delivery.');
        }
    }

    public static function empty(): self
    {
        $zero = Money::zero();

        return new self([], $zero, [], $zero, $zero, $zero, $zero);
    }

    public function toText(): string
    {
        if ($this->lines === []) {
            return "Basket is empty" . PHP_EOL . self::row('Total', Money::zero()->format());
        }

        $rows = [];

        foreach ($this->lines as $line) {
            $rows[] = self::row(
                sprintf('%s (%s) x %d', $line->name, $line->code, $line->quantity),
                $line->lineTotal->format(),
            );
        }

        $rows[] = self::row('Subtotal', $this->subtotal->format());

        foreach ($this->discounts as $discount) {
            $rows[] = $discount->description;
            $rows[] = self::row('Discount', '-' . $discount->amount->format());
        }

        $rows[] = self::row('Delivery', $this->delivery->format());
        $rows[] = self::row('Total', $this->total->format());

        return implode(PHP_EOL, $rows) . PHP_EOL;
    }

    private static function row(string $label, string $amount): string
    {
        return sprintf('%-48s %8s', $label, $amount);
    }
}
