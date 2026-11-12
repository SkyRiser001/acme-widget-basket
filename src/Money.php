<?php

declare(strict_types=1);

namespace Acme;

use InvalidArgumentException;

// Whole cents. A float cannot hold 32.95 exactly, and the sample totals depend on it.
final readonly class Money
{
    private function __construct(private int $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function dollars(string $amount): self
    {
        if (!preg_match('/^(?<dollars>\d+)\.(?<cents>\d{2})$/', $amount, $matches)) {
            throw new InvalidArgumentException(
                "Money must look like 32.95, got \"{$amount}\".",
            );
        }

        return new self(((int) $matches['dollars']) * 100 + (int) $matches['cents']);
    }

    public function cents(): int
    {
        return $this->amount;
    }

    public function plus(self $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function minus(self $other): self
    {
        if ($other->amount > $this->amount) {
            throw new InvalidArgumentException('Cannot subtract more money than is available.');
        }

        return new self($this->amount - $other->amount);
    }

    public function multipliedBy(int $quantity): self
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('A quantity cannot be negative.');
        }

        return new self($this->amount * $quantity);
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function isLessThan(self $other): bool
    {
        return $this->amount < $other->amount;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount;
    }

    public function format(): string
    {
        return sprintf('$%d.%02d', intdiv($this->amount, 100), $this->amount % 100);
    }
}
