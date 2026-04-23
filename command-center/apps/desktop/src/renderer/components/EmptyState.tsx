import type { ReactNode } from 'react';

interface EmptyStateProps {
  title: string;
  description: string;
  action?: ReactNode;
}

export function EmptyState({ title, description, action }: EmptyStateProps) {
  return (
    <div className="cc-empty-state">
      <h3 className="cc-empty-state__title">{title}</h3>
      <p className="cc-empty-state__description">{description}</p>
      {action ? <div className="cc-empty-state__action">{action}</div> : null}
    </div>
  );
}
