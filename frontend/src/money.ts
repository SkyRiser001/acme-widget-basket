export function formatCents(cents: number): string {
  const absolute = Math.abs(cents);
  const whole = Math.floor(absolute / 100);
  const fraction = (absolute % 100).toString().padStart(2, "0");
  return `${cents < 0 ? "-" : ""}$${whole}.${fraction}`;
}
