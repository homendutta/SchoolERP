/* EntityManager — a single reusable list+form CRUD surface for every Academic
 * entity, assembled ENTIRELY from the AX component library. Each Academic page
 * is a thin configuration of this manager (no duplicated table/modal/form UI). */
import { useEffect, useMemo, useState, type ReactNode } from 'react';
import {
  AXBadge,
  AXConfirm,
  AXForm,
  AXInput,
  AXModal,
  AXPagination,
  AXSearch,
  AXSelect,
  AXStatus,
  AXTable,
  useConfirm,
  type AXColumn,
  type AXPageMeta,
} from '@ui/ax';

export type FieldOption = { value: string; label: string };

export type Field =
  | { name: string; label: string; type: 'text' | 'number' | 'date'; required?: boolean }
  | { name: string; label: string; type: 'select'; options: FieldOption[]; required?: boolean }
  | { name: string; label: string; type: 'checkbox' }
  | { name: string; label: string; type: 'multiselect'; options: FieldOption[] };

export type FormState = Record<string, unknown>;

export interface FilterConfig {
  name: string;
  label: string;
  options: FieldOption[];
  value: string;
  onChange: (value: string) => void;
}

export interface EntityApi<T> {
  list: (params: Record<string, unknown>) => Promise<{ data: T[]; meta: AXPageMeta }>;
  create: (data: Record<string, unknown>) => Promise<unknown>;
  update: (id: number, data: Record<string, unknown>) => Promise<unknown>;
  archive: (id: number) => Promise<unknown>;
  restore: (id: number) => Promise<unknown>;
  bulkDelete: (ids: number[]) => Promise<unknown>;
}

interface EntityManagerProps<T extends { id: number; archived?: boolean }> {
  title: string;
  icon: string;
  unitLabel: string;
  api: EntityApi<T>;
  columns: AXColumn<T>[];
  fields: Field[];
  emptyForm: FormState;
  toForm: (row: T) => FormState;
  /** Extra values merged into every create payload (e.g. school_id). */
  createDefaults?: FormState;
  /** Server-side list params (filters), reloads when they change. */
  listParams?: Record<string, unknown>;
  searchKey?: string;
  searchPlaceholder?: string;
  sort?: string;
  filters?: FilterConfig[];
  /** Optional extra controls rendered into the row-action cell. */
  rowExtras?: (row: T, reload: () => void) => ReactNode;
  /** Disable the "Add" button + create flow (e.g. read-only screens). */
  canCreate?: boolean;
}

