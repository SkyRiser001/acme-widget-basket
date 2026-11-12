import type { DeliveryTier } from "../types";

type Props = {
  tiers: DeliveryTier[];
};

export function DeliveryRules({ tiers }: Props) {
  let previous: string | null = null;

  return (
    <section className="rules">
      <h2>Delivery</h2>
      <ul>
        {tiers.map((tier) => {
          const label = tier.below
            ? `Under ${tier.below}`
            : previous
              ? `${previous} and over`
              : "Every order";
          previous = tier.below;
          return (
            <li key={label}>
              <span>{label}</span>
              <strong>{tier.chargeCents === 0 ? "Free" : tier.charge}</strong>
            </li>
          );
        })}
      </ul>
    </section>
  );
}
