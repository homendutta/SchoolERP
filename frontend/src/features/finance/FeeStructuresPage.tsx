/* Fee Structures — combine multiple Fee Masters; students receive structures. */
import { useEffect, useState } from 'react';
import { useAuth } from '@core/auth/AuthContext';
import { apiClient } from '@core/api/client';
import { AXBadge, AXModal, AXSelect, AXTable, type AXColumn } from '@ui/ax';
import { EntityManager, type Field, type FieldOption } from '@features/academic/EntityManager';
import { useClasses, useYears } from '@features/academic/useReference';
import { financeApi, type FeeMaster, type FeeStructure } from './api';

export function FeeStructuresPage() {
  const { user } = useAuth();
  const years = useYears();
  const classes = useClasses();
  const [itemsModal, setItemsModal] = useState<FeeStructure | null>(null);

  const fields: Field[] = [
    { name: 'name', label: 'Name', type: 'text', required: true },
    { name: 'code', label: 'Code', type: 'text' },
    {
      name: 'academic_year_id',
      label: 'Academic year',
      type: 'select',
      options: [{ value: '', label: '—' }, ...years],
    },
    {
      name: 'class_id',
      label: 'Class',
      type: 'select',
      options: [{ value: '', label: 'All' }, ...classes],
    },
  ];

  const columns: AXColumn<FeeStructure>[] = [
    { key: 'name', header: 'Name', render: (r) => <span className="font-medium">{r.name}</span> },
    { key: 'code', header: 'Code', render: (r) => r.code ?? '—' },
    {
      key: 'items',
      header: 'Fees',
      render: (r) => <AXBadge tone="navy">{r.items_count ?? 0}</AXBadge>,
    },
  ];

  return (
    <>
      <EntityManager<FeeStructure>
        title="Fee Structures"
        icon="layer-group"
        unitLabel="fee structures"
        api={financeApi.structures}
        columns={columns}
        fields={fields}
        emptyForm={{ name: '', code: '', academic_year_id: '', class_id: '' }}
        toForm={(r) => ({
          name: r.name,
          code: r.code ?? '',
          academic_year_id: r.academic_year_id ? String(r.academic_year_id) : '',
          class_id: r.class_id ? String(r.class_id) : '',
        })}
        createDefaults={{ school_id: user?.school_id }}
        searchKey="name"
        searchPlaceholder="Search structures…"
        sort="name"
        rowExtras={(row) => (
          <button
            onClick={() => setItemsModal(row)}
            className="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700"
          >
            Fees
          </button>
        )}
      />

      {itemsModal && (
        <ItemsModal
          structure={itemsModal}
          schoolId={user?.school_id}
          onClose={() => setItemsModal(null)}
        />
      )}
    </>
  );
}

function ItemsModal({
  structure,
  schoolId,
  onClose,
}: {
  structure: FeeStructure;
  schoolId: number | null | undefined;
  onClose: () => void;
}) {
  const [masters, setMasters] = useState<FieldOption[]>([]);
  const [allMasters, setAllMasters] = useState<FeeMaster[]>([]);
  const [items, setItems] = useState<Array<{ fee_master_id: number; name: string }>>([]);
  const [pick, setPick] = useState('');

  const load = () => {
    financeApi.masters.list({ filter: { school_id: schoolId }, per_page: 500 }).then((r) => {
      setAllMasters(r.data);
      setMasters(r.data.map((m) => ({ value: String(m.id), label: `${m.name} (₹${m.amount})` })));
    });
    apiClient
      .get<FeeStructure>(`/finance/structures/${structure.id}`)
      .then((s) =>
        setItems(
          (s.items ?? []).map((i) => ({
            fee_master_id: i.fee_master_id,
            name: i.fee_master ?? `#${i.fee_master_id}`,
          }))
        )
      );
  };

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const save = async (next: Array<{ fee_master_id: number; name: string }>) => {
    await financeApi.structures.update(structure.id, {
      school_id: schoolId,
      items: next.map((i) => ({ fee_master_id: i.fee_master_id })),
    });
    setItems(next);
  };

  const add = async () => {
    if (!pick) return;
    const master = allMasters.find((m) => String(m.id) === pick);
    if (!master || items.some((i) => i.fee_master_id === master.id)) return;
    await save([...items, { fee_master_id: master.id, name: master.name }]);
    setPick('');
  };

  const remove = (id: number) => save(items.filter((i) => i.fee_master_id !== id));

  const columns: AXColumn<{ fee_master_id: number; name: string }>[] = [
    { key: 'name', header: 'Fee Master', render: (r) => r.name },
    {
      key: 'act',
      header: '',
      render: (r) => (
        <button
          onClick={() => remove(r.fee_master_id)}
          className="text-xs font-semibold text-[var(--danger)]"
        >
          Remove
        </button>
      ),
    },
  ];

  return (
    <AXModal open title={`Fees — ${structure.name}`} onClose={onClose}>
      <div className="space-y-3">
        <div className="flex items-end gap-2">
          <div className="flex-1">
            <AXSelect
              label="Add fee master"
              value={pick}
              onChange={(e) => setPick(e.target.value)}
              options={[{ value: '', label: 'Select…' }, ...masters]}
            />
          </div>
          <button
            onClick={add}
            disabled={!pick}
            className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white disabled:opacity-60"
          >
            Add
          </button>
        </div>
        <AXTable
          columns={columns}
          rows={items}
          rowKey={(r) => r.fee_master_id}
          empty="No fees yet."
        />
      </div>
    </AXModal>
  );
}
