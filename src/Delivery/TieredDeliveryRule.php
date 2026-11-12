<?php

declare(strict_types=1);

namespace Acme\Delivery;

use Acme\Money;
use InvalidArgumentException;
use LogicException;

final readonly class TieredDeliveryRule implements DeliveryChargeRule
{
    /** @var list<DeliveryTier> */
    private array $tiers;

    public function __construct(DeliveryTier ...$tiers)
    {
        if ($tiers === []) {
            throw new InvalidArgumentException('At least one delivery tier is required.');
        }

        $lastIndex = array_key_last($tiers);
        $previousThreshold = null;

        foreach ($tiers as $index => $tier) {
            $isLast = $index === $lastIndex;

            if ($tier->below === null && !$isLast) {
                throw new InvalidArgumentException('Only the last delivery tier can be open-ended.');
            }

            if ($tier->below !== null && $previousThreshold !== null && !$previousThreshold->isLessThan($tier->below)) {
                throw new InvalidArgumentException(
                    'Delivery tiers must be listed from the lowest threshold upwards.',
                );
            }

            $previousThreshold = $tier->below;
        }

        if ($tiers[$lastIndex]->below !== null) {
            throw new InvalidArgumentException('The last delivery tier must cover every remaining total.');
        }

        $this->tiers = array_values($tiers);
    }

    public function chargeFor(Money $goodsTotal): Money
    {
        foreach ($this->tiers as $tier) {
            if ($tier->below === null || $goodsTotal->isLessThan($tier->below)) {
                return $tier->charge;
            }
        }

        throw new LogicException('No delivery tier matched this total.');
    }

    /**
     * @return list<DeliveryTier>
     */
    public function tiers(): array
    {
        return $this->tiers;
    }
}
