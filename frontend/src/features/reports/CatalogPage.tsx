/* Report Catalog — browse the reusable report definitions registered per module. */
import { useEffect, useState } from 'react';
import { AXBadge } from '@ui/ax';
import { reportsApi, type CatalogItem } from './api';

export function CatalogPage() {
  const [catalog, setCatalog] = useState<CatalogItem[]>([]);

  useEffect(() => {
    reportsApi
      .catalog()
      .then(setCatalog)
      .catch(() => undefined);
  }, []);

  const byCategory = catalog.reduce<Record<string, CatalogItem[]>>((acc, item) => {
    (acc[item.category] ??= []).push(item);
    return acc;
  }, {});

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <i className="fas fa-book text-[var(--navy-primary)]" />
        <h2 className="text-lg font-semibold text-[var(--navy-primary)]">Report Catalog</h2>
      </div>

      {Object.entries(byCategory).map(([category, items]) => (
        <div key={category} className="erp-card">
          <h3 className="mb-3 text-sm font-semibold text-[var(--navy-primary)]">{category}</h3>
          <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
            {items.map((r) => (
              <div key={r.key} className="rounded-lg border border-gray-200 p-3">
                <div className="flex items-center justify-between">
                  <span className="font-medium">{r.name}</span>
                  <AXBadge tone="gray">{r.module}</AXBadge>
                </div>
                <div className="mt-1 text-xs text-gray-500">
                  {Object.values(r.columns).join(' · ')}
                </div>
                <code className="mt-1 block text-[11px] text-gray-400">{r.key}</code>
              </div>
            ))}
          </div>
        </div>
      ))}
      {catalog.length === 0 && <p className="text-sm text-gray-400">No reports registered.</p>}
    </div>
  );
}
