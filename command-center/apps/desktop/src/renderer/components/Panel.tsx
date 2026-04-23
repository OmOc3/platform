import type { PropsWithChildren, ReactNode } from 'react';

interface PanelProps extends PropsWithChildren {
  title?: string;
  eyebrow?: string;
  actions?: ReactNode;
  className?: string;
}

export function Panel({ title, eyebrow, actions, className = '', children }: PanelProps) {
  return (
    <section className={`cc-panel ${className}`.trim()}>
      {(title || eyebrow || actions) && (
        <header className="cc-panel__header">
          <div>
            {eyebrow ? <p className="cc-eyebrow">{eyebrow}</p> : null}
            {title ? <h2 className="cc-panel__title">{title}</h2> : null}
          </div>
          {actions ? <div className="cc-panel__actions">{actions}</div> : null}
        </header>
      )}
      {children}
    </section>
  );
}