export function EntityManager<T extends { id: number; archived?: boolean }>(
  props: EntityManagerProps<T>
) {
  const {
    title,
    icon,
    unitLabel,
    api,
    columns,
    fields,
    emptyForm,
    toForm,
    createDefaults = {},
    listParams = {},
    searchKey,
    searchPlaceholder = 'Search…',
    sort,
    filters = [],
    rowExtras,
    canCreate = true,
  } = props;

  const [rows, setRows] = useState<T[]>([]);
  const [meta, setMeta] = useState<AXPageMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 15,
  });
  const [loading, setLoading] = useState(false);
  const [q, setQ] = useState('');
  const [page, setPage] = useState(1);
  const [selected, setSelected] = useState<Array<string | number>>([]);
  const [modal, setModal] = useState<{ open: boolean; editing: T | null }>({
    open: false,
    editing: null,
  });
  const [form, setForm] = useState<FormState>(emptyForm);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const { confirmProps, ask } = useConfirm();

  const paramKey = JSON.stringify(listParams);
  const load = useMemo(
    () => () => {
      setLoading(true);
      const params: Record<string, unknown> = { ...listParams, page };
      if (sort) params.sort = sort;
      if (searchKey && q) params.search = { [searchKey]: q };
      api
        .list(params)
        .then((r) => {
          setRows(r.data);
          setMeta(r.meta);
        })
        .finally(() => setLoading(false));
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [paramKey, q, page, sort, searchKey]
  );

  useEffect(() => {
    load();
    setSelected([]);
  }, [load]);

  const openCreate = () => {
    setForm({ ...emptyForm });
    setError(null);
    setModal({ open: true, editing: null });
  };
  const openEdit = (row: T) => {
    setForm(toForm(row));
    setError(null);
    setModal({ open: true, editing: row });
  };

  const save = async () => {
    setSaving(true);
    setError(null);
    try {
      if (modal.editing) await api.update(modal.editing.id, form);
      else await api.create({ ...createDefaults, ...form });
      setModal({ open: false, editing: null });
      load();
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Save failed.');
    } finally {
      setSaving(false);
    }
  };

  const setField = (name: string, value: unknown) => setForm((f) => ({ ...f, [name]: value }));

  const actionColumn: AXColumn<T> = {
    key: 'actions',
    header: '',
    className: 'text-right',
    render: (r) => (
      <div className="flex justify-end gap-2 text-gray-500">
        {rowExtras?.(r, load)}
        <button
          onClick={() => openEdit(r)}
          title="Edit"
          className="hover:text-[var(--navy-accent)]"
        >
          <i className="fas fa-pen" />
        </button>
        {r.archived ? (
          <button
            onClick={() => api.restore(r.id).then(load)}
            title="Restore"
            className="hover:text-[var(--success)]"
          >
            <i className="fas fa-rotate-left" />
          </button>
        ) : (
          <button
            onClick={() => ask('Archive this record?', () => api.archive(r.id).then(load))}
            title="Archive"
            className="hover:text-[var(--danger)]"
          >
            <i className="fas fa-box-archive" />
          </button>
        )}
      </div>
    ),
  };

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <i className={`fas fa-${icon} text-[var(--navy-primary)]`} />
          <h2 className="text-lg font-semibold text-[var(--navy-primary)]">{title}</h2>
          <AXBadge tone="navy">
            {meta.total} {unitLabel}
          </AXBadge>
        </div>
        {canCreate && (
          <button
            onClick={openCreate}
            className="rounded-md bg-[var(--navy-primary)] px-3 py-2 text-sm font-semibold text-white hover:bg-[var(--navy-hover)]"
          >
            <i className="fas fa-plus mr-1" /> Add
          </button>
        )}
      </div>

      {(searchKey || filters.length > 0) && (
        <div className="flex flex-wrap items-center gap-3">
          {filters.map((f) => (
            <div key={f.name} className="w-52">
              <AXSelect
                value={f.value}
                onChange={(e) => {
                  f.onChange(e.target.value);
                  setPage(1);
                }}
                options={[{ value: '', label: `${f.label}: All` }, ...f.options]}
              />
            </div>
          ))}
          {searchKey && (
            <div className="min-w-[16rem] flex-1">
              <AXSearch
                onSearch={(t) => {
                  setQ(t);
                  setPage(1);
                }}
                placeholder={searchPlaceholder}
              />
            </div>
          )}
          {selected.length > 0 && (
            <button
              onClick={() =>
                ask(`Delete ${selected.length} ${unitLabel}?`, () =>
                  api.bulkDelete(selected.map(Number)).then(load)
                )
              }
              className="rounded-md bg-[var(--danger)] px-3 py-2 text-sm font-semibold text-white"
            >
              <i className="fas fa-trash mr-1" /> Delete ({selected.length})
            </button>
          )}
        </div>
      )}

      <AXTable
        columns={[...columns, actionColumn]}
        rows={rows}
        rowKey={(r) => r.id}
        loading={loading}
        selectable
        selected={selected}
        onToggle={(id) =>
          setSelected((s) => (s.includes(id) ? s.filter((x) => x !== id) : [...s, id]))
        }
        onToggleAll={(checked) => setSelected(checked ? rows.map((r) => r.id) : [])}
      />
      <AXPagination meta={meta} onPage={setPage} />

      <AXModal
        open={modal.open}
        title={modal.editing ? `Edit ${title}` : `Add ${title}`}
        onClose={() => setModal({ open: false, editing: null })}
      >
        <AXForm
          onSubmit={save}
          submitting={saving}
          onCancel={() => setModal({ open: false, editing: null })}
        >
          {error && (
            <div className="rounded-md bg-red-50 px-3 py-2 text-sm text-[var(--danger)]">
              {error}
            </div>
          )}
          {fields.map((field) => {
            const value = form[field.name];
            if (field.type === 'checkbox') {
              return (
                <label key={field.name} className="flex items-center gap-2 text-sm">
                  <input
                    type="checkbox"
                    checked={Boolean(value)}
                    onChange={(e) => setField(field.name, e.target.checked)}
                  />{' '}
                  {field.label}
                </label>
              );
            }
            if (field.type === 'select') {
              return (
                <AXSelect
                  key={field.name}
                  label={field.label}
                  value={value === undefined || value === null ? '' : String(value)}
                  onChange={(e) =>
                    setField(field.name, e.target.value === '' ? null : e.target.value)
                  }
                  options={[{ value: '', label: '—' }, ...field.options]}
                />
              );
            }
            if (field.type === 'multiselect') {
              const selectedIds = Array.isArray(value)
                ? (value as Array<string | number>).map(String)
                : [];
              return (
                <div key={field.name}>
                  <span className="mb-1 block text-sm font-medium text-gray-700">
                    {field.label}
                  </span>
                  <div className="flex max-h-40 flex-wrap gap-2 overflow-y-auto rounded-md border border-gray-300 p-2">
                    {field.options.map((o) => {
                      const on = selectedIds.includes(o.value);
                      return (
                        <button
                          type="button"
                          key={o.value}
                          onClick={() =>
                            setField(
                              field.name,
                              on
                                ? selectedIds.filter((v) => v !== o.value).map(Number)
                                : [...selectedIds, o.value].map(Number)
                            )
                          }
                          className={`rounded-full px-3 py-1 text-xs ${on ? 'bg-[var(--navy-primary)] text-white' : 'bg-gray-100 text-gray-600'}`}
                        >
                          {o.label}
                        </button>
                      );
                    })}
                    {field.options.length === 0 && (
                      <span className="text-xs text-gray-400">No options.</span>
                    )}
                  </div>
                </div>
              );
            }
            return (
              <AXInput
                key={field.name}
                label={field.label}
                type={field.type}
                required={field.required}
                value={value === undefined || value === null ? '' : String(value)}
                onChange={(e) =>
                  setField(
                    field.name,
                    field.type === 'number' ? Number(e.target.value) : e.target.value
                  )
                }
              />
            );
          })}
        </AXForm>
      </AXModal>

      <AXConfirm {...confirmProps} />
    </div>
  );
}

/** Shared status cell used by most Academic tables. */
export function statusCell<T extends { status?: string; archived?: boolean }>(r: T) {
  return (
    <AXStatus
      active={r.status === 'active' && !r.archived}
      inactiveLabel={r.archived ? 'Archived' : 'Inactive'}
    />
  );
}
