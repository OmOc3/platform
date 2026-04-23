interface MetricCardProps {
  label: string;
  value: string | number;
  hint?: string;
}

export function MetricCard({ label, value, hint }: MetricCardProps) {
  return (
    <article className="cc-metric-card">
      <p className="cc-metric-card__label">{label}</p>
      <p className="cc-metric-card__value">{value}</p>
      {hint ? <p className="cc-metric-card__hint">{hint}</p> : null}
    </article>
  );
}
