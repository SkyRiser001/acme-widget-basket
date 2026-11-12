export type Product = {
  code: string;
  name: string;
  price: string;
  priceCents: number;
};

export type DeliveryTier = {
  below: string | null;
  belowCents: number | null;
  charge: string;
  chargeCents: number;
};

export type Catalogue = {
  products: Product[];
  offers: string[];
  deliveryTiers: DeliveryTier[];
};

export type QuoteLine = {
  code: string;
  name: string;
  quantity: number;
  unitPrice: string;
  lineTotal: string;
};

export type DiscountLine = {
  description: string;
  amount: string;
};

export type Quote = {
  items: QuoteLine[];
  subtotal: string;
  discounts: DiscountLine[];
  discountTotal: string;
  goodsTotalCents: number;
  delivery: string;
  total: string;
};
