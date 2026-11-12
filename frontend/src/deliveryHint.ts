import { formatCents } from "./money";
import type { DeliveryTier } from "./types";

// Copy only. The total on screen comes from the PHP quote, not from this.
export function deliveryHint(goodsTotalCents: number, tiers: DeliveryTier[]): string | null {
  if (tiers.length === 0) {
    return null;
  }

  const sorted = [...tiers].sort((left, right) => {
    if (left.belowCents === null) {
      return 1;
    }
    if (right.belowCents === null) {
      return -1;
    }
    return left.belowCents - right.belowCents;
  });

  const currentIndex = sorted.findIndex(
    (tier) => tier.belowCents === null || goodsTotalCents < tier.belowCents,
  );
  const current = sorted[currentIndex];
  const next = sorted[currentIndex + 1];

  if (!current || !next || current.belowCents === null) {
    return "Delivery is free on this order.";
  }

  if (next.chargeCents >= current.chargeCents) {
    return null;
  }

  const gap = formatCents(current.belowCents - goodsTotalCents);
  if (next.chargeCents === 0) {
    return `Add ${gap} for free delivery.`;
  }

  return `Add ${gap} to bring delivery down to ${formatCents(next.chargeCents)}.`;
}
