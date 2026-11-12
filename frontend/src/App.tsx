import { useCallback, useEffect, useState } from "react";
import { ApiError, fetchCatalogue, fetchQuote } from "./api";
import { BasketPanel } from "./components/BasketPanel";
import { DeliveryRules } from "./components/DeliveryRules";
import { ProductCard } from "./components/ProductCard";
import type { Catalogue, Quote } from "./types";

const STORAGE_KEY = "acme-widget-basket";
const MAX_PER_PRODUCT = 20;

export function App() {
  const [catalogue, setCatalogue] = useState<Catalogue | null>(null);
  const [catalogueError, setCatalogueError] = useState<string | null>(null);
  const [attempt, setAttempt] = useState(0);
  const [codes, setCodes] = useState<string[]>(readStoredCodes);
  const [quote, setQuote] = useState<Quote | null>(null);
  const [quotedKey, setQuotedKey] = useState<string | null>(null);
  const [quoteError, setQuoteError] = useState<string | null>(null);
  const [pricing, setPricing] = useState(false);
  const basketKey = JSON.stringify(codes);

  useEffect(() => {
    const controller = new AbortController();
    let active = true;

    fetchCatalogue(controller.signal)
      .then((next) => {
        if (!active) {
          return;
        }
        setCatalogue(next);
        setCatalogueError(null);
      })
      .catch((error: unknown) => {
        if (!active || (error instanceof DOMException && error.name === "AbortError")) {
          return;
        }
        setCatalogueError(
          error instanceof ApiError
            ? error.message
            : "The catalogue is unavailable. Start the PHP server with composer serve, then try again.",
        );
      });

    return () => {
      active = false;
      controller.abort();
    };
  }, [attempt]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(codes));
    const controller = new AbortController();
    let active = true;
    setPricing(true);

    fetchQuote(codes, controller.signal)
      .then((next) => {
        if (!active) {
          return;
        }
        setQuote(next);
        setQuotedKey(JSON.stringify(codes));
        setQuoteError(null);
      })
      .catch((error: unknown) => {
        if (!active || (error instanceof DOMException && error.name === "AbortError")) {
          return;
        }
        setQuote(null);
        setQuotedKey(null);
        setQuoteError(error instanceof ApiError ? error.message : "Could not price the basket.");
      })
      .finally(() => {
        if (active) {
          setPricing(false);
        }
      });

    return () => {
      active = false;
      controller.abort();
    };
  }, [codes]);

  const add = useCallback((code: string) => {
    setCodes((current) => {
      const count = current.filter((item) => item === code).length;
      return count >= MAX_PER_PRODUCT ? current : [...current, code];
    });
  }, []);

  const removeOne = useCallback((code: string) => {
    setCodes((current) => {
      const index = current.lastIndexOf(code);
      if (index === -1) {
        return current;
      }
      return current.filter((_, itemIndex) => itemIndex !== index);
    });
  }, []);

  const clear = useCallback(() => setCodes([]), []);
  const counts = countCodes(codes);

  return (
    <div className="page">
      <header className="site">
        <div>
          <p className="eyebrow">Sales counter</p>
          <h1>Acme Widget Co.</h1>
        </div>
        <p className="lede">Made-up widgets, with delivery and offers priced in one place.</p>
      </header>

      {catalogueError ? (
        <div className="banner">
          <p>{catalogueError}</p>
          <button type="button" onClick={() => setAttempt((value) => value + 1)}>
            Try again
          </button>
        </div>
      ) : !catalogue ? (
        <p className="empty">Loading the catalogue…</p>
      ) : (
        <main className="layout">
          <section className="shop">
            {catalogue.offers.length > 0 ? (
              <aside className="offer">
                <p className="eyebrow">Current offer</p>
                <ul>
                  {catalogue.offers.map((offer) => (
                    <li key={offer}>{offer}</li>
                  ))}
                </ul>
              </aside>
            ) : null}
            <div className="grid">
              {catalogue.products.map((product) => (
                <ProductCard
                  key={product.code}
                  product={product}
                  quantity={counts.get(product.code) ?? 0}
                  atLimit={(counts.get(product.code) ?? 0) >= MAX_PER_PRODUCT}
                  onAdd={add}
                />
              ))}
            </div>
            <DeliveryRules tiers={catalogue.deliveryTiers} />
          </section>
          <BasketPanel
            codes={codes}
            quote={quotedKey === basketKey ? quote : null}
            error={quoteError}
            pricing={pricing}
            tiers={catalogue.deliveryTiers}
            onAdd={add}
            onRemove={removeOne}
            onClear={clear}
          />
        </main>
      )}
    </div>
  );
}

function countCodes(codes: string[]): Map<string, number> {
  const counts = new Map<string, number>();
  for (const code of codes) {
    counts.set(code, (counts.get(code) ?? 0) + 1);
  }
  return counts;
}

function readStoredCodes(): string[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      return [];
    }
    const parsed: unknown = JSON.parse(raw);
    if (!Array.isArray(parsed) || !parsed.every((code) => typeof code === "string")) {
      return [];
    }
    return parsed;
  } catch {
    return [];
  }
}
