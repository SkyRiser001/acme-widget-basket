import { deliveryHint } from "../deliveryHint";
import type { DeliveryTier, Quote } from "../types";

const MAX_PER_PRODUCT = 20;

type Props = {
  codes: string[];
  quote: Quote | null;
  error: string | null;
  pricing: boolean;
  tiers: DeliveryTier[];
  onAdd: (code: string) => void;
  onRemove: (code: string) => void;
  onClear: () => void;
};

export function BasketPanel({
  codes,
  quote,
  error,
  pricing,
  tiers,
  onAdd,
  onRemove,
  onClear,
}: Props) {
  const hint = quote && codes.length > 0 ? deliveryHint(quote.goodsTotalCents, tiers) : null;

  return (
    <aside className="receipt">
      <div className="receipt-head">
        <h2>Basket</h2>
        {codes.length > 0 ? (
          <button type="button" className="ghost" onClick={onClear}>
            Clear
          </button>
        ) : null}
      </div>

      {error ? <p className="banner">{error}</p> : null}

      {codes.length === 0 ? (
        <p className="empty">Add a widget. Delivery and the red-widget offer are applied when the basket is priced.</p>
      ) : quote ? (
        <>
          <ul className="lines">
            {quote.items.map((line) => {
              const count = codes.filter((code) => code === line.code).length;
              return (
                <li key={line.code}>
                  <div>
                    <p className="line-name">{line.name}</p>
                    <p className="code">
                      {line.code} · {line.unitPrice}
                    </p>
                  </div>
                  <div className="stepper">
                    <button type="button" onClick={() => onRemove(line.code)} aria-label={`Remove one ${line.name}`}>
                      −
                    </button>
                    <span>{line.quantity}</span>
                    <button
                      type="button"
                      onClick={() => onAdd(line.code)}
                      disabled={count >= MAX_PER_PRODUCT}
                      aria-label={`Add another ${line.name}`}
                    >
                      +
                    </button>
                  </div>
                  <p className="amount">{line.lineTotal}</p>
                </li>
              );
            })}
          </ul>
          <hr />
          <dl>
            <div>
              <dt>Subtotal</dt>
              <dd>{quote.subtotal}</dd>
            </div>
            {quote.discounts.map((discount) => (
              <div key={discount.description} className="discount">
                <dt>{discount.description}</dt>
                <dd>-{discount.amount}</dd>
              </div>
            ))}
            <div>
              <dt>Delivery</dt>
              <dd>{quote.delivery === "$0.00" ? "Free" : quote.delivery}</dd>
            </div>
          </dl>
          {hint ? <p className="hint">{hint}</p> : null}
        </>
      ) : error ? null : (
        <p className="empty">Pricing the basket…</p>
      )}

      <p className="grand">
        <span>Total</span>
        <strong aria-live="polite">{codes.length === 0 ? "$0.00" : (quote?.total ?? "…")}</strong>
      </p>
      <p className="status">{pricing ? "Updating total…" : "Priced by the PHP basket."}</p>
    </aside>
  );
}
