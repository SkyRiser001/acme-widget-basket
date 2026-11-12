import type { CSSProperties } from "react";
import type { Product } from "../types";

const WIDGETS: Record<string, string> = {
  R01: "#d4533e",
  G01: "#2d8a64",
  B01: "#2d6cb5",
};

type Props = {
  product: Product;
  quantity: number;
  atLimit: boolean;
  onAdd: (code: string) => void;
};

export function ProductCard({ product, quantity, atLimit, onAdd }: Props) {
  return (
    <article
      className="product"
      style={{ "--widget": WIDGETS[product.code] ?? "#8a8178" } as CSSProperties}
    >
      <div className="disc" aria-hidden="true">
        {quantity > 0 ? <span className="count">{quantity}</span> : null}
      </div>
      <div>
        <h3>{product.name}</h3>
        <p className="code">{product.code}</p>
      </div>
      <p className="price">{product.price}</p>
      <button type="button" onClick={() => onAdd(product.code)} disabled={atLimit}>
        {atLimit ? "Limit of 20" : "Add to basket"}
      </button>
    </article>
  );
}
