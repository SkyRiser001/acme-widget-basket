import type { Catalogue, Quote } from "./types";

export class ApiError extends Error {}

export async function fetchCatalogue(signal?: AbortSignal): Promise<Catalogue> {
  const body = await request("/api/catalogue", { signal });
  if (!isCatalogue(body)) {
    throw new ApiError("The catalogue response was not recognised.");
  }
  return body;
}

export async function fetchQuote(items: string[], signal?: AbortSignal): Promise<Quote> {
  const body = await request("/api/basket", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ items }),
    signal,
  });
  if (!isQuote(body)) {
    throw new ApiError("The quote response was not recognised.");
  }
  return body;
}

async function request(path: string, init: RequestInit): Promise<unknown> {
  let response: Response;
  try {
    response = await fetch(path, init);
  } catch (error) {
    if (error instanceof DOMException && error.name === "AbortError") {
      throw error;
    }
    throw new ApiError("The basket service is not running. Start it with composer serve.");
  }

  const body: unknown = await response.json().catch(() => null);
  if (!response.ok) {
    throw new ApiError(errorMessage(body) ?? "The basket service could not complete that request.");
  }
  return body;
}

function errorMessage(body: unknown): string | null {
  if (typeof body !== "object" || body === null || !("error" in body)) {
    return null;
  }
  return typeof body.error === "string" ? body.error : null;
}

function isCatalogue(value: unknown): value is Catalogue {
  return (
    typeof value === "object" &&
    value !== null &&
    "products" in value &&
    Array.isArray(value.products) &&
    "offers" in value &&
    Array.isArray(value.offers) &&
    "deliveryTiers" in value &&
    Array.isArray(value.deliveryTiers)
  );
}

function isQuote(value: unknown): value is Quote {
  return (
    typeof value === "object" &&
    value !== null &&
    "items" in value &&
    Array.isArray(value.items) &&
    "discounts" in value &&
    Array.isArray(value.discounts) &&
    "subtotal" in value &&
    typeof value.subtotal === "string" &&
    "delivery" in value &&
    typeof value.delivery === "string" &&
    "total" in value &&
    typeof value.total === "string" &&
    "goodsTotalCents" in value &&
    typeof value.goodsTotalCents === "number"
  );
}
